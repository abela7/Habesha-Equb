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
        $this->updateRateLimit($to_email, $template);
        
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
        
        // Replace variables
        foreach ($variables as $key => $value) {
            $html_content = str_replace('{{' . $key . '}}', htmlspecialchars($value), $html_content);
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
            $message = "From: {$this->smtp_config['from_name']} <{$this->smtp_config['from_email']}>\r\n";
            $message .= "To: {$to_name} <{$to_email}>\r\n";
            $message .= "Subject: {$subject}\r\n";
            $message .= "MIME-Version: 1.0\r\n";
            $message .= "Content-Type: multipart/alternative; boundary=\"boundary123\"\r\n";
            $message .= "List-Unsubscribe: <mailto:unsubscribe@habeshaequb.com>\r\n";
            $message .= "\r\n";
            
            // Multipart content
            $message .= "--boundary123\r\n";
            $message .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
            $message .= $text_content . "\r\n";
            
            $message .= "--boundary123\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
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
        $limits = [
            'email_verification' => ['count' => 3, 'period' => 3600], // 3 per hour
            'welcome' => ['count' => 1, 'period' => 86400], // 1 per day
            'approval_notification' => ['count' => 1, 'period' => 86400] // 1 per day
        ];
        
        $limit = $limits[$type] ?? ['count' => 5, 'period' => 3600];
        
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
            'approval_notification' => 86400 // 1 day
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
        $otp_code = sprintf('%06d', mt_rand(100000, 999999));
        $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Clean up old OTPs
        $stmt = $this->pdo->prepare("DELETE FROM user_otps WHERE email = ? AND otp_type = ?");
        $stmt->execute([$email, $type]);
        
        // Insert new OTP
        $stmt = $this->pdo->prepare("
            INSERT INTO user_otps (user_id, email, otp_code, otp_type, expires_at) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $email, $otp_code, $type, $expires_at]);
        
        return $otp_code;
    }
    
    /**
     * Verify OTP code
     */
    public function verifyOTP($email, $otp_code, $type = 'email_verification') {
        $stmt = $this->pdo->prepare("
            SELECT id, user_id, attempt_count FROM user_otps 
            WHERE email = ? AND otp_code = ? AND otp_type = ? 
            AND expires_at > NOW() AND is_used = 0
        ");
        $stmt->execute([$email, $otp_code, $type]);
        $otp = $stmt->fetch();
        
        if (!$otp) {
            // Increment attempt count for wrong codes
            $stmt = $this->pdo->prepare("
                UPDATE user_otps SET attempt_count = attempt_count + 1 
                WHERE email = ? AND otp_type = ? AND is_used = 0
            ");
            $stmt->execute([$email, $type]);
            return false;
        }
        
        // Mark as used
        $stmt = $this->pdo->prepare("UPDATE user_otps SET is_used = 1 WHERE id = ?");
        $stmt->execute([$otp['id']]);
        
        return $otp['user_id'];
    }
}
?>