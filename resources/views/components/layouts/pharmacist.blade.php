<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Dashboard - MedFlow</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        @import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css");

        .dashboard-layout {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }

        @media (min-width: 992px) {
            .dashboard-layout { flex-direction: row; }
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #ffffff, #fdfdfd);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.05);
            padding: 2rem 1.5rem;
            flex-shrink: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .main-content {
            flex-grow: 1;
            padding: 2.5rem;
            width: 100%;
            transition: all 0.3s ease;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.85rem 1.25rem;
            border-radius: 12px;
            text-decoration: none;
            color: #4b5563;
            font-size: 1.05rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            background-color: #ffffff;
            border: 2px solid #e1e7ef;
            transition: all 0.3s ease;
        }

        .sidebar-link i {
            font-size: 1.3rem;
        }

        .sidebar-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.08);
            border-color: #0066CC;
        }

        .sidebar-link.active {
            background: linear-gradient(135deg, #0066CC, #00B4A6);
            color: white;
            border-color: transparent;
            box-shadow: 0 10px 20px rgba(0, 180, 166, 0.3);
        }

        /* Mobile Responsive */
        .mobile-header {
            display: none;
            background: white;
            padding: 1rem 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1001;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(4px);
            z-index: 999;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                left: -280px;
                top: 0; bottom: 0;
            }
            .sidebar.show { left: 0; box-shadow: 10px 0 30px rgba(0,0,0,0.1); }
            .main-content { padding: 1.5rem 1rem; }
            .mobile-header { display: flex; justify-content: space-between; align-items: center; }
            .sidebar-overlay.show { display: block; }
        }
    
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap');
        
        body {
            font-family: 'Outfit', sans-serif !important;
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe, #f8fafc) !important;
            background-attachment: fixed !important;
        }
        
        .shape {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            z-index: -1;
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

<body>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="mobile-header">
        <button class="btn btn-light" id="sidebarToggle">
            <i class="bi bi-list fs-3"></i>
        </button>
        <img src="{{ asset('medflow-logo.png') }}" alt="MedFlow Logo" style="height: 40px; object-fit: contain;">
        <div style="width: 40px;"></div>
    </div>

    <div class="dashboard-layout">
        <aside class="sidebar" id="sidebar">
            <div class="d-flex justify-content-between align-items-center mb-4 d-lg-none">
                <span class="fw-bold h5 mb-0">Menu</span>
                <button class="btn-close" id="sidebarClose"></button>
            </div>
            <h1 class="h3 fw-bold mb-5 text-center d-none d-lg-block" style="color: #0066CC;">
                <img src="{{ asset('medflow-logo.png') }}" alt="MedFlow Logo" style="height: 140px; margin: -40px 0; max-width: 100%; object-fit: contain; mix-blend-mode: multiply; transform: scale(1.6);">
            </h1>
            <nav id="sidebarNav">
                <a href="{{ route('pharmacist.dashboard') }}" class="sidebar-link active">
                    <i class="bi bi-receipt"></i> Process Bills
                </a>
                <a href="#" class="sidebar-link" onclick="alert('Module in Development');">
                    <i class="bi bi-box-seam"></i> Inventory
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="d-flex justify-content-between align-items-center mb-4 mt-2 mt-lg-0">
                <a href="{{ route('homepage') }}" class="text-muted text-decoration-none small">← Home</a>
                <div class="small">
                    <span class="d-none d-md-inline">Welcome, {{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline ms-3">
                        @csrf
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();"
                            class="text-danger fw-bold text-decoration-none">Logout</a>
                    </form>
                </div>
            </header>
            {{ $slot }}
        </main>
    </div>
    @stack('scripts')
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggle = document.getElementById('sidebarToggle');
        const close = document.getElementById('sidebarClose');

        function toggleSidebar() {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        if(toggle) toggle.addEventListener('click', toggleSidebar);
        if(close) close.addEventListener('click', toggleSidebar);
        if(overlay) overlay.addEventListener('click', toggleSidebar);

        document.querySelectorAll('#sidebarNav .sidebar-link').forEach(link => {
            link.addEventListener('click', () => {
                if(window.innerWidth < 992) toggleSidebar();
            });
        });
    </script>
</body>

</html>