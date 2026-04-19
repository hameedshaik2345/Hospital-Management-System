<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->role === 'patient' && !Auth::user()->hasVerifiedPhone()) {
            if (!$request->is('patient/verify-phone*') && !$request->routeIs('logout')) {
                return redirect()->route('patient.verify-phone');
            }
        }

        return $next($request);
    }
}
