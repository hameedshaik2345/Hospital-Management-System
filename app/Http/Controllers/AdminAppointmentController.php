<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;

class AdminAppointmentController extends Controller
{
    public function index(Request $request)
    {
        $adminHospital = auth()->user()->hospital_name;
        
        $query = Appointment::with(['patient', 'doctor'])->whereIn('status', ['scheduled', 'confirmed'])->orderBy('appointment_date', 'desc');

        if ($adminHospital) {
            $query->whereHas('doctor.doctorProfile', function ($q) use ($adminHospital) {
                $q->where('hospital_name', $adminHospital);
            });
        }


        if ($request->filled('patient_name')) {
            $query->whereHas('patient', fn($q) => $q->where('name', 'like', '%' . $request->patient_name . '%'));
        }
        if ($request->filled('doctor_name')) {
            $query->whereHas('doctor', fn($q) => $q->where('name', 'like', '%' . $request->doctor_name . '%'));
        }
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today': $query->whereDate('appointment_date', Carbon::today()); break;
                case 'upcoming': $query->where('appointment_date', '>', Carbon::now()); break;
                case 'past': $query->where('appointment_date', '<', Carbon::now()); break;
            }
        }

        $appointments = $query->paginate(10);
        $doctorsQuery = User::where('role', 'doctor')->orderBy('name');
        if (isset($adminHospital) && $adminHospital) {
            $doctorsQuery->whereHas('doctorProfile', function ($q) use ($adminHospital) {
                $q->where('hospital_name', $adminHospital);
            });
        }
        $doctors = $doctorsQuery->get();

        return view('admin.appointments.index', compact('appointments', 'doctors'));
    }

    public function show(Appointment $appointment)
    {
        return response()->json($appointment->load(['patient', 'doctor']));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'appointment_date' => 'required|date_format:Y-m-d',
            'appointment_time' => 'required|date_format:H:i',
            'doctor_id' => 'required|exists:users,id',
            'status' => 'required|in:scheduled,confirmed,completed,cancelled',
        ]);

        $doctor = User::find($validated['doctor_id']);
        $fullDateTime = Carbon::parse($validated['appointment_date'] . ' ' . $validated['appointment_time']);

        // Final check to prevent double booking
        $isAlreadyBooked = Appointment::where('doctor_id', $doctor->id)
            ->where('appointment_date', $fullDateTime)
            ->where('id', '!=', $appointment->id) // Exclude the current appointment
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($isAlreadyBooked) {
            return back()->withErrors(['error' => 'This time slot is already booked for the selected doctor.']);
        }

        $appointment->update([
            'appointment_date' => $fullDateTime,
            'doctor_id' => $doctor->id,
            'doctor_name' => $doctor->name,
            'doctor_specialty' => $doctor->specialty,
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.appointments.index')->with('success', 'Appointment updated successfully.');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->update(['status' => 'cancelled']);
        return redirect()->route('admin.appointments.index')->with('success', 'Appointment cancelled successfully.');
    }
    
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'doctor_id' => 'required|exists:users,id',
            'appointment_id' => 'nullable|exists:appointments,id',
        ]);
        $date = Carbon::parse($request->date);
        $doctorId = $request->doctor_id;
        $appointmentId = $request->appointment_id;

        $startTime = $date->copy()->setHour(9);
        $endTime = $date->copy()->setHour(17);
        $allSlots = [];
        while ($startTime < $endTime) {
            $allSlots[] = $startTime->format('H:i');
            $startTime->addMinutes(30);
        }

        $bookedSlotsQuery = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->where('status', '!=', 'cancelled');

        if ($appointmentId) {
            $bookedSlotsQuery->where('id', '!=', $appointmentId);
        }

        $bookedSlots = $bookedSlotsQuery->get()->pluck('appointment_date')->map(fn($dt) => Carbon::parse($dt)->format('H:i'))->toArray();
        $availableSlots = array_diff($allSlots, $bookedSlots);

        return response()->json(array_values($availableSlots));
    }

    // Admin Walk-in Token Fetching
    public function getAdminTokens(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'doctor_id' => 'required|exists:users,id',
        ]);
        $date = \Carbon\Carbon::parse($request->date);
        $doctorId = $request->doctor_id;

        $bookedTokens = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('token_number')->toArray();

        $allTokens = [];
        for ($i = 1; $i <= 100; $i++) {
            $timing = Appointment::calculateTokenTiming($i, $date);
            $startTime = $timing['start'];
            $endTime = $timing['end'];

            $allTokens[] = [
                'token' => $i,
                'time_label' => $startTime->format('g:i A') . ' - ' . $endTime->format('g:i A'),
                'is_booked' => in_array($i, $bookedTokens),
                'is_past' => false, // Admins can book any available token regardless of time
                'is_admin_reserved' => ($i % 10 === 0)
            ];
        }
        return response()->json($allTokens);
    }

    // Admin Walk-in Booking
    public function storeWalkin(Request $request)
    {
        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'patient_phone' => 'required|string|max:15',
            'doctor_id' => 'required|exists:users,id',
            'appointment_date' => 'required|date_format:Y-m-d',
            'token_number' => 'required|integer|min:1|max:100',
        ]);

        $doctor = User::find($validated['doctor_id']);
        $dateStr = $validated['appointment_date'];

        $isAlreadyBooked = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $dateStr)
            ->where('token_number', $validated['token_number'])
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($isAlreadyBooked) {
            return back()->withErrors(['error' => 'That token is already booked!']);
        }

        // Find or create Walk-in Patient
        $patient = User::firstOrCreate(
            ['phone_number' => $validated['patient_phone']],
            [
                'name' => $validated['patient_name'],
                'role' => 'patient',
                'password' => \Illuminate\Support\Facades\Hash::make('Walkin@123'),
            ]
        );

        $timing = Appointment::calculateTokenTiming($validated['token_number'], $dateStr);
        $appointmentTime = $timing['start'];

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'doctor_name' => $doctor->name,
            'doctor_specialty' => $doctor->specialty ?? 'Specialist',
            'appointment_date' => $appointmentTime,
            'status' => 'scheduled',
            'reason' => 'Walk-in Consultation',
            'is_paid' => false,
            'token_number' => $validated['token_number'],
        ]);

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Walk-in appointment booked successfully! Token: ' . $validated['token_number'])
            ->with('last_booked_id', $appointment->id);
    }

public function edit(Appointment $appointment)
{
    $adminHospital = auth()->user()->hospital_name;
    $doctorsQuery = User::where('role', 'doctor')->orderBy('name');
    if ($adminHospital) {
        $doctorsQuery->whereHas('doctorProfile', function ($q) use ($adminHospital) {
            $q->where('hospital_name', $adminHospital);
        });
    }
    $doctors = $doctorsQuery->get();
    return view('admin.appointments.edit', compact('appointment', 'doctors'));
}
public function history(Request $request)
{
    $adminHospital = auth()->user()->hospital_name;
    $query = Appointment::with(['patient', 'doctor'])
                ->whereIn('status', ['completed', 'cancelled']) // <-- Fetches past appointments
                ->orderBy('appointment_date', 'desc');

    if ($adminHospital) {
        $query->whereHas('doctor.doctorProfile', function ($q) use ($adminHospital) {
            $q->where('hospital_name', $adminHospital);
        });
    }

    // ... (You can add the same filtering logic here if needed) ...

    $appointments = $query->paginate(10);

    return view('admin.appointments.history', compact('appointments'));
}

}