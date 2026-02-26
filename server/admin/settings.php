<?php
/**
 * Site Settings Management
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Security.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();

$error = '';
$success = '';

// Handle POST
if (isPost()) {
    $siteName = (string)Security::sanitizeString(getPost('site_name'), 255);
    $siteDescription = (string)Security::sanitizeString(getPost('site_description'), 500);
    $siteEmail = (string)Security::sanitizeEmail(getPost('site_email'));
    $sitePhone = (string)Security::sanitizePhone(getPost('site_phone'));
    $siteAddress = (string)Security::sanitizeString(getPost('site_address'), 255);
    $adminEmail = (string)Security::sanitizeEmail(getPost('admin_email'));

    $errors = [];
    if (empty($siteName)) $errors[] = 'Site name is required';
    if (empty($siteEmail)) $errors[] = 'Site email is required';
    if (empty($sitePhone)) $errors[] = 'Site phone is required';
    if (empty($adminEmail)) $errors[] = 'Admin email is required';

    if (empty($errors)) {
        try {
            // Update or insert settings
            $settings = [
                'site_name' => $siteName,
                'site_description' => $siteDescription,
                'site_email' => $siteEmail,
                'site_phone' => $sitePhone,
                'site_address' => $siteAddress,
                'admin_email' => $adminEmail
            ];

            foreach ($settings as $key => $value) {
                // Check if key exists
                $existing = $db->fetchOne('SELECT setting_id FROM settings WHERE setting_key = ?', [$key]);
                
                if ($existing) {
                    $db->execute('UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?', [$value, $key]);
                } else {
                    $db->execute('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)', [$key, $value]);
                }
            }

            $success = 'Settings updated successfully!';
            setFlash('success', $success);
            redirect('/server/admin/settings.php');
        } catch (Exception $e) {
            $error = 'Error updating settings: ' . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

// Load current settings
$settingsArray = [
    'site_name' => 'Akshayraj Industry',
    'site_description' => 'Manufacturing & Industrial Solutions',
    'site_email' => 'info@akshayrajindustry.in',
    'site_phone' => '+91 9877421070',
    'site_address' => 'Pune, Maharashtra',
    'admin_email' => 'admin@akshayrajindustry.in'
];

$allSettings = $db->fetchAll('SELECT setting_key, setting_value FROM settings');
foreach ($allSettings as $setting) {
    // Ensure value is a string, handle non-string types
    $value = $setting['setting_value'];
    if (is_array($value)) {
        $value = json_encode($value);
    } elseif (!is_string($value)) {
        $value = (string)$value;
    }
    $settingsArray[$setting['setting_key']] = $value;
}

// Get flash message
$flashMessage = getFlash('success');

// Helper function to safely display settings
function getSafeSetting($key, $default = '') {
    global $settingsArray;
    $value = $settingsArray[$key] ?? $default;
    
    // Convert arrays to JSON string
    if (is_array($value)) {
        $value = json_encode($value);
    }
    
    // Ensure scalar type
    if (!is_scalar($value)) {
        $value = (string)$value;
    }
    
    // Safely escape for HTML
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Settings - CMS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #8B4513 0%, #6b3410 100%);
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar .logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: block;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 5px;
            transition: all 0.3s;
            font-size: 14px;
            font-weight: 500;
        }

        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding-left: 20px;
        }

        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            border-left: 4px solid #FFD700;
            padding-left: 11px;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: 15px;
        }

        .admin-info {
            font-size: 12px;
            margin-bottom: 15px;
            color: rgba(255, 255, 255, 0.7);
        }

        .admin-info strong {
            display: block;
            color: white;
            font-size: 13px;
            margin-bottom: 3px;
        }

        .btn-logout {
            width: 100%;
            padding: 10px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 40px;
        }

        .header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }

        .header h1 {
            font-size: 28px;
            color: #333;
        }

        /* Alert Messages */
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Form Container */
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            max-width: 700px;
        }

        .form-section {
            margin-bottom: 35px;
        }

        .form-section h2 {
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        input,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: #8B4513;
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-help {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .btn-save {
            padding: 12px 30px;
            background: #8B4513;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-save:hover {
            background: #6b3410;
            transform: translateY(-2px);
        }

        /* Info Box */
        .info-box {
            background: #f0f4f8;
            padding: 20px;
            border-radius: 5px;
            border-left: 4px solid #8B4513;
            margin-bottom: 30px;
            font-size: 13px;
            color: #666;
        }

        .info-box strong {
            color: #8B4513;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar-footer {
                position: static;
                border-top: 1px solid rgba(255, 255, 255, 0.2);
                padding-top: 15px;
                margin-top: 15px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">🏭 CMS</div>

            <ul class="sidebar-menu">
                <li><a href="/server/admin/dashboard.php">📊 Dashboard</a></li>
                <li><a href="/server/admin/products.php">📦 Products</a></li>
                <li><a href="/server/admin/messages.php">💬 Messages</a></li>
                <li><a href="/server/admin/settings.php" class="active">⚙️ Settings</a></li>
                <li><a href="/server/admin/mailbox.php">📧 Mailbox</a></li>
            </ul>

            <div class="sidebar-footer">
                <div class="admin-info">
                    <strong><?php echo htmlspecialchars($_SESSION['admin_email'] ?? 'Admin'); ?></strong>
                    <span>Admin Account</span>
                </div>
                <form method="POST" action="/server/admin/logout.php" style="margin: 0;">
                    <button type="submit" class="btn-logout">🔓 Logout</button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1>⚙️ Site Settings</h1>
            </div>

            <?php if (!empty($flashMessage)): ?>
                <div class="alert alert-success"><?php echo is_array($flashMessage) ? htmlspecialchars(implode(' ', $flashMessage)) : htmlspecialchars($flashMessage); ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo is_array($error) ? htmlspecialchars(implode(' ', $error)) : $error; ?></div>
            <?php endif; ?>

            <div class="info-box">
                <strong>💡 Tip:</strong> These settings are used throughout your website and in automated emails. Keep them accurate and up-to-date.
            </div>

            <form method="POST" class="form-container">
                <!-- Site Information -->
                <div class="form-section">
                    <h2>Site Information</h2>

                    <div class="form-group">
                        <label for="site_name">Site Name *</label>
                        <input 
                            type="text" 
                            id="site_name" 
                            name="site_name" 
                            placeholder="Akshayraj Industry" 
                            value="<?php echo getSafeSetting('site_name'); ?>"
                            required
                        >
                        <p class="form-help">Your business or website name</p>
                    </div>

                    <div class="form-group">
                        <label for="site_description">Site Description</label>
                        <textarea 
                            id="site_description" 
                            name="site_description" 
                            placeholder="Brief description of your business"
                        ><?php echo getSafeSetting('site_description'); ?></textarea>
                        <p class="form-help">Used in meta descriptions and emails</p>
                    </div>

                    <div class="form-group">
                        <label for="site_address">Business Address</label>
                        <input 
                            type="text" 
                            id="site_address" 
                            name="site_address" 
                            placeholder="City, State, Country" 
                            value="<?php echo getSafeSetting('site_address'); ?>"
                        >
                        <p class="form-help">Your business location</p>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="form-section">
                    <h2>Contact Information</h2>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="site_email">Site Email *</label>
                            <input 
                                type="email" 
                                id="site_email" 
                                name="site_email" 
                                placeholder="info@example.com" 
                                value="<?php echo getSafeSetting('site_email'); ?>"
                                required
                            >
                            <p class="form-help">Main contact email displayed on site</p>
                        </div>

                        <div class="form-group">
                            <label for="site_phone">Business Phone *</label>
                            <input 
                                type="text" 
                                id="site_phone" 
                                name="site_phone" 
                                placeholder="+91 9877421070" 
                                value="<?php echo getSafeSetting('site_phone'); ?>"
                                required
                            >
                            <p class="form-help">Phone number for customer inquiries</p>
                        </div>
                    </div>
                </div>

                <!-- Admin Information -->
                <div class="form-section">
                    <h2>Admin Configuration</h2>

                    <div class="form-group">
                        <label for="admin_email">Admin Email *</label>
                        <input 
                            type="email" 
                            id="admin_email" 
                            name="admin_email" 
                            placeholder="admin@example.com" 
                            value="<?php echo getSafeSetting('admin_email'); ?>"
                            required
                        >
                        <p class="form-help">Receive notifications for new messages here</p>
                    </div>
                </div>

                <button type="submit" class="btn-save">💾 Save Settings</button>
            </form>
        </main>
    </div>
</body>
</html>
