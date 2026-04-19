<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - MedFlow</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        .dashboard-layout { display: flex; min-height: 100vh; flex-direction: column; }
        @media (min-width: 992px) {
            .dashboard-layout { flex-direction: row; }
        }
        .sidebar { 
            width: 260px; 
            background: white; 
            border-right: 1px solid #e2e8f0; 
            padding: 1.5rem; 
            flex-shrink: 0; 
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        .main-content { flex-grow: 1; padding: 2rem; width: 100%; transition: padding 0.3s ease; }
        .sidebar-link { display: block; padding: 0.75rem 1.25rem; border-radius: 0.5rem; text-decoration: none; color: #4b5563; font-weight: 500; margin-bottom: 0.5rem; transition: background 0.2s; }
        .sidebar-link.active, .sidebar-link:hover { background-color: #eef2ff; color: #4338ca; }
        .card { background-color: white; padding: 1.5rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; margin-bottom: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    
        /* Mobile Specifics */
        .mobile-header {
            display: none;
            background: white;
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 1001;
            justify-content: space-between;
            align-items: center;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.4);
            z-index: 999;
        }

        @media (max-width: 991.98px) {
            .sidebar { 
                position: fixed; 
                left: -260px; 
                top: 0; bottom: 0; 
            }
            .sidebar.show { left: 0; box-shadow: 10px 0 30px rgba(0,0,0,0.1); }
            .mobile-header { display: flex; }
            .main-content { padding: 1rem; }
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
        <span class="fw-bold text-primary">Admin Portal</span>
        <div style="width: 40px;"></div>
    </div>

    <div class="dashboard-layout">
        <!-- Admin Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="d-flex justify-content-between align-items-center mb-4 d-lg-none">
                <span class="fw-bold">Menu</span>
                <button type="button" class="btn-close" id="sidebarClose"></button>
            </div>
            <h1 class="h4 fw-bold mb-4 d-none d-lg-block" style="color: #0066CC;"><img src="{{ asset('medflow-logo.png') }}" alt="MedFlow Logo" style="height: 140px; margin: -40px 0; max-width: 100%; object-fit: contain; mix-blend-mode: multiply; transform: scale(1.6);"></h1>
            <p class="text-muted small d-none d-lg-block">Admin Portal</p>
          <nav id="adminNav">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">User Management</a>
        <a href="{{ route('admin.appointments.index') }}" class="sidebar-link {{ request()->routeIs('admin.appointments.index') ? 'active' : '' }}">All Appointments</a>
        <a href="{{ route('admin.appointments.history') }}" class="sidebar-link {{ request()->routeIs('admin.appointments.history') ? 'active' : '' }}">Appointment History</a>
        <a href="{{ route('admin.profile.edit') }}" class="sidebar-link {{ request()->routeIs('admin.profile.edit') ? 'active' : '' }}">My Profile</a>
    </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
             <header class="d-flex justify-content-between align-items-center mb-4 mt-2 mt-lg-0">
                <a href="/" class="text-muted text-decoration-none small">← Home</a>
                <div class="small">
                    <span class="d-none d-md-inline">Welcome, {{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline ms-3">
                        @csrf
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="text-danger fw-bold text-decoration-none">
                            Logout
                        </a>
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

        document.querySelectorAll('#adminNav .sidebar-link').forEach(link => {
            link.addEventListener('click', () => {
                if(window.innerWidth < 992) toggleSidebar();
            });
        });
    </script>
</body>
</html>