<?php
/**
 * Helper Functions
 * Common utility functions
 */

/**
 * Redirect to URL
 */
function redirect($url, $code = 302) {
    header('Location: ' . $url, true, $code);
    exit;
}

/**
 * JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

/**
 * JSON error response
 */
function jsonError($message, $statusCode = 400) {
    jsonResponse(['success' => false, 'error' => $message], $statusCode);
}

/**
 * JSON success response
 */
function jsonSuccess($message = 'Success', $data = null) {
    $response = ['success' => true, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    jsonResponse($response, 200);
}

/**
 * Format date
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    if (is_string($date)) {
        $date = strtotime($date);
    }
    return date($format, $date);
}

/**
 * Get time ago
 */
function timeAgo($timestamp) {
    $time = time() - strtotime($timestamp);
    
    if ($time < 60) {
        return 'just now';
    } elseif ($time < 3600) {
        $mins = floor($time / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($time < 86400) {
        $hours = floor($time / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($time < 604800) {
        $days = floor($time / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('Y-m-d', strtotime($timestamp));
    }
}

/**
 * Truncate text
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Get request method
 */
function getRequestMethod() {
    return $_SERVER['REQUEST_METHOD'] ?? 'GET';
}

/**
 * Check if POST request
 */
function isPost() {
    return getRequestMethod() === 'POST';
}

/**
 * Check if GET request
 */
function isGet() {
    return getRequestMethod() === 'GET';
}

/**
 * Get POST value
 */
function getPost($key, $default = '') {
    return $_POST[$key] ?? $default;
}

/**
 * Get GET value
 */
function getQuery($key, $default = '') {
    return $_GET[$key] ?? $default;
}

/**
 * Get server request value
 */
function getRequest($key, $default = '') {
    return getPost($key) ?: getQuery($key, $default);
}

/**
 * Set flash message
 */
function setFlash($key, $message, $type = 'info') {
    $_SESSION['flash'] = ['key' => $key, 'message' => $message, 'type' => $type];
}

/**
 * Get flash message
 */
function getFlash($key = null) {
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    
    if ($key && $flash['key'] !== $key) {
        return null;
    }
    
    return $flash;
}

/**
 * Debug output
 */
function debug($var, $die = false) {
    if (DEBUG) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
        if ($die) {
            exit;
        }
    }
}

/**
 * Generate pagination
 */
function generatePagination($currentPage, $totalPages, $baseUrl) {
    $pagination = [];
    
    if ($currentPage > 1) {
        $pagination[] = ['page' => 1, 'label' => 'First'];
        $pagination[] = ['page' => $currentPage - 1, 'label' => 'Previous'];
    }
    
    // Show page numbers (up to 5)
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $pagination[] = [
            'page' => $i,
            'label' => $i,
            'active' => $i === $currentPage
        ];
    }
    
    if ($currentPage < $totalPages) {
        $pagination[] = ['page' => $currentPage + 1, 'label' => 'Next'];
        $pagination[] = ['page' => $totalPages, 'label' => 'Last'];
    }
    
    return $pagination;
}

/**
 * Initialize directories
 */
function initializeDirectories() {
    $dirs = [
        UPLOADS_PATH,
        UPLOADS_PRODUCTS,
        ROOT_PATH . '/logs'
    ];
    
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
