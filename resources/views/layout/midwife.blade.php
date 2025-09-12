<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Midwife Dashboard')</title> 
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Add Font Awesome -->
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
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false }">
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
              ></div>

        <!-- Left Sidebar Navigation -->
        <div class="fixed inset-y-0 left-0 z-50 w-64 bg-secondary text-white flex flex-col transform lg:translate-x-0 lg:static lg:inset-0 transition-transform duration-300 ease-in-out"
             :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
             x-show="sidebarOpen || window.innerWidth >= 1024"
              >
            
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
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('dashboard') ? 'bg-primary text-white' : 'hover:bg-primary' }}" 
                           data-section="dashboard">
                            <i class="fas fa-tachometer-alt w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            Dashboard
                        </a>
                    </li>

                    <!-- Patient Registration -->
                    <li>
                        <a href="{{ route('midwife.patients.index') }}" 
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('midwife.patients.*') ? 'bg-primary text-white' : 'hover:bg-primary' }}" 
                           data-section="patients">
                            <i class="fas fa-user-plus w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            Patient Registration
                        </a>
                    </li>

                    <!-- Prenatal Records -->
                    <li>
                        <a href="{{ route('midwife.prenatalrecord.index') }}" 
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('midwife.prenatalrecord.*') ? 'bg-primary text-white' : 'hover:bg-primary' }}" 
                           data-section="prenatal">
                            <i class="fas fa-file-medical w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            <span class="hidden sm:inline">Prenatal Records</span>
                            <span class="sm:hidden">Prenatal</span>
                        </a>
                    </li>

                    <!-- Prenatal Check-ups -->
                    <li>
                        <a href="{{ route('midwife.prenatalcheckup.index') }}" 
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('midwife.prenatalcheckup.*') ? 'bg-primary text-white' : 'hover:bg-primary' }}" 
                           data-section="prenatal-checkups">
                            <i class="fas fa-stethoscope w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            <span class="hidden sm:inline">Prenatal Check-ups</span>
                            <span class="sm:hidden">Checkups</span>
                        </a>
                    </li>

                    <!-- Child Records -->
                    <li>
                        <a href="{{ route('midwife.childrecord.index') }}" 
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('midwife.childrecord.*') ? 'bg-primary text-white' : 'hover:bg-primary' }}" 
                           data-section="child-records">
                            <i class="fas fa-child w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            <span class="hidden sm:inline">Child Records</span>
                            <span class="sm:hidden">Children</span>
                        </a>
                    </li>

                    <!-- Immunization -->
                    <li>
                        <a href="{{ route('midwife.immunization.index') }}" 
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('midwife.immunizations.*') ? 'bg-primary text-white' : 'hover:bg-primary' }}" 
                           data-section="immunizations">
                            <i class="fas fa-syringe w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            <span class="hidden sm:inline">Immunization</span>
                            <span class="sm:hidden">Vaccines</span>
                        </a>
                    </li>

                    <!-- Vaccine Management -->
                    <li>
                        <a href="{{ route('midwife.vaccines.index') }}" 
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('midwife.vaccines.*') ? 'bg-primary text-white' : 'hover:bg-primary' }}" 
                           data-section="vaccines">
                            <i class="fas fa-vial w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            <span class="hidden sm:inline">Vaccine Management</span>
                            <span class="sm:hidden">Vaccines</span>
                        </a>
                    </li>

                    <!-- User Management -->
                    <li>
                        <a href="{{ route('midwife.user.index') }}" 
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('midwife.user.*') ? 'bg-primary text-white' : 'hover:bg-primary' }}" 
                           data-section="user-management">
                            <i class="fas fa-users-cog w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            User Management
                        </a>
                    </li> 
                    
                    <!-- Cloud Backup Section -->
                    <li>
                        <a href="{{ route('midwife.cloudbackup.index') }}" 
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('midwife.cloudbackup.*') ? 'bg-primary text-white' : 'hover:bg-primary' }}" 
                           data-section="cloud-backup">
                           <i class="fas fa-cloud-upload-alt w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            Cloud Backup
                        </a>
                    </li> 
                    
                    <!-- Reports -->
                    <li>
                        <a href="{{ route('midwife.report') }}" 
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('midwife.report*') ? 'bg-primary text-white' : 'hover:bg-primary' }}" 
                           data-section="reports">
                            <i class="fas fa-chart-bar w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            Reports
                        </a>
                    </li>

                    <!-- Notifications -->
                    <li>
                        <a href="{{ route('notifications.index') }}" 
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('notifications.*') ? 'bg-primary text-white' : 'hover:bg-primary' }}" 
                           data-section="notifications">
                            <i class="fas fa-bell w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            <span class="hidden sm:inline">Notifications</span>
                            <span class="sm:hidden">Alerts</span>
                        </a>
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
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="p-2 text-gray-400 hover:text-gray-600 relative">
                                <i class="fas fa-bell w-5 h-5 sm:w-6 sm:h-6"></i>
                                <!-- Notification Badge -->
                                <span id="notification-badge" class="notification-badge-count absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 sm:h-5 sm:w-5 flex items-center justify-center hidden">0</span>
                            </button>
                            
                            <!-- Notifications Dropdown -->
                            <div x-show="open" @click.outside="open = false" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 transform scale-100"
                                 x-transition:leave-end="opacity-0 transform scale-95"
                                 class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                <div class="p-4 border-b border-gray-200">
                                    <div class="flex justify-between items-center">
                                        <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                                        <a href="{{ route('notifications.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            View All
                                        </a>
                                    </div>
                                </div>
                                <div id="recent-notifications" class="max-h-64 overflow-y-auto">
                                    <div class="p-4 text-center text-gray-500">
                                        Loading notifications...
                                    </div>
                                </div>
                                <div class="p-3 border-t border-gray-200 bg-gray-50 rounded-b-lg">
                                    <button onclick="markAllAsRead()" class="w-full text-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                                        Mark All as Read
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 p-3 sm:p-4 lg:p-6 overflow-y-scroll custom-scrollbar">
                <div class="content-wrapper">
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
                </div>
            </main>
        </div>
    </div>

    <!-- Alpine.js for dropdown functionality -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Flowbite JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>
    
    <!-- Notification System -->
    <script>
        // Notification system functions
        function loadNotificationCount() {
            fetch('/notifications/unread-count')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notification-badge');
                    if (badge) {
                        badge.textContent = data.count;
                        if (data.count > 0) {
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }
                })
                .catch(error => console.error('Error loading notification count:', error));
        }

        function loadRecentNotifications() {
            fetch('/notifications/recent')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('recent-notifications');
                    if (container && data.notifications) {
                        if (data.notifications.length === 0) {
                            container.innerHTML = '<div class="p-4 text-center text-gray-500">No new notifications</div>';
                        } else {
                            container.innerHTML = data.notifications.map(notification => {
                                const type = notification.data.type || 'info';
                                const icons = {
                                    'info': 'fa-info-circle text-blue-500',
                                    'success': 'fa-check-circle text-green-500',
                                    'warning': 'fa-exclamation-triangle text-yellow-500',
                                    'error': 'fa-times-circle text-red-500'
                                };
                                
                                return `
                                    <div class="p-3 hover:bg-gray-50 border-b border-gray-100 cursor-pointer" onclick="window.location.href='${notification.data.action_url || '/notifications'}'">
                                        <div class="flex items-start space-x-3">
                                            <i class="fas ${icons[type] || icons.info} mt-1"></i>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-sm font-medium text-gray-900 truncate">
                                                    ${notification.data.title || 'Notification'}
                                                </div>
                                                <div class="text-sm text-gray-600 truncate">
                                                    ${notification.data.message || ''}
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    ${formatDate(notification.created_at)}
                                                </div>
                                            </div>
                                            ${!notification.read_at ? '<div class="w-2 h-2 bg-blue-500 rounded-full"></div>' : ''}
                                        </div>
                                    </div>
                                `;
                            }).join('');
                        }
                    }
                })
                .catch(error => console.error('Error loading recent notifications:', error));
        }

        function markAllAsRead() {
            fetch('/notifications/mark-all-as-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotificationCount();
                    loadRecentNotifications();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(minutes / 60);
            const days = Math.floor(hours / 24);

            if (minutes < 1) return 'Just now';
            if (minutes < 60) return `${minutes}m ago`;
            if (hours < 24) return `${hours}h ago`;
            return `${days}d ago`;
        }
        

        // Load notifications on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadNotificationCount();
            
            // Load recent notifications when dropdown opens
            const notificationButton = document.querySelector('[x-data*="open"]');
            if (notificationButton) {
                notificationButton.addEventListener('click', loadRecentNotifications);
            }
            
            // Refresh notifications every 2 minutes
            setInterval(loadNotificationCount, 120000);
        });
    </script>
    
    <!-- Custom Scripts -->
    @stack('scripts')
    
    {{-- Include Global Confirmation Modal --}}
    @include('components.confirmation-modal')
</body>
</html>