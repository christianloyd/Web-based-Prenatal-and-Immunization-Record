<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Healthcare Portal - Login</title> 
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
</head>
<body class="min-h-screen flex flex-col lg:flex-row">
    <!-- Left Side - Healthcare Design -->
    <div class="flex-1 bg-gradient-to-br from-charcoal to-paynes-gray flex items-center justify-center p-4 sm:p-6 lg:p-8">
        <div class="text-center text-white max-w-md w-full">
            <!-- Healthcare Icon -->
            <div class="mb-6 lg:mb-8">
                <div class="w-12 h-12 sm:w-16 sm:h-16 mx-auto mb-4 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
            </div>
            
            <h1 class="text-2xl sm:text-3xl font-bold mb-3 sm:mb-4">HealthCare Portal</h1>
            <p class="text-base sm:text-lg opacity-90 mb-4 sm:mb-6">Dedicated platform for healthcare professionals</p>
            
            <!-- Healthcare Elements -->
            <div class="flex justify-center space-x-4 sm:space-x-6 mb-6 sm:mb-8">
                <div class="text-center">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mb-2">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-xs sm:text-sm opacity-80">Patient Records</p>
                </div>
                <div class="text-center">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mb-2">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-xs sm:text-sm opacity-80">Scheduling</p>
                </div>
                <div class="text-center">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mb-2">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <p class="text-xs sm:text-sm opacity-80">Analytics</p>
                </div>
            </div>
            
            <div class="text-xs sm:text-sm opacity-75">
                <p>Secure • Reliable • Professional</p>
            </div>
        </div>
    </div>

    <!-- Right Side - Login Form -->
    <div class="flex-1 bg-gray-50 flex items-center justify-center p-4 sm:p-6 lg:p-8">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8">
                <div class="text-center mb-6 sm:mb-8">
                    <h2 class="text-xl sm:text-2xl font-bold text-charcoal mb-2">Welcome Back</h2>
                    <p class="text-sm sm:text-base text-paynes-gray">Sign in to access your healthcare portal</p>
                </div>

                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-6">
                        <p class="text-sm text-green-700 text-center">{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-6">
                        <p class="text-sm text-red-700 text-center">{{ session('error') }}</p>
                    </div>
                @endif 

                <form action="{{ route('login.authenticate') }}" method="POST" class="space-y-5 sm:space-y-6">
                    @csrf
                    <!-- Username Field -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-charcoal mb-2">
                            Username
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user h-4 w-4 sm:h-5 sm:w-5 text-paynes-gray"></i>
                            </div>
                            <input 
                                type="text" 
                                id="username" 
                                name="username"
                                required
                                value="{{ old('username') }}"
                                class="block w-full pl-10 pr-3 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-charcoal focus:border-charcoal transition-colors @error('username') border-red-300 @enderror"
                                placeholder="Enter your username"
                            >
                        </div>
                        @error('username')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-charcoal mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock h-4 w-4 sm:h-5 sm:w-5 text-paynes-gray"></i>
                            </div>
                            <input 
                                type="password" 
                                id="password" 
                                name="password"
                                required
                                class="block w-full pl-10 pr-10 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-charcoal focus:border-charcoal transition-colors @error('password') border-red-300 @enderror"
                                placeholder="Enter your password"
                            >
                            <!-- Show/Hide Password Toggle -->
                            <button 
                                type="button" 
                                id="togglePassword" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-paynes-gray hover:text-charcoal transition-colors"
                                aria-label="Toggle password visibility"
                            >
                                <i class="fa-solid fa-eye-slash h-4 w-4 sm:h-5 sm:w-5"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>  
                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input 
                                id="remember-me" 
                                name="remember" 
                                type="checkbox" 
                                class="h-4 w-4 text-charcoal focus:ring-charcoal border-gray-300 rounded"
                            >
                            <label for="remember-me" class="ml-2 block text-sm text-paynes-gray">
                                Remember me
                            </label>
                        </div>
                        <div class="text-sm">
                            <a href="#" class="font-medium text-charcoal hover:text-paynes-gray transition-colors">
                                Forgot password?
                            </a>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        class="w-full flex justify-center items-center py-2.5 sm:py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-charcoal hover:bg-paynes-gray focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-charcoal transition-colors"
                    >
                    <i class="fa-solid fa-right-to-bracket mr-3 sm:mr-5"></i>
                        Sign In
                    </button>
                </form>

                <!-- Footer -->
                <div class="mt-4 sm:mt-6 text-center">
                    <p class="text-xs text-paynes-gray">
                        Secure healthcare portal for authorized personnel only
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = togglePassword.querySelector('i');
            
            togglePassword.addEventListener('click', function() {
                // Toggle password visibility
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle eye icon
                if (type === 'text') {
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                } else {
                    eyeIcon.classList.remove('fa-eye');
                    eyeIcon.classList.add('fa-eye-slash');
                }
            });
        });
    </script>
</body>
</html>