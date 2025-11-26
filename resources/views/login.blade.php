<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Healthcare Portal - Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Complete reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            width: 100%;
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        /* Table layout for guaranteed side-by-side */
        .login-table {
            display: table;
            width: 100%;
            height: 100vh;
            border-collapse: collapse;
        }
        
        .login-cell {
            display: table-cell;
            vertical-align: middle;
            height: 100vh;
        }
        
        .login-cell-left {
            width: 50%;
            background: linear-gradient(135deg, #D4A373 0%, #ecb99e 100%);
        }
        
        .login-cell-right {
            width: 50%;
            background-color: #FEFAE0;
        }
        
        /* Content styling */
        .left-content {
            text-align: center;
            color: white;
            padding: 2rem;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .right-content {
            padding: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        
        .form-box {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            padding: 2rem;
            width: 100%;
            max-width: 380px;
        }
        
        /* Form inputs */
        .input-group {
            margin-bottom: 1rem;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #000;
            font-weight: 500;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.2s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #D4A373;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .remember-forgot label {
            display: flex;
            align-items: center;
            color: #6b7280;
            font-size: 14px;
        }
        
        .remember-forgot input[type="checkbox"] {
            margin-right: 8px;
            accent-color: #D4A373;
        }
        
        .forgot-link {
            color: #D4A373;
            text-decoration: none;
            font-size: 14px;
        }
        
        .forgot-link:hover {
            text-decoration: underline;
        }
        
        .login-btn {
            width: 100%;
            background: #D4A373;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .login-btn:hover {
            background: #B8956A;
        }
        
        .logo-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 4px solid #D4A373;
            margin-bottom: 1rem;
        }
        
        .feature-icon {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .login-table {
                display: block;
            }
            
            .login-cell {
                display: block;
                width: 100%;
                height: 50vh;
            }
            
            .login-cell-left {
                height: 40vh;
            }
            
            .login-cell-right {
                height: 60vh;
            }
        }
    </style>
</head>
<body>
    <table class="login-table">
        <tr>
            <!-- Left Side -->
            <td class="login-cell login-cell-left">
                <div class="left-content">
                    <div class="feature-icon">
                        <i class="fas fa-heartbeat" style="font-size: 20px;"></i>
                    </div>
                    <h1 style="font-size: 28px; margin-bottom: 1rem;">HealthCare Portal</h1>
                    <p style="font-size: 16px; opacity: 0.9; margin-bottom: 2rem;">Dedicated platform for healthcare professionals</p>
                    
                    <div style="display: flex; justify-content: center; gap: 2rem; margin-bottom: 2rem;">
                        <div style="text-align: center;">
                            <div class="feature-icon">
                                <i class="fas fa-file-medical" style="font-size: 16px;"></i>
                            </div>
                            <p style="font-size: 12px; opacity: 0.8;">Patient Records</p>
                        </div>
                        <div style="text-align: center;">
                            <div class="feature-icon">
                                <i class="fas fa-clock" style="font-size: 16px;"></i>
                            </div>
                            <p style="font-size: 12px; opacity: 0.8;">Scheduling</p>
                        </div>
                        <div style="text-align: center;">
                            <div class="feature-icon">
                                <i class="fas fa-chart-line" style="font-size: 16px;"></i>
                            </div>
                            <p style="font-size: 12px; opacity: 0.8;">Analytics</p>
                        </div>
                    </div>
                    
                    <p style="font-size: 12px; opacity: 0.75;">Secure • Reliable • Professional</p>
                </div>
            </td>
            
            <!-- Right Side -->
            <td class="login-cell login-cell-right">
                <div class="right-content">
                    <div class="form-box">
                        <div style="text-align: center; margin-bottom: 2rem;">
                            <img src="{{ asset('images/logo_final.jpg') }}" alt="Logo" class="logo-img">
                            <h2 style="color: #000; font-size: 24px; margin-bottom: 0.5rem;">Welcome Back</h2>
                            <p style="color: #6b7280; font-size: 14px;">Sign in to access your healthcare portal</p>
                        </div>

                        @include('components.flowbite-alert')

                        <form action="{{ route('login.authenticate') }}" method="POST">
                            @csrf
                            
                            <div class="input-group">
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
                                    >
                                </div>
                                @error('username')
                                    <p style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="input-group">
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
                                    >
                                    <button type="button" class="password-toggle" onclick="togglePassword()">
                                        <i class="fa-solid fa-eye-slash" id="toggleIcon"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <p style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="remember-forgot">
                                <label>
                                    <input type="checkbox" name="remember">
                                    Remember me
                                </label>
                                <a href="#" class="forgot-link">Forgot password?</a>
                            </div>

                            <button type="submit" class="login-btn">
                                <i class="fa-solid fa-right-to-bracket" style="margin-right: 8px;"></i>
                                Sign In
                            </button>
                        </form>

                        <div style="text-align: center; margin-top: 1.5rem;">
                            <p style="color: #6b7280; font-size: 12px;">
                                Secure healthcare portal for authorized personnel only
                            </p>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

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