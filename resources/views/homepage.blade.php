<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedFlow - Modern Healthcare</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe, #f8fafc); /* Clean, ultra-modern very light icy blue/white */
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Abstract Background Elements */
        .shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            z-index: 0;
            opacity: 0.3; /* Subtle */
            animation: float 20s infinite ease-in-out alternate;
        }

        .shape-1 { width: 500px; height: 500px; background: #0ea5e9; top: -150px; left: -100px; }
        .shape-2 { width: 600px; height: 600px; background: #38bdf8; bottom: -200px; right: -100px; animation-delay: -5s; }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(50px, 50px) scale(1.1); }
        }

        /* Removed The White Box (Landing Card is transparent now) */
        .landing-wrapper {
            max-width: 900px;
            width: 90%;
            z-index: 10;
            text-align: center;
            animation: popIn 1s cubic-bezier(0.19, 1, 0.22, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo-container {
            margin-bottom: 0px;
            height: 180px; /* Hide padded white space */
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-img {
            height: 400px; /* huge scaling */
            object-fit: contain;
            mix-blend-mode: multiply; /* Will cleanly rub out the white background against the light body bg */
        }

        .subtitle {
            font-size: 1.8rem;
            font-weight: 400;
            color: #475569;
            letter-spacing: 0.5px;
            margin-bottom: 3.5rem;
            margin-top: 1rem;
        }

        .subtitle strong {
            color: #0284c7;
            font-weight: 700;
        }

        /* Modern Gradient Buttons */
        .btn-portal {
            font-size: 1.15rem;
            font-weight: 600;
            border-radius: 50px;
            padding: 1.2rem 3rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            color: white;
            border: none;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 1;
        }

        .btn-patient {
            background: linear-gradient(135deg, #0284c7, #0369a1);
            box-shadow: 0 10px 25px rgba(2, 132, 199, 0.3);
        }

        .btn-staff {
            background: white;
            color: #0369a1 !important;
            border: 2px solid #0369a1;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .btn-portal:hover {
            transform: translateY(-5px) scale(1.02);
            color: white;
        }
        
        .btn-staff:hover {
            background: #0369a1;
            color: white !important;
            box-shadow: 0 15px 30px rgba(3, 105, 161, 0.4);
        }
        
        .btn-patient:hover { box-shadow: 0 15px 30px rgba(2, 132, 199, 0.4); }

        .btn-portal i { font-size: 1.3rem; transition: transform 0.3s ease; }
        .btn-portal:hover i { transform: translateX(3px); }

        @keyframes popIn {
            0% { opacity: 0; transform: scale(0.95) translateY(20px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }

        /* Feature Tags */
        .feature-tags {
            margin-top: 4rem;
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .tag {
            background: rgba(255, 255, 255, 0.6);
            color: #334155;
            padding: 0.6rem 1.4rem;
            border-radius: 50px;
            font-size: 0.95rem;
            font-weight: 600;
            border: 1px solid rgba(2, 132, 199, 0.2);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .tag i {
            color: #0284c7;
            margin-right: 6px;
        }

    </style>

    <link rel="icon" type="image/svg+xml" href="{{ asset('medflow-favicon.svg') }}">
</head>

<body>
    
    <!-- Background Decor (Very Subtle) -->
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <div class="landing-wrapper">
        <!-- Logo Container properly cropping whitespace -->
        <div class="logo-container">
            <img src="{{ asset('medflow-logo.png') }}" alt="MedFlow Logo" class="logo-img">
        </div>
        
        <h2 class="subtitle">The Operating System for <strong>Modern Healthcare</strong></h2>
        
        <div class="d-flex flex-column flex-sm-row justify-content-center gap-4">
            <a href="{{ route('login') }}" class="btn-portal btn-patient">
                <i class="bi bi-person-heart"></i> Patient Portal
            </a>
            <a href="{{ route('staff.login') }}" class="btn-portal btn-staff">
                <i class="bi bi-hospital"></i> Staff Dashboard
            </a>
        </div>

        <div class="feature-tags">
            <span class="tag"><i class="bi bi-robot"></i> AI Symptom Engine</span>
            <span class="tag"><i class="bi bi-broadcast"></i> Live Token Polling</span>
            <span class="tag"><i class="bi bi-geo-alt"></i> Location Based</span>
            <span class="tag"><i class="bi bi-shield-check"></i> Enterprise Secure</span>
        </div>
    </div>

</body>
</html>