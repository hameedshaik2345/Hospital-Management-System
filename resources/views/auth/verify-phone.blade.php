<x-auth-layout>
    <x-slot name="title">Verify Phone - MedFlow</x-slot>

    <div class="text-center mb-4">
        <div class="mb-3">
            <i class="bi bi-phone-vibrate text-primary" style="font-size: 3rem;"></i>
        </div>
        <h3 class="fw-bold">Verify Your Phone</h3>
        <p class="text-muted">
            We've sent a 6-digit verification code to
            <strong>{{ Auth::user()->phone_number }}</strong>.
        </p>
    </div>

    @if (session('status'))
        <div class="alert alert-success mb-4 text-center">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('patient.verify-phone') }}">
        @csrf
        
        <div class="mb-4">
            <label for="otp" class="form-label fw-semibold">Enter 6-Digit Code</label>
            <input type="text" 
                   name="otp" 
                   id="otp" 
                   class="form-control form-control-lg text-center fw-bold @error('otp') is-invalid @enderror" 
                   placeholder="000000" 
                   maxlength="6" 
                   required 
                   autofocus
                   style="letter-spacing: 12px; font-size: 2rem;">
            
            @error('otp')
                <div class="invalid-feedback text-center">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="d-grid gap-2 mb-4">
            <button type="submit" class="btn btn-primary btn-lg fw-bold py-3 shadow-sm" style="border-radius: 12px;">
                Verify & Continue
            </button>
        </div>
    </form>

    <div class="text-center">
        <p class="text-muted small mb-2">Didn't receive the code?</p>
        <form method="POST" action="{{ route('patient.verify-phone.resend') }}">
            @csrf
            <button type="submit" class="btn btn-link text-decoration-none fw-semibold">
                Resend Code
            </button>
        </form>
    </div>

    <div class="mt-4 pt-3 border-top text-center">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm">
                Logout
            </button>
        </form>
    </div>
</x-auth-layout>
