<?php
/**
 * Database Configuration
 * Secure database connection settings
 */

// Environment detection
define('ENV', getenv('APP_ENV') ?: 'production');
define('DEBUG', ENV === 'development');

// Database credentials - PRODUCTION
define('DB_HOST', 'localhost');
define('DB_USER', 'u442016196_akshayraj_user');
define('DB_PASS', 'zzzz');
define('DB_NAME', 'u442016196_akshayraj_db');

// Site settings
define('SITE_NAME', 'akshayrajindustry');
define('SITE_URL', 'https://akshayrajindustry.in');
define('ADMIN_URL', SITE_URL . '/server/admin');
define('API_URL', SITE_URL . '/api');
define('CONTACT_NUMBER', '+91 99774 21070');
define('WHATSAPP_NUMBER', '+91 99774 21070');

// Security settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_IMAGE_EXT', ['jpg', 'jpeg', 'png', 'webp']);

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('UPLOADS_PRODUCTS', UPLOADS_PATH . '/products');

// Email settings
define('ADMIN_EMAIL', 'info@akshayrajindustry.in');
define('PUBLIC_EMAIL', 'info@akshayrajindustry.in');
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 465);
define('SMTP_USER', 'info@akshayrajindustry.in');
define('SMTP_PASS', '(ADD MAILBOX PASSWORD HERE)');
define('SMTP_ENCRYPTION', 'ssl');
define('MAIL_FROM', 'info@akshayrajindustry.in');

// IMAP settings for mailbox viewer
define('IMAP_HOST', '{imap.hostinger.com:993/imap/ssl}INBOX');
define('IMAP_USER', 'info@akshayrajindustry.in');
define('IMAP_PASS', '(ADD MAILBOX PASSWORD HERE)');
// Security & API keys
define('RECAPTCHA_SITE_KEY', '6LdZQnMsAAAAAAj_kLOf5pQEqCJ80peYgfHfS4UN');
define('RECAPTCHA_SECRET', '6LdZQnMsAAAAAN954JyMU4__QazcialegE4honbX');
define('ENCRYPTION_KEY', 'A9xF7sdP92Lm4Qw8Kz3dFh7RsV6nX2bT');
define('SSL_ENABLED', true);

// Ensure upload directories exist with proper permissions
function ensureUploadDir($dir, $perms = 0755) {
    if (!is_dir($dir)) {
        if (!mkdir($dir, $perms, true)) {
            error_log('Failed to create upload directory: ' . $dir);
            return false;
        }
        chmod($dir, $perms);
    }
    
    // Verify directory is writable
    if (!is_writable($dir)) {
        error_log('Upload directory is not writable: ' . $dir);
        return false;
    }
    
    return true;
}

ensureUploadDir(UPLOADS_PATH);
ensureUploadDir(UPLOADS_PRODUCTS);

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_TIMEOUT,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => true, // HTTPS only
        'httponly' => true, // Prevent XSS
        'samesite' => 'Strict' // CSRF protection
    ]);
    session_start();
}

// Error handling
if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . '/logs/error.log');
}
