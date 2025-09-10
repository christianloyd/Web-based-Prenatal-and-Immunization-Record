<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Barangay Health Worker Dashboard') - Laravel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
     
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @stack('styles')
    @stack('scripts')
   
   
</head>
<body class="bg-gray-50 font-sans">
    <div class="flex h-screen">
        <!-- Left Sidebar Navigation -->
        <!-- TODO: Replace with DaisyUI drawer component -->
        <!-- Original: div with bg-secondary -->
        <div class="w-64 bg-secondary text-white flex flex-col">
            <!-- TODO: Replace with DaisyUI navbar brand -->
            <!-- Original: div with border-b border-primary -->
            <div class="p-6 border-b border-primary">
                <h1 class="text-xl font-bold">Barangay Health Worker Portal</h1>
                <p class="text-sm text-gray-300 mt-1">Healthcare Dashboard</p>
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
                        <a href="{{ route('bhw.patients.index') }}" class="nav-link flex items-center p-3 rounded-lg {{ request()->routeIs('bhw.patients.*') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors" data-section="prenatalrecord">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                           Patients Registration
                        </a>
                    </li> 
                    <li>
                        <!-- TODO: Replace with DaisyUI menu-item -->
                        <!-- Original: a with nav-link class -->
                        <a href="{{ route('bhw.prenatalrecord.index') }}" class="nav-link flex items-center p-3 rounded-lg {{ request()->routeIs('bhw.prenatalrecord.*') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors" data-section="prenatalrecord">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                            Prenatal Records
                        </a>
                    </li> 
                    <li>
                        <!-- TODO: Replace with DaisyUI menu-item -->
                        <!-- Original: a with nav-link class -->
                        <a href="{{ route('bhw.childrecord.index') }}" class="nav-link flex items-center p-3 rounded-lg {{ request()->routeIs('bhw.childrecord.*') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors" data-section="immunization">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Child Record
                        </a>
                    </li>
                     
                     
                    <li>
                        <!-- TODO: Replace with DaisyUI menu-item -->
                        <!-- Original: a with nav-link class -->
                        <a href="#" class="nav-link flex items-center p-3 rounded-lg {{ request()->routeIs('reports') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors" data-section="reports">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"></path>
                            </svg>
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
            <main class="flex-1 p-6 overflow-y-auto custom-scrollbar">
                @yield('content')
            </main>
        </div>
    </div> 
    @stack('scripts')
</body>
</html>