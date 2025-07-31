<?php
/**
 * HabeshaEqub - Advanced Payout Date Synchronization Service
 * TOP TIER LOGIC: Ensures all payout dates are perfectly calculated and synchronized
 */

class PayoutSyncService {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Calculate exact payout date for a member based on their equb settings
     * 
     * @param int $member_id
     * @param bool $update_database Whether to update the member's payout_month in database
     * @return array Detailed payout information
     */
    public function calculateMemberPayoutDate($member_id, $update_database = false) {
        try {
            // Get member and equb data in one query
            $stmt = $this->db->prepare("
                SELECT 
                    m.id,
                    m.payout_position,
                    m.payout_month as current_payout_month,
                    m.first_name,
                    m.last_name,
                    es.start_date,
                    es.duration_months,
                    es.payout_day,
                    es.equb_name,
                    es.status as equb_status,
                    es.end_date
                FROM members m
                JOIN equb_settings es ON m.equb_settings_id = es.id
                WHERE m.id = ? AND m.is_active = 1
            ");
            $stmt->execute([$member_id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$data) {
                throw new Exception("Member not found or not assigned to active equb");
            }
            
            // Calculate exact payout date
            $start_date = new DateTime($data['start_date']);
            $payout_position = (int)$data['payout_position'];
            $payout_day = (int)$data['payout_day'];
            
            // Calculate the payout month (position 1 = start month, position 2 = start + 1 month, etc.)
            $payout_date = clone $start_date;
            $payout_date->add(new DateInterval('P' . ($payout_position - 1) . 'M'));
            $payout_date->setDate(
                $payout_date->format('Y'), 
                $payout_date->format('n'), 
                $payout_day
            );
            
            // Prepare result
            $result = [
                'member_id' => $member_id,
                'member_name' => trim($data['first_name'] . ' ' . $data['last_name']),
                'payout_position' => $payout_position,
                'equb_name' => $data['equb_name'],
                'equb_status' => $data['equb_status'],
                'calculated_payout_date' => $payout_date->format('Y-m-d'),
                'formatted_payout_date' => $payout_date->format('F j, Y'),
                'payout_month_year' => $payout_date->format('Y-m'),
                'days_until_payout' => $this->calculateDaysUntil($payout_date),
                'is_overdue' => $payout_date < new DateTime(),
                'current_db_date' => $data['current_payout_month'],
                'needs_update' => $data['current_payout_month'] !== $payout_date->format('Y-m-d')
            ];
            
            // Update database if requested and needed
            if ($update_database && $result['needs_update']) {
                $this->updateMemberPayoutDate($member_id, $payout_date->format('Y-m-d'));
                $result['updated'] = true;
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("PayoutSyncService Error: " . $e->getMessage());
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Sync all members' payout dates for a specific equb term
     */
    public function syncEqubPayoutDates($equb_settings_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT id FROM members 
                WHERE equb_settings_id = ? AND is_active = 1
                ORDER BY payout_position ASC
            ");
            $stmt->execute([$equb_settings_id]);
            $member_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $results = [];
            foreach ($member_ids as $member_id) {
                $results[] = $this->calculateMemberPayoutDate($member_id, true);
            }
            
            return [
                'success' => true,
                'updated_members' => count($member_ids),
                'results' => $results
            ];
            
        } catch (Exception $e) {
            error_log("PayoutSyncService Equb Sync Error: " . $e->getMessage());
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get comprehensive payout schedule for an equb term
     */
    public function getEqubPayoutSchedule($equb_settings_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    m.id,
                    m.first_name,
                    m.last_name,
                    m.payout_position,
                    m.payout_month,
                    m.monthly_payment,
                    m.has_received_payout,
                    es.start_date,
                    es.payout_day,
                    es.equb_name,
                    po.status as payout_status,
                    po.actual_payout_date
                FROM members m
                JOIN equb_settings es ON m.equb_settings_id = es.id
                LEFT JOIN payouts po ON m.id = po.member_id
                WHERE m.equb_settings_id = ? AND m.is_active = 1
                ORDER BY m.payout_position ASC
            ");
            $stmt->execute([$equb_settings_id]);
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $schedule = [];
            foreach ($members as $member) {
                $payout_info = $this->calculateMemberPayoutDate($member['id']);
                $schedule[] = array_merge($member, $payout_info);
            }
            
            return [
                'success' => true,
                'schedule' => $schedule
            ];
            
        } catch (Exception $e) {
            error_log("PayoutSyncService Schedule Error: " . $e->getMessage());
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update member's payout date in database
     */
    private function updateMemberPayoutDate($member_id, $payout_date) {
        $stmt = $this->db->prepare("
            UPDATE members 
            SET payout_month = ?, updated_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$payout_date, $member_id]);
    }
    
    /**
     * Calculate days until payout
     */
    private function calculateDaysUntil($payout_date) {
        $now = new DateTime();
        $diff = $now->diff($payout_date);
        
        if ($payout_date < $now) {
            return -$diff->days; // Negative for overdue
        }
        
        return $diff->days;
    }
    
    /**
     * Get member's current payout status with detailed information
     */
    public function getMemberPayoutStatus($member_id) {
        $payout_info = $this->calculateMemberPayoutDate($member_id, true);
        
        if (isset($payout_info['error'])) {
            return $payout_info;
        }
        
        // Get payout record if exists
        $stmt = $this->db->prepare("
            SELECT * FROM payouts 
            WHERE member_id = ? 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$member_id]);
        $payout_record = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $payout_info['payout_record'] = $payout_record;
        $payout_info['has_payout_record'] = !empty($payout_record);
        
        if ($payout_record) {
            $payout_info['payout_status'] = $payout_record['status'];
            $payout_info['actual_payout_date'] = $payout_record['actual_payout_date'];
            $payout_info['payout_amount'] = $payout_record['net_amount'];
        }
        
        return $payout_info;
    }
}

/**
 * Quick helper function to get payout sync service instance
 */
function getPayoutSyncService() {
    global $pdo, $db;
    $database = isset($db) ? $db : $pdo;
    return new PayoutSyncService($database);
}

/**
 * Helper function for templates - get member payout info
 */
function getMemberPayoutInfo($member_id, $auto_sync = true) {
    $service = getPayoutSyncService();
    return $service->getMemberPayoutStatus($member_id);
}
?>