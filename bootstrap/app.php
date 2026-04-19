<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Auth; // <-- Add this line
use Illuminate\Http\Request; // <-- Add this line
use Illuminate\Auth\Middleware\RedirectIfAuthenticated; // <-- Add this line

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    // In bootstrap/app.php

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'is_patient' => \App\Http\Middleware\IsPatient::class,
            'guest_patient' => \App\Http\Middleware\RedirectIfPatientAuthenticated::class,
            'guest_staff' => \App\Http\Middleware\RedirectIfStaffAuthenticated::class,
            'is_doctor' => \App\Http\Middleware\IsDoctor::class,
            'is_admin' => \App\Http\Middleware\IsAdmin::class,
            'is_pharmacist' => \App\Http\Middleware\IsPharmacist::class,
            'verified_phone' => \App\Http\Middleware\EnsurePhoneIsVerified::class,
        ]);

        // CORRECTED REDIRECTION LOGIC
        RedirectIfAuthenticated::redirectUsing(function (Request $request) {
            $user = Auth::user();
            if ($user && in_array($user->role, ['doctor', 'admin', 'pharmacist'])) {
                return route('staff.dashboard.router');
            }
            return route('patient.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();