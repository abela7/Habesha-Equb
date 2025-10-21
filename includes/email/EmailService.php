<?php
/**
 * HabeshaEqub Professional Email Service
 * Modern, spam-compliant email system with file-based templates
 */

class EmailService {
    private $pdo;
    private $smtp_config;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->loadSMTPConfig();
    }
    
    /**
     * Build and send a program notification email to a member with enforced rules:
     * - Subject is ALWAYS the English title
     * - Body is based on member language preference (Amharic for 1, English otherwise)
     */
    public function sendProgramNotificationToMember(array $memberRow, string $title_en, string $title_am, string $body_en, string $body_am)
    {
        $toEmail = $memberRow['email'] ?? '';
        $toName = trim(($memberRow['first_name'] ?? '') . ' ' . ($memberRow['last_name'] ?? ''));
        $isAmharic = (int)($memberRow['language_preference'] ?? 0) === 1;

        $subject = $title_en; // always English subject for deliverability
        $body = $isAmharic ? $body_am : $body_en;

        // Convert newlines to HTML line breaks
        $body_html = nl2br($body, false);
        
        // Make URLs clickable - match full URLs and create proper HTML links
        // Do NOT use htmlspecialchars on URLs as it can corrupt them
        $body_html = preg_replace_callback(
            '/(https?:\/\/[^\s<>"\']+)/',
            function($matches) {
                $url = $matches[1];
                // Use URL as-is without encoding to prevent corruption
                return '<a href="' . $url . '" style="color:#13665C;text-decoration:underline;font-weight:600;word-break:break-all;">' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '</a>';
            },
            $body_html
        );

        $vars = [
            'subject' => $subject,
            'title' => $subject,
            'body' => $body_html,
            'app_name' => 'HabeshaEqub'
        ];

        return $this->send('program_notification', $toEmail, $toName, $vars);
    }

    private function loadSMTPConfig() {
        $stmt = $this->pdo->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_category = 'email'");
        $stmt->execute();
        
        $config = [];
        while ($row = $stmt->fetch()) {
            $config[$row['setting_key']] = $row['setting_value'];
        }
        
        $this->smtp_config = [
            'host' => $config['smtp_host'] ?? 'smtp-relay.sendinblue.com',
            'port' => (int)($config['smtp_port'] ?? 587),
            'username' => $config['smtp_username'] ?? '',
            'password' => $config['smtp_password'] ?? '',
            'encryption' => $config['smtp_encryption'] ?? 'tls',
            'from_email' => $config['from_email'] ?? 'noreply@habeshaequb.com',
            'from_name' => $config['from_name'] ?? 'HabeshaEqub'
        ];
    }
    
    /**
     * Send email using template
     */
    public function send($template, $to_email, $to_name, $variables = []) {
        // Rate limiting check
        if (!$this->checkRateLimit($to_email, $template)) {
            throw new Exception('Rate limit exceeded for this email type');
        }
        
        // Load template
        $email_content = $this->loadTemplate($template, $variables);
        if (!$email_content) {
            throw new Exception('Email template not found: ' . $template);
        }
        
        // Send via SMTP
        $result = $this->sendViaSMTP($to_email, $to_name, $email_content['subject'], $email_content['html'], $email_content['text']);
        
        // Update rate limit
        if ($result['success']) {
            $this->updateRateLimit($to_email, $template);
        }
        
        return $result;
    }
    
    /**
     * Load email template from file
     */
    private function loadTemplate($template, $variables) {
        $template_path = __DIR__ . '/templates/' . $template . '.html';
        
        if (!file_exists($template_path)) {
            return false;
        }
        
        $html_content = file_get_contents($template_path);
        
        // Replace variables - don't escape body HTML, but escape other variables
        foreach ($variables as $key => $value) {
            if ($key === 'body') {
                // Body should be HTML - don't escape it
                $html_content = str_replace('{{' . $key . '}}', $value, $html_content);
            } else {
                // Other variables should be escaped for security
                $html_content = str_replace('{{' . $key . '}}', htmlspecialchars($value), $html_content);
            }
        }
        
        // Extract subject from template
        preg_match('/<title>(.*?)<\/title>/i', $html_content, $matches);
        $subject = $matches[1] ?? 'HabeshaEqub Notification';
        
        // Create text version
        $text_content = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html_content));
        
        return [
            'subject' => $subject,
            'html' => $html_content,
            'text' => $text_content
        ];
    }
    
    /**
     * Send email via SMTP with enhanced connection handling
     */
    private function sendViaSMTP($to_email, $to_name, $subject, $html_content, $text_content) {
        $start_time = microtime(true);
        
        try {
            // Try multiple hostnames for reliability
            $hosts = [$this->smtp_config['host']];
            if ($this->smtp_config['host'] === 'smtp-relay.brevo.com') {
                $hosts[] = 'smtp-relay.sendinblue.com';
            }
            
            $smtp = null;
            foreach ($hosts as $host) {
                $context = stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ]);
                
                $smtp = stream_socket_client("tcp://{$host}:{$this->smtp_config['port']}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
                
                if ($smtp) {
                    break;
                }
            }
            
            if (!$smtp) {
                throw new Exception("Could not connect to SMTP server");
            }
            
            // Read welcome
            $response = fgets($smtp, 515);
            if (substr($response, 0, 3) !== '220') {
                throw new Exception("SMTP server not ready: " . $response);
            }
            
            // EHLO
            fputs($smtp, "EHLO habeshaequb.com\r\n");
            $response = fgets($smtp, 515);
            while (substr($response, 3, 1) === '-') {
                $response = fgets($smtp, 515);
            }
            
            // STARTTLS
            if ($this->smtp_config['encryption'] === 'tls') {
                fputs($smtp, "STARTTLS\r\n");
                $response = fgets($smtp, 515);
                if (substr($response, 0, 3) !== '220') {
                    throw new Exception("STARTTLS failed");
                }
                
                if (!stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new Exception("TLS encryption failed");
                }
                
                // EHLO again after TLS
                fputs($smtp, "EHLO habeshaequb.com\r\n");
                $response = fgets($smtp, 515);
                while (substr($response, 3, 1) === '-') {
                    $response = fgets($smtp, 515);
                }
            }
            
            // AUTH LOGIN
            fputs($smtp, "AUTH LOGIN\r\n");
            $response = fgets($smtp, 515);
            if (substr($response, 0, 3) !== '334') {
                throw new Exception("AUTH LOGIN failed");
            }
            
            fputs($smtp, base64_encode($this->smtp_config['username']) . "\r\n");
            $response = fgets($smtp, 515);
            if (substr($response, 0, 3) !== '334') {
                throw new Exception("Username failed");
            }
            
            fputs($smtp, base64_encode($this->smtp_config['password']) . "\r\n");
            $response = fgets($smtp, 515);
            if (substr($response, 0, 3) !== '235') {
                throw new Exception("Authentication failed");
            }
            
            // MAIL FROM
            fputs($smtp, "MAIL FROM:<{$this->smtp_config['from_email']}>\r\n");
            $response = fgets($smtp, 515);
            if (substr($response, 0, 3) !== '250') {
                throw new Exception("MAIL FROM failed");
            }
            
            // RCPT TO
            fputs($smtp, "RCPT TO:<{$to_email}>\r\n");
            $response = fgets($smtp, 515);
            if (substr($response, 0, 3) !== '250') {
                throw new Exception("RCPT TO failed");
            }
            
            // DATA
            fputs($smtp, "DATA\r\n");
            $response = fgets($smtp, 515);
            if (substr($response, 0, 3) !== '354') {
                throw new Exception("DATA command failed");
            }
            
            // Email headers and content
            // Encode non-ASCII safely per RFC 2047
            $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
            $fromNameSafe = preg_replace('/[^\x20-\x7E]/', '', $this->smtp_config['from_name']); // ASCII-only display name

            $message = "From: {$fromNameSafe} <{$this->smtp_config['from_email']}>\r\n";
            $message .= "To: {$to_name} <{$to_email}>\r\n";
            $message .= "Subject: {$encodedSubject}\r\n";
            
            // Generate unique Message-ID to prevent email threading/grouping
            $unique_id = uniqid('', true) . '.' . time() . '.habeshaequb@' . ($_SERVER['HTTP_HOST'] ?? 'habeshaequb.com');
            $message .= "Message-ID: <{$unique_id}>\r\n";
            
            $message .= "MIME-Version: 1.0\r\n";
            $message .= "Content-Type: multipart/alternative; boundary=\"boundary123\"; charset=UTF-8\r\n";
            $message .= "List-Unsubscribe: <mailto:unsubscribe@habeshaequb.com>\r\n";
            $message .= "List-Unsubscribe-Post: List-Unsubscribe=One-Click\r\n";
            $message .= "X-Mailer: HabeshaEqub Mailer\r\n";
            $message .= "Auto-Submitted: auto-generated\r\n";
            $message .= "\r\n";
            
            // Multipart content - use 8bit encoding to avoid URL corruption
            $message .= "--boundary123\r\n";
            $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $message .= $text_content . "\r\n";
            
            $message .= "--boundary123\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $message .= $html_content . "\r\n";
            
            $message .= "--boundary123--\r\n";
            $message .= "\r\n.\r\n";
            
            fputs($smtp, $message);
            $response = fgets($smtp, 515);
            
            if (substr($response, 0, 3) !== '250') {
                throw new Exception("Email sending failed: " . $response);
            }
            
            fputs($smtp, "QUIT\r\n");
            fclose($smtp);
            
            $delivery_time = round((microtime(true) - $start_time) * 1000, 2);
            
            return [
                'success' => true,
                'delivery_time' => $delivery_time,
                'message' => 'Email sent successfully'
            ];
            
        } catch (Exception $e) {
            if (isset($smtp) && $smtp) {
                fclose($smtp);
            }
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'delivery_time' => round((microtime(true) - $start_time) * 1000, 2)
            ];
        }
    }
    
    /**
     * Check rate limiting for anti-spam
     */
    private function checkRateLimit($email, $type) {
        // TESTING MODE: More lenient rate limits for testing approvals
        $limits = [
            'email_verification' => ['count' => 10, 'period' => 3600], // 10 per hour
            'welcome' => ['count' => 5, 'period' => 3600], // 5 per hour 
            'approval_notification' => ['count' => 5, 'period' => 3600], // 5 per hour
            'account_approved' => ['count' => 10, 'period' => 3600], // 10 per hour (for testing)
            'otp_login' => ['count' => 15, 'period' => 3600] // 15 per hour (frequent logins)
        ];
        
        $limit = $limits[$type] ?? ['count' => 10, 'period' => 3600];
        
        $stmt = $this->pdo->prepare("
            SELECT sent_count FROM email_rate_limits 
            WHERE email_address = ? AND email_type = ? AND reset_at > NOW()
        ");
        $stmt->execute([$email, $type]);
        $current = $stmt->fetch();
        
        return !$current || $current['sent_count'] < $limit['count'];
    }
    
    /**
     * Update rate limit counter
     */
    private function updateRateLimit($email, $type) {
        $limits = [
            'email_verification' => 3600, // 1 hour
            'welcome' => 86400, // 1 day  
            'approval_notification' => 86400, // 1 day
            'account_approved' => 86400 // 1 day
        ];
        
        $period = $limits[$type] ?? 3600;
        
        $stmt = $this->pdo->prepare("
            INSERT INTO email_rate_limits (email_address, email_type, sent_count, reset_at) 
            VALUES (?, ?, 1, DATE_ADD(NOW(), INTERVAL ? SECOND))
            ON DUPLICATE KEY UPDATE 
            sent_count = sent_count + 1,
            last_sent_at = NOW()
        ");
        $stmt->execute([$email, $type, $period]);
    }
    
    /**
     * Generate OTP code
     */
    public function generateOTP($user_id, $email, $type = 'email_verification') {
        // Use 6-digit codes for admin login for extra security; 4-digit for others to preserve UX
        if ($type === 'admin_login') {
            $otp_code = sprintf('%06d', mt_rand(100000, 999999));
        } else {
            $otp_code = sprintf('%04d', mt_rand(1000, 9999));
        }
        
        error_log("Generating OTP - User ID: $user_id, Email: $email, Code: $otp_code, Type: $type");
        
        // Clean up only expired/used OTPs to avoid race conditions
        $stmt = $this->pdo->prepare("DELETE FROM user_otps WHERE email = ? AND otp_type = ? AND (expires_at < NOW() OR is_used = 1)");
        $stmt->execute([$email, $type]);
        
        // Insert new OTP using database NOW() + INTERVAL to avoid timezone issues
        $stmt = $this->pdo->prepare("
            INSERT INTO user_otps (user_id, email, otp_code, otp_type, expires_at) 
            VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))
        ");
        $result = $stmt->execute([$user_id, $email, $otp_code, $type]);
        
        if ($result) {
            error_log("OTP stored successfully in database");
            
            // Log the actual stored values for debugging
            $check_stmt = $this->pdo->prepare("
                SELECT expires_at, created_at, NOW() as current_db_time 
                FROM user_otps 
                WHERE email = ? AND otp_code = ? AND otp_type = ? 
                ORDER BY id DESC LIMIT 1
            ");
            $check_stmt->execute([$email, $otp_code, $type]);
            $stored_otp = $check_stmt->fetch();
            if ($stored_otp) {
                error_log("OTP times - Created: {$stored_otp['created_at']}, Expires: {$stored_otp['expires_at']}, Current: {$stored_otp['current_db_time']}");
            }
        } else {
            error_log("Failed to store OTP in database");
        }
        
        return $otp_code;
    }
    
    /**
     * Verify OTP code
     */
    public function verifyOTP($email, $otp_code, $type = 'email_verification') {
        // Debug logging
        error_log("OTP Verification - Email: $email, Code: $otp_code, Type: $type");
        
        // First check if there are any OTPs for this email and type
        $debug_stmt = $this->pdo->prepare("
            SELECT otp_code, otp_type, expires_at, is_used, user_id 
            FROM user_otps 
            WHERE email = ? AND otp_type = ?
            ORDER BY id DESC LIMIT 5
        ");
        $debug_stmt->execute([$email, $type]);
        $debug_otps = $debug_stmt->fetchAll();
        error_log("Found OTPs for $email ($type): " . json_encode($debug_otps));
        
        $stmt = $this->pdo->prepare("
            SELECT id, user_id, attempt_count, expires_at, created_at, NOW() as current_db_time,
                   (expires_at > NOW()) as is_not_expired,
                   (is_used = 0) as is_not_used
            FROM user_otps 
            WHERE email = ? AND otp_code = ? AND otp_type = ?
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$email, $otp_code, $type]);
        $otp_debug = $stmt->fetch();
        
        if ($otp_debug) {
            error_log("OTP found - Created: {$otp_debug['created_at']}, Expires: {$otp_debug['expires_at']}, Current: {$otp_debug['current_db_time']}, Not Expired: {$otp_debug['is_not_expired']}, Not Used: {$otp_debug['is_not_used']}");
        }
        
        $stmt = $this->pdo->prepare("
            SELECT id, user_id, attempt_count FROM user_otps 
            WHERE email = ? AND otp_code = ? AND otp_type = ? 
            AND expires_at > NOW() AND is_used = 0
        ");
        $stmt->execute([$email, $otp_code, $type]);
        $otp = $stmt->fetch();
        
        if (!$otp) {
            error_log("OTP verification failed - no matching valid OTP found");
            // Increment attempt count for wrong codes
            $stmt = $this->pdo->prepare("
                UPDATE user_otps SET attempt_count = attempt_count + 1 
                WHERE email = ? AND otp_type = ? AND is_used = 0
            ");
            $stmt->execute([$email, $type]);
            return false;
        }
        
        error_log("OTP verification successful for user: " . $otp['user_id']);
        
        // Mark as used
        $stmt = $this->pdo->prepare("UPDATE user_otps SET is_used = 1 WHERE id = ?");
        $stmt->execute([$otp['id']]);
        
        return $otp['user_id'];
    }
}
?>