<?php
/**
 * HabeshaEqub - Persistent Rate Limiting System
 * Database-based rate limiting to prevent brute force attacks
 */

class PersistentRateLimiter {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->createTableIfNotExists();
    }
    
    /**
     * Create rate limiting table if it doesn't exist
     */
    private function createTableIfNotExists() {
        $sql = "
            CREATE TABLE IF NOT EXISTS rate_limits (
                id INT AUTO_INCREMENT PRIMARY KEY,
                identifier VARCHAR(255) NOT NULL,
                attempts INT DEFAULT 1,
                locked_until TIMESTAMP NULL,
                last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_identifier (identifier),
                INDEX idx_locked_until (locked_until),
                INDEX idx_last_attempt (last_attempt)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Rate limiter table creation error: " . $e->getMessage());
        }
    }
    
    /**
     * Check if action is allowed for given identifier
     */
    public function isAllowed($identifier, $maxAttempts = 5, $timeWindow = 900, $lockoutDuration = 900) {
        $identifier_hash = hash('sha256', $identifier);
        $now = date('Y-m-d H:i:s');
        
        try {
            // Clean up old entries (older than time window)
            $cleanupStmt = $this->pdo->prepare("
                DELETE FROM rate_limits 
                WHERE last_attempt < DATE_SUB(NOW(), INTERVAL ? SECOND)
                AND (locked_until IS NULL OR locked_until < NOW())
            ");
            $cleanupStmt->execute([$timeWindow]);
            
            // Check current status
            $stmt = $this->pdo->prepare("
                SELECT attempts, locked_until, last_attempt 
                FROM rate_limits 
                WHERE identifier = ?
            ");
            $stmt->execute([$identifier_hash]);
            $record = $stmt->fetch();
            
            if (!$record) {
                return ['allowed' => true, 'attempts' => 0];
            }
            
            // Check if currently locked
            if ($record['locked_until'] && strtotime($record['locked_until']) > time()) {
                $remaining = strtotime($record['locked_until']) - time();
                return [
                    'allowed' => false,
                    'message' => "Too many attempts. Try again in " . ceil($remaining / 60) . " minutes.",
                    'retry_after' => $record['locked_until'],
                    'attempts' => $record['attempts']
                ];
            }
            
            // If lock expired, reset
            if ($record['locked_until'] && strtotime($record['locked_until']) <= time()) {
                $this->resetAttempts($identifier);
                return ['allowed' => true, 'attempts' => 0];
            }
            
            // Check attempts within time window
            if ($record['attempts'] >= $maxAttempts) {
                // Lock the identifier
                $lockUntil = date('Y-m-d H:i:s', time() + $lockoutDuration);
                $updateStmt = $this->pdo->prepare("
                    UPDATE rate_limits 
                    SET locked_until = ?, attempts = attempts + 1
                    WHERE identifier = ?
                ");
                $updateStmt->execute([$lockUntil, $identifier_hash]);
                
                return [
                    'allowed' => false,
                    'message' => "Too many attempts. Account locked for " . ceil($lockoutDuration / 60) . " minutes.",
                    'retry_after' => $lockUntil,
                    'attempts' => $record['attempts'] + 1
                ];
            }
            
            return ['allowed' => true, 'attempts' => $record['attempts']];
            
        } catch (Exception $e) {
            error_log("Rate limiter error: " . $e->getMessage());
            // Fail open for availability
            return ['allowed' => true, 'attempts' => 0];
        }
    }
    
    /**
     * Record a failed attempt
     */
    public function recordAttempt($identifier) {
        $identifier_hash = hash('sha256', $identifier);
        
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO rate_limits (identifier, attempts) 
                VALUES (?, 1)
                ON DUPLICATE KEY UPDATE 
                attempts = attempts + 1,
                last_attempt = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$identifier_hash]);
            
            // Log security event
            error_log("SECURITY: Failed login attempt from " . $_SERVER['REMOTE_ADDR'] . " for identifier: " . substr($identifier, 0, 20) . "...");
            
        } catch (Exception $e) {
            error_log("Rate limiter record error: " . $e->getMessage());
        }
    }
    
    /**
     * Reset attempts for identifier (successful login)
     */
    public function resetAttempts($identifier) {
        $identifier_hash = hash('sha256', $identifier);
        
        try {
            $stmt = $this->pdo->prepare("DELETE FROM rate_limits WHERE identifier = ?");
            $stmt->execute([$identifier_hash]);
        } catch (Exception $e) {
            error_log("Rate limiter reset error: " . $e->getMessage());
        }
    }
    
    /**
     * Get current attempt count for identifier
     */
    public function getAttemptCount($identifier) {
        $identifier_hash = hash('sha256', $identifier);
        
        try {
            $stmt = $this->pdo->prepare("SELECT attempts FROM rate_limits WHERE identifier = ?");
            $stmt->execute([$identifier_hash]);
            $result = $stmt->fetch();
            return $result ? $result['attempts'] : 0;
        } catch (Exception $e) {
            error_log("Rate limiter count error: " . $e->getMessage());
            return 0;
        }
    }
}
?>