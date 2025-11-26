<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Payouts Payment System')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            /* Primary Brand Colors */
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            
            /* Secondary Colors */
            --secondary-color: #64748b;
            --secondary-dark: #475569;
            --secondary-light: #94a3b8;
            
            /* Status Colors */
            --success-color: #10b981;
            --success-dark: #059669;
            --success-light: #34d399;
            --success-gradient: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            
            --danger-color: #ef4444;
            --danger-dark: #dc2626;
            --danger-light: #f87171;
            --danger-gradient: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
            
            --warning-color: #f59e0b;
            --warning-dark: #d97706;
            --warning-light: #fbbf24;
            --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            
            --info-color: #3b82f6;
            --info-dark: #2563eb;
            --info-light: #60a5fa;
            --info-gradient: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
            
            /* Neutral Colors */
            --dark-color: #0f172a;
            --dark-secondary: #1e293b;
            --dark-tertiary: #334155;
            --light-bg: #f8fafc;
            --light-secondary: #f1f5f9;
            --light-tertiary: #e2e8f0;
            --border-color: #e2e8f0;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            
            /* Accent Colors */
            --accent-purple: #8b5cf6;
            --accent-pink: #ec4899;
            --accent-cyan: #06b6d4;
            --accent-emerald: #10b981;
            
            /* Layout */
            --sidebar-width: 260px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --shadow-xl: 0 20px 25px rgba(0,0,0,0.15);
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: var(--primary-gradient) !important;
            box-shadow: var(--shadow-lg);
            padding: 1rem 0;
            z-index: 1030;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }
        
        .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 0 0.5rem;
        }
        
        .nav-link:hover {
            transform: translateY(-2px);
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #ffffff 0%, var(--light-bg) 100%);
            box-shadow: var(--shadow-lg);
            transition: all 0.3s ease;
            z-index: 1020;
            overflow-y: auto;
            padding-top: 70px;
            border-right: 1px solid var(--border-color);
        }
        
        .sidebar.collapsed {
            margin-left: calc(-1 * var(--sidebar-width));
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 1rem;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu-item {
            margin-bottom: 0.25rem;
        }
        
        .sidebar-menu-link {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.5rem;
            color: var(--dark-color);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu-link:hover {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.08) 0%, transparent 100%);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
            transform: translateX(4px);
        }
        
        .sidebar-menu-link.active {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.12) 0%, transparent 100%);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
            font-weight: 600;
            box-shadow: inset 0 0 20px rgba(99, 102, 241, 0.05);
        }
        
        .sidebar-menu-link i {
            width: 24px;
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }
        
        .sidebar-menu-title {
            padding: 1rem 1.5rem 0.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--secondary-color);
            letter-spacing: 0.5px;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            transition: all 0.3s ease;
            min-height: 100vh;
        }
        
        .main-content.expanded {
            margin-left: 0;
        }
        
        .sidebar-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.5rem;
            margin-right: 1rem;
        }
        
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 1.5rem;
            background: #ffffff;
            overflow: hidden;
        }
        
        .card:hover {
            box-shadow: var(--shadow-xl);
            transform: translateY(-4px);
        }
        
        .card-header {
            background: linear-gradient(135deg, #ffffff 0%, var(--light-bg) 100%);
            border-bottom: 2px solid var(--border-color);
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            border-radius: 16px 16px 0 0 !important;
            color: var(--text-primary);
        }
        
        .stat-card {
            border-radius: 16px;
            padding: 1.5rem;
            color: white;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .stat-card:hover::before {
            opacity: 1;
        }
        
        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
        }
        
        .stat-card .card-title {
            font-size: 0.9rem;
            font-weight: 500;
            opacity: 0.9;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }
        
        .stat-card i {
            font-size: 2.5rem;
            opacity: 0.3;
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .table {
            background: white;
        }
        
        .table thead {
            background: linear-gradient(135deg, var(--light-bg) 0%, var(--light-secondary) 100%);
        }
        
        .table thead th {
            border: none;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.8px;
            color: var(--text-primary);
            padding: 1rem;
        }
        
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: var(--border-color);
            color: var(--text-primary);
        }
        
        .table tbody tr {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .table tbody tr:hover {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.03) 0%, transparent 100%);
            transform: scale(1.01);
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1.25rem;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            color: white;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
        }
        
        .btn-success {
            background: var(--success-gradient);
            border: none;
            color: white;
            font-weight: 600;
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, var(--success-dark) 0%, var(--success-color) 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }
        
        .btn-danger {
            background: var(--danger-gradient);
            border: none;
            color: white;
            font-weight: 600;
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, var(--danger-dark) 0%, var(--danger-color) 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
        }
        
        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            font-weight: 600;
            background: transparent;
        }
        
        .btn-outline-primary:hover {
            background: var(--primary-gradient);
            border-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid var(--border-color);
            padding: 0.75rem 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #ffffff;
            color: var(--text-primary);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            outline: none;
            background: #ffffff;
        }
        
        .form-control::placeholder {
            color: var(--text-muted);
        }
        
        /* Password Toggle Styles */
        .password-input-wrapper {
            position: relative;
        }
        
        .password-toggle-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 0.5rem;
            z-index: 10;
            transition: color 0.3s ease;
        }
        
        .password-toggle-btn:hover {
            color: var(--primary-color);
        }
        
        .password-toggle-btn:focus {
            outline: none;
            color: var(--primary-color);
        }
        
        .password-input-wrapper .form-control {
            padding-right: 45px;
        }
        
        .badge {
            padding: 0.5rem 0.875rem;
            font-weight: 600;
            border-radius: 8px;
            font-size: 0.75rem;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        
        .badge.bg-success {
            background: var(--success-gradient) !important;
            color: white;
        }
        
        .badge.bg-danger {
            background: var(--danger-gradient) !important;
            color: white;
        }
        
        .badge.bg-warning {
            background: var(--warning-gradient) !important;
            color: white;
        }
        
        .badge.bg-info {
            background: var(--info-gradient) !important;
            color: white;
        }
        
        .badge.bg-primary {
            background: var(--primary-gradient) !important;
            color: white;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            box-shadow: var(--shadow-md);
            padding: 1rem 1.25rem;
            font-weight: 500;
        }
        
        .alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
            color: var(--success-dark);
            border-left: 4px solid var(--success-color);
        }
        
        .alert-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
            color: var(--danger-dark);
            border-left: 4px solid var(--danger-color);
        }
        
        .alert-warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%);
            color: var(--warning-dark);
            border-left: 4px solid var(--warning-color);
        }
        
        .alert-info {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%);
            color: var(--info-dark);
            border-left: 4px solid var(--info-color);
        }
        
        main {
            min-height: calc(100vh - 200px);
            padding: 2rem 0;
        }
        
        .page-header {
            margin-bottom: 2rem;
        }
        
        .page-header h2 {
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .page-header .text-muted {
            color: var(--text-secondary) !important;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-secondary);
        }
        
        .empty-state i {
            font-size: 4rem;
            opacity: 0.2;
            margin-bottom: 1rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            
            .sidebar.show {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .page-header h2 {
                font-size: 1.5rem;
            }
            
            .stat-card h3 {
                font-size: 1.5rem;
            }
            
            .stat-card .card-title {
                font-size: 0.875rem;
            }
            
            .table {
                font-size: 0.875rem;
            }
            
            .table thead th {
                font-size: 0.7rem;
                padding: 0.75rem 0.5rem;
            }
            
            .table tbody td {
                padding: 0.75rem 0.5rem;
            }
            
            main {
                padding: 1rem 0;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }
        }
        
        @media (max-width: 576px) {
            .page-header h2 {
                font-size: 1.25rem;
            }
            
            .stat-card h3 {
                font-size: 1.25rem;
            }
            
            .card-header h5 {
                font-size: 1rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            @auth
                <button class="sidebar-toggle" type="button" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
            @endauth
            <a class="navbar-brand" href="{{ auth()->check() ? (auth()->user()->isAdmin() ? route('admin.dashboard') : route('user.dashboard')) : route('login') }}">
                <i class="bi bi-wallet2 me-2"></i>Payouts
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ auth()->user()->isAdmin() ? route('admin.profile.show') : route('user.profile.show') }}">
                                        <i class="bi bi-person-circle me-2"></i>My Profile
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="bi bi-person-plus me-1"></i>Register
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    @auth
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h6 class="mb-0 fw-bold text-uppercase text-muted small">Navigation</h6>
        </div>
        <ul class="sidebar-menu">
            @if(auth()->user()->isAdmin())
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-menu-title">Management</li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.users.index') }}" class="sidebar-menu-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.transactions.index') }}" class="sidebar-menu-link {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
                        <i class="bi bi-receipt-cutoff"></i>
                        <span>Transactions</span>
                    </a>
                </li>
                <li class="sidebar-menu-title">Account</li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.profile.show') }}" class="sidebar-menu-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                        <i class="bi bi-person-circle"></i>
                        <span>My Profile</span>
                    </a>
                </li>
            @else
                <li class="sidebar-menu-item">
                    <a href="{{ route('user.dashboard') }}" class="sidebar-menu-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-menu-title">Transactions</li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('user.transactions.index') }}" class="sidebar-menu-link {{ request()->routeIs('user.transactions.index') || request()->routeIs('user.transactions.show') ? 'active' : '' }}">
                        <i class="bi bi-list-ul"></i>
                        <span>All Transactions</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('user.transactions.create') }}" class="sidebar-menu-link {{ request()->routeIs('user.transactions.create') ? 'active' : '' }}">
                        <i class="bi bi-plus-circle"></i>
                        <span>New Transaction</span>
                    </a>
                </li>
                <li class="sidebar-menu-title">Account</li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('user.profile.show') }}" class="sidebar-menu-link {{ request()->routeIs('user.profile.*') ? 'active' : '' }}">
                        <i class="bi bi-person-circle"></i>
                        <span>My Profile</span>
                    </a>
                </li>
            @endif
        </ul>
    </aside>
    @endauth

    <div class="main-content @guest{{ 'expanded' }}@endguest" id="mainContent">
        <main class="container my-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="bg-white border-top mt-5 py-4">
            <div class="container text-center text-muted">
                <p class="mb-0">&copy; {{ date('Y') }} Payouts Payment System. All rights reserved.</p>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Find all password toggle buttons
            const toggleButtons = document.querySelectorAll('.password-toggle-btn');
            
            toggleButtons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Find the input field - it's the previous sibling or we can find it in the wrapper
                    const wrapper = this.closest('.password-input-wrapper');
                    const input = wrapper ? wrapper.querySelector('input[type="password"], input[type="text"]') : this.previousElementSibling;
                    const icon = this.querySelector('i');
                    
                    if (!input || !icon) return;
                    
                    if (input.type === 'password') {
                        // Show password - change to text type and show open eye
                        input.type = 'text';
                        icon.classList.remove('bi-eye-slash');
                        icon.classList.add('bi-eye');
                    } else {
                        // Hide password - change to password type and show eye with line
                        input.type = 'password';
                        icon.classList.remove('bi-eye');
                        icon.classList.add('bi-eye-slash');
                    }
                });
            });
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            if (sidebarToggle && sidebar && mainContent) {
                // Check if sidebar state is saved in localStorage
                const sidebarState = localStorage.getItem('sidebarCollapsed');
                if (sidebarState === 'true') {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('expanded');
                }
                
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                    
                    // Save state to localStorage
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                });
                
                // Mobile: close sidebar when clicking outside
                if (window.innerWidth <= 768) {
                    document.addEventListener('click', function(e) {
                        if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target) && sidebar.classList.contains('show')) {
                            sidebar.classList.remove('show');
                        }
                    });
                    
                    sidebarToggle.addEventListener('click', function() {
                        sidebar.classList.toggle('show');
                    });
                }
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
