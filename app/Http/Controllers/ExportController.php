<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    // Generate PDF for the logged-in doctor's daily schedule
    public function exportDoctorSchedule()
    {
        $doctor = Auth::user();
        $today = Carbon::today();

        $appointments = Appointment::with('patient')
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $today)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->orderBy('appointment_date')
            ->get();

        $data = [
            'title' => 'Daily Schedule for ' . $doctor->name,
            'date' => $today->format('F j, Y'),
            'appointments' => $appointments
        ];

        $pdf = Pdf::loadView('pdf.schedule', $data);
        return $pdf->download('daily_schedule_' . $today->format('Y-m-d') . '.pdf');
    }

    // Generate PDF for the system-wide daily schedule (for Admin)
    public function exportAdminSchedule()
    {
        $today = Carbon::today();

        $appointments = Appointment::with(['patient', 'doctor'])
            ->whereDate('appointment_date', $today)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->orderBy('doctor_id')
            ->orderBy('appointment_date')
            ->get();

        $data = [
            'title' => 'System-Wide Daily Schedule',
            'date' => $today->format('F j, Y'),
            'appointments' => $appointments
        ];

        $pdf = Pdf::loadView('pdf.schedule', $data);
        return $pdf->download('system_daily_schedule_' . $today->format('Y-m-d') . '.pdf');
    }
    // In ExportController.php

    public function exportPatientConfirmation(Appointment $appointment)
    {
        $user = Auth::user();
        // Security check: Allow if the logged-in user is the patient OR is an admin
        if ($appointment->patient_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Unauthorized Action');
        }

    $data = ['appointment' => $appointment->load(['patient', 'doctor'])];
    $pdf = Pdf::loadView('pdf.confirmation', $data);

    return $pdf->download('appointment-confirmation-' . $appointment->id . '.pdf');
}

// In app/Http/Controllers/ExportController.php

// Update the Doctor History method
public function exportDoctorHistory()
{
    $doctor = Auth::user();
    $appointments = Appointment::with('patient')
        ->where('doctor_id', $doctor->id)
        ->whereIn('status', ['completed', 'cancelled'])
        ->orderBy('appointment_date', 'desc')
        ->get();

    $data = [
        'title' => 'Appointment History for ' . $doctor->name,
        'date_range' => 'All Past Appointments',
        'appointments' => $appointments
    ];

    // CORRECTED: Use the new history template
    $pdf = Pdf::loadView('pdf.history', $data);
    return $pdf->download('doctor_history_' . $doctor->id . '.pdf');
}

// Update the Admin History method
public function exportAdminHistory()
{
    $appointments = Appointment::with(['patient', 'doctor'])
        ->whereIn('status', ['completed', 'cancelled'])
        ->orderBy('appointment_date', 'desc')
        ->get();
    
    $data = [
        'title' => 'System-Wide Appointment History',
        'date_range' => 'All Past Appointments',
        'appointments' => $appointments
    ];

    // CORRECTED: Use the new history template
    $pdf = Pdf::loadView('pdf.history', $data);
    return $pdf->download('system_appointment_history.pdf');
}

}