<?php
/**
 * Products API Endpoint
 * GET /api/products.php - Get all products or single product
 * POST /api/products.php - Create product (admin only)
 * PUT /api/products.php - Update product (admin only)
 * DELETE /api/products.php?id=X - Delete product (admin only)
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Security.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/ImageHandler.php';
require_once __DIR__ . '/../includes/helpers.php';

$db = Database::getInstance();
$method = $_SERVER['REQUEST_METHOD'];

// Rate limiting for API
$clientIp = Security::getClientIP();
if (!Security::rateLimit('api:products:' . $clientIp, 100, 3600)) {
    jsonError('Rate limit exceeded. Max 100 requests per hour.', 429);
}

try {
    if ($method === 'GET') {
        handleGetRequest();
    } elseif ($method === 'POST') {
        handlePostRequest();
    } elseif ($method === 'PUT') {
        handlePutRequest();
    } elseif ($method === 'DELETE') {
        handleDeleteRequest();
    } else {
        jsonError('Method not allowed', 405);
    }
} catch (Exception $e) {
    Security::logSecurity('API_ERROR', 'Products API: ' . $e->getMessage());
    jsonError('Internal server error: ' . $e->getMessage(), 500);
}

/**
 * Handle GET requests
 * /api/products.php - Get all products
 * /api/products.php?id=1 - Get single product
 * /api/products.php?category=tanks - Filter by category
 * /api/products.php?limit=10&offset=0 - Pagination
 */
function handleGetRequest() {
    global $db;

    $id = getQuery('id');
    $category = getQuery('category');
    $limit = intval(getQuery('limit', 50));
    $offset = intval(getQuery('offset', 0));
    $search = Security::sanitizeString(getQuery('search', ''), 255);

    // Validate limits
    $limit = min($limit, 100);
    $offset = max($offset, 0);

    // Build query
    $query = 'SELECT id, title, description, image_path, category, capacity, features, price, is_active, created_at, updated_at FROM products WHERE is_active = 1';
    $params = [];

    // Single product by ID
    if (!empty($id)) {
        $id = intval($id);
        $product = $db->fetchOne('SELECT id, title, description, image_path, category, capacity, features, price, is_active, created_at, updated_at FROM products WHERE id = ? AND is_active = 1', [$id]);
        
        if (!$product) {
            jsonError('Product not found', 404);
        }

        // Decode features JSON
        if (!empty($product['features'])) {
            $product['features'] = json_decode($product['features'], true);
        }

        // Normalize image_path to a string URL/path
        if (!empty($product['image_path'])) {
            if (is_string($product['image_path'])) {
                $val = $product['image_path'];
                $decoded = json_decode($val, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $product['image_path'] = $decoded['url'] ?? $decoded['path'] ?? $val;
                } else {
                    $un = @unserialize($val);
                    if ($un !== false && is_array($un)) {
                        $product['image_path'] = $un['url'] ?? $un['path'] ?? $val;
                    } else {
                        $product['image_path'] = $val;
                    }
                }
            } elseif (is_array($product['image_path'])) {
                $product['image_path'] = $product['image_path']['url'] ?? $product['image_path']['path'] ?? '';
            }
        }

        return jsonSuccess('Product retrieved successfully', $product);
    }

    // Filter by category
    if (!empty($category)) {
        $category = Security::sanitizeString($category, 100);
        $query .= ' AND category = ?';
        $params[] = $category;
    }

    // Search by title or description
    if (!empty($search)) {
        $query .= ' AND (title LIKE ? OR description LIKE ?)';
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    // Count total
    $countQuery = str_replace('SELECT id, title, description, image_path, category, capacity, features, price, is_active, created_at, updated_at', 'SELECT COUNT(*) as count', $query);
    $countResult = $db->fetchOne($countQuery, $params);
    $total = $countResult['count'];

    // Pagination
    $query .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
    $params[] = $limit;
    $params[] = $offset;

    $products = $db->fetchAll($query, $params);

    // Decode features for all products and normalize image paths
    foreach ($products as &$product) {
        if (!empty($product['features'])) {
            $product['features'] = json_decode($product['features'], true);
        }

        if (!empty($product['image_path'])) {
            if (is_string($product['image_path'])) {
                $val = $product['image_path'];
                $decoded = json_decode($val, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $product['image_path'] = $decoded['url'] ?? $decoded['path'] ?? $val;
                } else {
                    $un = @unserialize($val);
                    if ($un !== false && is_array($un)) {
                        $product['image_path'] = $un['url'] ?? $un['path'] ?? $val;
                    } else {
                        $product['image_path'] = $val;
                    }
                }
            } elseif (is_array($product['image_path'])) {
                $product['image_path'] = $product['image_path']['url'] ?? $product['image_path']['path'] ?? '';
            }
        }
    }

    $response = [
        'products' => $products,
        'pagination' => [
            'limit' => $limit,
            'offset' => $offset,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];

    jsonSuccess('Products retrieved successfully', $response);
}

/**
 * Handle POST requests (Admin only)
 * Create new product
 */
function handlePostRequest() {
    global $db;

    // Check authentication
    $auth = new Auth();
    if (!$auth->isLoggedIn()) {
        jsonError('Unauthorized', 401);
    }

    $title = Security::sanitizeString(getPost('title'), 255);
    $description = Security::sanitizeHTML(getPost('description'));
    $category = Security::sanitizeString(getPost('category'), 100);
    $capacity = Security::sanitizeString(getPost('capacity'), 100);
    $price = floatval(getPost('price', 0));
    $features = getPost('features', '');

    // Validation
    $errors = [];
    if (empty($title)) $errors[] = 'Title is required';
    if (empty($description)) $errors[] = 'Description is required';
    if (empty($category)) $errors[] = 'Category is required';
    if ($price < 0) $errors[] = 'Price cannot be negative';

    if (!empty($errors)) {
        jsonError('Validation failed: ' . implode(', ', $errors), 422);
    }

    // Handle image upload
    $imagePath = '';
    if (!empty($_FILES['image']['name'])) {
        try {
            $handler = new ImageHandler();
            $uploaded = $handler->uploadImage($_FILES['image']);
            $imagePath = is_array($uploaded) ? ($uploaded['url'] ?? $uploaded['path'] ?? '') : (string)$uploaded;
        } catch (Exception $e) {
            jsonError('Image upload failed: ' . $e->getMessage(), 422);
        }
    }

    try {
        // Convert features to JSON
        $featuresArray = array_filter(array_map('trim', preg_split('/,/', $features)));
        $featuresJson = json_encode($featuresArray);

        // Insert product
        $db->execute(
            'INSERT INTO products (title, description, image_path, category, capacity, features, price, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())',
            [$title, $description, $imagePath, $category, $capacity, $featuresJson, $price]
        );

        $productId = $db->getInstance()->lastInsertId();

        $response = [
            'id' => $productId,
            'title' => $title,
            'message' => 'Product created successfully'
        ];

        Security::logSecurity('PRODUCT_CREATED', 'Product ID: ' . $productId);
        jsonSuccess($response, 'Product created successfully', 201);
    } catch (Exception $e) {
        jsonError('Failed to create product: ' . $e->getMessage(), 500);
    }
}

/**
 * Handle PUT requests (Admin only)
 * Update existing product
 */
function handlePutRequest() {
    global $db;

    // Check authentication
    $auth = new Auth();
    if (!$auth->isLoggedIn()) {
        jsonError('Unauthorized', 401);
    }

    // Parse JSON body for PUT
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id = intval($input['id'] ?? 0);
    $title = Security::sanitizeString($input['title'] ?? '', 255);
    $description = Security::sanitizeHTML($input['description'] ?? '');
    $category = Security::sanitizeString($input['category'] ?? '', 100);
    $capacity = Security::sanitizeString($input['capacity'] ?? '', 100);
    $price = floatval($input['price'] ?? 0);
    $features = $input['features'] ?? '';

    // Validation
    if (empty($id)) jsonError('Product ID is required', 422);
    if (empty($title)) jsonError('Title is required', 422);
    if (empty($description)) jsonError('Description is required', 422);
    if (empty($category)) jsonError('Category is required', 422);

    // Check if product exists
    $existing = $db->fetchOne('SELECT image_path FROM products WHERE id = ?', [$id]);
    if (!$existing) {
        jsonError('Product not found', 404);
    }

    try {
        // Normalize existing image_path to string
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

        // Handle image if provided in $_FILES (overwrite)
        if (!empty($_FILES['image']['name'])) {
            try {
                $handler = new ImageHandler();
                $uploaded = $handler->uploadImage($_FILES['image']);
                $imagePath = is_array($uploaded) ? ($uploaded['url'] ?? $uploaded['path'] ?? '') : (string)$uploaded;
            } catch (Exception $e) {
                jsonError('Image upload failed: ' . $e->getMessage(), 422);
            }
        }

        // Convert features to JSON
        $featuresArray = array_filter(array_map('trim', preg_split('/,/', $features)));
        $featuresJson = json_encode($featuresArray);

        // Update product
        $db->execute(
            'UPDATE products SET title = ?, description = ?, image_path = ?, category = ?, capacity = ?, features = ?, price = ?, updated_at = NOW() WHERE id = ?',
            [$title, $description, $imagePath, $category, $capacity, $featuresJson, $price, $id]
        );

        Security::logSecurity('PRODUCT_UPDATED', 'Product ID: ' . $id);
        jsonSuccess(['id' => $id], 'Product updated successfully');
    } catch (Exception $e) {
        jsonError('Failed to update product: ' . $e->getMessage(), 500);
    }
}

/**
 * Handle DELETE requests (Admin only)
 * Soft delete product by ID
 */
function handleDeleteRequest() {
    global $db;

    // Check authentication
    $auth = new Auth();
    if (!$auth->isLoggedIn()) {
        jsonError('Unauthorized', 401);
    }

    $id = intval(getQuery('id', 0));

    if (empty($id)) {
        jsonError('Product ID is required', 422);
    }

    try {
        // Check if product exists
        $product = $db->fetchOne('SELECT id FROM products WHERE id = ?', [$id]);
        if (!$product) {
            jsonError('Product not found', 404);
        }

        // Soft delete
        $db->execute('UPDATE products SET is_active = 0 WHERE id = ?', [$id]);

        Security::logSecurity('PRODUCT_DELETED', 'Product ID: ' . $id);
        jsonSuccess(['id' => $id], 'Product deleted successfully');
    } catch (Exception $e) {
        jsonError('Failed to delete product: ' . $e->getMessage(), 500);
    }
}
