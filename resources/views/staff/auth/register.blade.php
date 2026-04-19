<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Registration</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    <style>
        body { background-color: #f0f2f5; display: flex; align-items: center; justify-content: center; padding: 2rem 0; }
        .card { width: 100%; max-width: 600px; }
        [x-cloak] { display: none !important; }
    </style>

    <link rel="icon" type="image/svg+xml" href="{{ asset('medflow-favicon.svg') }}">
</head>
<body>
    <div class="card shadow-lg">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                 <div class="icon-circle-sm bg-primary bg-opacity-10 text-primary mx-auto mb-3">
    <!-- Shield with checkmark icon -->
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-shield-check" viewBox="0 0 16 16">
        <path d="M5.072 0a.5.5 0 0 0-.21.045l-4 2A.5.5 0 0 0 .5 2.5v5c0 3.493 2.918 6.907 7.26 9.29a.5.5 0 0 0 .48 0C12.582 14.407 15.5 10.993 15.5 7.5v-5a.5.5 0 0 0-.362-.455l-4-2A.5.5 0 0 0 11 0H5.072zM8 1.056 13.5 3.21v4.29c0 2.948-2.514 6.062-6 8.155C4.514 13.562 2 10.448 2 7.5V3.21L8 1.056zm3.854 4.646a.5.5 0 0 0-.708-.708L7.5 8.643 5.854 7a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l4-4z"/>
    </svg>
</div>

                <h3 class="fw-bold">Staff Registration</h3>
                <p class="text-muted">Create your staff account</p>
            </div>

            <form method="POST" action="{{ route('staff.register') }}" x-data="{ role: 'doctor' }">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="role" class="form-label">Role</label>
                        <select name="role" id="role" class="form-select" x-model="role" required>
                            <option value="doctor">Doctor</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="phone_number" class="form-label">Phone Number</label>
                        <input type="tel" name="phone_number" id="phone_number" class="form-control" value="{{ old('phone_number') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}" required>
                    </div>
                     <div class="col-md-6">
                        <label for="gender" class="form-label">Gender</label>
                        <select name="gender" id="gender" class="form-select" required>
                            <option value="" disabled selected>Select gender</option>
                            <option value="male" @selected(old('gender') == 'male')>Male</option>
                            <option value="female" @selected(old('gender') == 'female')>Female</option>
                            <option value="other" @selected(old('gender') == 'other')>Other</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" name="address" id="address" class="form-control" value="{{ old('address') }}" required>
                    </div>
                    
                    <div class="col-12" x-show="role === 'doctor'" x-transition x-cloak>
                        <hr class="my-3">
                        <p class="text-muted small">The fields below are required for the 'Doctor' role.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="specialty" class="form-label">Specialty</label>
                                <input type="text" name="specialty" id="specialty" class="form-control" value="{{ old('specialty') }}" placeholder="e.g., Cardiology, Pediatrics">
                            </div>
                            <div class="col-md-6">
                                <label for="license_number" class="form-label">License Number</label>
                                <input type="text" name="license_number" id="license_number" class="form-control" value="{{ old('license_number') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="experience_years" class="form-label">Years of Experience</label>
                                <input type="number" name="experience_years" id="experience_years" class="form-control" value="{{ old('experience_years') }}">
                            </div>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="col-12 mt-3">
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                    
                    <div class="col-12 mt-4">
                        <button class="btn btn-primary w-100 py-2" type="submit">Create Account</button>
                    </div>
                </div>
            </form>
            <div class="text-center mt-4">
                <p class="text-muted">Already have an account? <a href="{{ route('staff.login') }}">Sign In</a></p>
                <a href="/" class="text-muted small">← Back to Home</a>
            </div>
        </div>
    </div>
    <style>
    .icon-circle-sm {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
</style>
</body>
</html>