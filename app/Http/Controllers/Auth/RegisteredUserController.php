<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;



class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
   // At the top, make sure you have:

// ... inside the RegisteredUserController class
// In app/Http/Controllers/Auth/RegisteredUserController.php

public function store(Request $request): RedirectResponse
{
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
        'phone_number' => ['required', 'string', 'max:20'],
        'date_of_birth' => ['required', 'date'],
        'gender' => ['required', 'string', 'in:male,female,other'],
        'address' => ['required', 'string', 'max:500'],
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'phone_number' => $request->phone_number,
        'date_of_birth' => $request->date_of_birth,
        'gender' => $request->gender,
        'address' => $request->address,
        'role' => 'patient', // Explicitly set role
    ]);

    event(new Registered($user));

    Auth::login($user);

    // Redirect directly to patient dashboard (phone verification disabled)
    return redirect()->route('patient.dashboard');
}
}
