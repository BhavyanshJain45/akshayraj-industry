<?php
/**
 * Security Class
 * Input sanitization, CSRF protection, and security utilities
 */

class Security {
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Sanitize input string
     */
    public static function sanitizeString($input, $maxLength = 255) {
        if (!is_string($input)) {
            return '';
        }
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return substr($input, 0, $maxLength);
    }

    /**
     * Sanitize email
     */
    public static function sanitizeEmail($email) {
        $email = trim($email);
        $email = strtolower($email);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '';
        }
        return $email;
    }

    /**
     * Sanitize phone number
     */
    public static function sanitizePhone($phone) {
        return preg_replace('/[^0-9+\-\s]/', '', $phone);
    }

    /**
     * Sanitize text for rich editor (HTML allowed)
     */
    public static function sanitizeHTML($input) {
        $allowed = '<p><br><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img>';
        return strip_tags($input, $allowed);
    }

    /**
     * Sanitize filename
     */
    public static function sanitizeFilename($filename) {
        $filename = basename($filename);
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        $filename = preg_replace('/\.{2,}/', '.', $filename);
        return $filename;
    }

    /**
     * Prevent Local File Inclusion (LFI)
     */
    public static function preventLFI($page) {
        // Remove directory traversal patterns
        $page = str_replace(['../', '..\\', '..'], '', $page);
        
        // Remove null bytes
        $page = str_replace(chr(0), '', $page);
        
        // Whitelist allowed pages
        $allowed = ['home', 'about', 'products', 'contact', 'manufacturing'];
        if (!in_array($page, $allowed)) {
            $page = 'home';
        }
        
        return $page;
    }

    /**
     * Validate page parameter
     */
    public static function validatePage($page) {
        $allowed = ['home', 'about', 'products', 'contact', 'manufacturing'];
        return in_array($page, $allowed) ? $page : 'home';
    }

    /**
     * Hash password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Generate secure random token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Log security event
     */
    public static function logSecurity($event, $details = '') {
        $logFile = ROOT_PATH . '/logs/security.log';
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $message = "[$timestamp] IP: $ip | Event: $event | Details: $details\n";
        
        file_put_contents($logFile, $message, FILE_APPEND);
    }

    /**
     * Rate limiting check
     */
    public static function rateLimit($key, $limit = 5, $timeWindow = 300) {
        $sessionKey = 'rate_limit_' . md5($key);
        
        if (!isset($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = [];
        }
        
        $now = time();
        $_SESSION[$sessionKey] = array_filter($_SESSION[$sessionKey], function($time) use ($now, $timeWindow) {
            return $time > ($now - $timeWindow);
        });
        
        if (count($_SESSION[$sessionKey]) >= $limit) {
            return false;
        }
        
        $_SESSION[$sessionKey][] = $now;
        return true;
    }

    /**
     * Validate required fields
     */
    public static function validateRequired($fields) {
        $errors = [];
        foreach ($fields as $field => $label) {
            if (empty($_POST[$field] ?? '')) {
                $errors[$field] = "$label is required";
            }
        }
        return $errors;
    }

    /**
     * Get client IP
     */
    public static function getClientIP() {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }
}
