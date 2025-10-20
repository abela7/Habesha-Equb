<?php
/**
 * HabeshaEqub - Equb Management API
 * Robust API for equb term management with proper error handling
 */

// Strict error handling
error_reporting(0);
ini_set('display_errors', 0);

// Session and authentication
session_start();
require_once '../../includes/db.php';

// Use existing database connection from db.php
try {
    // The $pdo connection is already available from db.php
    if (!isset($pdo)) {
        json_response(false, 'Database connection not available', ['error' => 'CONNECTION_FAILED']);
    }
} catch (Exception $e) {
    json_response(false, 'Database connection failed', ['error' => 'CONNECTION_FAILED']);
}

// Security headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

/**
 * Send JSON response
 */
function json_response($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Check if admin is authenticated
 */
function is_admin_authenticated() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Generate unique equb ID
 */
function generate_equb_id($pdo) {
    $year = date('Y');
    $stmt = $pdo->prepare("SELECT COUNT(*) + 1 as next_num FROM equb_settings WHERE equb_id LIKE ?");
    $stmt->execute(["EQB-{$year}-%"]);
    $result = $stmt->fetch();
    $num = str_pad($result['next_num'], 3, '0', STR_PAD_LEFT);
    return "EQB-{$year}-{$num}";
}

/**
 * Validate equb data
 */
function validate_equb_data($data) {
    $errors = [];
    
    if (empty($data['equb_name']) || strlen($data['equb_name']) < 3) {
        $errors[] = 'Equb name must be at least 3 characters long';
    }
    
    if (!isset($data['max_members']) || $data['max_members'] < 2 || $data['max_members'] > 50) {
        $errors[] = 'Maximum members must be between 2 and 50';
    }
    
    if (!isset($data['duration_months']) || $data['duration_months'] < 1 || $data['duration_months'] > 24) {
        $errors[] = 'Duration must be between 1 and 24 months';
    }
    
    if (empty($data['start_date']) || !strtotime($data['start_date'])) {
        $errors[] = 'Valid start date is required';
    }
    
    if (!empty($data['admin_fee']) && ($data['admin_fee'] < 0 || $data['admin_fee'] > 1000)) {
        $errors[] = 'Admin fee must be between 0 and 1000';
    }
    
    if (!empty($data['late_fee']) && ($data['late_fee'] < 0 || $data['late_fee'] > 1000)) {
        $errors[] = 'Late fee must be between 0 and 1000';
    }
    
    if (!empty($data['grace_period_days']) && ($data['grace_period_days'] < 0 || $data['grace_period_days'] > 10)) {
        $errors[] = 'Grace period must be between 0 and 10 days';
    }
    
    if (!empty($data['payment_tiers'])) {
        $tiers = is_string($data['payment_tiers']) ? json_decode($data['payment_tiers'], true) : $data['payment_tiers'];
        if (!is_array($tiers) || empty($tiers)) {
            $errors[] = 'At least one payment tier is required';
        } else {
            foreach ($tiers as $tier) {
                if (!isset($tier['amount']) || $tier['amount'] <= 0) {
                    $errors[] = 'Payment tier amounts must be greater than 0';
                    break;
                }
            }
        }
    }
    
    return $errors;
}

/**
 * Calculate total pool amount - DYNAMIC FROM DATABASE (NO HARDCODE!)
 */
function calculate_total_pool($equb_id, $duration_months) {
    global $pdo;

    // Calculate REAL monthly pool from actual member contributions
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(
            CASE
                WHEN m.membership_type = 'joint' THEN m.individual_contribution
                ELSE m.monthly_payment
            END
        ), 0) as real_monthly_pool
        FROM members m
        WHERE m.equb_settings_id = ? AND m.is_active = 1
    ");
    $stmt->execute([$equb_id]);
    $real_monthly_pool = $stmt->fetchColumn() ?: 0;

    // Total pool = monthly pool × duration (total contributions over entire term)
    return $real_monthly_pool * $duration_months;
}

// Check authentication
if (!is_admin_authenticated()) {
    json_response(false, 'Authentication required', null, 401);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Only POST requests allowed', null, 405);
}

// Get request data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['action'])) {
    json_response(false, 'Invalid request format', null, 400);
}

$action = $data['action'];

try {
    switch ($action) {
        case 'load':
            // Load all equb settings with proper statistics
            $stmt = $pdo->prepare("
                SELECT 
                    es.*,
                    COUNT(DISTINCT CASE WHEN m.is_active = 1 THEN m.id END) as current_members,
                    COALESCE(SUM(CASE WHEN p.status = 'paid' THEN p.amount ELSE 0 END), 0) as collected_amount,
                    COALESCE(SUM(CASE WHEN po.status = 'completed' THEN po.net_amount ELSE 0 END), 0) as distributed_amount
                FROM equb_settings es
                LEFT JOIN members m ON m.equb_settings_id = es.id
                LEFT JOIN payments p ON p.member_id = m.id
                LEFT JOIN payouts po ON po.member_id = m.id
                GROUP BY es.id
                ORDER BY es.created_at DESC
            ");
            $stmt->execute();
            $equbs = $stmt->fetchAll();
            
            // Update current members count in database
            foreach ($equbs as &$equb) {
                $stmt = $pdo->prepare("
                    UPDATE equb_settings 
                    SET current_members = ?, 
                        collected_amount = ?, 
                        distributed_amount = ? 
                    WHERE id = ?
                ");
                $stmt->execute([
                    $equb['current_members'], 
                    $equb['collected_amount'], 
                    $equb['distributed_amount'], 
                    $equb['id']
                ]);
            }
            
            // Calculate overall statistics
            $stats = [
                'total_equbs' => count($equbs),
                'active_equbs' => count(array_filter($equbs, fn($e) => $e['status'] === 'active')),
                'total_pool' => array_sum(array_column($equbs, 'total_pool_amount')),
                'total_members' => array_sum(array_column($equbs, 'current_members'))
            ];
            
            json_response(true, 'Data loaded successfully', [
                'equbs' => $equbs,
                'stats' => $stats
            ]);
            break;
            
        case 'create':
            // Validate input data
            $validation_errors = validate_equb_data($data);
            if (!empty($validation_errors)) {
                json_response(false, 'Validation failed', ['errors' => $validation_errors], 422);
            }
            
            // Generate unique equb ID
            $equb_id = generate_equb_id($pdo);
            
            // Calculate end date
            $start_date = new DateTime($data['start_date']);
            $end_date = clone $start_date;
            $end_date->add(new DateInterval('P' . $data['duration_months'] . 'M'));
            
            // Total pool will be calculated after members are added
            // For new EQUB, set to 0 initially
            $total_pool = 0;
            
            // Prepare data for insertion
            $insert_data = [
                'equb_id' => $equb_id,
                'equb_name' => $data['equb_name'],
                'equb_description' => $data['equb_description'] ?? null,
                'status' => $data['status'] ?? 'planning',
                'max_members' => $data['max_members'],
                'duration_months' => $data['duration_months'],
                'start_date' => $data['start_date'],
                'end_date' => $end_date->format('Y-m-d'),
                'payment_tiers' => is_string($data['payment_tiers']) ? $data['payment_tiers'] : json_encode($data['payment_tiers']),
                'payout_day' => $data['payout_day'] ?? 5,
                'admin_fee' => $data['admin_fee'] ?? 10.00,
                'late_fee' => $data['late_fee'] ?? 20.00,
                'grace_period_days' => $data['grace_period_days'] ?? 2,
                'auto_assign_positions' => isset($data['auto_assign_positions']) ? 1 : 0,
                'approval_required' => isset($data['approval_required']) ? 1 : 0,
                'registration_start_date' => $data['registration_start_date'] ?? null,
                'registration_end_date' => $data['registration_end_date'] ?? null,
                'is_public' => isset($data['is_public']) ? 1 : 0,
                'is_featured' => isset($data['is_featured']) ? 1 : 0,
                'total_pool_amount' => $total_pool,
                'notes' => $data['notes'] ?? null,
                'created_by_admin_id' => $_SESSION['admin_id']
            ];
            
            // Insert into database
            $fields = implode(',', array_keys($insert_data));
            $placeholders = ':' . implode(', :', array_keys($insert_data));
            
            $stmt = $pdo->prepare("INSERT INTO equb_settings ({$fields}) VALUES ({$placeholders})");
            $stmt->execute($insert_data);
            
            json_response(true, 'Equb term created successfully', ['equb_id' => $equb_id]);
            break;
            
        case 'update':
            if (!isset($data['id'])) {
                json_response(false, 'Equb ID is required', null, 400);
            }
            
            // Validate input data
            $validation_errors = validate_equb_data($data);
            if (!empty($validation_errors)) {
                json_response(false, 'Validation failed', ['errors' => $validation_errors], 422);
            }
            
            // Calculate end date if duration or start date changed
            if (isset($data['start_date']) || isset($data['duration_months'])) {
                $start_date = new DateTime($data['start_date']);
                $end_date = clone $start_date;
                $end_date->add(new DateInterval('P' . $data['duration_months'] . 'M'));
                $data['end_date'] = $end_date->format('Y-m-d');
            }
            
            // Recalculate total pool if duration changed (using REAL member data)
            if (isset($data['duration_months'])) {
                $data['total_pool_amount'] = calculate_total_pool(
                    $data['id'], 
                    $data['duration_months']
                );
            }
            
            // Build update query
            $update_fields = [];
            $update_values = [];
            
            $allowed_fields = [
                'equb_name', 'equb_description', 'status', 'max_members', 'duration_months',
                'start_date', 'end_date', 'payment_tiers', 'payout_day', 'admin_fee',
                'late_fee', 'grace_period_days', 'auto_assign_positions', 'approval_required',
                'registration_start_date', 'registration_end_date', 'is_public', 'is_featured',
                'total_pool_amount', 'notes'
            ];
            
            foreach ($allowed_fields as $field) {
                if (isset($data[$field])) {
                    $update_fields[] = "{$field} = ?";
                    $value = $data[$field];
                    
                    // Handle JSON fields
                    if ($field === 'payment_tiers' && is_array($value)) {
                        $value = json_encode($value);
                    }
                    
                    // Handle boolean fields
                    if (in_array($field, ['auto_assign_positions', 'approval_required', 'is_public', 'is_featured'])) {
                        $value = $value ? 1 : 0;
                    }
                    
                    $update_values[] = $value;
                }
            }
            
            if (empty($update_fields)) {
                json_response(false, 'No valid fields to update', null, 400);
            }
            
            $update_values[] = $data['id'];
            $update_query = "UPDATE equb_settings SET " . implode(', ', $update_fields) . " WHERE id = ?";
            
            $stmt = $pdo->prepare($update_query);
            $stmt->execute($update_values);
            
            json_response(true, 'Equb term updated successfully');
            break;
            
        case 'delete':
            if (!isset($data['id'])) {
                json_response(false, 'Equb ID is required', null, 400);
            }
            
            // Check if equb has enrolled members
            $stmt = $pdo->prepare("SELECT COUNT(*) as member_count FROM members WHERE equb_settings_id = ?");
            $stmt->execute([$data['id']]);
            $result = $stmt->fetch();
            
            if ($result['member_count'] > 0) {
                json_response(false, 'Cannot delete equb with enrolled members', null, 422);
            }
            
            // Check if equb has payment history
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as payment_count 
                FROM payments p 
                JOIN members m ON p.member_id = m.id 
                WHERE m.equb_settings_id = ?
            ");
            $stmt->execute([$data['id']]);
            $result = $stmt->fetch();
            
            if ($result['payment_count'] > 0) {
                // Soft delete - change status to cancelled instead of hard delete
                $stmt = $pdo->prepare("UPDATE equb_settings SET status = 'cancelled' WHERE id = ?");
                $stmt->execute([$data['id']]);
                json_response(true, 'Equb term cancelled (has payment history)');
            } else {
                // Hard delete if no payment history
                $stmt = $pdo->prepare("DELETE FROM equb_settings WHERE id = ?");
                $stmt->execute([$data['id']]);
                json_response(true, 'Equb term deleted successfully');
            }
            break;
            
        case 'get_stats':
            // Advanced statistics
            $stats = [];
            
            // Monthly growth
            $stmt = $pdo->query("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as count
                FROM equb_settings 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY month
                ORDER BY month
            ");
            $stats['monthly_growth'] = $stmt->fetchAll();
            
            // Status distribution
            $stmt = $pdo->query("
                SELECT status, COUNT(*) as count 
                FROM equb_settings 
                GROUP BY status
            ");
            $stats['status_distribution'] = $stmt->fetchAll();
            
            // Performance metrics
            $stmt = $pdo->query("
                SELECT 
                    AVG(CASE WHEN max_members > 0 THEN current_members / max_members * 100 ELSE 0 END) as avg_fill_rate,
                    AVG(CASE WHEN total_pool_amount > 0 THEN collected_amount / total_pool_amount * 100 ELSE 0 END) as avg_collection_rate,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_equbs,
                    AVG(duration_months) as avg_duration
                FROM equb_settings
                WHERE total_pool_amount > 0
            ");
            $stats['performance'] = $stmt->fetch();
            
            json_response(true, 'Statistics retrieved successfully', $stats);
            break;
            
        case 'recalculate_all_values':
            // Include the enhanced calculator
            require_once '../../includes/enhanced_equb_calculator.php';
            $calculator = getEnhancedEqubCalculator();
            
            $updated_equbs = 0;
            $updated_members = 0;
            $updated_positions = 0;
            $total_pool_updated = 0;
            $errors = [];
            
            // Get all equb settings
            $stmt = $pdo->query("SELECT * FROM equb_settings ORDER BY id");
            $equbs = $stmt->fetchAll();
            
            foreach ($equbs as $equb) {
                try {
                    // Calculate positions using enhanced calculator
                    $calculation_result = $calculator->calculateEqubPositions($equb['id']);
                    
                    if ($calculation_result['success']) {
                        $monthly_pool = $calculation_result['total_monthly_pool'];
                        $duration = $equb['duration_months'];
                        $new_total_pool = $monthly_pool * $duration;
                        
                        // Update equb settings with recalculated values
                        $stmt = $pdo->prepare("
                            UPDATE equb_settings 
                            SET 
                                total_pool_amount = ?,
                                current_members = (
                                    SELECT COUNT(*) FROM members 
                                    WHERE equb_settings_id = ? AND is_active = 1
                                ),
                                collected_amount = (
                                    SELECT COALESCE(SUM(p.amount), 0) 
                                    FROM payments p 
                                    JOIN members m ON p.member_id = m.id 
                                    WHERE m.equb_settings_id = ? AND p.status = 'paid'
                                ),
                                distributed_amount = (
                                    SELECT COALESCE(SUM(po.net_amount), 0) 
                                    FROM payouts po 
                                    JOIN members m ON po.member_id = m.id 
                                    WHERE m.equb_settings_id = ? AND po.status = 'completed'
                                ),
                                updated_at = NOW()
                            WHERE id = ?
                        ");
                        $stmt->execute([
                            $new_total_pool,
                            $equb['id'],
                            $equb['id'],
                            $equb['id'],
                            $equb['id']
                        ]);
                        
                        $updated_equbs++;
                        $total_pool_updated += $new_total_pool;
                        
                        // Update member position coefficients and expected payouts
                        foreach ($calculation_result['position_analysis'] as $member_analysis) {
                            $stmt = $pdo->prepare("
                                UPDATE members 
                                SET 
                                    expected_payout = ?,
                                    position_coefficient = ?,
                                    updated_at = NOW()
                                WHERE id = ?
                            ");
                            $stmt->execute([
                                $member_analysis['expected_payout'],
                                $member_analysis['position_coefficient'],
                                $member_analysis['member_id']
                            ]);
                            
                            $updated_members++;
                        }
                        
                        // Update payout positions table if exists
                        $stmt = $pdo->prepare("
                            UPDATE payout_positions pp
                            JOIN members m ON pp.member_id = m.id
                            SET 
                                pp.expected_payout = m.expected_payout,
                                pp.updated_at = NOW()
                            WHERE m.equb_settings_id = ?
                        ");
                        $stmt->execute([$equb['id']]);
                        $updated_positions += $stmt->rowCount();
                        
                    } else {
                        $errors[] = "Failed to calculate equb {$equb['equb_name']}: " . ($calculation_result['error'] ?? 'Unknown error');
                    }
                    
                } catch (Exception $e) {
                    $errors[] = "Error processing equb {$equb['equb_name']}: " . $e->getMessage();
                }
            }
            
            // Update regular_payment_tier in equb_settings based on most common payment amount
            $stmt = $pdo->query("
                UPDATE equb_settings es
                SET regular_payment_tier = (
                    SELECT COALESCE(
                        (SELECT monthly_payment
                         FROM members m
                         WHERE m.equb_settings_id = es.id
                           AND m.is_active = 1
                           AND m.membership_type = 'individual'
                         GROUP BY monthly_payment
                         ORDER BY COUNT(*) DESC
                         LIMIT 1),
                        (SELECT AVG(monthly_payment) FROM members WHERE equb_settings_id = es.id AND is_active = 1 AND membership_type = 'individual')
                    )
                )
                WHERE id IN (SELECT DISTINCT equb_settings_id FROM members WHERE is_active = 1)
            ");
            
            $message = "Recalculation completed! Updated {$updated_equbs} equb terms with total pool value of £" . number_format($total_pool_updated, 2);
            
            if (!empty($errors)) {
                $message .= ". Some errors occurred: " . implode(', ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= " and " . (count($errors) - 3) . " more...";
                }
            }
            
            json_response(true, $message, [
                'updated_equbs' => $updated_equbs,
                'updated_members' => $updated_members,
                'updated_positions' => $updated_positions,
                'total_pool_updated' => $total_pool_updated,
                'errors' => $errors
            ]);
            break;
            
        default:
            json_response(false, 'Invalid action specified', null, 400);
    }
    
} catch (PDOException $e) {
    error_log("Database error in equb-management.php: " . $e->getMessage());
    json_response(false, 'Database operation failed', ['error' => 'DB_ERROR'], 500);
} catch (Exception $e) {
    error_log("General error in equb-management.php: " . $e->getMessage());
    json_response(false, 'An unexpected error occurred', ['error' => 'GENERAL_ERROR'], 500);
}
?>