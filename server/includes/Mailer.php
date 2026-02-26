<?php
/**
 * Mailer Class
 * Email sending functionality
 */

class Mailer {
    /**
     * Send contact form notification email
     */
    public static function sendContactNotification($name, $email, $phone, $message) {
        try {
            $to = ADMIN_EMAIL;
            $subject = 'New Contact Form Submission - ' . SITE_NAME;

            $htmlBody = self::getContactEmailTemplate($name, $email, $phone, $message);

            $headers = [
                'From' => 'noreply@akshayrajindustry.in',
                'Reply-To' => $email,
                'Content-Type' => 'text/html; charset=UTF-8',
                'X-Mailer' => 'PHP/' . phpversion()
            ];

            $headerString = '';
            foreach ($headers as $key => $value) {
                $headerString .= $key . ': ' . $value . "\r\n";
            }

            $result = mail($to, $subject, $htmlBody, $headerString);

            if (!$result) {
                error_log('Email delivery failed for contact form from: ' . $email);
            }

            return $result;
        } catch (Exception $e) {
            error_log('Mailer error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send admin notification
     */
    public static function sendAdminNotification($subject, $message, $to = null) {
        try {
            if (!$to) $to = ADMIN_EMAIL;
            $htmlBody = "<html><body>";
            $htmlBody .= "<h2>" . htmlspecialchars($subject) . "</h2>";
            $htmlBody .= "<p>" . nl2br(htmlspecialchars($message)) . "</p>";
            $htmlBody .= "<p><small>Sent from " . SITE_NAME . " System</small></p>";
            $htmlBody .= "</body></html>";

            $headers = [
                'From' => 'noreply@akshayrajindustry.in',
                'Content-Type' => 'text/html; charset=UTF-8'
            ];

            $headerString = '';
            foreach ($headers as $key => $value) {
                $headerString .= $key . ': ' . $value . "\r\n";
            }

            return mail($to, $subject, $htmlBody, $headerString);
        } catch (Exception $e) {
            error_log('Mailer error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Contact form email template
     */
    private static function getContactEmailTemplate($name, $email, $phone, $message) {
        $htmlBody = "<!DOCTYPE html>";
        $htmlBody .= "<html>";
        $htmlBody .= "<head>";
        $htmlBody .= "<style>";
        $htmlBody .= "body { font-family: Arial, sans-serif; color: #333; }";
        $htmlBody .= ".container { max-width: 600px; margin: 0 auto; padding: 20px; }";
        $htmlBody .= ".header { background-color: #8B4513; color: white; padding: 20px; border-radius: 5px; }";
        $htmlBody .= ".content { padding: 20px; border: 1px solid #ddd; margin-top: 20px; }";
        $htmlBody .= ".field { margin: 15px 0; }";
        $htmlBody .= ".label { font-weight: bold; color: #8B4513; }";
        $htmlBody .= "</style>";
        $htmlBody .= "</head>";
        $htmlBody .= "<body>";
        $htmlBody .= "<div class='container'>";
        $htmlBody .= "<div class='header'>";
        $htmlBody .= "<h2>New Contact Form Submission</h2>";
        $htmlBody .= "</div>";
        $htmlBody .= "<div class='content'>";
        $htmlBody .= "<div class='field'>";
        $htmlBody .= "<span class='label'>Name:</span><br>";
        $htmlBody .= htmlspecialchars($name);
        $htmlBody .= "</div>";
        $htmlBody .= "<div class='field'>";
        $htmlBody .= "<span class='label'>Email:</span><br>";
        $htmlBody .= htmlspecialchars($email);
        $htmlBody .= "</div>";
        $htmlBody .= "<div class='field'>";
        $htmlBody .= "<span class='label'>Phone:</span><br>";
        $htmlBody .= htmlspecialchars($phone);
        $htmlBody .= "</div>";
        $htmlBody .= "<div class='field'>";
        $htmlBody .= "<span class='label'>Message:</span><br>";
        $htmlBody .= nl2br(htmlspecialchars($message));
        $htmlBody .= "</div>";
        $htmlBody .= "<p style='margin-top: 30px; color: #999; font-size: 12px;'>";
        $htmlBody .= "This email was sent from " . SITE_NAME . " contact form. ";
        $htmlBody .= "Sent from: " . Security::getClientIP();
        $htmlBody .= "</p>";
        $htmlBody .= "</div>";
        $htmlBody .= "</div>";
        $htmlBody .= "</body>";
        $htmlBody .= "</html>";

        return $htmlBody;
    }
}
