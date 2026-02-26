<?php
/**
 * Authentication Class
 * Admin login and session management
 */

class Auth {
    private $db;
    private $sessionTimeout = SESSION_TIMEOUT;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        if (!isset($_SESSION['admin_id'])) {
            return false;
        }

        // Check session timeout
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $this->sessionTimeout) {
            $this->logout();
            return false;
        }

        // Update last activity time
        $_SESSION['last_activity'] = time();
        return true;
    }

    /**
     * Login admin user
     */
    public function login($email, $password) {
        // Rate limiting
        if (!Security::rateLimit('login:' . $email, 5, 300)) {
            Security::logSecurity('BRUTE_FORCE_ATTEMPT', 'Email: ' . $email);
            throw new Exception('Too many login attempts. Please try again later.');
        }

        // Sanitize and validate
        $email = Security::sanitizeEmail($email);
        if (empty($email)) {
            throw new Exception('Invalid email format');
        }

        // Check if user exists
        $user = $this->db->fetchOne('SELECT id, email, password FROM admins WHERE email = ?', [$email]);
        if (!$user) {
            Security::logSecurity('FAILED_LOGIN', 'Email: ' . $email);
            throw new Exception('Invalid email or password');
        }

        // Verify password
        if (!Security::verifyPassword($password, $user['password'])) {
            Security::logSecurity('FAILED_LOGIN', 'Email: ' . $email);
            throw new Exception('Invalid email or password');
        }

        // Set session
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_email'] = $user['email'];
        $_SESSION['last_activity'] = time();

        Security::logSecurity('ADMIN_LOGIN', 'Email: ' . $email);
        return true;
    }

    /**
     * Logout admin user
     */
    public function logout() {
        Security::logSecurity('ADMIN_LOGOUT', 'Email: ' . ($_SESSION['admin_email'] ?? 'UNKNOWN'));
        
        session_unset();
        session_destroy();
        return true;
    }

    /**
     * Get current admin data
     */
    public function getCurrentAdmin() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return $this->db->fetchOne('SELECT id, email FROM admins WHERE id = ?', [$_SESSION['admin_id']]);
    }

    /**
     * Create admin user (for installation)
     */
    public function createAdmin($email, $password) {
        $email = Security::sanitizeEmail($email);
        if (empty($email)) {
            throw new Exception('Invalid email');
        }

        if (strlen($password) < 8) {
            throw new Exception('Password must be at least 8 characters');
        }

        $hashedPassword = Security::hashPassword($password);

        $this->db->execute(
            'INSERT INTO admins (email, password) VALUES (?, ?)',
            [$email, $hashedPassword]
        );

        return $this->db->lastInsertId();
    }

    /**
     * Change admin password
     */
    public function changePassword($adminId, $newPassword) {
        if (strlen($newPassword) < 8) {
            throw new Exception('Password must be at least 8 characters');
        }

        $hashedPassword = Security::hashPassword($newPassword);

        $this->db->execute(
            'UPDATE admins SET password = ? WHERE id = ?',
            [$hashedPassword, $adminId]
        );

        return true;
    }

    /**
     * Require login
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            $loginUrl = defined('ADMIN_URL') ? (ADMIN_URL . '/') : '/admin/';
            header('Location: ' . $loginUrl);
            exit;
        }
    }
}
