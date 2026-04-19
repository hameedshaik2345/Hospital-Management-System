<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'MedFlow' }}</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    <style>
        .auth-card {
            width: 100%;
            max-width: 500px;
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

    <link rel="icon" type="image/svg+xml" href="{{ asset('medflow-favicon.svg') }}">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 flex-column">

    <!-- Background Decor (Very Subtle) -->
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <div class="card shadow-lg auth-card">
        <div class="card-body p-5">
            {{ $slot }}
        </div>
    </div>
</body>
</html>