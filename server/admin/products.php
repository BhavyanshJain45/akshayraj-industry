<?php
/**
 * Product Management
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Security.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/ImageHandler.php';
require_once __DIR__ . '/../includes/helpers.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$action = getQuery('action', 'list');
$id = getQuery('id');

$error = '';
$success = '';

// Handle POST actions
if (isPost()) {
    $action = getPost('action');
    // Allow ID to be passed via POST (e.g., delete form)
    $postId = getPost('id');
    if (!empty($postId)) {
        $id = $postId;
    }

    if ($action === 'add' || $action === 'edit') {
        $title = Security::sanitizeString(getPost('title'), 255);
        $description = Security::sanitizeHTML(getPost('description'));
        $category = Security::sanitizeString(getPost('category'), 100);
        $capacity = Security::sanitizeString(getPost('capacity'), 100);
        $features = getPost('features');
        $price = floatval(getPost('price', 0));

        $errors = [];

        if (empty($title)) $errors[] = 'Title is required';
        if (empty($description)) $errors[] = 'Description is required';
        if (empty($category)) $errors[] = 'Category is required';

        if (empty($errors)) {
            try {
                // Handle image upload
                $imagePath = '';
                if (!empty($_FILES['image']['name'])) {
                    try {
                        // Call static method correctly
                        $uploaded = ImageHandler::uploadImage($_FILES['image']);
                        if (is_array($uploaded)) {
                            // Prefer path over URL to avoid length issues
                            $imagePath = $uploaded['path'] ?? $uploaded['url'] ?? '';
                            if (empty($imagePath)) {
                                $errors[] = 'Image upload failed: No valid path returned';
                            }
                        } else {
                            $errors[] = 'Image upload failed: Invalid response format';
                        }
                    } catch (Exception $e) {
                        $errors[] = 'Image upload failed: ' . $e->getMessage();
                        error_log('Product image upload error: ' . $e->getMessage());
                    }
                } elseif ($action === 'edit' && !empty($id)) {
                    // Keep existing image if no new upload - normalize stored value
                    $existing = $db->fetchOne('SELECT image_path FROM products WHERE id = ?', [$id]);
                    $imagePath = '';
                    if (!empty($existing['image_path'])) {
                        $val = $existing['image_path'];
                        $decoded = json_decode($val, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $imagePath = $decoded['url'] ?? $decoded['path'] ?? $val;
                        } else {
                            $un = @unserialize($val);
                            if ($un !== false && is_array($un)) {
                                $imagePath = $un['url'] ?? $un['path'] ?? $val;
                            } else {
                                $imagePath = $val;
                            }
                        }
                    }
                }

                if (empty($errors)) {
                    // Convert features array to JSON
                    $featuresJson = !empty($features) ? json_encode(array_filter(array_map('trim', preg_split('/,/', $features)))) : '[]';

                    try {
                        if ($action === 'add') {
                            $result = $db->execute(
                                'INSERT INTO products (title, description, image_path, category, capacity, features, price, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
                                [$title, $description, $imagePath, $category, $capacity, $featuresJson, $price]
                            );
                            if ($result) {
                                $success = 'Product added successfully!';
                            } else {
                                $error = 'Failed to add product to database';
                                error_log('Product insert failed for title: ' . $title);
                            }
                        } else {
                            $result = $db->execute(
                                'UPDATE products SET title = ?, description = ?, image_path = ?, category = ?, capacity = ?, features = ?, price = ?, updated_at = NOW() WHERE id = ?',
                                [$title, $description, $imagePath, $category, $capacity, $featuresJson, $price, $id]
                            );
                            if ($result) {
                                $success = 'Product updated successfully!';
                            } else {
                                $error = 'Failed to update product in database';
                                error_log('Product update failed for id: ' . $id);
                            }
                        }

                        // Redirect after success
                        if (!empty($success)) {
                            setFlash('success', $success);
                            redirect('/server/admin/products.php');
                        }
                    } catch (Exception $dbError) {
                        $error = 'Database error: ' . $dbError->getMessage();
                        error_log('Database error in product operation: ' . $dbError->getMessage());
                    }
                }
            } catch (Exception $e) {
                $error = 'Database error: ' . $e->getMessage();
                error_log('Product operation error: ' . $e->getMessage());
            }
        } else {
            $error = implode('<br>', $errors);
        }
    } elseif ($action === 'delete' && !empty($id)) {
        try {
            // Permanent delete: remove image file (if any) and delete DB row
            $existing = $db->fetchOne('SELECT image_path FROM products WHERE id = ?', [$id]);
            if ($existing && !empty($existing['image_path'])) {
                $img = $existing['image_path'];
                $path = '';
                if (is_string($img)) {
                    $decoded = json_decode($img, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $path = $decoded['path'] ?? $decoded['url'] ?? $decoded['filename'] ?? $img;
                    } else {
                        $un = @unserialize($img);
                        if ($un !== false && is_array($un)) {
                            $path = $un['path'] ?? $un['url'] ?? $un['filename'] ?? $img;
                        } else {
                            $path = $img;
                        }
                    }
                } elseif (is_array($img)) {
                    $path = $img['path'] ?? $img['url'] ?? $img['filename'] ?? '';
                }

                if (!empty($path)) {
                    // Normalize to server-relative uploads path
                    if (strpos($path, SITE_URL) === 0) {
                        $path = substr($path, strlen(SITE_URL));
                    }
                    if (strpos($path, 'http') === 0) {
                        $u = parse_url($path);
                        $path = $u['path'] ?? $path;
                    }
                    if ($path && $path[0] !== '/') {
                        $path = '/uploads/products/' . basename($path);
                    }
                    if (strpos($path, '/uploads') !== 0) {
                        $p = strpos($path, 'uploads');
                        if ($p !== false) $path = '/' . substr($path, $p);
                    }

                    try {
                        ImageHandler::deleteImage($path);
                    } catch (Exception $e) {
                        // Log but continue with delete
                        error_log('Failed to delete product image: ' . $e->getMessage());
                    }
                }
            }

            // Delete DB row permanently
            $db->execute('DELETE FROM products WHERE id = ?', [$id]);
            setFlash('success', 'Product permanently deleted!');
            redirect('/server/admin/products.php');
        } catch (Exception $e) {
            $error = 'Delete failed: ' . $e->getMessage();
        }
    }
}

// Get flash message
$flashMessage = getFlash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $action === 'list' ? 'Products' : (empty($id) ? 'Add Product' : 'Edit Product'); ?> - CMS</title>
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
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }

        .header h1 {
            font-size: 28px;
            color: #333;
        }

        .btn-add {
            padding: 10px 20px;
            background: #8B4513;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-add:hover {
            background: #6b3410;
            transform: translateY(-2px);
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

        /* Products Table */
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

        tr:hover {
            background: #fafafa;
        }

        .product-title {
            font-weight: 600;
            color: #333;
        }

        .product-title-small {
            font-size: 12px;
            color: #999;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-category {
            background: #e8f4f8;
            color: #0c5460;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-edit {
            background: #4ECDC4;
            color: white;
        }

        .btn-edit:hover {
            background: #3ab5ae;
        }

        .btn-delete {
            background: #FF6B6B;
            color: white;
        }

        .btn-delete:hover {
            background: #ff5252;
        }

        /* Form Styles */
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            max-width: 700px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        input[type="text"],
        input[type="number"],
        input[type="email"],
        input[type="file"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="email"]:focus,
        input[type="file"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #8B4513;
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 150px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .file-input-label {
            display: inline-block;
            padding: 10px 20px;
            background: #f0f0f0;
            border: 2px dashed #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-input-label:hover {
            border-color: #8B4513;
            background: #f9f8f7;
        }

        input[type="file"] {
            display: none;
        }

        .btn-submit {
            padding: 12px 30px;
            background: #8B4513;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background: #6b3410;
            transform: translateY(-2px);
        }

        .btn-back {
            padding: 12px 30px;
            background: #ddd;
            color: #333;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: #ccc;
        }

        .form-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
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

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
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
                <li><a href="/server/admin/products.php" class="active">📦 Products</a></li>
                <li><a href="/server/admin/messages.php">💬 Messages</a></li>
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
            <?php if ($action === 'list'): ?>
                <!-- Products List -->
                <div class="header">
                    <h1>📦 Products</h1>
                    <a href="?action=add" class="btn-add">➕ Add Product</a>
                </div>

                <?php if (!empty($flashMessage)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($flashMessage); ?></div>
                <?php endif; ?>

                <?php 
                $products = $db->fetchAll('SELECT * FROM products ORDER BY created_at DESC');
                if (count($products) > 0):
                ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Capacity</th>
                                    <th>Price</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <div class="product-title"><?php echo htmlspecialchars($product['title']); ?></div>
                                            <div class="product-title-small"><?php echo htmlspecialchars(substr($product['description'], 0, 50)); ?>...</div>
                                        </td>
                                        <td><span class="badge badge-category"><?php echo htmlspecialchars($product['category']); ?></span></td>
                                        <td><?php echo htmlspecialchars($product['capacity']); ?></td>
                                        <td>₹<?php echo number_format($product['price'], 2); ?></td>
                                        <td><?php echo formatDate($product['created_at']); ?></td>
                                        <td>
                                            <div class="actions">
                                                <a href="?action=edit&id=<?php echo $product['id']; ?>" class="btn-sm btn-edit">✏️ Edit</a>
                                                <form method="POST" style="display: inline; margin: 0;" onsubmit="return confirm('Delete this product?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
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
                            <div class="empty-state-icon">📦</div>
                            <h3>No products found</h3>
                            <p>Start by adding your first product</p>
                            <a href="?action=add" class="btn-add" style="margin-top: 20px; display: inline-block;">➕ Add Product</a>
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- Add/Edit Product Form -->
                <div class="header">
                    <h1><?php echo empty($id) ? '➕ Add Product' : '✏️ Edit Product'; ?></h1>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="form-container">
                    <input type="hidden" name="action" value="<?php echo empty($id) ? 'add' : 'edit'; ?>">
                    <?php if (!empty($id)): ?>
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                    <?php endif; ?>

                    <?php
                    $product = null;
                    if (!empty($id)) {
                        $product = $db->fetchOne('SELECT * FROM products WHERE id = ?', [$id]);
                        if (!$product) {
                            echo '<div class="alert alert-error">Product not found</div>';
                            exit;
                        }
                    }
                    ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="title">Product Title *</label>
                            <input 
                                type="text" 
                                id="title" 
                                name="title" 
                                placeholder="e.g., Industrial Steel Tank" 
                                value="<?php echo $product ? htmlspecialchars($product['title']) : ''; ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="category">Category *</label>
                            <input 
                                type="text" 
                                id="category" 
                                name="category" 
                                placeholder="e.g., Tanks" 
                                value="<?php echo $product ? htmlspecialchars($product['category']) : ''; ?>"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            placeholder="Detailed product description"
                            required
                        ><?php echo $product ? htmlspecialchars($product['description']) : ''; ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="capacity">Capacity / Specifications</label>
                            <input 
                                type="text" 
                                id="capacity" 
                                name="capacity" 
                                placeholder="e.g., 1000L" 
                                value="<?php echo $product ? htmlspecialchars($product['capacity']) : ''; ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="price">Price (₹)</label>
                            <input 
                                type="number" 
                                id="price" 
                                name="price" 
                                placeholder="0.00" 
                                step="0.01"
                                value="<?php echo $product ? htmlspecialchars($product['price']) : ''; ?>"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="features">Features (comma-separated)</label>
                        <input 
                            type="text" 
                            id="features" 
                            name="features" 
                            placeholder="e.g., Rust-proof, Durable, ..." 
                            value="<?php 
                                if ($product && !empty($product['features'])) {
                                    $features = json_decode($product['features'], true);
                                    echo htmlspecialchars(implode(', ', $features));
                                }
                            ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="image">Product Image (JPG, PNG, WebP - Max 5MB)</label>
                        <?php if ($product && !empty($product['image_path'])):
                            $curr = $product['image_path'];
                            $path = '';
                            if (is_string($curr)) {
                                $decoded = json_decode($curr, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                    $path = $decoded['filename'] ?? $decoded['path'] ?? $decoded['url'] ?? '';
                                } else {
                                    $un = @unserialize($curr);
                                    if ($un !== false && is_array($un)) {
                                        $path = $un['filename'] ?? $un['path'] ?? $un['url'] ?? '';
                                    } else {
                                        $path = $curr;
                                    }
                                }
                            } elseif (is_array($curr)) {
                                $path = $curr['filename'] ?? $curr['path'] ?? $curr['url'] ?? '';
                            }
                            $basename = $path ? basename($path) : '';
                        ?>
                            <p style="font-size: 12px; color: #666; margin-bottom: 10px;">
                                Current: <strong><?php echo htmlspecialchars($basename); ?></strong>
                            </p>
                        <?php endif; ?>
                        <label for="image" class="file-input-label">
                            📁 Choose Image
                        </label>
                        <div id="imageError" style="display: none; padding: 10px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-top: 8px; font-size: 13px;"></div>
                        <input 
                            type="file" 
                            id="image" 
                            name="image" 
                            accept="image/jpeg,image/png,image/webp"
                        >
                    </div>

                    <div class="form-buttons">
                        <button type="submit" class="btn-submit" id="submitBtn">
                            <?php echo empty($id) ? '✅ Create Product' : '💾 Update Product'; ?>
                        </button>
                        <a href="/server/admin/products.php" class="btn-back">← Back to List</a>
                    </div>
                </form>
            <?php endif; ?>
        </main>
    </div>

    <script>
        const MAX_FILE_SIZE = 10 * 1024 * 1024; // 5MB in bytes
        const imageInput = document.getElementById('image');
        const imageError = document.getElementById('imageError');
        const label = document.querySelector('.file-input-label');
        const submitBtn = document.getElementById('submitBtn');
        const form = document.querySelector('form');

        // Validate image on file selection
        imageInput?.addEventListener('change', function(e) {
            imageError.style.display = 'none';
            imageError.textContent = '';
            submitBtn.disabled = false;

            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                label.textContent = '✓ ' + file.name;

                // Check file size
                if (file.size > MAX_FILE_SIZE) {
                    const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                    const maxSizeMB = (MAX_FILE_SIZE / (1024 * 1024)).toFixed(2);
                    imageError.textContent = `❌ File size (${fileSizeMB} MB) exceeds maximum allowed (${maxSizeMB} MB). Please choose a smaller image.`;
                    imageError.style.display = 'block';
                    submitBtn.disabled = true;
                    return;
                }

                // Check file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    imageError.textContent = '❌ Invalid file type. Only JPG, PNG, and WebP images are allowed.';
                    imageError.style.display = 'block';
                    submitBtn.disabled = true;
                    return;
                }
            }
        });

        // Prevent form submission if file validation failed
        form?.addEventListener('submit', function(e) {
            if (imageInput.files && imageInput.files.length > 0) {
                const file = imageInput.files[0];
                if (file.size > MAX_FILE_SIZE) {
                    e.preventDefault();
                    const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                    const maxSizeMB = (MAX_FILE_SIZE / (1024 * 1024)).toFixed(2);
                    imageError.textContent = `❌ File size (${fileSizeMB} MB) exceeds maximum allowed (${maxSizeMB} MB). Please choose a smaller image.`;
                    imageError.style.display = 'block';
                    submitBtn.disabled = true;
                    return false;
                }
            }
        });
    </script>
</body>
</html>
