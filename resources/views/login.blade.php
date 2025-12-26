<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Healthcare Portal - Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            height: 100vh;
            overflow: hidden;
        }

        .login-container {
            display: flex;
            height: 100vh;
            width: 100%;
        }

        /* Left Panel - Imagery */
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #D4A373 0%, #ECB99E 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px 40px;
            position: relative;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 15s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.1); opacity: 0.5; }
        }

        .branding {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }

        .logo-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }

        .logo-circle img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
        }

        .branding h1 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 12px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .branding p {
            font-size: 16px;
            opacity: 0.95;
            font-weight: 400;
        }

        .illustration {
            position: relative;
            z-index: 2;
            max-width: 500px;
            width: 100%;
            margin-top: 40px;
        }

        .illustration img {
            width: 100%;
            height: auto;
            filter: drop-shadow(0 20px 40px rgba(0,0,0,0.15));
        }

        .features {
            position: relative;
            z-index: 2;
            margin-top: 50px;
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            font-size: 14px;
            background: rgba(255,255,255,0.15);
            padding: 10px 20px;
            border-radius: 25px;
            backdrop-filter: blur(10px);
        }

        .feature-item i {
            font-size: 18px;
        }

        /* Right Panel - Login Form */
        .right-panel {
            flex: 1;
            background: #f9fafb;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .login-box {
            width: 100%;
            max-width: 440px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .secure-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fef3c7;
            color: #92400e;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-top: 12px;
        }

        .login-description {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 32px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 16px;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            font-size: 15px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: white;
        }

        .form-input:focus {
            outline: none;
            border-color: #D4A373;
            box-shadow: 0 0 0 4px rgba(212, 163, 115, 0.1);
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            font-size: 16px;
            padding: 8px;
            transition: color 0.2s;
        }

        .password-toggle:hover {
            color: #D4A373;
        }

        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            font-size: 14px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #4b5563;
            cursor: pointer;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #D4A373;
            cursor: pointer;
        }

        .forgot-link {
            color: #D4A373;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: #B8956A;
            text-decoration: underline;
        }

        .login-button {
            width: 100%;
            background: linear-gradient(135deg, #D4A373 0%, #B8956A 100%);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(212, 163, 115, 0.3);
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(212, 163, 115, 0.4);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .portal-note {
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
            color: #6b7280;
            padding: 16px;
            background: #fef3c7;
            border-radius: 10px;
            border-left: 4px solid #D4A373;
        }

        .footer-text {
            text-align: center;
            margin-top: 32px;
            font-size: 12px;
            color: #9ca3af;
        }

        .footer-text span {
            display: block;
            margin-top: 8px;
            font-weight: 600;
            letter-spacing: 1px;
            color: #D4A373;
        }

        /* Error Messages */
        .error-message {
            color: #ef4444;
            font-size: 13px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .left-panel {
                display: none;
            }
            
            .right-panel {
                flex: 1;
                background: linear-gradient(135deg, #f9fafb 0%, #e5e7eb 100%);
            }
        }

        @media (max-width: 640px) {
            .right-panel {
                padding: 24px 20px;
            }

            .login-header h2 {
                font-size: 24px;
            }

            .login-box {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Panel - Branding & Imagery -->
        <div class="left-panel">
            <div class="branding">
                <div class="logo-circle">
                    <img src="{{ asset('images/logo_final.jpg') }}" alt="Healthcare Logo">
                </div>
                <h1>HealthCare Portal</h1>
                <p>Prenatal & Immunization Management System</p>
            </div>

            <div class="illustration">
                <img src="{{ asset('images/maternal-care.png') }}" alt="Maternal Care" onerror="this.style.display='none'">
            </div>

            <div class="features">
                <div class="feature-item">
                    <i class="fas fa-shield-heart"></i>
                    <span>Secure & Reliable</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-baby"></i>
                    <span>Prenatal Care</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-syringe"></i>
                    <span>Immunization</span>
                </div>
            </div>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="right-panel">
            <div class="login-box">
                <div class="login-header">
                    <h2>Welcome Back</h2>
                    <div class="secure-badge">
                        <i class="fas fa-lock"></i>
                        <span>Authorized Access Only</span>
                    </div>
                </div>

                <p class="login-description">
                    Sign in with your assigned credentials to manage prenatal and immunization records for your community.
                </p>

                @include('components.flowbite-alert')

                <form action="{{ route('login.authenticate') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                required
                                value="{{ old('username') }}"
                                class="form-input"
                                placeholder="Enter your username"
                                autocomplete="username"
                            >
                        </div>
                        @error('username')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                class="form-input"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye-slash" id="toggleIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <div class="form-footer">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-link">Forgot password?</a>
                    </div>

                    <button type="submit" class="login-button">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Sign In</span>
                    </button>
                </form>

                <div class="portal-note">
                    <i class="fas fa-info-circle"></i>
                    Only authorized barangay health workers and midwives have access to this portal.
                </div>

                <div class="footer-text">
                    Preventive Healthcare Management System © {{ date('Y') }}
                    <span>SECURE • RELIABLE • PROFESSIONAL</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            }
        }
    </script>
</body>
</html>