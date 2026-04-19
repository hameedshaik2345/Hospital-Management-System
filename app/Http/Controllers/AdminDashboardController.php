<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Appointment;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $adminHospital = auth()->user()->hospital_name;

        // Base Queries
        $patientsQuery = User::where('role', 'patient');
        
        $doctorsQuery = User::where('role', 'doctor');
        if ($adminHospital) {
            $doctorsQuery->whereHas('doctorProfile', function ($query) use ($adminHospital) {
                $query->where('hospital_name', $adminHospital);
            });
        }
        $doctorIds = $doctorsQuery->pluck('id');

        $appointmentsQuery = Appointment::query();
        if ($adminHospital) {
            $appointmentsQuery->whereIn('doctor_id', $doctorIds);
        }

        // Stats Cards Data
        $totalPatients = $patientsQuery->count(); // Patients aren't strictly scoped to hospitals, they are global. But we count all.
        $totalDoctors = $doctorsQuery->count();
        
        $appointmentsToday = (clone $appointmentsQuery)
            ->whereDate('appointment_date', Carbon::today())
            ->where('status', '!=', 'cancelled')
            ->count();
            
        $pendingAppointments = (clone $appointmentsQuery)
            ->where('status', 'scheduled')
            ->count();

        // Revenue logic
        // Bills are not explicitly scoped to hospital either. Wait, if $adminHospital is set, only sum bills from their doctors' prescriptions.
        if ($adminHospital) {
            $totalRevenue = \App\Models\Bill::whereHas('prescription.appointment', function($q) use ($doctorIds) {
                $q->whereIn('doctor_id', $doctorIds);
            })->sum('total_amount');
        } else {
            $totalRevenue = \App\Models\Bill::sum('total_amount');
        }

        $doctorStats = (clone $appointmentsQuery)
            ->select('doctor_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('doctor_id')
            ->with('doctor')
            ->get();

        // Recent Registrations (Last 5 users)
        $recentUsers = User::latest()->take(5)->get();

        // Upcoming Appointments (Next 5 across the system/hospital)
        $upcomingAppointments = (clone $appointmentsQuery)
            ->with(['patient', 'doctor'])
            ->where('appointment_date', '>', Carbon::now())
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->orderBy('appointment_date', 'asc')
            ->take(5)
            ->get();

        return view('staff.admin.dashboard', compact(
            'totalPatients',
            'totalDoctors',
            'appointmentsToday',
            'pendingAppointments',
            'totalRevenue',
            'doctorStats',
            'recentUsers',
            'upcomingAppointments'
        ));
    }
}