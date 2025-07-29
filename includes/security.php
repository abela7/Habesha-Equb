<?php
/**
 * HabeshaEqub - Advanced Security System
 * Comprehensive protection against common web application attacks
 */

if (!defined('SECURITY_LOADED')) {
    define('SECURITY_LOADED', true);
}

/**
 * Rate Limiting Class
 * Prevents brute force attacks
 */
class RateLimiter {
    private static $attempts = [];
    private static $lockouts = [];
    
    public static function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 900) { // 15 minutes
        $now = time();
        $key = hash('sha256', $identifier);
        
        // Clean old lockouts
        if (isset(self::$lockouts[$key]) && self::$lockouts[$key] < $now) {
            unset(self::$lockouts[$key]);
            unset(self::$attempts[$key]);
        }
        
        // Check if locked out
        if (isset(self::$lockouts[$key])) {
            $remainingTime = self::$lockouts[$key] - $now;
            return [
                'allowed' => false, 
                'message' => "Too many attempts. Try again in " . ceil($remainingTime / 60) . " minutes.",
                'retry_after' => self::$lockouts[$key]
            ];
        }
        
        // Initialize attempts array
        if (!isset(self::$attempts[$key])) {
            self::$attempts[$key] = [];
        }
        
        // Clean old attempts
        self::$attempts[$key] = array_filter(self::$attempts[$key], function($time) use ($now, $timeWindow) {
            return ($now - $time) < $timeWindow;
        });
        
        // Check if exceeded attempts
        if (count(self::$attempts[$key]) >= $maxAttempts) {
            self::$lockouts[$key] = $now + $timeWindow;
            return [
                'allowed' => false, 
                'message' => "Too many attempts. Account locked for 15 minutes.",
                'retry_after' => self::$lockouts[$key]
            ];
        }
        
        return ['allowed' => true];
    }
    
    public static function recordAttempt($identifier) {
        $key = hash('sha256', $identifier);
        if (!isset(self::$attempts[$key])) {
            self::$attempts[$key] = [];
        }
        self::$attempts[$key][] = time();
        
        // Log suspicious activity
        error_log("Security Alert: Failed attempt from " . $identifier . " at " . date('Y-m-d H:i:s'));
    }
    
    public static function resetAttempts($identifier) {
        $key = hash('sha256', $identifier);
        unset(self::$attempts[$key]);
        unset(self::$lockouts[$key]);
    }
}

/**
 * Advanced Input Validation and Sanitization
 */
class SecurityValidator {
    
    /**
     * Comprehensive input sanitization
     */
    public static function sanitizeInput($data, $type = 'text') {
        if (is_array($data)) {
            return array_map(function($item) use ($type) {
                return self::sanitizeInput($item, $type);
            }, $data);
        }
        
        // Remove null bytes (potential for file inclusion attacks)
        $data = str_replace("\0", '', $data);
        
        // Basic sanitization
        $data = trim($data);
        
        switch ($type) {
            case 'sql':
                // For SQL LIKE queries - escape special characters
                return addcslashes($data, '%_\\');
                
            case 'filename':
                // Remove dangerous characters from filenames
                return preg_replace('/[^a-zA-Z0-9._-]/', '', $data);
                
            case 'alphanum':
                // Only alphanumeric characters
                return preg_replace('/[^a-zA-Z0-9]/', '', $data);
                
            case 'numeric':
                // Only numbers
                return preg_replace('/[^0-9.]/', '', $data);
                
            case 'email':
                // Email sanitization
                return filter_var($data, FILTER_SANITIZE_EMAIL);
                
            case 'url':
                // URL sanitization
                return filter_var($data, FILTER_SANITIZE_URL);
                
            case 'html':
                // Allow basic HTML but prevent XSS
                return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                
            case 'json':
                // Validate JSON
                json_decode($data);
                return (json_last_error() === JSON_ERROR_NONE) ? $data : '';
                
            default:
                // Default: prevent XSS
                return htmlspecialchars(strip_tags($data), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }
    
    /**
     * Validate input against specific patterns
     */
    public static function validateInput($data, $type, $required = true) {
        if (empty($data) && $required) {
            return ['valid' => false, 'message' => ucfirst($type) . ' is required'];
        }
        
        if (empty($data) && !$required) {
            return ['valid' => true, 'value' => ''];
        }
        
        switch ($type) {
            case 'username':
                if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $data)) {
                    return ['valid' => false, 'message' => 'Username must be 3-30 characters (letters, numbers, underscore only)'];
                }
                break;
                
            case 'email':
                if (!filter_var($data, FILTER_VALIDATE_EMAIL)) {
                    return ['valid' => false, 'message' => 'Invalid email format'];
                }
                if (strlen($data) > 255) {
                    return ['valid' => false, 'message' => 'Email too long'];
                }
                break;
                
            case 'phone':
                $phone = preg_replace('/[^0-9+]/', '', $data);
                if (strlen($phone) < 10 || strlen($phone) > 15) {
                    return ['valid' => false, 'message' => 'Invalid phone number'];
                }
                break;
                
            case 'password':
                if (strlen($data) < 8) {
                    return ['valid' => false, 'message' => 'Password must be at least 8 characters'];
                }
                if (!preg_match('/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $data)) {
                    return ['valid' => false, 'message' => 'Password must contain uppercase, lowercase, and number'];
                }
                if (preg_match('/(.)\1{2,}/', $data)) {
                    return ['valid' => false, 'message' => 'Password cannot have repeating characters'];
                }
                break;
                
            case 'name':
                if (!preg_match('/^[a-zA-Z\s\'-]{2,50}$/', $data)) {
                    return ['valid' => false, 'message' => 'Name must be 2-50 characters (letters, spaces, hyphens, apostrophes only)'];
                }
                break;
                
            case 'amount':
                if (!is_numeric($data) || $data < 0 || $data > 999999.99) {
                    return ['valid' => false, 'message' => 'Invalid amount'];
                }
                break;
        }
        
        return ['valid' => true, 'value' => self::sanitizeInput($data, $type)];
    }
    
    /**
     * Check for suspicious patterns
     */
    public static function detectSuspiciousInput($data) {
        $suspiciousPatterns = [
            // SQL Injection patterns
            '/(\bunion\b|\bselect\b|\binsert\b|\bupdate\b|\bdelete\b|\bdrop\b|\bcreate\b|\balter\b)/i',
            '/(\bor\b|\band\b)\s*\d*\s*=\s*\d*/i',
            '/[\'";].*(\bor\b|\band\b)/i',
            '/(script|javascript|vbscript|onload|onerror|onclick)/i',
            
            // XSS patterns
            '/<script[^>]*>.*?<\/script>/i',
            '/<iframe[^>]*>.*?<\/iframe>/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/on\w+\s*=/i',
            
            // File inclusion patterns
            '/\.\.[\/\\]/',
            '/\/etc\/passwd/i',
            '/\/proc\//i',
            
            // Command injection
            '/[;&|`$(){}[\]]/i',
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $data)) {
                return true;
            }
        }
        
        return false;
    }
}

/**
 * CSRF Protection
 */
class CSRFProtection {
    
    public static function generateToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function validateToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public static function getTokenField() {
        return '<input type="hidden" name="csrf_token" value="' . self::generateToken() . '">';
    }
}

/**
 * SQL Injection Protection
 */
class SQLProtection {
    
    /**
     * Secure database query wrapper
     */
    public static function query($pdo, $sql, $params = []) {
        try {
            // Check for suspicious SQL patterns
            if (self::detectSQLInjection($sql)) {
                error_log("SECURITY ALERT: Suspicious SQL detected: " . $sql);
                throw new Exception("Invalid query");
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
            
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Database operation failed");
        }
    }
    
    private static function detectSQLInjection($sql) {
        $dangerousPatterns = [
            '/;\s*drop\s+/i',
            '/;\s*delete\s+/i',
            '/;\s*truncate\s+/i',
            '/union\s+select/i',
            '/\'\s*or\s*\'\s*=\s*\'/i',
            '/\"\s*or\s*\"\s*=\s*\"/i',
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $sql)) {
                return true;
            }
        }
        return false;
    }
}

/**
 * Session Security
 */
class SessionSecurity {
    
    public static function startSecureSession() {
        if (session_status() === PHP_SESSION_NONE) {
            $cookieParams = [
                'lifetime' => 3600, // 1 hour
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict'
            ];
            
            session_set_cookie_params($cookieParams);
            session_start();
            
            // Regenerate session ID on login
            if (!isset($_SESSION['regenerated'])) {
                session_regenerate_id(true);
                $_SESSION['regenerated'] = true;
            }
        }
    }
    
    public static function validateSession() {
        // Check session hijacking
        if (isset($_SESSION['user_agent'])) {
            if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
                session_destroy();
                return false;
            }
        } else {
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }
        
        // Check session timeout
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > 3600) { // 1 hour timeout
                session_destroy();
                return false;
            }
        }
        $_SESSION['last_activity'] = time();
        
        return true;
    }
}

/**
 * File Upload Security
 */
class FileUploadSecurity {
    
    private static $allowedTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    public static function validateFile($file, $maxSize = 5242880) { // 5MB default
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['valid' => false, 'message' => 'Invalid file upload'];
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'message' => 'File too large. Maximum size: ' . ($maxSize / 1024 / 1024) . 'MB'];
        }
        
        // Get file extension
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Check allowed extensions
        if (!array_key_exists($fileExt, self::$allowedTypes)) {
            return ['valid' => false, 'message' => 'File type not allowed'];
        }
        
        // Verify MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if ($mimeType !== self::$allowedTypes[$fileExt]) {
            return ['valid' => false, 'message' => 'File type mismatch'];
        }
        
        // Check for embedded PHP code
        $content = file_get_contents($file['tmp_name']);
        if (strpos($content, '<?php') !== false || strpos($content, '<?=') !== false) {
            return ['valid' => false, 'message' => 'Malicious file detected'];
        }
        
        return ['valid' => true];
    }
}

/**
 * Security Logger
 */
class SecurityLogger {
    
    public static function logSecurityEvent($event, $details = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'event' => $event,
            'details' => $details,
            'session_id' => session_id()
        ];
        
        $logLine = json_encode($logEntry) . "\n";
        file_put_contents(__DIR__ . '/../logs/security.log', $logLine, FILE_APPEND | LOCK_EX);
        
        // Also log to PHP error log for critical events
        if (in_array($event, ['sql_injection_attempt', 'xss_attempt', 'brute_force', 'session_hijack'])) {
            error_log("CRITICAL SECURITY EVENT: " . $event . " from " . ($logEntry['ip']));
        }
    }
}

/**
 * Request Validation
 */
class RequestValidator {
    
    public static function validateRequest() {
        // Check for suspicious user agents
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $suspiciousAgents = ['sqlmap', 'nmap', 'nikto', 'curl', 'wget'];
        
        foreach ($suspiciousAgents as $agent) {
            if (stripos($userAgent, $agent) !== false) {
                SecurityLogger::logSecurityEvent('suspicious_user_agent', ['agent' => $userAgent]);
                http_response_code(403);
                die('Access denied');
            }
        }
        
        // Check request size
        $maxSize = 50 * 1024 * 1024; // 50MB
        if ($_SERVER['CONTENT_LENGTH'] ?? 0 > $maxSize) {
            SecurityLogger::logSecurityEvent('oversized_request', ['size' => $_SERVER['CONTENT_LENGTH']]);
            http_response_code(413);
            die('Request too large');
        }
        
        // Check for suspicious parameters
        $allInput = array_merge($_GET, $_POST, $_COOKIE);
        foreach ($allInput as $key => $value) {
            if (SecurityValidator::detectSuspiciousInput($value)) {
                SecurityLogger::logSecurityEvent('suspicious_input', ['key' => $key, 'value' => substr($value, 0, 100)]);
                http_response_code(400);
                die('Invalid request');
            }
        }
    }
}

// Initialize security
SessionSecurity::startSecureSession();
RequestValidator::validateRequest();

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' cdnjs.cloudflare.com cdn.jsdelivr.net; style-src \'self\' \'unsafe-inline\' cdnjs.cloudflare.com cdn.jsdelivr.net; img-src \'self\' data:; font-src \'self\' cdnjs.cloudflare.com;');

?> 