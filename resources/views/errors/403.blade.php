<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Forbidden</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            text-align: center;
            background: white;
            border-radius: 20px;
            padding: 3rem 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 2rem;
        }
        .error-code {
            font-size: 6rem;
            font-weight: 800;
            color: #e53e3e;
            line-height: 1;
            margin-bottom: 1rem;
        }
        .error-title {
            font-size: 2rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 1rem;
        }
        .error-message {
            color: #718096;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .error-details {
            background: #f7fafc;
            border-left: 4px solid #e53e3e;
            padding: 1rem;
            margin: 1rem 0;
            text-align: left;
            border-radius: 0 8px 8px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #e2e8f0;
            color: #4a5568;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 500;
            margin: 0 4px;
        }
        .role-badge.bhw { background: #bee3f8; color: #2b6cb0; }
        .role-badge.midwife { background: #c6f6d5; color: #276749; }
        .role-badge.admin { background: #fed7d7; color: #c53030; }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">403</div>
        <div class="error-title">Access Forbidden</div>
        <div class="error-message">
            You don't have permission to access this resource.
        </div>
        
        @if(auth()->check())
            <div class="error-details">
                <strong>Your Role:</strong> 
                <span class="role-badge {{ auth()->user()->role }}">
                    {{ ucfirst(auth()->user()->role) }}
                </span>
                <br><br>
                <strong>Issue:</strong> This page is restricted to specific user roles only.
            </div>
            
            <p style="color: #718096; font-size: 0.9rem; margin: 1.5rem 0;">
                If you believe this is an error, please contact your system administrator.
            </p>
            
            <a href="{{ auth()->user()->role === 'midwife' ? route('midwife.dashboard') : (auth()->user()->role === 'bhw' ? route('bhw.dashboard') : route('admin.dashboard')) }}" class="btn">
                <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>
                Back to Dashboard
            </a>
        @else
            <div class="error-details">
                <strong>Authentication Required:</strong> You must be logged in to access this resource.
            </div>
            
            <a href="{{ route('login') }}" class="btn">
                <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i>
                Login
            </a>
        @endif
    </div>

    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js"></script>
</body>
</html>