<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Barangay Health Worker Dashboard') - Laravel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
     
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Flowbite CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.css" rel="stylesheet" />
    
    <style>
        /* Import Inter font for system-wide use - optimized weights only */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        /* Apply Inter font system-wide */
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Fix layout shaking by ensuring consistent scrollbar behavior */
        html {
            overflow-y: scroll; /* Always show vertical scrollbar space */
            scroll-behavior: smooth;
        }
        
        body {
            overflow-x: hidden; /* Prevent horizontal scroll */
        }
        
        /* Custom scrollbar styling */
        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #d1d5db #f3f4f6;
        }
        
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f3f4f6;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 4px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
        
        /* Ensure layout stability */
        .main-container {
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        /* Prevent content jumping during navigation */
        .content-wrapper {
            min-height: calc(100vh - 120px); /* Adjust based on header height */
        }
        
        /* Prevent layout shifts during transitions */
        * {
            box-sizing: border-box;
        }
        
        /* Smooth transitions for all elements */
        .transition-all {
            transition-duration: 150ms !important;
        }
        
        /* Prevent transform origin issues */
        [class*="transform"] {
            transform-origin: center;
        }
        
        /* Ensure consistent width calculations */
        .w-64 {
            width: 16rem !important;
        }
        
        /* Fix navigation menu button movements */
        .nav-link {
            position: relative !important;
            display: flex !important;
            align-items: center !important;
            text-decoration: none !important;
            transition: background-color 0.15s ease, color 0.15s ease !important;
            transform: none !important;
        }
        
        .nav-link:hover,
        .nav-link:focus,
        .nav-link:active {
            transform: none !important;
            outline: none !important;
        }
        
        /* Prevent any button movement or jumping */
        nav a, nav button {
            transform: none !important;
            will-change: auto !important;
        }
        
        /* Ensure sidebar stays fixed during navigation */
        .sidebar-nav {
            position: fixed !important;
            transform: none !important;
        }
        
        /* Stop all transforms and animations on navigation */
        nav *, nav i, .fas, .fa {
            transform: none !important;
            animation: none !important;
            transition: background-color 0.15s ease, color 0.15s ease !important;
        }
        
        /* Prevent flash of unstyled content */
        main {
            opacity: 1;
            visibility: visible;
        }
        
        /* Force navigation text visibility */
        .sidebar-nav a,
        .sidebar-nav li a,
        nav a,
        nav ul li a {
            color: white !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        /* Ensure text in navigation is always visible */
        .w-64.bg-secondary a,
        .bg-secondary a {
            color: rgba(255, 255, 255, 1) !important;
        }
        
    </style>
    
    @stack('styles')
    @stack('scripts')
   
   
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Left Sidebar Navigation -->
        <!-- TODO: Replace with DaisyUI drawer component -->
        <!-- Original: div with bg-secondary -->
        <div class="w-64 bg-secondary text-white flex flex-col">
            <!-- TODO: Replace with DaisyUI navbar brand -->
            <!-- Original: div with border-b border-primary -->
            <div class="p-6 border-b border-primary">
                <div class="flex items-center">
                    <!--<img src="{{ asset('images/logo1.webp') }}" 
                         alt="Healthcare Logo" 
                         class="w-10 h-10 mr-3 object-contain">!-->
                    <div>
                        <h1 class="text-xl font-bold">Barangay Health Worker Portal</h1>
                        <p class="text-sm text-gray-300 mt-1">Healthcare Dashboard</p>
                    </div>
                </div>
            </div>
            
            <!-- TODO: Replace with DaisyUI menu component -->
            <!-- Original: nav with flex-1 p-4 -->
            <nav class="flex-1 p-4">
                <ul class="space-y-2">
                    <li>
                        <!-- TODO: Replace with DaisyUI menu-item -->
                        <!-- Original: a with nav-link class -->
                        <a href="{{ route('dashboard') }}" class="nav-link flex items-center p-3 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors" data-section="dashboard">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <!-- TODO: Replace with DaisyUI menu-item -->
                        <!-- Original: a with nav-link class -->
                        <a href="{{ route('bhw.patients.index') }}" class="nav-link flex items-center p-3 rounded-lg {{ request()->routeIs('bhw.patients.*') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors" data-section="patients">
                            <i class="fas fa-user-plus w-5 h-5 mr-3"></i>
                           Patient Registration
                        </a>
                    </li> 
                    <li>
                        <!-- TODO: Replace with DaisyUI menu-item -->
                        <!-- Original: a with nav-link class -->
                        <a href="{{ route('bhw.prenatalrecord.index') }}" class="nav-link flex items-center p-3 rounded-lg {{ request()->routeIs('bhw.prenatalrecord.*') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors" data-section="prenatal">
                            <i class="fas fa-file-medical w-5 h-5 mr-3"></i>
                            Prenatal Records
                        </a>
                    </li> 
                    <li>
                        <!-- TODO: Replace with DaisyUI menu-item -->
                        <!-- Original: a with nav-link class -->
                        <a href="{{ route('bhw.childrecord.index') }}" class="nav-link flex items-center p-3 rounded-lg {{ request()->routeIs('bhw.childrecord.*') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors" data-section="child-records">
                            <i class="fas fa-child w-5 h-5 mr-3"></i>
                            Child Records
                        </a>
                    </li>
                     
                     
                    <li>
                        <!-- TODO: Replace with DaisyUI menu-item -->
                        <!-- Original: a with nav-link class -->
                        <a href="{{ route('bhw.report') }}" class="nav-link flex items-center p-3 rounded-lg {{ request()->routeIs('bhw.report*') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors" data-section="reports">
                            <i class="fas fa-chart-bar w-5 h-5 mr-3"></i>
                            Reports
                        </a>
                    </li>
                </ul>
            </nav>
            
            <!-- TODO: Replace with DaisyUI dropdown and avatar -->
            <!-- Original: div with border-t border-primary -->
            <div class="p-4 border-t border-primary">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <!-- TODO: Replace with DaisyUI avatar -->
                        <!-- Original: div with bg-primary rounded-full -->
                        <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center">
                            <span class="text-sm font-semibold">{{ strtoupper(substr(auth()->user()->name ?? 'MW', 0, 2)) }}</span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium">{{ auth()->user()->name ?? 'Dr. Sarah Johnson' }}</p>
                        </div>
                    </div>
                    <!-- TODO: Replace with DaisyUI button -->
                    <!-- Original: form with button -->
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="p-2 text-gray-300 hover:text-white hover:bg-primary rounded-lg transition-colors" title="Logout">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <!-- TODO: Replace with DaisyUI navbar -->
            <!-- Original: header with bg-white shadow-sm -->
            <header class="bg-white shadow-sm border-b p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800" id="page-title">@yield('page-title', 'Dashboard Overview')</h2>
                        <p class="text-gray-600 text-sm" id="page-subtitle">@yield('page-subtitle', 'Monitor patient care and health records')</p>
                    </div>
                    
                    
                </div>
            </header>

            <!-- Main Content -->
            <!-- TODO: Replace with DaisyUI container -->
            <!-- Original: main with custom-scrollbar -->
            <main class="flex-1 p-6 overflow-y-scroll custom-scrollbar">
                <div class="content-wrapper">
                    @yield('content')
                </div>
            </main>
        </div>
    </div> 
    <!-- Flowbite JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>
    
    
    @stack('scripts')
    
    {{-- Include Global Confirmation Modal --}}
    @include('components.confirmation-modal')
</body>
</html>