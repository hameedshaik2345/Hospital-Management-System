<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Login</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap');
        
        body {
            font-family: 'Outfit', sans-serif !important;
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe, #f8fafc) !important;
            background-attachment: fixed !important;
        }
        
        .shape {
            position: fixed; /* fixed so they stay when scrolling */
            border-radius: 50%;
            filter: blur(100px);
            z-index: 0;
            opacity: 0.3;
            animation: float 20s infinite ease-in-out alternate;
            pointer-events: none;
        }

        .shape-1 { width: 500px; height: 500px; background: #0ea5e9; top: -150px; left: -100px; }
        .shape-2 { width: 600px; height: 600px; background: #38bdf8; bottom: -200px; right: -100px; animation-delay: -5s; }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(50px, 50px) scale(1.1); }
        }
        
    </style>

    <link rel="icon" type="image/svg+xml" href="{{ asset('medflow-favicon.svg') }}">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 flex-column">

    <!-- Background Decor (Very Subtle) -->
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <div class="card shadow-lg" style="width: 400px;">
        <div class="card-body p-5 text-center">
            <div class="icon-circle-sm bg-primary bg-opacity-10 text-primary mx-auto mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-shield-check" viewBox="0 0 16 16">
        <path d="M5.072 0a.5.5 0 0 0-.21.045l-4 2A.5.5 0 0 0 .5 2.5v5c0 3.493 2.918 6.907 7.26 9.29a.5.5 0 0 0 .48 0C12.582 14.407 15.5 10.993 15.5 7.5v-5a.5.5 0 0 0-.362-.455l-4-2A.5.5 0 0 0 11 0H5.072zM8 1.056 13.5 3.21v4.29c0 2.948-2.514 6.062-6 8.155C4.514 13.562 2 10.448 2 7.5V3.21L8 1.056zm3.854 4.646a.5.5 0 0 0-.708-.708L7.5 8.643 5.854 7a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l4-4z"/>
    </svg>
            </div>
            <h3 class="fw-bold mb-3">Staff Login</h3>
            <p class="text-muted mb-4">Access your staff dashboard</p>

            <form method="POST" action="{{ route('staff.login') }}">
                @csrf
                <div class="form-floating mb-3">
                    <!-- CORRECTED LINE: Added autocomplete="username" -->
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
                    <label for="email">Email Address</label>
                    @error('email') <div class="invalid-feedback text-start">{{ $message }}</div> @enderror
                </div>
                <div class="form-floating mb-4">
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password" placeholder="Password" required autocomplete="current-password">
                    <label for="password">Password</label>
                    @error('password') <div class="invalid-feedback text-start">{{ $message }}</div> @enderror
                </div>
                <button class="btn btn-primary w-100 py-2" type="submit">Sign In</button>
            </form>

            <hr class="my-4">
            <p class="text-muted">Don't have an account? <a href="{{ route('staff.register') }}">Register</a></p>
            <a href="/" class="text-muted small">← Back to Home</a>
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
    
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap');
        
        body {
            font-family: 'Outfit', sans-serif !important;
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe, #f8fafc) !important;
            background-attachment: fixed !important;
        }
        
        .shape {
            position: fixed; /* fixed so they stay when scrolling */
            border-radius: 50%;
            filter: blur(100px);
            z-index: 0;
            opacity: 0.3;
            animation: float 20s infinite ease-in-out alternate;
            pointer-events: none;
        }

        .shape-1 { width: 500px; height: 500px; background: #0ea5e9; top: -150px; left: -100px; }
        .shape-2 { width: 600px; height: 600px; background: #38bdf8; bottom: -200px; right: -100px; animation-delay: -5s; }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(50px, 50px) scale(1.1); }
        }
        
    </style>
</body>
</html>