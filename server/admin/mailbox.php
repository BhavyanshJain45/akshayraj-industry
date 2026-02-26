<?php
/**
 * Email Mailbox Viewer (IMAP)
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Security.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$auth = new Auth();
$auth->requireLogin();

$error = '';
$emails = [];
$imapAvailable = extension_loaded('imap');

// Try to fetch emails from IMAP
if ($imapAvailable && defined('IMAP_HOST') && defined('IMAP_USER') && defined('IMAP_PASS')) {
    try {
        // Standardized IMAP config: only hostname in config.php, build string here
        $imapHost = defined('IMAP_HOST') ? IMAP_HOST : '';
        $imapPort = defined('IMAP_PORT') ? IMAP_PORT : 993;
        $imapFlags = defined('IMAP_FLAGS') ? IMAP_FLAGS : '/imap/ssl/novalidate-cert';
        // If IMAP_HOST is already a full string (legacy), use as is, else build
        if (strpos($imapHost, '{') === 0) {
            $imapPath = $imapHost;
        } else {
            $imapPath = '{' . $imapHost . ':' . $imapPort . $imapFlags . '}INBOX';
        }
        $mailbox = @imap_open($imapPath, IMAP_USER, IMAP_PASS);

        if ($mailbox) {
            $emailCount = imap_num_msg($mailbox);
            $limit = min(10, $emailCount);
            if ($emailCount > 0) {
                $overview = imap_fetch_overview($mailbox, ($emailCount - $limit + 1) . ":$emailCount", 0);
                foreach (array_reverse($overview) as $msg) {
                    $body = imap_fetchbody($mailbox, $msg->msgno, 1);
                    $emails[] = [
                        'id' => $msg->msgno,
                        'from' => isset($msg->from) ? $msg->from : '',
                        'from_email' => isset($msg->from) ? $msg->from : '',
                        'subject' => isset($msg->subject) ? $msg->subject : '',
                        'date' => isset($msg->udate) ? date('M d, Y H:i', $msg->udate) : '',
                        'body' => substr(trim(strip_tags($body)), 0, 200),
                        'unread' => (isset($msg->seen) && !$msg->seen)
                    ];
                }
            }
            imap_close($mailbox);
        } else {
            $error = 'Failed to connect to IMAP mailbox. Check your IMAP settings in config.php.';
            $imapLastError = imap_last_error();
            if ($imapLastError) {
                $error .= ' IMAP: ' . htmlspecialchars($imapLastError);
            }
            $imapErrors = imap_errors();
            if ($imapErrors) {
                $error .= ' Details: ' . htmlspecialchars(implode('; ', $imapErrors));
            }
        }
    } catch (Throwable $e) {
        $error = 'IMAP Error: ' . $e->getMessage();
    }
} elseif (!$imapAvailable) {
    $error = 'IMAP extension is not enabled on this server. Contact your host to enable it.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Mailbox - CMS</title>
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

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        /* Info Box */
        .info-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            max-width: 700px;
        }

        .info-box h2 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #333;
        }

        .info-box p {
            line-height: 1.6;
            margin-bottom: 15px;
            color: #666;
        }

        .code-block {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            overflow-x: auto;
            margin: 15px 0;
        }

        .setup-steps {
            margin-top: 20px;
        }

        .setup-steps h3 {
            font-size: 14px;
            margin: 15px 0 10px 0;
            color: #333;
        }

        .setup-steps ol {
            margin-left: 20px;
            color: #666;
        }

        .setup-steps li {
            margin-bottom: 8px;
        }

        /* Emails Table */
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f8f8;
            border-bottom: 2px solid #ddd;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #666;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #fafafa;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-unread {
            background: #FF6B6B;
            color: white;
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

            table {
                font-size: 12px;
            }

            th, td {
                padding: 10px;
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
                <li><a href="/server/admin/settings.php">⚙️ Settings</a></li>
                <li><a href="/server/admin/mailbox.php" class="active">📧 Mailbox</a></li>
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
                <h1>📧 Email Mailbox</h1>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">⚠️ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (!$imapAvailable): ?>
                <div class="info-box">
                    <h2>📧 IMAP Mailbox Viewer</h2>
                    <p>This feature allows you to view emails from your business email inbox directly in the CMS admin panel.</p>

                    <div class="alert alert-info">
                        <strong>Note:</strong> The IMAP extension is not enabled on your server. To enable this feature, contact your hosting provider.
                    </div>

                    <div class="setup-steps">
                        <h3>What is IMAP?</h3>
                        <p>IMAP (Internet Message Access Protocol) is a protocol that allows you to access emails stored on a mail server. With it enabled, you can:</p>
                        <ol>
                            <li>View emails from your business mailbox</li>
                            <li>Mark emails as read/unread</li>
                            <li>Manage your inbox directly from the CMS</li>
                        </ol>

                        <h3>How to Enable IMAP</h3>
                        <p><strong>For Hostinger:</strong></p>
                        <ol>
                            <li>Log in to Hostinger Control Panel</li>
                            <li>Go to PHP Configuration / PHP Options</li>
                            <li>Search for "IMAP" extension</li>
                            <li>Enable it and save</li>
                            <li>Wait 5-10 minutes for changes to take effect</li>
                        </ol>

                        <h3>IMAP Configuration</h3>
                        <p>Once IMAP is enabled, you can configure your email account in <code>server/includes/config.php</code>:</p>
                        <div class="code-block">
define('IMAP_HOST', 'imap.hostinger.com');<br>
define('IMAP_USER', 'your-email@example.com');<br>
define('IMAP_PASS', 'your-email-password');<br>
define('IMAP_PORT', '993');
                        </div>

                        <h3>Security Note</h3>
                        <p>IMAP passwords are stored in plain text in config.php. For production, consider:</p>
                        <ol>
                            <li>Using environment variables (.env file)</li>
                            <li>Creating a separate email account with limited access</li>
                            <li>Restricting file access via .htaccess</li>
                        </ol>
                    </div>
                </div>
            <?php elseif (count($emails) === 0 && empty($error)): ?>
                <div class="alert alert-info">
                    📭 No emails found in your mailbox.
                </div>
            <?php elseif (count($emails) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>From</th>
                                <th>Subject</th>
                                <th>Preview</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($emails as $email): ?>
                                <tr class="<?php echo $email['unread'] ? 'unread' : ''; ?>">
                                    <td>
                                        <div><strong><?php echo htmlspecialchars($email['from']); ?></strong></div>
                                        <div style="font-size: 12px; color: #999;"><?php echo htmlspecialchars($email['from_email']); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($email['subject']); ?></td>
                                    <td><?php echo htmlspecialchars($email['body']); ?></td>
                                    <td style="font-size: 12px; color: #999;"><?php echo $email['date']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
