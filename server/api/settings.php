<?php
/**
 * Settings API Endpoint
 * GET /api/settings.php - Get all site settings (public)
 * GET /api/settings.php?key=site_name - Get single setting
 * POST /api/settings.php - Update settings (admin only)
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Security.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$db = Database::getInstance();
$method = $_SERVER['REQUEST_METHOD'];

// Rate limiting for API
$clientIp = Security::getClientIP();
if (!Security::rateLimit('api:settings:' . $clientIp, 100, 3600)) {
    jsonError('Rate limit exceeded. Max 100 requests per hour.', 429);
}

try {
    if ($method === 'GET') {
        handleGetRequest();
    } elseif ($method === 'POST') {
        handlePostRequest();
    } else {
        jsonError('Method not allowed', 405);
    }
} catch (Exception $e) {
    Security::logSecurity('API_ERROR', 'Settings API: ' . $e->getMessage());
    jsonError('Internal server error: ' . $e->getMessage(), 500);
}

/**
 * Handle GET requests (Public)
 * /api/settings.php - Get all settings
 * /api/settings.php?key=site_name - Get single setting
 */
function handleGetRequest() {
    global $db;

    $key = getQuery('key');

    // Default settings
    $defaults = [
        'site_name' => 'Akshayraj Industry',
        'site_description' => 'Manufacturing & Industrial Solutions',
        'site_email' => 'info@akshayrajindustry.in',
        'site_phone' => '+91 9877421070',
        'site_address' => 'Pune, Maharashtra',
        'admin_email' => 'admin@akshayrajindustry.in'
    ];

    // Get single setting
    if (!empty($key)) {
        $key = Security::sanitizeString($key, 100);

        // Validate key to prevent injection
        if (!array_key_exists($key, $defaults)) {
            jsonError('Setting not found', 404);
        }

        $setting = $db->fetchOne('SELECT setting_value FROM settings WHERE setting_key = ?', [$key]);
        $value = $setting ? $setting['setting_value'] : $defaults[$key];

        return jsonSuccess(
            ['key' => $key, 'value' => $value],
            'Setting retrieved successfully'
        );
    }

    // Get all settings
    $allSettings = $db->fetchAll('SELECT setting_key, setting_value FROM settings');
    
    $settingsArray = $defaults;
    foreach ($allSettings as $setting) {
        $settingsArray[$setting['setting_key']] = $setting['setting_value'];
    }

    jsonSuccess($settingsArray, 'Settings retrieved successfully');
}

/**
 * Handle POST requests (Admin only)
 * Update single or multiple settings
 * POST body: { "setting_key": "value", "another_key": "another_value" }
 */
function handlePostRequest() {
    global $db;

    // Check authentication
    $auth = new Auth();
    if (!$auth->isLoggedIn()) {
        jsonError('Unauthorized', 401);
    }

    // Parse JSON body
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input)) {
        jsonError('No settings provided', 422);
    }

    // Allowed settings keys
    $allowedKeys = ['site_name', 'site_description', 'site_email', 'site_phone', 'site_address', 'admin_email'];

    $updatedSettings = [];
    $errors = [];

    foreach ($input as $key => $value) {
        $key = Security::sanitizeString($key, 100);

        // Validate key
        if (!in_array($key, $allowedKeys)) {
            $errors[] = "Invalid setting key: $key";
            continue;
        }

        // Sanitize value based on key
        if ($key === 'site_email' || $key === 'admin_email') {
            $value = Security::sanitizeEmail($value);
            if (empty($value)) {
                $errors[] = "$key must be a valid email";
                continue;
            }
        } elseif ($key === 'site_phone') {
            $value = Security::sanitizePhone($value);
        } else {
            $value = Security::sanitizeString($value, 500);
        }

        if (empty($value)) {
            $errors[] = "$key is required";
            continue;
        }

        try {
            // Check if key exists
            $existing = $db->fetchOne('SELECT id FROM settings WHERE setting_key = ?', [$key]);
            
            if ($existing) {
                $db->execute('UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?', [$value, $key]);
            } else {
                $db->execute('INSERT INTO settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW())', [$key, $value]);
            }

            $updatedSettings[$key] = $value;
        } catch (Exception $e) {
            $errors[] = "Failed to update $key: " . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        jsonError('Some settings failed to update: ' . implode('; ', $errors), 422);
    }

    if (empty($updatedSettings)) {
        jsonError('No settings were updated', 422);
    }

    Security::logSecurity('SETTINGS_UPDATED', 'Updated keys: ' . implode(', ', array_keys($updatedSettings)));

    jsonSuccess(
        ['updated' => $updatedSettings],
        'Settings updated successfully'
    );
}
