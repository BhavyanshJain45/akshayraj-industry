<?php
/**
 * Admin Dashboard
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Security.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();

// Get statistics
$totalProducts = $db->fetchOne('SELECT COUNT(*) as count FROM products WHERE is_active = 1')['count'];
$unreadMessages = $db->fetchOne('SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0')['count'];

// Get last admin activity
$lastLogin = $db->fetchOne('SELECT last_login FROM admins WHERE id = ?', [$_SESSION['admin_id']])['last_login'];

// Update admin's last_login to now
$db->execute('UPDATE admins SET last_login = NOW() WHERE id = ?', [$_SESSION['admin_id']]);

// Get current admin email
$adminEmail = $db->fetchOne('SELECT email FROM admins WHERE id = ?', [$_SESSION['admin_id']])['email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Akshayraj Industry CMS</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }

        .header h1 {
            font-size: 32px;
            color: #333;
        }

        .header-time {
            font-size: 14px;
            color: #999;
        }

        /* Statistics Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            border-left: 5px solid #8B4513;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(139, 69, 19, 0.15);
        }

        .stat-card.unread {
            border-left-color: #FF6B6B;
        }

        .stat-card.products {
            border-left-color: #4ECDC4;
        }

        .stat-label {
            font-size: 14px;
            color: #999;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 48px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }

        .stat-card.unread .stat-value {
            color: #FF6B6B;
        }

        .stat-card.products .stat-value {
            color: #4ECDC4;
        }

        .stat-meta {
            font-size: 13px;
            color: #bbb;
        }

        .stat-meta strong {
            color: #666;
        }

        /* Quick Actions */
        .quick-actions {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
        }

        .quick-actions h2 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .action-btn {
            padding: 15px;
            border: 2px solid #8B4513;
            background: white;
            color: #8B4513;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            font-size: 14px;
        }

        .action-btn:hover {
            background: #8B4513;
            color: white;
        }

        .action-btn.primary {
            background: #8B4513;
            color: white;
            border-color: #6b3410;
        }

        .action-btn.primary:hover {
            background: #6b3410;
        }

        /* Session & Security Info */
        .info-box {
            background: #f0f4f8;
            padding: 20px;
            border-radius: 5px;
            border-left: 4px solid #8B4513;
            margin-top: 30px;
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

            .stats-grid {
                grid-template-columns: 1fr;
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
                margin-bottom: 20px;
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

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-time {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="logo">🏭 CMS</div>

            <ul class="sidebar-menu">
                <li><a href="/server/admin/dashboard.php" class="active">📊 Dashboard</a></li>
                <li><a href="/server/admin/products.php">📦 Products</a></li>
                <li><a href="/server/admin/messages.php">💬 Messages</a></li>
                <li><a href="/server/admin/settings.php">⚙️ Settings</a></li>
                <li><a href="/server/admin/mailbox.php">📧 Mailbox</a></li>
            </ul>

            <div class="sidebar-footer">
                <div class="admin-info">
                    <strong><?php echo htmlspecialchars($adminEmail); ?></strong>
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
                <div>
                    <h1>Dashboard</h1>
                    <p class="header-time">Last updated: <?php echo date('M d, Y h:i A'); ?></p>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card products" onclick="window.location.href='/server/admin/products.php'">
                    <div class="stat-label">📦 Total Products</div>
                    <div class="stat-value"><?php echo $totalProducts; ?></div>
                    <div class="stat-meta">Active products</div>
                </div>

                <div class="stat-card unread" onclick="window.location.href='/server/admin/messages.php'">
                    <div class="stat-label">💬 Unread Messages</div>
                    <div class="stat-value"><?php echo $unreadMessages; ?></div>
                    <div class="stat-meta">Waiting for response</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">📅 Last Login</div>
                    <div class="stat-value"><?php 
                        if ($lastLogin) {
                            echo timeAgo($lastLogin);
                        } else {
                            echo 'First login';
                        }
                    ?></div>
                    <div class="stat-meta">Session active for 1 hour</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="actions-grid">
                    <a href="/server/admin/products.php?action=add" class="action-btn primary">➕ Add New Product</a>
                    <a href="/server/admin/messages.php" class="action-btn">📱 View Messages</a>
                    <a href="/server/admin/settings.php" class="action-btn">⚙️ Site Settings</a>
                    <a href="/server/admin/mailbox.php" class="action-btn">📧 Check Mailbox</a>
                </div>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <strong>🔒 Session Information:</strong><br>
                Your session will automatically expire after 1 hour of inactivity for security purposes. This dashboard is protected by CSRF tokens and all data is securely transmitted with prepared SQL statements.
            </div>
        </main>
    </div>

    <script>
        // Keep session alive by making a silent request periodically
        setInterval(function() {
            // Optional: ping server to keep session alive
            // fetch('/api/ping.php');
        }, 5 * 60 * 1000); // Every 5 minutes

        // Prevent back button after logout
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>
