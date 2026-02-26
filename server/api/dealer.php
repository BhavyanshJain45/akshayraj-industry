<?php
/**
 * Dealer & Distributor Inquiry API Handler
 * Receives dealer and distributor partnership inquiries
 * Saves to unified contact_messages table with inquiry_type = 'dealer' or 'distributor'
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

// Get form data with sanitization
$inquiry_type = in_array(getPost('inquiry_type'), ['dealer', 'distributor']) ? getPost('inquiry_type') : 'dealer';
$full_name = Security::sanitizeString(getPost('full_name'), 100);
$company_name = Security::sanitizeString(getPost('company_name'), 100);
$email = Security::sanitizeEmail(getPost('email'));
$phone = Security::sanitizePhone(getPost('phone'));
$city = Security::sanitizeString(getPost('city'), 50);
$state = Security::sanitizeString(getPost('state'), 50);
$business_experience = Security::sanitizeString(getPost('business_experience'), 2000);
$message = Security::sanitizeString(getPost('message'), 2000);
$captcha_token = getPost('g-recaptcha-response', '');

try {
    // Validate required fields
    $errors = [];
    
    if (empty($full_name)) {
        $errors['full_name'] = 'Full name is required';
    }
    if (empty($company_name)) {
        $errors['company_name'] = 'Company name is required';
    }
    if (empty($email)) {
        $errors['email'] = 'Valid email is required';
    }
    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required';
    }
    if (empty($city)) {
        $errors['city'] = 'City is required';
    }
    if (empty($state)) {
        $errors['state'] = 'State is required';
    }
    if (empty($business_experience)) {
        $errors['business_experience'] = 'Business experience is required';
    }
    if (empty($message)) {
        $errors['message'] = 'Message is required';
    }

    if (!empty($errors)) {
        jsonError('Validation failed: ' . implode(', ', array_keys($errors)), 422);
    }

    // reCAPTCHA verification (optional, if configured)
    $recaptcha_secret = getenv('RECAPTCHA_SECRET_KEY') ?: '';
    if (!empty($recaptcha_secret) && !empty($captcha_token)) {
        $response = @json_decode(file_get_contents(
            'https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptcha_secret . '&response=' . $captcha_token
        ));
        
        if (empty($response->success) || $response->score < 0.5) {
            Security::logSecurity('DEALER_CAPTCHA_FAILED', 'Email: ' . $email);
            jsonError('CAPTCHA verification failed. Please try again.', 403);
        }
    }

    // Rate limiting - max 3 dealer inquiries per IP per day
    if (!Security::rateLimit('dealer_inquiry:' . Security::getClientIP(), 3, 86400)) {
        Security::logSecurity('DEALER_INQUIRY_RATE_LIMIT', 'IP: ' . Security::getClientIP());
        jsonError('Too many dealer inquiries from your IP. Please try again tomorrow.', 429);
    }

    // Check for duplicate inquiries from same email within 24 hours
    $db = Database::getInstance();
    $recent = $db->fetchOne(
        'SELECT id FROM contact_messages 
         WHERE inquiry_type != "contact" AND email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)',
        [$email]
    );
    
    if ($recent) {
        jsonError('You have already submitted a dealer/distributor inquiry. Please check your email for confirmation.', 409);
    }

    // Save to database
    $stmt = $db->execute(
        'INSERT INTO contact_messages 
         (inquiry_type, name, email, phone, company_name, city, state, business_experience, message, ip_address, user_agent) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [
            $inquiry_type,
            $full_name,
            $email,
            $phone,
            $company_name,
            $city,
            $state,
            $business_experience,
            $message,
            Security::getClientIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]
    );

    $inquiry_id = $db->lastInsertId();

    // Send dealer inquiry notification email to admin
    $admin_email = getenv('ADMIN_EMAIL') ?: 'admin@yourdomain.com';
    
    $dealer_type_label = ucfirst($inquiry_type);
    
    $admin_body = "<!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #8B4513; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
            .content { padding: 20px; border: 1px solid #ddd; margin-bottom: 20px; }
            .field { margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
            .label { font-weight: bold; color: #8B4513; }
            .value { color: #555; }
            .footer { text-align: center; color: #999; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>New {$dealer_type_label} Inquiry - ID: #{$inquiry_id}</h2>
            </div>
            <div class='content'>
                <h3>Inquiry Details:</h3>
                <div class='field'>
                    <div class='label'>Inquiry Type:</div>
                    <div class='value'>{$dealer_type_label}</div>
                </div>
                <div class='field'>
                    <div class='label'>Full Name:</div>
                    <div class='value'>" . htmlspecialchars($full_name) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>Company Name:</div>
                    <div class='value'>" . htmlspecialchars($company_name) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>Email:</div>
                    <div class='value'><a href='mailto:{$email}'>{$email}</a></div>
                </div>
                <div class='field'>
                    <div class='label'>Phone:</div>
                    <div class='value'>" . htmlspecialchars($phone) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>City:</div>
                    <div class='value'>" . htmlspecialchars($city) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>State:</div>
                    <div class='value'>" . htmlspecialchars($state) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>Business Experience:</div>
                    <div class='value'>" . nl2br(htmlspecialchars($business_experience)) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>Message:</div>
                    <div class='value'>" . nl2br(htmlspecialchars($message)) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>IP Address:</div>
                    <div class='value'>" . Security::getClientIP() . "</div>
                </div>
                <div style='margin-top: 30px; padding-top: 20px; border-top: 2px solid #8B4513;'>
                    <a href='https://yourdomain.com/admin/messages.php?filter=dealer' style='background-color: #8B4513; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        View in Admin Panel
                    </a>
                </div>
            </div>
            <div class='footer'>
                <p>This is an automated notification. Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>";

    // Send notification with fallback to native mail() if Mailer unavailable
    if (class_exists('Mailer') && method_exists('Mailer', 'send')) {
        Mailer::send($admin_email, 
            'New ' . $dealer_type_label . ' Partnership Inquiry - ' . SITE_NAME,
            $admin_body, 
            $email  // reply-to
        );
    } else {
        // Fallback to native PHP mail() function
        mail($admin_email, 
            'New ' . $dealer_type_label . ' Partnership Inquiry - ' . SITE_NAME,
            $admin_body, 
            $header_string
        );
    }

    // Send confirmation email to inquirer
    $confirmation_body = "<!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #8B4513; color: white; padding: 20px; border-radius: 5px; }
            .content { padding: 20px; border: 1px solid #ddd; margin-top: 20px; line-height: 1.6; }
            .footer { text-align: center; color: #999; font-size: 12px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Thank You for Your Partnership Inquiry</h2>
            </div>
            <div class='content'>
                <p>Dear " . htmlspecialchars($full_name) . ",</p>
                <p>Thank you for your interest in becoming a " . strtolower($dealer_type_label) . " partner with " . SITE_NAME . ". We are excited to explore this partnership opportunity with you.</p>
                
                <h3>Next Steps:</h3>
                <ul style='line-height: 2;'>
                    <li>Our team will review your inquiry within 24-48 business hours.</li>
                    <li>We will contact you via email or phone with further details.</li>
                    <li>We will discuss partnership terms, benefits, and requirements.</li>
                    <li>If mutually interested, we will proceed with formal documentation.</li>
                </ul>

                <p><strong>Your Inquiry Reference:</strong> #" . str_pad($inquiry_id, 6, '0', STR_PAD_LEFT) . "</p>
                
                <h3>Why Partner With Us?</h3>
                <ul>
                    <li>✓ High demand manufacturing products with guaranteed quality</li>
                    <li>✓ Direct manufacturer pricing for maximum margins</li>
                    <li>✓ Reliable supply chain and on-time delivery</li>
                    <li>✓ Dedicated support and marketing assistance</li>
                    <li>✓ Growth opportunities and exclusive territories</li>
                </ul>

                <p style='margin-top: 30px;'>If you have any immediate questions, please feel free to contact us at:</p>
                <p>
                    📱 <strong>" . (getenv('SITE_PHONE') ?: '+91-9877421070') . "</strong><br>
                    📧 <strong>" . $admin_email . "</strong>
                </p>

                <p style='margin-top: 30px;'>Best regards,<br><strong>" . SITE_NAME . " Partnership Team</strong></p>
            </div>
            <div class='footer'>
                <p>This is an automated confirmation email. Please save this email for your records.</p>
            </div>
        </div>
    </body>
    </html>";

    $headers = [
        'From' => 'noreply@akshayrajindustry.in',
        'Content-Type' => 'text/html; charset=UTF-8',
        'Reply-To' => $admin_email
    ];

    $header_string = '';
    foreach ($headers as $key => $value) {
        $header_string .= $key . ': ' . $value . "\r\n";
    }

    mail($email, 
         'Partnership Inquiry Confirmation - ' . SITE_NAME, 
         $confirmation_body, 
         $header_string
    );

    // Log successful submission
    Security::logSecurity(
        'DEALER_INQUIRY_SUBMITTED',
        'Type: ' . $inquiry_type . ' | Name: ' . $full_name . ' | Email: ' . $email . ' | City: ' . $city . ' | ID: ' . $inquiry_id
    );

    // Return success response
    jsonSuccess(
        'Thank you! Your partnership inquiry has been received. We will contact you within 24-48 hours. Reference: #' . str_pad($inquiry_id, 6, '0', STR_PAD_LEFT),
        [
            'inquiry_id' => $inquiry_id,
            'reference_number' => str_pad($inquiry_id, 6, '0', STR_PAD_LEFT),
            'inquiry_type' => $inquiry_type
        ]
    );

} catch (Exception $e) {
    error_log('Dealer Inquiry Error: ' . $e->getMessage());
    Security::logSecurity('DEALER_INQUIRY_ERROR', $e->getMessage());
    jsonError('An error occurred while processing your inquiry. Please try again later.', 500);
}
