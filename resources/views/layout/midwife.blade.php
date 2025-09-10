<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Midwife Dashboard')</title>
    <link rel="icon" type="image/png" sizes="40x40" href="{{ asset('images/logo1.webp') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Add Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-gray-50 font-sans" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Mobile Overlay -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="transition-opacity ease-linear duration-300" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 lg:hidden bg-gray-600 bg-opacity-75"
             @click="sidebarOpen = false"
             style="display: none;"></div>

        <!-- Left Sidebar Navigation -->
        <div class="fixed inset-y-0 left-0 z-50 w-64 bg-secondary text-white flex flex-col transform lg:translate-x-0 lg:static lg:inset-0 transition-transform duration-300 ease-in-out"
             :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
             x-show="sidebarOpen || window.innerWidth >= 1024"
             style="display: none;">
            
            <div class="p-4 sm:p-6 border-b border-primary">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <!--<img src="{{ asset('images/logo1.webp') }}" 
                             alt="Healthcare Logo" 
                             class="w-8 h-8 sm:w-10 sm:h-10 mr-3 object-contain">-->
                        <div>
                            <h1 class="text-lg sm:text-xl font-bold">Midwife Portal</h1>
                            <p class="text-xs sm:text-sm text-gray-300 mt-1">Healthcare Dashboard</p>
                        </div>
                    </div>
                    <!-- Close button for mobile -->
                    <button @click="sidebarOpen = false" 
                            class="lg:hidden p-2 rounded-md text-gray-300 hover:text-white hover:bg-primary transition-colors">
                        <i class="fas fa-times w-5 h-5"></i>
                    </button>
                </div>
            </div>
            
            <nav class="flex-1 p-3 sm:p-4 overflow-y-auto">
                <ul class="space-y-1 sm:space-y-2">
                    <li>
                        <a href="{{ route('dashboard') }}" 
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('dashboard') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors" 
                           data-section="dashboard"
                           @click="window.innerWidth < 1024 && (sidebarOpen = false)">
                            <i class="fas fa-tachometer-alt w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            Dashboard
                        </a>
                    </li>

                    <!-- Patients Section with Dropdown -->
                    <li class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="nav-link flex items-center justify-between w-full p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('midwife.patients.*') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors" 
                                data-section="patients">
                            <div class="flex items-center">
                                <i class="fas fa-user-plus w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                                Patients
                            </div>
                            <i class="fas fa-chevron-down w-3 h-3 sm:w-4 sm:h-4 transform transition-transform" :class="{ 'rotate-180': open }"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <ul x-show="open" 
                            x-transition:enter="transition ease-out duration-500" 
                            x-transition:enter-start="transform opacity-0 scale-95" 
                            x-transition:enter-end="transform opacity-100 scale-100" 
                            x-transition:leave="transition ease-in duration-450" 
                            x-transition:leave-start="transform opacity-100 scale-100" 
                            x-transition:leave-end="transform opacity-0 scale-95" 
                            class="ml-4 sm:ml-6 mt-1 sm:mt-2 space-y-1" 
                            style="display: none;">
                            <li>
                                <a href="{{ route('midwife.patients.index') }}" 
                                   class="nav-link flex items-center p-2 rounded-lg text-xs sm:text-sm {{ request()->routeIs('midwife.patients.index') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors"
                                   @click="window.innerWidth < 1024 && (sidebarOpen = false)">
                                    <i class="fas fa-user-plus w-3 h-3 sm:w-4 sm:h-4 mr-2"></i>
                                    Patient Registration
                                </a>
                            </li>
                            <li>
                                <a href="#" 
                                   class="nav-link flex items-center p-2 rounded-lg text-xs sm:text-sm {{ request()->routeIs('midwife.patients.profiles') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors"
                                   @click="window.innerWidth < 1024 && (sidebarOpen = false)">
                                    <i class="fas fa-id-card w-3 h-3 sm:w-4 sm:h-4 mr-2"></i>
                                    Patient Profiles
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Prenatal Monitoring Section with Dropdown -->
                    <li class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="nav-link flex items-center justify-between w-full p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('midwife.prenatalrecord.*') || request()->routeIs('midwife.prenatalcheckup.*') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors" 
                                data-section="prenatal">
                            <div class="flex items-center">
                                <i class="fas fa-baby w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                                <span class="hidden sm:inline">Prenatal Monitoring</span>
                                <span class="sm:hidden">Prenatal</span>
                            </div>
                            <i class="fas fa-chevron-down w-3 h-3 sm:w-4 sm:h-4 transform transition-transform" :class="{ 'rotate-180': open }"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <ul x-show="open" 
                            x-transition:enter="transition ease-out duration-500" 
                            x-transition:enter-start="transform opacity-0 scale-95" 
                            x-transition:enter-end="transform opacity-100 scale-100" 
                            x-transition:leave="transition ease-in duration-450" 
                            x-transition:leave-start="transform opacity-100 scale-100" 
                            x-transition:leave-end="transform opacity-0 scale-95" 
                            class="ml-4 sm:ml-6 mt-1 sm:mt-2 space-y-1" 
                            style="display: none;">
                            <li>
                                <a href="{{ route('midwife.prenatalrecord.index') }}" 
                                   class="nav-link flex items-center p-2 rounded-lg text-xs sm:text-sm {{ request()->routeIs('midwife.prenatalrecord.*') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors"
                                   @click="window.innerWidth < 1024 && (sidebarOpen = false)">
                                    <i class="fas fa-file-medical w-3 h-3 sm:w-4 sm:h-4 mr-2"></i>
                                    Prenatal Records
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('midwife.prenatalcheckup.index') }}" 
                                   class="nav-link flex items-center p-2 rounded-lg text-xs sm:text-sm {{ request()->routeIs('midwife.prenatalcheckup.*') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors"
                                   @click="window.innerWidth < 1024 && (sidebarOpen = false)">
                                    <i class="fas fa-stethoscope w-3 h-3 sm:w-4 sm:h-4 mr-2"></i>
                                    Prenatal Check-ups
                                </a>
                            </li>
                            <li>
                                <a href="" 
                                   class="nav-link flex items-center p-2 rounded-lg text-xs sm:text-sm {{ request()->routeIs('midwife.highrisk.*') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors"
                                   @click="window.innerWidth < 1024 && (sidebarOpen = false)">
                                    <i class="fas fa-exclamation-triangle w-3 h-3 sm:w-4 sm:h-4 mr-2"></i>
                                    High-risk Cases
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Immunization Tracking Section with Dropdown -->
                    <li class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="nav-link flex items-center justify-between w-full p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('midwife.childrecord.*') || request()->routeIs('midwife.immunization.*') || request()->routeIs('midwife.vaccines.*') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors" 
                                data-section="immunization">
                            <div class="flex items-center">
                                <i class="fas fa-syringe w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                                <span class="hidden sm:inline">Immunization Tracking</span>
                                <span class="sm:hidden">Immunization</span>
                            </div>
                            <i class="fas fa-chevron-down w-3 h-3 sm:w-4 sm:h-4 transform transition-transform" :class="{ 'rotate-180': open }"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <ul x-show="open" 
                            x-transition:enter="transition ease-out duration-500" 
                            x-transition:enter-start="transform opacity-0 scale-95" 
                            x-transition:enter-end="transform opacity-100 scale-100" 
                            x-transition:leave="transition ease-in duration-450" 
                            x-transition:leave-start="transform opacity-100 scale-100" 
                            x-transition:leave-end="transform opacity-0 scale-95" 
                            class="ml-4 sm:ml-6 mt-1 sm:mt-2 space-y-1" 
                            style="display: none;">
                            <li>
                                <a href="{{ route('midwife.childrecord.index') }}" 
                                   class="nav-link flex items-center p-2 rounded-lg text-xs sm:text-sm {{ request()->routeIs('midwife.childrecord.*') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors"
                                   @click="window.innerWidth < 1024 && (sidebarOpen = false)">
                                    <i class="fas fa-child w-3 h-3 sm:w-4 sm:h-4 mr-2"></i>
                                    Child Records
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('midwife.immunization.index') }}" 
                                   class="nav-link flex items-center p-2 rounded-lg text-xs sm:text-sm {{ request()->routeIs('midwife.immunization.*') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors"
                                   @click="window.innerWidth < 1024 && (sidebarOpen = false)">
                                    <i class="fas fa-syringe w-3 h-3 sm:w-4 sm:h-4 mr-2"></i>
                                    Immunization Records
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('midwife.vaccines.index') }}" 
                                   class="nav-link flex items-center p-2 rounded-lg text-xs sm:text-sm {{ request()->routeIs('midwife.vaccines.*') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors"
                                   @click="window.innerWidth < 1024 && (sidebarOpen = false)">
                                    <i class="fas fa-vial w-3 h-3 sm:w-4 sm:h-4 mr-2"></i>
                                    Vaccine Management
                                </a>
                            </li>
                            <li>
                                <a href="" 
                                   class="nav-link flex items-center p-2 rounded-lg text-xs sm:text-sm {{ request()->routeIs('midwife.schedule.*') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors"
                                   @click="window.innerWidth < 1024 && (sidebarOpen = false)">
                                    <i class="fas fa-calendar-alt w-3 h-3 sm:w-4 sm:h-4 mr-2"></i>
                                    Vaccine Schedules
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- User Management -->
                    <li>
                        <a href="{{ route('midwife.user.index') }}" 
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('midwife.user.*') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors" 
                           data-section="user-management"
                           @click="window.innerWidth < 1024 && (sidebarOpen = false)">
                            <i class="fas fa-users-cog w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            User Management
                        </a>
                    </li> 
                    
                    <!-- Cloud Backup Section -->
                    <li>
                        <a href="{{ route('midwife.cloudbackup.index') }}" 
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('midwife.cloudbackup.*') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors" 
                           data-section="cloud-backup"
                           @click="window.innerWidth < 1024 && (sidebarOpen = false)">
                           <i class="fas fa-cloud-upload-alt w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            Cloud Backup
                        </a>
                    </li> 
                    
                    <!-- Reports Section with Dropdown -->
                    <li class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="nav-link flex items-center justify-between w-full p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('midwife.reports.*') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors" 
                                data-section="reports">
                            <div class="flex items-center">
                                <i class="fas fa-chart-bar w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                                Reports
                            </div>
                            <i class="fas fa-chevron-down w-3 h-3 sm:w-4 sm:h-4 transform transition-transform" :class="{ 'rotate-180': open }"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <ul x-show="open" 
                            x-transition:enter="transition ease-out duration-500" 
                            x-transition:enter-start="transform opacity-0 scale-95" 
                            x-transition:enter-end="transform opacity-100 scale-100" 
                            x-transition:leave="transition ease-in duration-450" 
                            x-transition:leave-start="transform opacity-100 scale-100" 
                            x-transition:leave-end="transform opacity-0 scale-95" 
                            class="ml-4 sm:ml-6 mt-1 sm:mt-2 space-y-1" 
                            style="display: none;">
                            <li>
                                <a href="" 
                                   class="nav-link flex items-center p-2 rounded-lg text-xs sm:text-sm {{ request()->routeIs('midwife.reports.prenatal') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors"
                                   @click="window.innerWidth < 1024 && (sidebarOpen = false)">
                                    <i class="fas fa-baby w-3 h-3 sm:w-4 sm:h-4 mr-2"></i>
                                    Prenatal Reports
                                </a>
                            </li>
                            <li>
                                <a href="" 
                                   class="nav-link flex items-center p-2 rounded-lg text-xs sm:text-sm {{ request()->routeIs('midwife.reports.immunization') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors"
                                   @click="window.innerWidth < 1024 && (sidebarOpen = false)">
                                    <i class="fas fa-syringe w-3 h-3 sm:w-4 sm:h-4 mr-2"></i>
                                    Immunization Reports
                                </a>
                            </li>
                            <li>
                                <a href="" 
                                   class="nav-link flex items-center p-2 rounded-lg text-xs sm:text-sm {{ request()->routeIs('midwife.reports.child-health') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors"
                                   @click="window.innerWidth < 1024 && (sidebarOpen = false)">
                                    <i class="fas fa-heartbeat w-3 h-3 sm:w-4 sm:h-4 mr-2"></i>
                                    Child Health Reports
                                </a>
                            </li>
                            <li>
                                <a href="" 
                                   class="nav-link flex items-center p-2 rounded-lg text-xs sm:text-sm {{ request()->routeIs('midwife.reports.monthly-summary') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors"
                                   @click="window.innerWidth < 1024 && (sidebarOpen = false)">
                                    <i class="fas fa-calendar-alt w-3 h-3 sm:w-4 sm:h-4 mr-2"></i>
                                    Monthly Summary
                                </a>
                            </li>
                            <li>
                                <a href=" " 
                                   class="nav-link flex items-center p-2 rounded-lg text-xs sm:text-sm {{ request()->routeIs('midwife.reports.patient-statistics') ? 'bg-primary text-white' : 'hover:bg-primary' }} transition-colors"
                                   @click="window.innerWidth < 1024 && (sidebarOpen = false)">
                                    <i class="fas fa-chart-line w-3 h-3 sm:w-4 sm:h-4 mr-2"></i>
                                    Patient Statistics
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>
            
            <div class="p-3 sm:p-4 border-t border-primary">
                <div class="flex items-center justify-between">
                    <div class="flex items-center min-w-0">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-primary rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-xs sm:text-sm font-semibold">{{ strtoupper(substr(auth()->user()->name ?? 'MW', 0, 2)) }}</span>
                        </div>
                        <div class="ml-2 sm:ml-3 min-w-0">
                            <p class="text-xs sm:text-sm font-medium truncate">{{ auth()->user()->name ?? 'Midwife' }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ auth()->user()->role ?? 'Midwife' }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="p-2 text-gray-300 hover:text-white hover:bg-primary rounded-lg transition-colors flex-shrink-0" title="Logout">
                            <i class="fas fa-sign-out-alt w-4 h-4 sm:w-5 sm:h-5"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 lg:ml-0">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b p-3 sm:p-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center min-w-0">
                        <!-- Mobile menu button -->
                        <button @click="sidebarOpen = !sidebarOpen" 
                                class="lg:hidden p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary mr-2 sm:mr-3">
                            <i class="fas fa-bars w-5 h-5"></i>
                        </button>
                        
                        <div class="min-w-0">
                            <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 truncate" id="page-title">@yield('page-title', 'Dashboard Overview')</h2>
                            <p class="text-gray-600 text-xs sm:text-sm truncate" id="page-subtitle">@yield('page-subtitle', 'Monitor patient care and health records')</p>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="flex items-center space-x-2 sm:space-x-3 flex-shrink-0">
                        <div class="hidden md:flex items-center space-x-2"> 
                        </div>
                        
                        <!-- Notifications -->
                        <div class="relative">
                            <button class="p-2 text-gray-400 hover:text-gray-600 relative">
                                <i class="fas fa-bell w-5 h-5 sm:w-6 sm:h-6"></i>
                                <!-- Notification Badge -->
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 sm:h-5 sm:w-5 flex items-center justify-center">3</span>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 p-3 sm:p-4 lg:p-6 overflow-y-auto custom-scrollbar">
                <!-- Breadcrumb -->
                @if(!request()->routeIs('dashboard'))
                <nav class="mb-3 sm:mb-4">
                    <ol class="flex items-center space-x-1 sm:space-x-2 text-xs sm:text-sm text-gray-500">
                        <li><a href="{{ route('dashboard') }}" class="hover:text-gray-700 truncate">Dashboard</a></li>
                        <li><span>/</span></li>
                        <li class="text-gray-700 font-medium truncate">@yield('page-title', 'Current Page')</li>
                    </ol>
                </nav>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Alpine.js for dropdown functionality -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Custom Scripts -->
    @stack('scripts')
</body>
</html>