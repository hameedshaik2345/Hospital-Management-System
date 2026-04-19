<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send an OTP via Fast2SMS (Indian SMS Gateway)
     * 
     * @param string $phone
     * @param string $otp
     * @return bool
     */
    public function sendOtp($phone, $otp)
    {
        $apiKey = env('FAST2SMS_API_KEY');
        
        if (empty($apiKey)) {
            Log::warning("Fast2SMS API Key is missing. OTP for $phone: $otp");
            return false;
        }

        try {
            // Using Fast2SMS Quick SMS API (No DLT registration needed for testing)
            $response = Http::withoutVerifying()->withHeaders([
                'authorization' => $apiKey,
            ])->get('https://www.fast2sms.com/dev/bulkV2', [
                'message'  => "Your MedFlow OTP is: $otp. Valid for 10 minutes. Do not share it with anyone.",
                'language' => 'english',
                'route'    => 'q',  // Quick SMS - NO DLT needed
                'numbers'  => $phone,
            ]);

            if ($response->successful()) {
                Log::info("SMS successfully sent to $phone via Fast2SMS.");
                return true;
            } else {
                Log::error("Fast2SMS Error: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("SMS Service Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a custom text message (for token alerts etc.)
     *
     * @param string $phone
     * @param string $message
     * @return bool
     */
    public function sendSms($phone, $message)
    {
        $apiKey = env('FAST2SMS_API_KEY');

        if (empty($apiKey)) {
            Log::warning("Fast2SMS API Key is missing. Message to $phone: $message");
            return false;
        }

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'authorization' => $apiKey,
            ])->get('https://www.fast2sms.com/dev/bulkV2', [
                'message'  => $message,
                'language' => 'english',
                'route'    => 'q', // Quick SMS (transactional-style without DLT)
                'numbers'  => $phone,
            ]);

            if ($response->successful() && $response->json('return') === true) {
                Log::info("Custom SMS sent to $phone.");
                return true;
            } else {
                Log::error("Fast2SMS Custom SMS Error: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("SMS Service Exception (custom): " . $e->getMessage());
            return false;
        }
    }
}
