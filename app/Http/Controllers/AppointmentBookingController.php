<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Appointment;
use Carbon\Carbon;

class AppointmentBookingController extends Controller
{
    // Step 1: Show Patient Info
    public function createStepOne(Request $request)
    {
        // Clear session data from previous bookings
        $request->session()->forget('booking');
        $patient = auth()->user();
        $request->session()->put('booking.patient', $patient);
        return view('patient.book.step-one', compact('patient'));
    }

    public function createStepTwo(Request $request)
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');

        $doctors = User::where('role', 'doctor')->with('doctorProfile')->get();
        $dbHospitals = \App\Models\Hospital::all()->keyBy('name');

        // Add distance to doctors
        $doctors->transform(function ($doctor) use ($lat, $lng, $dbHospitals) {
            $hospitalName = $doctor->doctorProfile->hospital_name ?? 'General Hospital';
            $hospitalObj = $dbHospitals->get($hospitalName);
            $distance = null;

            if ($hospitalObj) {
                $doctor->hospital_lat = $hospitalObj->latitude;
                $doctor->hospital_lng = $hospitalObj->longitude;
            }

            if ($lat && $lng && $hospitalObj && $hospitalObj->latitude && $hospitalObj->longitude) {
                $theta = $lng - $hospitalObj->longitude;
                $dist = sin(deg2rad($lat)) * sin(deg2rad($hospitalObj->latitude)) +  cos(deg2rad($lat)) * cos(deg2rad($hospitalObj->latitude)) * cos(deg2rad($theta));
                $dist = acos($dist);
                $dist = rad2deg($dist);
                $distance = round($dist * 60 * 1.1515 * 1.609344, 2);
            }
            
            $doctor->hospital_distance = $distance;
            return $doctor;
        });

        if ($lat && $lng) {
            $doctors = $doctors->sortBy('hospital_distance')->values();
        }

        $hospitals = $doctors->map(function ($doctor) {
            return $doctor->doctorProfile->hospital_name ?? 'General Hospital';
        })->unique()->filter()->values();

        $booking = $request->session()->get('booking');
        return view('patient.book.step-two', compact('doctors', 'hospitals', 'booking', 'lat', 'lng'));
    }

    // POST Step 2: Store selected doctor
    public function storeStepTwo(Request $request)
    {
        $validated = $request->validate(['doctor_id' => 'required|exists:users,id']);
        $doctor = User::find($validated['doctor_id']);
        $request->session()->put('booking.doctor', $doctor);
        return redirect()->route('patient.book.create.step.three');
    }

    // Step 3: Show Date & Time Selection
    // In app/Http/Controllers/AppointmentBookingController.php

    public function createStepThree(Request $request)
    {
        $booking = $request->session()->get('booking');
        $today = Carbon::today();

        // For simplicity, we'll generate a static list of available time slots.
        // In a real app, you would query the doctor's schedule for this date.
        $availableTimeSlots = [
            '09:00',
            '10:30',
            '14:00',
            '15:30',
            '16:30'
        ];

        return view('patient.book.step-three', [
            'booking' => $booking,
            'today' => $today,
            'availableTimeSlots' => $availableTimeSlots,
        ]);
    }
    // POST Step 3: Store selected date & token
    public function storeStepThree(Request $request)
    {
        $validated = $request->validate([
            'appointment_date' => 'required|date',
            'token_number' => 'required|integer|min:1|max:100'
        ]);
        $request->session()->put('booking.appointment_date', $validated['appointment_date']);
        $request->session()->put('booking.token_number', $validated['token_number']);
        return redirect()->route('patient.book.create.step.four');
    }

    // Step 4: Show Confirmation Page
    public function createStepFour(Request $request)
    {
        $booking = $request->session()->get('booking');
        
        if (!$booking || !isset($booking['doctor'])) {
            return redirect()->route('patient.book.create.step.one')->with('error', 'Please select a doctor to continue.');
        }

        return view('patient.book.step-four', compact('booking'));
    }

    // Final POST: Store the appointment
    public function store(Request $request)
    {
        $booking = $request->session()->get('booking');

        if (!$booking || !isset($booking['appointment_date']) || !isset($booking['doctor'])) {
            return redirect()->route('patient.book.create.step.one')->with('error', 'Booking session expired. Please start again.');
        }

        $appointmentDateStr = \Carbon\Carbon::parse($booking['appointment_date'])->toDateString();

        $isAlreadyBooked = Appointment::where('doctor_id', $booking['doctor']->id)
            ->whereDate('appointment_date', $appointmentDateStr)
            ->where('token_number', $booking['token_number'])
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($isAlreadyBooked) {
            return redirect()->route('patient.book.create.step.three')
                ->withErrors(['appointment_time' => 'Sorry, this token was just booked. Please select a different token.']);
        }

        $doctorProfile = \App\Models\Doctor::where('user_id', $booking['doctor']->id)->first();
        $specialtyFallback = $booking['doctor']->specialty ?? ($doctorProfile ? $doctorProfile->specialization : 'Specialist');
        $reason = $request->input('reason');
        if (empty($reason)) {
            $reason = 'Consultation';
        }

        $group = floor(($booking['token_number'] - 1) / 10);
        $appointmentTime = \Carbon\Carbon::parse($appointmentDateStr)->setTime(9 + floor($group / 2), ($group % 2) * 30, 0);

        $appointment = Appointment::create([
            'patient_id' => $booking['patient']->id,
            'doctor_id' => $booking['doctor']->id,
            'doctor_name' => $booking['doctor']->name,
            'doctor_specialty' => $specialtyFallback,
            'appointment_date' => $appointmentTime,
            'status' => 'scheduled',
            'reason' => $reason,
            'is_paid' => false,
            'token_number' => $booking['token_number'],
        ]);

        $request->session()->forget('booking');

        return view('patient.book.payment', compact('appointment'));
    }

    public function pay(Request $request, Appointment $appointment)
    {
        if (!$appointment || $appointment->patient_id !== auth()->id()) {
            abort(403);
        }

        $amount = 200 * 100; // Hardcoded booking fee of ₹200 for now. In paise.

        // Create Razorpay Order
        $keyId = env('RAZORPAY_KEY_ID');
        $keySecret = env('RAZORPAY_KEY_SECRET');

        $response = \Illuminate\Support\Facades\Http::withoutVerifying()
            ->withBasicAuth($keyId, $keySecret)
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => $amount,
                'currency' => 'INR',
                'receipt' => 'book_' . $appointment->id,
            ]);

        if ($response->successful()) {
            return response()->json([
                'order_id' => $response->json()['id'],
                'amount' => $amount,
                'key' => $keyId,
                'name' => 'MedFlow Booking',
                'description' => 'Appointment for Dr. ' . $appointment->doctor_name,
                'user_name' => auth()->user()->name,
                'user_email' => auth()->user()->email,
                'user_phone' => auth()->user()->phone ?? '',
                'is_demo' => false
            ]);
        }

        // AUTO-SIMULATION: Return a mock order if API fails or keys are missing
        return response()->json([
            'order_id' => 'order_demo_' . time(),
            'amount' => $amount,
            'key' => 'rzp_test_demo',
            'name' => 'MedFlow Simulation',
            'description' => 'Demo Mode: Payment Simulator',
            'user_name' => auth()->user()->name,
            'user_email' => auth()->user()->email,
            'user_phone' => auth()->user()->phone ?? '',
            'is_demo' => true
        ]);
    }

    public function verifyBookingPayment(Request $request, Appointment $appointment)
    {
        $input = $request->all();
        $keySecret = env('RAZORPAY_KEY_SECRET');

        // Verify Razorpay Signature
        $attributes = [
            'razorpay_order_id' => $input['razorpay_order_id'],
            'razorpay_payment_id' => $input['razorpay_payment_id'],
            'razorpay_signature' => $input['razorpay_signature']
        ];

        $expectedSignature = hash_hmac('sha256', $attributes['razorpay_order_id'] . '|' . $attributes['razorpay_payment_id'], $keySecret);

        if ($expectedSignature === $attributes['razorpay_signature']) {
            $appointment->update([
                'is_paid' => true,
                'status' => 'confirmed' // Mark as confirmed after payment
            ]);

            $request->session()->put('last_booked_appointment_id', $appointment->id);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'error' => 'Payment verification failed.'], 400);
    }

    public function confirmation(Request $request)
    {
        $appointmentId = session('last_booked_appointment_id');
        if (!$appointmentId) {
            return redirect()->route('patient.dashboard');
        }
        $appointment = Appointment::find($appointmentId);
        return view('patient.book.confirmation', compact('appointment'));
    }

    // In AppointmentBookingController.php

    public function getAvailableSlots(Request $request, User $doctor)
    {
        $request->validate(['date' => 'required|date_format:Y-m-d']);
        $date = \Carbon\Carbon::parse($request->date);

        $now = \Carbon\Carbon::now();

        // Get booked tokens
        $bookedTokens = \App\Models\Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('token_number')->toArray();

        $allTokens = [];
        for ($i = 1; $i <= 100; $i++) {
            $group = floor(($i - 1) / 10);
            $startTime = $date->copy()->setTime(9 + floor($group / 2), ($group % 2) * 30, 0);
            $endTime = $startTime->copy()->addMinutes(30);

            $isPast = false;
            if ($date->isToday()) {
                $isPast = $startTime < $now;
            } else if ($date->isPast() && !$date->isToday()) {
                $isPast = true;
            }

            // Every 10th token is reserved for Walk-in / Admin
            $isAdminReserved = ($i % 10 === 0);

            $allTokens[] = [
                'token' => $i,
                'time_label' => $startTime->format('g:i A') . ' - ' . $endTime->format('g:i A'),
                'is_booked' => in_array($i, $bookedTokens) || $isAdminReserved,
                'is_past' => $isPast,
                'is_admin_reserved' => $isAdminReserved
            ];
        }

        return response()->json($allTokens);
    }
    
    public function getLiveStatus(Request $request, User $doctor)
    {
        $doctorProfile = \App\Models\Doctor::where('user_id', $doctor->id)->first();
        return response()->json([
            'current_token' => $doctorProfile ? $doctorProfile->current_token : 0,
            'live_status' => $doctorProfile ? $doctorProfile->live_status : 'Available'
        ]);
    }
}