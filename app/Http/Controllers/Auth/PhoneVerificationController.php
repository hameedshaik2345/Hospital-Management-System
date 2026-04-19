<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PhoneOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use App\Services\SmsService;

class PhoneVerificationController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function show()
    {
        if (Auth::user()->hasVerifiedPhone()) {
            return redirect()->route('patient.dashboard');
        }

        // Send OTP if one doesn't exist or is expired
        $this->ensureOtpSent(Auth::user()->phone_number);

        return view('auth.verify-phone');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $otpRecord = PhoneOtp::where('phone_number', Auth::user()->phone_number)
            ->where('otp', $request->otp)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otpRecord) {
            return back()->withErrors(['otp' => 'The provided OTP is invalid or has expired.']);
        }

        $user = Auth::user();
        $user->phone_verified_at = Carbon::now();
        $user->save();

        // Delete the OTP after successful verification
        $otpRecord->delete();

        return redirect()->route('patient.dashboard')->with('status', 'Phone number verified successfully!');
    }

    public function resend()
    {
        $this->sendNewOtp(Auth::user()->phone_number);
        return back()->with('status', 'A new OTP has been sent to your phone number.');
    }

    private function ensureOtpSent($phone)
    {
        $exists = PhoneOtp::where('phone_number', $phone)
            ->where('expires_at', '>', Carbon::now())
            ->exists();

        if (!$exists) {
            $this->sendNewOtp($phone);
        }
    }

    private function sendNewOtp($phone)
    {
        // Delete old OTPs for this number
        PhoneOtp::where('phone_number', $phone)->delete();

        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        PhoneOtp::create([
            'phone_number' => $phone,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // REAL SMS SENDING: Call Fast2SMS
        $this->smsService->sendOtp($phone, $otp);
        
        Log::info("OTP for phone $phone: $otp");
    }
}
