<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\TokenAlertMail;

class DoctorDashboardController extends Controller
{
  // In app/Http/Controllers/DoctorDashboardController.php

public function index()
{
    $doctor = Auth::user();
    $appointments = Appointment::with('patient')->where('doctor_id', $doctor->id)->get();

    // The stats calculation remains the same
    $stats = [
        'total' => $appointments->count(),
        'upcoming' => $appointments->whereIn('status', ['scheduled', 'confirmed'])->count(),
        'completed' => $appointments->where('status', 'completed')->count(),
        'patients_today' => $appointments->where('appointment_date', '>=', Carbon::today()->startOfDay())
                                        ->where('appointment_date', '<=', Carbon::today()->endOfDay())
                                        ->where('status', '!=', 'cancelled')
                                        ->count(),
    ];

    // --- UPDATED LOGIC FOR TODAY'S SCHEDULE ---
    // Now, it only gets appointments for today that are still active.
    $todaysSchedule = $appointments->where('appointment_date', '>=', Carbon::today()->startOfDay())
                                ->where('appointment_date', '<=', Carbon::today()->endOfDay())
                                ->whereIn('status', ['scheduled', 'confirmed']) // This is the new filter
                                ->sortBy('appointment_date');

    return view('staff.doctor.dashboard', compact('doctor', 'stats', 'todaysSchedule'));
}

// In DoctorDashboardController.php

    public function getAppointmentsForDate(Request $request)
    {
        $request->validate(['date' => 'required|date_format:Y-m-d']);
        $date = Carbon::parse($request->date);

        $appointments = Appointment::with('patient')
            ->where('doctor_id', Auth::id())
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->orderBy('appointment_date')
            ->get();

        return response()->json($appointments);
    }
    
    public function updateLiveStatus(Request $request) {
        $doctorProfile = \App\Models\Doctor::where('user_id', Auth::id())->first();
        if($doctorProfile) {
            $doctorProfile->live_status = $request->input('status', 'Available');
            $doctorProfile->save();
        }
        return response()->json(['success' => true]);
    }

    public function incrementLiveToken(Request $request) {
        $doctorProfile = \App\Models\Doctor::where('user_id', Auth::id())->first();
        if (!$doctorProfile) {
            return response()->json(['success' => false]);
        }

        $doctorProfile->current_token += 1;
        $doctorProfile->save();

        $newToken = $doctorProfile->current_token;
        $doctorUser = Auth::user();

        // NOTIFICATION: Alert patients who are exactly 10 tokens away
        $ALERT_THRESHOLD = 10;

        $upcomingAppointments = Appointment::with('patient')
            ->where('doctor_id', Auth::id())
            ->whereDate('appointment_date', Carbon::today())
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->whereNotNull('token_number')
            ->get();

        foreach ($upcomingAppointments as $appointment) {
            $patientToken = (int) $appointment->token_number;
            $tokensLeft = $patientToken - $newToken;

            if ($tokensLeft === $ALERT_THRESHOLD) {
                $patient = $appointment->patient;
                if (!$patient) continue;

                Log::info("Sending token alert to patient {$patient->name} (Token #{$patientToken}, Current #{$newToken})");

                // Send Web Push Notification
                \App\Http\Controllers\PushNotificationController::sendToUser(
                    $patient->id,
                    "⏰ Your Turn is Approaching!",
                    "Dr. {$doctorUser->name} is now on Token #{$newToken}. Your Token is #{$patientToken} — only {$tokensLeft} patients before you! Head to the clinic now.",
                    '/patient/dashboard'
                );

                // Send Email Notification
                if ($patient->email) {
                    try {
                        Mail::to($patient->email)->send(new TokenAlertMail($patientToken, $doctorUser->name, $tokensLeft, $newToken));
                        Log::info("Token alert email sent to {$patient->email}");
                    } catch (\Exception $e) {
                        Log::error("Failed to send token alert email to {$patient->email}: " . $e->getMessage());
                    }
                }
            }
        }

        return response()->json(['success' => true, 'current_token' => $newToken]);
    }
}