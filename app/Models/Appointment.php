<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'doctor_name',
        'doctor_specialty',
        'appointment_date',
        'status',
        'reason',
        'token_number',
        'is_paid'
    ];
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');

    }

    // In Appointment.php
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Centralized schedule logic for token timing
     * 1-10: 9:00 - 9:30
     * 11-20: 9:40 - 10:20
     * ... 10 min breaks ...
     * 1:00 - 4:00 Lunch
     * 4:00 - 8:00 Evening
     */
    public static function calculateTokenTiming($tokenNumber, $date)
    {
        $group = floor(($tokenNumber - 1) / 10);
        $carbonDate = \Carbon\Carbon::parse($date);

        if ($group == 0) {
            $start = $carbonDate->copy()->setTime(9, 0);
            $duration = 30;
        } elseif ($group <= 4) {
            // Groups 1-4 (Tokens 11-50)
            // Group 1 starts at 9:40. Each group is 40 min + 10 min cycle = 50 min
            $start = $carbonDate->copy()->setTime(9, 40)->addMinutes(($group - 1) * 50);
            $duration = 40;
        } else {
            // Groups 5-9 (Tokens 51-100)
            // Starts after lunch at 4:00 PM (16:00)
            $start = $carbonDate->copy()->setTime(16, 0)->addMinutes(($group - 5) * 50);
            $duration = 40;
        }

        return [
            'start' => $start,
            'end' => $start->copy()->addMinutes($duration)
        ];
    }
}

