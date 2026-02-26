<?php
/**
 * Contact Form API Handler
 * Receives contact form submissions and saves to database
 * Also sends email notifications
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Security.php';
require_once __DIR__ . '/../includes/Mailer.php';
require_once __DIR__ . '/../includes/helpers.php';

// Only accept POST requests
if (!isPost()) {
    jsonError('Invalid request method', 405);
}

// Get form data
$name = Security::sanitizeString(getPost('name'), 100);
$email = Security::sanitizeEmail(getPost('email'));
$phone = Security::sanitizePhone(getPost('phone'));
$message = Security::sanitizeString(getPost('message'), 1000);

try {
    // Validate required fields
    $errors = [];
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }
    if (empty($email)) {
        $errors['email'] = 'Valid email is required';
    }
    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required';
    }
    if (empty($message)) {
        $errors['message'] = 'Message is required';
    }

    if (!empty($errors)) {
        jsonError('Validation failed', 422);
    }

    // Rate limiting - max 5 submissions per IP per hour
    if (!Security::rateLimit('contact_form:' . Security::getClientIP(), 5, 3600)) {
        Security::logSecurity('CONTACT_FORM_RATE_LIMIT', 'IP: ' . Security::getClientIP());
        jsonError('Too many submissions from your IP. Please try again later.', 429);
    }

    // Save to database
    $db = Database::getInstance();
    
    $stmt = $db->execute(
        'INSERT INTO contact_messages (name, email, phone, message, ip_address, user_agent) 
         VALUES (?, ?, ?, ?, ?, ?)',
        [
            $name,
            $email,
            $phone,
            $message,
            Security::getClientIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]
    );

    $messageId = $db->lastInsertId();

    // Send email notification to admin
    Mailer::sendContactNotification($name, $email, $phone, $message);

    // Send confirmation email to user (optional)
    $confirmationBody = "<!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #8B4513; color: white; padding: 20px; border-radius: 5px; }
            .content { padding: 20px; border: 1px solid #ddd; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Thank You for Contacting Us</h2>
            </div>
            <div class='content'>
                <p>Dear " . htmlspecialchars($name) . ",</p>
                <p>Thank you for reaching out to " . SITE_NAME . ". We have received your message and will get back to you as soon as possible.</p>
                <p><strong>Your Message Reference:</strong> #" . str_pad($messageId, 6, '0', STR_PAD_LEFT) . "</p>
                <p>Best regards,<br>" . SITE_NAME . " Team</p>
            </div>
        </div>
    </body>
    </html>";

    $headers = [
        'From' => 'noreply@akshayrajindustry.in',
        'Content-Type' => 'text/html; charset=UTF-8'
    ];

    $headerString = '';
    foreach ($headers as $key => $value) {
        $headerString .= $key . ': ' . $value . "\r\n";
    }

    mail($email, 'We received your message - ' . SITE_NAME, $confirmationBody, $headerString);

    // Log successful submission
    Security::logSecurity('CONTACT_FORM_SUBMITTED', 'Email: ' . $email . ' | ID: ' . $messageId);

    // Return success response
    jsonSuccess('Thank you! Your message has been sent successfully. Reference: #' . str_pad($messageId, 6, '0', STR_PAD_LEFT), [
        'message_id' => $messageId,
        'reference_number' => str_pad($messageId, 6, '0', STR_PAD_LEFT)
    ]);

} catch (Exception $e) {
    error_log('Contact Form Error: ' . $e->getMessage());
    Security::logSecurity('CONTACT_FORM_ERROR', $e->getMessage());
    jsonError('An error occurred while processing your request. Please try again later.', 500);
}
