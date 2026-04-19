<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use Carbon\Carbon;

class DoctorAppointmentController extends Controller
{
    // List all appointments for the doctor with filtering
    public function index(Request $request)
    {
        $query = Appointment::with('patient')
            ->where('doctor_id', Auth::id())
            ->orderBy('appointment_date', 'desc');

        // In the index() method, add this to the query:
        $query->whereIn('status', ['scheduled', 'confirmed']);
        // Handle search filter
        if ($request->filled('search')) {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        // Handle status filter
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // Handle date range filter
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('appointment_date', Carbon::today());
                    break;
                case 'upcoming':
                    $query->where('appointment_date', '>', Carbon::now());
                    break;
                case 'past':
                    $query->where('appointment_date', '<', Carbon::now());
                    break;
            }
        }

        $appointments = $query->get();

        return view('staff.doctor.my-appointments', compact('appointments'));
    }

    // Update the status of an appointment
    public function updateStatus(Request $request, Appointment $appointment)
    {
        // Authorization check
        if ($appointment->doctor_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => 'required|in:confirmed,completed,cancelled',
        ]);

        $appointment->update(['status' => $validated['status']]);

        return back()->with('status', 'Appointment status updated successfully!');
    }

    // Add this new method to the class
    public function history(Request $request)
    {
        $query = Appointment::with('patient')
            ->where('doctor_id', Auth::id())
            ->whereIn('status', ['completed', 'cancelled'])
            ->orderBy('appointment_date', 'desc');

        $appointments = $query->paginate(10);

        return view('staff.doctor.appointment-history', compact('appointments'));
    }

    public function createPrescription(Appointment $appointment)
    {
        if ($appointment->doctor_id !== Auth::id()) {
            abort(403);
        }
        return view('staff.doctor.prescriptions.create', compact('appointment'));
    }

    public function storePrescription(Request $request, Appointment $appointment)
    {
        if ($appointment->doctor_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'medicines' => 'required|array',
            'notes' => 'nullable|string'
        ]);

        $doctorProfile = \App\Models\Doctor::where('user_id', $appointment->doctor_id)->first();

        if (!$doctorProfile) {
            return back()->with('error', 'Critical Error: Your official Doctor Profile was not found in the database. Please contact Admin.');
        }

        \App\Models\Prescription::create([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $doctorProfile->id,
            'appointment_id' => $appointment->id,
            'medicines' => json_encode($request->medicines),
            'notes' => $request->notes,
            'status' => 'pending'
        ]);

        $appointment->update(['status' => 'completed']);

        return redirect()->route('doctor.appointments.index')->with('status', 'Prescription created successfully.');
    }
}