<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules as PasswordRules;

class UserManagementController extends Controller
{
   
public function index(Request $request)
{
    

    $query = User::orderBy('created_at', 'desc')
                ->where('id', '!=', Auth::id());

    $adminHospital = Auth::user()->hospital_name;
    if ($adminHospital) {
        $query->where(function($q) use ($adminHospital) {
            $q->where('role', 'patient') // Admins can see all patients for system usage
              ->orWhere(function($subQ) use ($adminHospital) {
                  $subQ->where('role', 'doctor')
                       ->whereHas('doctorProfile', function($docQ) use ($adminHospital) {
                           $docQ->where('hospital_name', $adminHospital);
                       });
              });
        });
    }

    // Handle role filter
    if ($request->filled('role') && $request->role != 'all') {
        $query->where('role', $request->role);
    }

    // Handle search filter
    if ($request->filled('search')) {
        $searchTerm = $request->search;
        $query->where(function($q) use ($searchTerm) {
            $q->where('name', 'like', '%' . $searchTerm . '%')
              ->orWhere('email', 'like', '%' . $searchTerm . '%');
        });
    }

    $users = $query->paginate(10);

    return view('admin.users.index', compact('users'));
}

    public function show(User $user)
    {
        if (in_array($user->role, ['patient', 'doctor'])) {
            $appointments = $user->role === 'patient' ? $user->appointments()->get() : $user->doctorAppointments()->get();
            $user->stats = [
                'total' => $appointments->count(),
                'upcoming' => $appointments->whereIn('status', ['scheduled', 'confirmed'])->count(),
                'completed' => $appointments->where('status', 'completed')->count(),
            ];
        }
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', PasswordRules\Password::defaults()],
            'role' => 'required|in:patient,doctor,admin',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'specialty' => 'nullable|required_if:role,doctor|string|max:255',
            'license_number' => 'nullable|required_if:role,doctor|string|max:255',
            'experience_years' => 'nullable|required_if:role,doctor|integer|min:0',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);
        User::create($validatedData);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', PasswordRules\Password::defaults()],
            'role' => 'required|in:patient,doctor,admin',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'specialty' => 'nullable|required_if:role,doctor|string|max:255',
            'license_number' => 'nullable|required_if:role,doctor|string|max:255',
            'experience_years' => 'nullable|required_if:role,doctor|integer|min:0',
        ]);
        
        if (!empty($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']);
        }

        $user->update($validatedData);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }
        
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}