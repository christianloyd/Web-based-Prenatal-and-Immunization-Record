<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Healthcare Portal - Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #D4A373;
            --primary-dark: #B8956A;
            --card-bg: rgba(255, 255, 255, 0.92);
            --card-border: rgba(255, 255, 255, 0.65);
            --text-muted: rgba(255, 255, 255, 0.85);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            width: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #fff;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding: 48px 24px;
            background-color: #1f2937;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: url('{{ asset('images/bg.jpg') }}') center / cover no-repeat;
            z-index: -2;
        }

        body::after {
            content: "";
            position: fixed;
            inset: 0;
            background: linear-gradient(120deg, rgba(212, 163, 115, 0.78), rgba(236, 185, 158, 0.68));
            z-index: -1;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
        }

        .login-wrapper::before,
        .login-wrapper::after {
            content: "";
            position: absolute;
            border-radius: 32px;
            background: rgba(255, 255, 255, 0.12);
            filter: blur(0.5px);
            z-index: -1;
        }

        .login-wrapper::before {
            width: 340px;
            height: 340px;
            top: -120px;
            right: -120px;
            opacity: 0.55;
        }

        .login-wrapper::after {
            width: 260px;
            height: 260px;
            bottom: -110px;
            left: -110px;
            opacity: 0.35;
        }

        .brand {
            margin-bottom: 28px;
        }

        .brand-logo {
            width: 86px;
            height: 86px;
            border-radius: 50%;
            border: 5px solid var(--primary);
            object-fit: cover;
            margin-bottom: 16px;
            background: rgba(255, 255, 255, 0.4);
        }

        .brand h1 {
            font-size: 30px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .brand p {
            margin-top: 6px;
            font-size: 14px;
            color: var(--text-muted);
        }

        .login-card {
            width: 100%;
            background: var(--card-bg);
            border-radius: 22px;
            padding: 32px 28px;
            box-shadow: 0 24px 60px rgba(62, 36, 8, 0.28);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(8px);
        }

        .card-header {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
            color: #1f1f1f;
        }

        .card-header h2 {
            font-size: 20px;
            font-weight: 600;
        }

        .card-header .support {
            font-size: 13px;
            color: var(--primary);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .guidance-text {
            font-size: 13px;
            color: #5f5f5f;
            margin-bottom: 22px;
        }

        .input-group {
            margin-bottom: 18px;
            text-align: left;
        }

        .input-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 500;
            color: #3a3a3a;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 14px;
        }

        .form-input {
            width: 100%;
            padding: 12px 44px;
            font-size: 15px;
            border-radius: 12px;
            border: 1px solid rgba(209, 213, 219, 0.95);
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(212, 163, 115, 0.2);
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            font-size: 15px;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            font-size: 13px;
        }

        .remember-forgot label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #4b5563;
            cursor: pointer;
        }

        .remember-forgot input[type="checkbox"] {
            accent-color: var(--primary);
        }

        .forgot-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
        }

        .login-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 16px 30px rgba(62, 36, 8, 0.25);
        }

        .portal-note {
            margin-top: 22px;
            font-size: 12px;
            color: #4b5563;
        }

        footer {
            margin-top: 36px;
            font-size: 12px;
            color: var(--text-muted);
        }

        footer span {
            display: block;
            margin-top: 6px;
            font-size: 11px;
            letter-spacing: 0.8px;
        }

        @media (max-width: 640px) {
            body {
                padding: 32px 16px;
            }

            .login-card {
                padding: 28px 22px;
            }

            .login-wrapper::before,
            .login-wrapper::after {
                display: none;
            }
        }
    </style>
</head>
<body>
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
    <div class="login-wrapper">
        <div class="brand">
            <img src="{{ asset('images/logo_final.jpg') }}" alt="HealthCare Logo" class="brand-logo">
            <h1>HealthCare Portal</h1>
            <p>Prenatal &amp; Immunization Management System</p>
        </div>

        <div class="login-card">
            <div class="card-header">
                <h2>Authorized Access</h2>
                <span class="support"><i class="fa-solid fa-shield-heart"></i> Secure Area</span>
            </div>

            <p class="guidance-text">Sign in with your assigned credentials to manage prenatal and immunization records.</p>

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
                        @error('username')
                            <p style="color: #ef4444; font-size: 12px; margin-top: 6px;">{{ $message }}</p>
                        @enderror
                    </div>
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
                        @error('password')
                            <p style="color: #ef4444; font-size: 12px; margin-top: 6px;">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="remember-forgot">
                    <label>
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Sign In
                </button>
            </form>

            <p class="portal-note">Only authorized barangay health workers and midwives have access to this portal.</p>
        </div>

        <footer>
            Preventive Healthcare Management System &copy; {{ date('Y') }}
            <span>SECURE • RELIABLE • PROFESSIONAL</span>
        </footer>
    </div>
</body>
</html>