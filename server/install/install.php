<?php
/**
 * Installation Wizard
 * Run this file once to set up the CMS
 * Then delete this file for security
 */

// Check if already installed
if (file_exists(__DIR__ . '/../.installed')) {
    die('CMS is already installed. Delete the install folder if you need to reinstall.');
}

// Set base path for includes
define('ROOT_PATH', dirname(__DIR__));

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// Step 1: Database Configuration
if ($step === 1) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $db_host = $_POST['db_host'] ?? '';
        $db_user = $_POST['db_user'] ?? '';
        $db_pass = $_POST['db_pass'] ?? '';
        $db_name = $_POST['db_name'] ?? '';

        // Test connection
        try {
            $pdo = new PDO(
                "mysql:host=$db_host",
                $db_user,
                $db_pass
            );

            // Try to create database
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
            $pdo->exec("USE `$db_name`");

            // Save to .env file
            $envContent = "APP_ENV=production\n";
            $envContent .= "DB_HOST=$db_host\n";
            $envContent .= "DB_USER=$db_user\n";
            $envContent .= "DB_PASS=$db_pass\n";
            $envContent .= "DB_NAME=$db_name\n";

            file_put_contents(ROOT_PATH . '/.env', $envContent);

            // Store in session for next step
            session_set_cookie_params(['secure' => true, 'httponly' => true]);
            session_start();
            $_SESSION['install_db_host'] = $db_host;
            $_SESSION['install_db_user'] = $db_user;
            $_SESSION['install_db_pass'] = $db_pass;
            $_SESSION['install_db_name'] = $db_name;
            $_SESSION['install_pdo'] = serialize($pdo);

            header('Location: ?step=2');
            exit;
        } catch (Exception $e) {
            $error = 'Database connection failed: ' . $e->getMessage();
        }
    }
}

// Step 2: Import Database Schema
elseif ($step === 2) {
    session_start();
    if (!isset($_SESSION['install_db_host'])) {
        header('Location: ?step=1');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $pdo = new PDO(
                "mysql:host={$_SESSION['install_db_host']};dbname={$_SESSION['install_db_name']}",
                $_SESSION['install_db_user'],
                $_SESSION['install_db_pass']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Read and execute SQL schema
            $schema = file_get_contents(__DIR__ . '/schema.sql');
            $statements = array_filter(array_map('trim', explode(';', $schema)));

            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }

            header('Location: ?step=3');
            exit;
        } catch (Exception $e) {
            $error = 'Database import failed: ' . $e->getMessage();
        }
    }
}

// Step 3: Create Admin User
elseif ($step === 3) {
    session_start();
    if (!isset($_SESSION['install_db_host'])) {
        header('Location: ?step=1');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $admin_email = trim($_POST['admin_email'] ?? '');
        $admin_password = $_POST['admin_password'] ?? '';
        $admin_password_confirm = $_POST['admin_password_confirm'] ?? '';

        $errors = [];

        if (empty($admin_email) || !filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }

        if (strlen($admin_password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }

        if ($admin_password !== $admin_password_confirm) {
            $errors[] = 'Passwords do not match';
        }

        if (empty($errors)) {
            try {
                $pdo = new PDO(
                    "mysql:host={$_SESSION['install_db_host']};dbname={$_SESSION['install_db_name']}",
                    $_SESSION['install_db_user'],
                    $_SESSION['install_db_pass']
                );
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $hashed_password = password_hash($admin_password, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt = $pdo->prepare('INSERT INTO admins (email, password) VALUES (?, ?)');
                $stmt->execute([$admin_email, $hashed_password]);

                // Create installed marker
                touch(ROOT_PATH . '/.installed');

                // Clean environment
                unset($_SESSION['install_db_host']);
                unset($_SESSION['install_db_user']);
                unset($_SESSION['install_db_pass']);
                unset($_SESSION['install_db_name']);

                header('Location: ?step=4');
                exit;
            } catch (Exception $e) {
                $error = 'Failed to create admin user: ' . $e->getMessage();
            }
        } else {
            $error = implode(', ', $errors);
        }
    }
}

// Step 4: Completion
elseif ($step === 4) {
    $success = 'Installation completed successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akshayraj Industry CMS - Installation</title>
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

        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #8B4513;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 14px;
        }

        .progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            font-size: 12px;
            color: #999;
        }

        .step.active {
            color: #8B4513;
            font-weight: bold;
        }

        .step.done {
            color: #28a745;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="number"]:focus,
        select:focus {
            outline: none;
            border-color: #8B4513;
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #8B4513;
            color: white;
        }

        .btn-primary:hover {
            background: #6b3410;
        }

        .btn-secondary {
            background: #e9ecef;
            color: #333;
        }

        .btn-secondary:hover {
            background: #dee2e6;
        }

        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 14px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .success-box {
            text-align: center;
            padding: 40px 20px;
        }

        .success-box .checkmark {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
        }

        .success-box h2 {
            color: #28a745;
            margin-bottom: 15px;
        }

        .success-box p {
            color: #666;
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .info-box {
            background: #f0f4f8;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #8B4513;
        }

        .info-box strong {
            color: #8B4513;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Akshayraj Industry CMS</h1>
            <p>Installation Wizard</p>
        </div>

        <div class="progress">
            <div class="step <?php echo $step >= 1 ? 'active' : ''; ?>">Step 1<br>Database</div>
            <div class="step <?php echo $step >= 2 ? 'active' : ''; ?>">Step 2<br>Schema</div>
            <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">Step 3<br>Admin</div>
            <div class="step <?php echo $step >= 4 ? 'active' : ''; ?>">Step 4<br>Done</div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Step 1: Database Configuration -->
        <?php if ($step === 1): ?>
            <form method="POST">
                <div class="info-box">
                    <strong>📝 Note:</strong> You can find these credentials in cPanel > Databases
                </div>

                <div class="form-group">
                    <label for="db_host">Database Host</label>
                    <input type="text" id="db_host" name="db_host" value="localhost" required>
                    <small style="color: #999;">Usually 'localhost' or your hosting provider's DB host</small>
                </div>

                <div class="form-group">
                    <label for="db_user">Database Username</label>
                    <input type="text" id="db_user" name="db_user" required>
                </div>

                <div class="form-group">
                    <label for="db_pass">Database Password</label>
                    <input type="password" id="db_pass" name="db_pass">
                </div>

                <div class="form-group">
                    <label for="db_name">Database Name</label>
                    <input type="text" id="db_name" name="db_name" value="akshayraj" required>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn-primary">Test & Continue →</button>
                </div>
            </form>
        <?php endif; ?>

        <!-- Step 2: Database Schema -->
        <?php if ($step === 2): ?>
            <div class="info-box">
                <strong>📊 About to import database tables:</strong> This will create all necessary tables for the CMS including products, contact messages, admin users, and settings.
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Tables to be created:</label>
                    <ul style="list-style: none; padding-left: 0; color: #666; font-size: 14px;">
                        <li>✓ admins</li>
                        <li>✓ products</li>
                        <li>✓ contact_messages</li>
                        <li>✓ settings</li>
                        <li>✓ email_templates</li>
                        <li>✓ activity_logs</li>
                    </ul>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn-primary">Import Tables & Continue →</button>
                </div>
            </form>
        <?php endif; ?>

        <!-- Step 3: Create Admin User -->
        <?php if ($step === 3): ?>
            <div class="info-box">
                <strong>👤 Create your admin account:</strong> This will be used to login to the admin panel.
            </div>

            <form method="POST">
                <div class="form-group">
                    <label for="admin_email">Admin Email</label>
                    <input type="email" id="admin_email" name="admin_email" required>
                    <small style="color: #999;">Use a secure email address you have access to</small>
                </div>

                <div class="form-group">
                    <label for="admin_password">Password</label>
                    <input type="password" id="admin_password" name="admin_password" minlength="8" required>
                    <small style="color: #999;">Minimum 8 characters, use strong password with numbers &amp; symbols</small>
                </div>

                <div class="form-group">
                    <label for="admin_password_confirm">Confirm Password</label>
                    <input type="password" id="admin_password_confirm" name="admin_password_confirm" minlength="8" required>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn-primary">Create Account & Complete →</button>
                </div>
            </form>
        <?php endif; ?>

        <!-- Step 4: Completion -->
        <?php if ($step === 4): ?>
            <div class="success-box">
                <div class="checkmark">✓</div>
                <h2>Installation Complete!</h2>
                <p>Your Akshayraj Industry CMS is now ready to use.</p>

                <div class="info-box" style="text-align: left; margin-top: 20px;">
                    <strong>Next Steps:</strong>
                    <ol style="margin-top: 10px; margin-left: 20px; color: #666; font-size: 14px; line-height: 1.8;">
                        <li>Delete the <code>install/</code> folder from your server</li>
                        <li>Login to <code>/admin/</code> with your credentials</li>
                        <li>Configure site settings in the admin panel</li>
                        <li>Start adding products and managing content</li>
                    </ol>
                </div>

                <div class="button-group" style="margin-top: 30px;">
                    <a href="/admin/" style="text-decoration: none;">
                        <button class="btn-primary" style="width: 100%;">Go to Admin Panel →</button>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
