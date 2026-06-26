<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AI Manager Assistant</title>
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
            padding: 60px 80px;
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
            font-size: 14px;
            color: #334155;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            font-size: 15px;
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
                    <h1>Smarter Management,<br>Powered by AI</h1>
                    <p>Unlock insights, optimize resources, and lead your team with the intelligence of our integrated AI platform.</p>
                </div>
            </div>
            
            <div class="auth-illustration">
                <div class="ill-card">
                    <div class="ill-card-title">Team Efficiency</div>
                    <div class="ill-card-value">94% <i class="bi bi-arrow-up-right text-success" style="font-size:16px;"></i></div>
                </div>
                <div class="ill-card">
                    <div class="ill-card-title">Tasks Resolved</div>
                    <div class="ill-card-value">12.5k <i class="bi bi-check2-circle text-primary" style="font-size:16px;"></i></div>
                </div>
            </div>
        </div>
        <div class="auth-right">
            <h2 class="auth-form-title">Welcome back</h2>
            <p class="auth-form-subtitle">Please enter your details to sign in.</p>

            @if ($errors->any())
                <div class="alert alert-danger" style="border-radius:12px; font-size:14px; padding:12px 16px; margin-bottom:24px;">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@company.com">
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember" style="font-size:14px; color:#64748b;">
                            Remember me
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mb-4">Sign in</button>

                <div class="text-center" style="font-size:14px; color:var(--gray-text);">
                    Don't have an account? <a href="{{ route('register') }}" class="auth-link">Create Account</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
