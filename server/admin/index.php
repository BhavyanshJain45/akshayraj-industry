<?php
/**
 * Admin Panel Login
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Security.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$auth = new Auth();

// Redirect to dashboard if already logged in
if ($auth->isLoggedIn()) {
    redirect('/server/admin/dashboard.php');
}

$error = '';
$csrfToken = Security::generateCSRFToken();

if (isPost()) {
    $email = getPost('email');
    $password = getPost('password');
    $token = getPost('csrf_token');

    if (!Security::verifyCSRFToken($token)) {
        $error = 'Security token invalid. Please try again.';
    } else {
        try {
            $auth->login($email, $password);
            redirect('/server/admin/dashboard.php');
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Akshayraj Industry CMS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #8B4513 0%, #D2691E 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
            max-width: 450px;
            width: 100%;
            padding: 50px 40px;
        }

        .brand {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo {
            font-size: 36px;
            color: #8B4513;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .brand-text {
            color: #666;
            font-size: 14px;
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #8B4513;
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }

        .forgot-password {
            text-align: right;
            margin-bottom: 20px;
        }

        .forgot-password a {
            color: #8B4513;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: #8B4513;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #6b3410;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(139, 69, 19, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .login-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #999;
            font-size: 12px;
        }

        .security-info {
            background: #f0f4f8;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 12px;
            color: #666;
            border-left: 4px solid #8B4513;
        }

        .security-info strong {
            color: #8B4513;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="brand">
            <div class="logo">🏭</div>
            <div class="brand-text">Akshayraj Industry CMS</div>
        </div>

        <h1>Admin Login</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="security-info">
            <strong>🔒 Security Note:</strong> This is a secure admin area. Never share your credentials.
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="admin@akshayraj.local" 
                    required 
                    autocomplete="email"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="••••••••" 
                    required 
                    autocomplete="current-password"
                >
            </div>

            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

            <button type="submit" class="btn-login">Login to Dashboard</button>
        </form>

        <div class="login-footer">
            <p>© <?php echo date('Y'); ?> Akshayraj Industry. All rights reserved.</p>
            <p style="margin-top: 10px; color: #ccc;">For support: info@akshayrajindustry.in</p>
        </div>
    </div>

    <script>
        // Prevent form submission if empty
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
            }
        });

        // Clear any browser cached data on page load (security)
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>
