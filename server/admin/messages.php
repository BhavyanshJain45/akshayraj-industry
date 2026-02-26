<?php
/**
 * Contact Messages Inbox
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Security.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();

// Handle POST actions
if (isPost()) {
    $action = getPost('action');
    $id = getPost('id');

    if ($action === 'mark_read' && !empty($id)) {
        $db->execute('UPDATE contact_messages SET is_read = 1 WHERE id = ?', [$id]);
        setFlash('success', 'Message marked as read');
        redirect('/server/admin/messages.php');
    } elseif ($action === 'mark_unread' && !empty($id)) {
        $db->execute('UPDATE contact_messages SET is_read = 0 WHERE id = ?', [$id]);
        setFlash('success', 'Message marked as unread');
        redirect('/server/admin/messages.php');
    } elseif ($action === 'delete' && !empty($id)) {
        $db->execute('DELETE FROM contact_messages WHERE id = ?', [$id]);
        setFlash('success', 'Message deleted');
        redirect('/server/admin/messages.php');
    } elseif ($action === 'reply_email') {
        $to = getPost('to_email');
        $subject = getPost('subject');
        $body = getPost('body');
        require_once __DIR__ . '/../includes/Mailer.php';
        $result = Mailer::sendAdminNotification($subject, $body, $to);
        if ($result) {
            setFlash('success', 'Reply sent to ' . htmlspecialchars($to));
        } else {
            setFlash('error', 'Failed to send reply.');
        }
        redirect('/server/admin/messages.php');
    } elseif ($action === 'compose_email') {
        $to = getPost('to_email');
        $subject = getPost('subject');
        $body = getPost('body');
        require_once __DIR__ . '/../includes/Mailer.php';
        $result = Mailer::sendAdminNotification($subject, $body, $to);
        if ($result) {
            setFlash('success', 'Email sent to ' . htmlspecialchars($to));
        } else {
            setFlash('error', 'Failed to send email.');
        }
        redirect('/server/admin/messages.php');
    }
}

// Get search/filter parameters
$search = Security::sanitizeString(getQuery('search', ''), 255);
$filter = getQuery('filter', 'all'); // all, contact, dealer, distributor, unread, read
$inquiry_filter = getQuery('inquiry_type', 'all'); // all, contact, dealer, distributor

// Build query
$query = 'SELECT * FROM contact_messages WHERE 1=1';
$params = [];

// Filter by inquiry type
if ($inquiry_filter === 'contact') {
    $query .= ' AND inquiry_type = "contact"';
} elseif ($inquiry_filter === 'dealer') {
    $query .= ' AND inquiry_type = "dealer"';
} elseif ($inquiry_filter === 'distributor') {
    $query .= ' AND inquiry_type = "distributor"';
}

// Filter by read status
if ($filter === 'unread') {
    $query .= ' AND is_read = 0';
} elseif ($filter === 'read') {
    $query .= ' AND is_read = 1';
}

// Search in name, email, phone, company_name, city
if (!empty($search)) {
    $query .= ' AND (name LIKE ? OR email LIKE ? OR phone LIKE ? OR company_name LIKE ? OR city LIKE ?)';
    $searchTerm = '%' . $search . '%';
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm];
}

$query .= ' ORDER BY created_at DESC LIMIT 100';

$messages = $db->fetchAll($query, $params);
$unreadCount = $db->fetchOne('SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0')['count'];
$contactCount = $db->fetchOne('SELECT COUNT(*) as count FROM contact_messages WHERE inquiry_type = "contact"')['count'];
$dealerCount = $db->fetchOne('SELECT COUNT(*) as count FROM contact_messages WHERE inquiry_type = "dealer"')['count'];
$distributorCount = $db->fetchOne('SELECT COUNT(*) as count FROM contact_messages WHERE inquiry_type = "distributor"')['count'];

// Get flash message
$flashMessage = getFlash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - CMS</title>
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
            margin-bottom: 10px;
        }

        .header-info {
            font-size: 14px;
            color: #999;
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

        /* Controls */
        .controls {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: grid;
            grid-template-columns: 1fr 200px;
            gap: 15px;
        }

        .search-form {
            display: flex;
            gap: 10px;
        }

        .search-form input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 13px;
        }

        .search-form input:focus {
            outline: none;
            border-color: #8B4513;
        }

        .search-form button {
            padding: 10px 15px;
            background: #8B4513;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }

        .search-form button:hover {
            background: #6b3410;
        }

        .filter-buttons {
            display: flex;
            gap: 5px;
        }

        .filter-btn {
            padding: 8px 15px;
            border: 1px solid #ddd;
            background: white;
            color: #333;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .filter-btn:hover {
            border-color: #8B4513;
            color: #8B4513;
        }

        .filter-btn.active {
            background: #8B4513;
            color: white;
            border-color: #8B4513;
        }

        /* Messages Table */
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
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

        tr {
            transition: all 0.3s;
        }

        tr:hover {
            background: #fafafa;
        }

        tr.unread {
            background: #faf8f3;
            font-weight: 600;
        }

        tr.unread td {
            color: #8B4513;
        }

        .message-preview {
            color: #666;
            font-size: 12px;
            margin-top: 3px;
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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

        .badge-read {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-view {
            background: #4ECDC4;
            color: white;
        }

        .btn-view:hover {
            background: #3ab5ae;
        }

        .btn-delete {
            background: #FF6B6B;
            color: white;
        }

        .btn-delete:hover {
            background: #ff5252;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .modal-close {
            color: #999;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }

        .modal-close:hover {
            color: #333;
        }

        .modal-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 20px;
        }

        .modal-body {
            margin-bottom: 20px;
        }

        .message-field {
            margin-bottom: 15px;
        }

        .message-field-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .message-field-value {
            color: #333;
            word-break: break-word;
        }

        .message-text {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-modal {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-modal-primary {
            background: #8B4513;
            color: white;
        }

        .btn-modal-primary:hover {
            background: #6b3410;
        }

        .btn-modal-secondary {
            background: #ddd;
            color: #333;
        }

        .btn-modal-secondary:hover {
            background: #ccc;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 15px;
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

            .controls {
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

            .controls {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
            }

            .btn-sm {
                width: 100%;
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
                <li><a href="/server/admin/messages.php" class="active">💬 Messages</a></li>
                <li><a href="/server/admin/settings.php">⚙️ Settings</a></li>
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
                <h1>💬 Contact Messages</h1>
                <div class="header-info">
                    Total: <?php echo count($messages); ?> | Unread: <strong><?php echo $unreadCount; ?></strong>
                </div>
            </div>

            <?php if (!empty($flashMessage)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($flashMessage); ?></div>
            <?php endif; ?>

            <!-- Search & Filter -->
            <div class="controls">
                <form method="GET" class="search-form">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search by name, email, phone, city, or company..." 
                        value="<?php echo htmlspecialchars($search); ?>"
                    >
                    <select name="inquiry_type" style="padding: 10px 15px; border: 1px solid #ddd; border-radius: 5px; font-size: 13px;">
                        <option value="all">All Types</option>
                        <option value="contact" <?php echo $inquiry_filter === 'contact' ? 'selected' : ''; ?>>Contact Inquiries (<?php echo $contactCount; ?>)</option>
                        <option value="dealer" <?php echo $inquiry_filter === 'dealer' ? 'selected' : ''; ?>>Dealer Inquiries (<?php echo $dealerCount; ?>)</option>
                        <option value="distributor" <?php echo $inquiry_filter === 'distributor' ? 'selected' : ''; ?>>Distributor Inquiries (<?php echo $distributorCount; ?>)</option>
                    </select>
                    <button type="submit">🔍 Search</button>
                </form>

                <div class="filter-buttons">
                    <a href="/server/admin/messages.php" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">All Status</a>
                    <a href="/server/admin/messages.php?filter=unread" class="filter-btn <?php echo $filter === 'unread' ? 'active' : ''; ?>">Unread (<?php echo $unreadCount; ?>)</a>
                    <a href="/server/admin/messages.php?filter=read" class="filter-btn <?php echo $filter === 'read' ? 'active' : ''; ?>">Read</a>
                </div>
            </div>

            <!-- Messages Table -->
            <?php if (count($messages) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name / Email</th>
                                <th>Phone</th>
                                <th>Type</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $msg): ?>
                                <tr class="<?php echo $msg['is_read'] == 0 ? 'unread' : ''; ?>">
                                    <td>
                                        <div><strong><?php echo htmlspecialchars($msg['name']); ?></strong></div>
                                        <div style="font-size: 12px; color: #999;"><?php echo htmlspecialchars($msg['email']); ?></div>
                                        <?php if (!empty($msg['company_name'])): ?>
                                            <div style="font-size: 11px; color: #999;">📦 <?php echo htmlspecialchars($msg['company_name']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($msg['phone']); ?></td>
                                    <td>
                                        <span class="badge" style="background: <?php echo $msg['inquiry_type'] === 'dealer' ? '#FFB800' : ($msg['inquiry_type'] === 'distributor' ? '#8B4513' : '#4ECDC4'); ?>; color: white;">
                                            <?php echo ucfirst($msg['inquiry_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="message-preview"><?php echo htmlspecialchars(substr($msg['message'], 0, 60)); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $msg['is_read'] == 0 ? 'badge-unread' : 'badge-read'; ?>">
                                            <?php echo $msg['is_read'] == 0 ? 'Unread' : 'Read'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($msg['created_at']); ?></td>
                                    <td>
                                        <div class="actions">
                                            <button class="btn-sm btn-view" onclick="viewMessage(<?php echo $msg['id']; ?>)">👁️ View</button>
                                            <form method="POST" style="display: inline; margin: 0;" onsubmit="return confirm('Delete this message?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                                <button type="submit" class="btn-sm btn-delete">🗑️ Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <h3>No messages found</h3>
                        <p><?php echo !empty($search) ? 'Try adjusting your search' : 'No contact messages yet'; ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Message Modal -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <div class="modal-header">
                <h2>Message Details</h2>
            </div>
            <div class="modal-body">
                <div class="message-field">
                    <div class="message-field-label">Inquiry Type</div>
                    <div class="message-field-value">
                        <span class="badge" id="modalType" style="padding: 5px 10px; background: #4ECDC4; color: white;"></span>
                    </div>
                </div>
                <div class="message-field">
                    <div class="message-field-label">From Name</div>
                    <div class="message-field-value" id="modalName"></div>
                </div>
                <div class="message-field" id="fieldCompany" style="display: none;">
                    <div class="message-field-label">Company Name</div>
                    <div class="message-field-value" id="modalCompany"></div>
                </div>
                <div class="message-field">
                    <div class="message-field-label">Email</div>
                    <div class="message-field-value" id="modalEmail"></div>
                </div>
                <div class="message-field">
                    <div class="message-field-label">Phone</div>
                    <div class="message-field-value" id="modalPhone"></div>
                </div>
                <div class="message-field" id="fieldCity" style="display: none;">
                    <div class="message-field-label">City</div>
                    <div class="message-field-value" id="modalCity"></div>
                </div>
                <div class="message-field" id="fieldState" style="display: none;">
                    <div class="message-field-label">State</div>
                    <div class="message-field-value" id="modalState"></div>
                </div>
                <div class="message-field" id="fieldExperience" style="display: none;">
                    <div class="message-field-label">Business Experience</div>
                    <div class="message-text" id="modalExperience"></div>
                </div>
                <div class="message-field">
                    <div class="message-field-label">Message</div>
                    <div class="message-text" id="modalMessage"></div>
                </div>
                <div class="message-field">
                    <div class="message-field-label">Received</div>
                    <div class="message-field-value" id="modalDate"></div>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn-modal btn-modal-secondary" onclick="closeModal()">Close</button>
                <form method="POST" style="margin: 0; display: inline;">
                    <input type="hidden" name="action" id="modalAction" value="">
                    <input type="hidden" name="id" id="modalId" value="">
                    <button type="submit" class="btn-modal btn-modal-primary" id="modalActionBtn">Mark as Read</button>
                </form>
                <!-- Reply to user form -->
                <form method="POST" style="margin: 0; display: inline-block; vertical-align: middle;">
                    <input type="hidden" name="action" value="reply_email">
                    <input type="hidden" name="to_email" id="replyToEmail" value="">
                    <input type="text" name="subject" placeholder="Subject" required style="margin: 5px 0; width: 90%;">
                    <textarea name="body" placeholder="Type your reply here..." required style="width: 90%; height: 60px; margin-bottom: 5px;"></textarea>
                    <button type="submit" class="btn-modal btn-modal-primary">Send Reply</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Compose Email Modal -->
    <div id="composeModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeComposeModal()">&times;</span>
            <div class="modal-header">
                <h2>Compose Email</h2>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="compose_email">
                <div class="message-field">
                    <div class="message-field-label">To</div>
                    <input type="email" name="to_email" placeholder="Recipient email" required style="width: 95%;">
                </div>
                <div class="message-field">
                    <div class="message-field-label">Subject</div>
                    <input type="text" name="subject" placeholder="Subject" required style="width: 95%;">
                </div>
                <div class="message-field">
                    <div class="message-field-label">Message</div>
                    <textarea name="body" placeholder="Type your message here..." required style="width: 95%; height: 80px;"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-modal btn-modal-secondary" onclick="closeComposeModal()">Cancel</button>
                    <button type="submit" class="btn-modal btn-modal-primary">Send Email</button>
                </div>
            </form>
        </div>
    </div>

    <button onclick="openComposeModal()" style="position:fixed;bottom:30px;right:30px;z-index:1000;padding:12px 24px;background:#4ECDC4;color:#fff;border:none;border-radius:50px;font-size:18px;box-shadow:0 2px 8px rgba(0,0,0,0.15);cursor:pointer;">✉️ Compose</button>
    <script>
                // Compose modal logic
                function openComposeModal() {
                    document.getElementById('composeModal').style.display = 'block';
                }
                function closeComposeModal() {
                    document.getElementById('composeModal').style.display = 'none';
                }
        const messages = <?php echo json_encode($messages); ?>;

        function viewMessage(id) {
            const msg = messages.find(m => m.id == id);
            if (!msg) return;

            // Show inquiry type
            const typeColors = {
                'contact': '#4ECDC4',
                'dealer': '#FFB800',
                'distributor': '#8B4513'
            };
            document.getElementById('modalType').textContent = msg.inquiry_type.charAt(0).toUpperCase() + msg.inquiry_type.slice(1);
            document.getElementById('modalType').style.background = typeColors[msg.inquiry_type] || '#4ECDC4';

            document.getElementById('modalName').textContent = msg.name;
            document.getElementById('modalEmail').textContent = msg.email;
            document.getElementById('modalPhone').textContent = msg.phone;
            document.getElementById('modalMessage').textContent = msg.message;
            document.getElementById('modalDate').textContent = new Date(msg.created_at).toLocaleString();
            document.getElementById('modalId').value = msg.id;
            document.getElementById('replyToEmail').value = msg.email;

            // Show/hide dealer-specific fields
            if (msg.inquiry_type === 'contact') {
                document.getElementById('fieldCompany').style.display = 'none';
                document.getElementById('fieldCity').style.display = 'none';
                document.getElementById('fieldState').style.display = 'none';
                document.getElementById('fieldExperience').style.display = 'none';
            } else {
                document.getElementById('fieldCompany').style.display = 'block';
                document.getElementById('fieldCity').style.display = 'block';
                document.getElementById('fieldState').style.display = 'block';
                document.getElementById('fieldExperience').style.display = 'block';
                
                document.getElementById('modalCompany').textContent = msg.company_name || 'N/A';
                document.getElementById('modalCity').textContent = msg.city || 'N/A';
                document.getElementById('modalState').textContent = msg.state || 'N/A';
                document.getElementById('modalExperience').textContent = msg.business_experience || 'N/A';
            }

            if (msg.is_read == 0) {
                document.getElementById('modalAction').value = 'mark_read';
                document.getElementById('modalActionBtn').textContent = '✓ Mark as Read';
            } else {
                document.getElementById('modalAction').value = 'mark_unread';
                document.getElementById('modalActionBtn').textContent = '↻ Mark as Unread';
            }

            document.getElementById('messageModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('messageModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('messageModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
