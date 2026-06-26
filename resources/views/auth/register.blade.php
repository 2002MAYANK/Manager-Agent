<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - AI Manager Assistant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue: #2563EB;
            --blue-hover: #1d4ed8;
            --bg-color: #f8fafc;
            --text-color: #0f172a;
            --card-bg: #ffffff;
            --gray-text: #64748b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-card {
            background-color: var(--card-bg);
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            display: flex;
            width: 100%;
            max-width: 1100px;
            min-height: 600px;
        }

        .auth-left {
            background: linear-gradient(135deg, #e0e7ff 0%, #dbeafe 100%);
            padding: 60px 40px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .auth-right {
            padding: 40px 80px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-logo {
            font-size: 28px;
            font-weight: 700;
            color: var(--blue);
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
        }

        .auth-logo i {
            font-size: 32px;
        }

        .auth-description h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 16px;
            color: #1e3a8a;
        }

        .auth-description p {
            font-size: 16px;
            color: #3b82f6;
            line-height: 1.6;
            max-width: 400px;
        }

        .auth-illustration {
            margin-top: 40px;
            display: flex;
            gap: 20px;
        }

        .ill-card {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 20px;
            flex: 1;
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.05);
        }

        .ill-card-title {
            font-size: 12px;
            font-weight: 600;
            color: var(--blue);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .ill-card-value {
            font-size: 24px;
            font-weight: 700;
            color: #1e3a8a;
        }

        .auth-form-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-color);
        }

        .auth-form-subtitle {
            font-size: 15px;
            color: var(--gray-text);
            margin-bottom: 32px;
        }

        .form-label {
            font-weight: 500;
            font-size: 13.5px;
            color: #334155;
            margin-bottom: 6px;
        }

        .form-control {
            border-radius: 12px;
            padding: 10px 16px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            font-size: 14.5px;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--blue);
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--blue), #3b82f6);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            font-size: 16px;
            width: 100%;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
            transition: all 0.2s;
            margin-top: 10px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.3);
            background: linear-gradient(135deg, var(--blue-hover), var(--blue));
        }

        .auth-link {
            color: var(--blue);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .auth-link:hover {
            color: var(--blue-hover);
            text-decoration: underline;
        }

        @media (max-width: 992px) {
            .auth-left {
                display: none;
            }
            .auth-right {
                padding: 40px;
            }
        }
    </style>
</head>
<body>

    <div class="auth-card">
        <div class="auth-left">
            <div>
                <div class="auth-logo">
                    <i class="bi bi-robot"></i>
                    AI Manager
                </div>
                <div class="auth-description">
                    <h1>Join the future of<br>Management</h1>
                    <p>Create your account and gain access to advanced AI-powered analytics and team insights today.</p>
                </div>
            </div>
            
            <div class="auth-illustration">
                <div class="ill-card">
                    <div class="ill-card-title">Setup Time</div>
                    <div class="ill-card-value">< 2 min <i class="bi bi-lightning-charge text-warning" style="font-size:16px;"></i></div>
                </div>
                <div class="ill-card">
                    <div class="ill-card-title">AI Ready</div>
                    <div class="ill-card-value">100% <i class="bi bi-cpu text-primary" style="font-size:16px;"></i></div>
                </div>
            </div>
        </div>
        <div class="auth-right">
            <h2 class="auth-form-title">Create Account</h2>
            <p class="auth-form-subtitle">Enter your details below to get started.</p>

            @if ($errors->any())
                <div class="alert alert-danger" style="border-radius:12px; font-size:13px; padding:10px 14px; margin-bottom:20px;">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="John Doe">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required placeholder="name@company.com">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="••••••••">
                </div>

                <button type="submit" class="btn btn-primary mb-4">Create Account</button>

                <div class="text-center" style="font-size:14px; color:var(--gray-text);">
                    Already have an account? <a href="{{ route('login') }}" class="auth-link">Sign in</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
