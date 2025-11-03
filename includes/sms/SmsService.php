<?php
/**
 * HabeshaEqub SMS Service
 * Sends transactional SMS via Brevo API
 */

class SmsService {
    private $pdo;
    private $api_key;
    private $sender_name;
    private $api_endpoint = 'https://api.brevo.com/v3/transactionalSMS/sms';
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->loadSMSConfig();
    }
    
    /**
     * Load SMS configuration from system_settings
     */
    private function loadSMSConfig() {
        $stmt = $this->pdo->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_category = 'sms'");
        $stmt->execute();
        
        $config = [];
        while ($row = $stmt->fetch()) {
            $config[$row['setting_key']] = $row['setting_value'];
        }
        
        $this->api_key = $config['sms_api_key'] ?? '';
        $this->sender_name = $config['sms_sender_name'] ?? 'HabeshaEqub';
    }
    
    /**
     * Send program notification SMS to a member
     * @param array $memberRow Member data from database
     * @param string $title_en English title
     * @param string $title_am Amharic title
     * @param string $body_en English body
     * @param string $body_am Amharic body
     * @return array Result with success status
     */
    public function sendProgramNotificationToMember(array $memberRow, string $title_en, string $title_am, string $body_en, string $body_am) {
        $phone = $memberRow['phone'] ?? '';
        $firstName = $memberRow['first_name'] ?? '';
        $isAmharic = (int)($memberRow['language_preference'] ?? 0) === 1;
        
        // Choose language based on member preference
        $title = $isAmharic ? $title_am : $title_en;
        $body = $isAmharic ? $body_am : $body_en;
        
        // Format SMS content (160 chars for standard, 70 for Unicode/Amharic)
        // For Amharic, we need to be mindful of character limits
        $greeting = $firstName ? ($isAmharic ? "ውድ $firstName, " : "Dear $firstName, ") : '';
        $message = $greeting . $body;
        
        // If message is too long, truncate with ellipsis
        $maxLength = $isAmharic ? 300 : 500; // Allow for concatenated SMS
        if (mb_strlen($message) > $maxLength) {
            $message = mb_substr($message, 0, $maxLength - 3) . '...';
        }
        
        return $this->sendSMS($phone, $message);
    }
    
    /**
     * Send SMS via Brevo API
     * @param string $phone Phone number in E.164 format (e.g., +447123456789)
     * @param string $message SMS content
     * @return array Result array with success status
     */
    public function sendSMS($phone, $message) {
        $start_time = microtime(true);
        
        // Validate API key
        if (empty($this->api_key)) {
            return [
                'success' => false,
                'message' => 'SMS API key not configured',
                'delivery_time' => 0
            ];
        }
        
        // Validate phone number
        $phone = $this->formatPhoneNumber($phone);
        if (!$phone) {
            return [
                'success' => false,
                'message' => 'Invalid phone number format',
                'delivery_time' => 0
            ];
        }
        
        // Check rate limit
        if (!$this->checkRateLimit($phone)) {
            return [
                'success' => false,
                'message' => 'SMS rate limit exceeded',
                'delivery_time' => 0
            ];
        }
        
        // Prepare API request
        $payload = [
            'type' => 'transactional',
            'sender' => $this->sender_name,
            'recipient' => $phone,
            'content' => $message
        ];
        
        // Send via Brevo API
        $ch = curl_init($this->api_endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'api-key: ' . $this->api_key,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        $delivery_time = round((microtime(true) - $start_time) * 1000, 2);
        
        // Handle response
        if ($curl_error) {
            return [
                'success' => false,
                'message' => 'SMS API connection error: ' . $curl_error,
                'delivery_time' => $delivery_time
            ];
        }
        
        $response_data = json_decode($response, true);
        
        if ($http_code === 201 || $http_code === 200) {
            // SMS sent successfully
            $this->updateRateLimit($phone);
            
            return [
                'success' => true,
                'message' => 'SMS sent successfully',
                'delivery_time' => $delivery_time,
                'message_id' => $response_data['messageId'] ?? null,
                'credits_remaining' => $response_data['remainingCredits'] ?? null
            ];
        } else {
            // SMS failed
            $error_message = $response_data['message'] ?? 'Unknown error';
            
            return [
                'success' => false,
                'message' => 'SMS sending failed: ' . $error_message,
                'delivery_time' => $delivery_time,
                'http_code' => $http_code,
                'response' => $response_data
            ];
        }
    }
    
    /**
     * Format phone number to E.164 format
     * @param string $phone Raw phone number
     * @return string|false Formatted phone or false if invalid
     */
    private function formatPhoneNumber($phone) {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // If already in E.164 format (starts with +)
        if (strpos($phone, '+') === 0) {
            return $phone;
        }
        
        // If starts with 0 (UK format), convert to +44
        if (strpos($phone, '0') === 0) {
            return '+44' . substr($phone, 1);
        }
        
        // If starts with 44 (without +), add +
        if (strpos($phone, '44') === 0) {
            return '+' . $phone;
        }
        
        // If it's just digits and looks like UK number (10-11 digits)
        if (strlen($phone) >= 10 && strlen($phone) <= 11) {
            return '+44' . ltrim($phone, '0');
        }
        
        // Invalid format
        return false;
    }
    
    /**
     * Check SMS rate limiting
     * @param string $phone Phone number
     * @return bool True if within limits
     */
    private function checkRateLimit($phone) {
        // Allow 10 SMS per hour per phone number
        $limit = 10;
        $period = 3600; // 1 hour in seconds
        
        $stmt = $this->pdo->prepare("
            SELECT sent_count FROM sms_rate_limits 
            WHERE phone_number = ? AND reset_at > NOW()
        ");
        $stmt->execute([$phone]);
        $current = $stmt->fetch();
        
        return !$current || $current['sent_count'] < $limit;
    }
    
    /**
     * Update SMS rate limit counter
     * @param string $phone Phone number
     */
    private function updateRateLimit($phone) {
        $period = 3600; // 1 hour
        
        $stmt = $this->pdo->prepare("
            INSERT INTO sms_rate_limits (phone_number, sent_count, reset_at, last_sent_at) 
            VALUES (?, 1, DATE_ADD(NOW(), INTERVAL ? SECOND), NOW())
            ON DUPLICATE KEY UPDATE 
            sent_count = sent_count + 1,
            last_sent_at = NOW()
        ");
        $stmt->execute([$phone, $period]);
    }
    
    /**
     * Get SMS balance/credits from Brevo (optional monitoring)
     * @return array|false Account info or false on error
     */
    public function getAccountInfo() {
        if (empty($this->api_key)) {
            return false;
        }
        
        $ch = curl_init('https://api.brevo.com/v3/account');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'api-key: ' . $this->api_key,
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            return json_decode($response, true);
        }
        
        return false;
    }
}

