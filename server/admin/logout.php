<?php
/**
 * Admin Logout
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Security.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$auth = new Auth();
try {
	// Only perform logout on POST to avoid accidental GET logouts
	if (getRequestMethod() === 'POST') {
		$auth->logout();

		// Clear session cookie if present
		if (ini_get('session.use_cookies')) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
		}
	}
} catch (Exception $e) {
	// Log and continue to redirect
	if (class_exists('Security')) {
		try { Security::logSecurity('LOGOUT_ERROR', $e->getMessage()); } catch (Exception $ex) {}
	}
}

// Redirect to admin login page
if (defined('ADMIN_URL') && ADMIN_URL) {
	redirect(ADMIN_URL . '/');
} else {
	redirect('/server/admin/');
}
