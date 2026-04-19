<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SymptomMapping;
use App\Models\SymptomLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SymptomAnalyzerController extends Controller
{
    public function index()
    {
        return view('patient.symptoms.index');
    }

    public function analyze(Request $request)
    {
        $request->validate(['symptoms' => 'required|string']);
        $symptomsStr = strtolower($request->symptoms);
        $symptomsArr = array_map('trim', explode(',', $symptomsStr));

        $matchedSpecializations = [];
        $mappings = SymptomMapping::all();

        foreach ($symptomsArr as $symptom) {
            foreach ($mappings as $mapping) {
                if (str_contains($symptom, strtolower($mapping->keyword))) {
                    $matchedSpecializations[] = $mapping->specialization;
                }
            }
        }

        $matchedSpecializations = array_unique($matchedSpecializations);

        // default if no match
        if (empty($matchedSpecializations)) {
            $matchedSpecializations[] = 'General Physician';
        }

        SymptomLog::create([
            'patient_id' => Auth::id(),
            'symptoms' => $request->symptoms,
            'recommended_specializations' => json_encode($matchedSpecializations),
        ]);

        $doctors = User::where('role', 'doctor')
            ->where(function($query) use ($matchedSpecializations) {
                foreach ($matchedSpecializations as $spec) {
                    $query->orWhere('specialty', 'LIKE', '%' . $spec . '%');
                }
            })
            ->with('doctorProfile')
            ->get();

        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $dbHospitals = \App\Models\Hospital::all()->keyBy('name');

        $doctors->transform(function ($doctor) use ($lat, $lng, $dbHospitals) {
            $hospitalName = $doctor->doctorProfile->hospital_name ?? 'MedFlow';
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

        return view('patient.symptoms.results', compact('matchedSpecializations', 'doctors', 'symptomsStr'));
    }
}
