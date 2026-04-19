<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prescription;
use App\Models\Bill;

class PharmacistController extends Controller
{
    public function index(Request $request)
    {
        $pharmacistHospital = auth()->user()->hospital_name;
        
        $doctorsQuery = \App\Models\User::where('role', 'doctor')->with('doctorProfile');
        if ($pharmacistHospital) {
            $doctorsQuery->whereHas('doctorProfile', function($q) use ($pharmacistHospital) {
                $q->where('hospital_name', $pharmacistHospital);
            });
        }
        $doctors = $doctorsQuery->get();
        $prescription = null;

        if ($request->filled('token_number') && $request->filled('doctor_id')) {
            $doctorProfile = \App\Models\Doctor::where('user_id', $request->doctor_id)->first();

            if ($doctorProfile) {
                $prescription = Prescription::with(['patient', 'doctor', 'appointment'])
                    ->where('doctor_id', $doctorProfile->id)
                    ->where('status', 'pending')
                    ->doesntHave('bill')
                    ->whereHas('appointment', function ($q) use ($request) {
                        $q->where('token_number', $request->token_number);
                    })
                    ->first();
            }
        }

        return view('pharmacist.dashboard.index', compact('doctors', 'prescription'));
    }

    public function createBill(Prescription $prescription)
    {
        return view('pharmacist.prescriptions.bill', compact('prescription'));
    }

    public function storeBill(Request $request, Prescription $prescription)
    {
        $request->validate([
            'consultation_fee' => 'required|numeric',
            'medicine_cost' => 'required|numeric',
        ]);

        $total = $request->consultation_fee + $request->medicine_cost;

        Bill::create([
            'prescription_id' => $prescription->id,
            'consultation_fee' => $request->consultation_fee,
            'medicine_cost' => $request->medicine_cost,
            'total_amount' => $total,
        ]);

        return redirect()->route('pharmacist.dashboard')->with('success', 'Bill generated! Sent to Patient Dashboard for payment.');
    }
}
