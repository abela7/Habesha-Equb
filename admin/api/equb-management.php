<?php
// Bulletproof error handling and output control
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Security headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// CORS handling for development
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit;
}

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

/**
 * Enterprise-grade JSON response function
 */
function json_response($success, $message, $data = null, $code = 200) {
    ob_clean();
    http_response_code($code);
    
    $response = [
        'success' => $success,
        'message' => $message,
        'timestamp' => date('c'),
        'request_id' => uniqid('req_', true)
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Admin authentication check
 */
function is_admin_authenticated() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Generate unique equb ID
 */
function generate_equb_id($pdo) {
    $year = date('Y');
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM equb_settings WHERE equb_id LIKE ?");
    $stmt->execute(["EQB-{$year}-%"]);
    $count = $stmt->fetchColumn() + 1;
    return sprintf("EQB-%s-%03d", $year, $count);
}

/**
 * Comprehensive input validation
 */
function validate_equb_data($data, $is_update = false) {
    $errors = [];
    
    // Required fields for creation
    if (!$is_update) {
        if (empty($data['equb_name'])) {
            $errors[] = 'Equb name is required';
        }
        if (empty($data['max_members']) || $data['max_members'] < 2) {
            $errors[] = 'Maximum members must be at least 2';
        }
        if (empty($data['duration_months']) || $data['duration_months'] < 1) {
            $errors[] = 'Duration must be at least 1 month';
        }
        if (empty($data['start_date'])) {
            $errors[] = 'Start date is required';
        }
        if (empty($data['payment_tiers'])) {
            $errors[] = 'At least one payment tier is required';
        }
    }
    
    // Validate equb name
    if (isset($data['equb_name'])) {
        if (strlen($data['equb_name']) < 3 || strlen($data['equb_name']) > 100) {
            $errors[] = 'Equb name must be between 3 and 100 characters';
        }
        if (!preg_match('/^[a-zA-Z0-9\s\-_]+$/', $data['equb_name'])) {
            $errors[] = 'Equb name contains invalid characters';
        }
    }
    
    // Validate max members
    if (isset($data['max_members'])) {
        if (!is_numeric($data['max_members']) || $data['max_members'] < 2 || $data['max_members'] > 50) {
            $errors[] = 'Maximum members must be between 2 and 50';
        }
    }
    
    // Validate duration
    if (isset($data['duration_months'])) {
        if (!is_numeric($data['duration_months']) || $data['duration_months'] < 1 || $data['duration_months'] > 24) {
            $errors[] = 'Duration must be between 1 and 24 months';
        }
    }
    
    // Validate dates
    if (isset($data['start_date'])) {
        if (!strtotime($data['start_date'])) {
            $errors[] = 'Invalid start date format';
        } else {
            $start_date = new DateTime($data['start_date']);
            $today = new DateTime();
            if ($start_date < $today->sub(new DateInterval('P30D'))) {
                $errors[] = 'Start date cannot be more than 30 days in the past';
            }
        }
    }
    
    // Validate financial fields
    if (isset($data['admin_fee'])) {
        if (!is_numeric($data['admin_fee']) || $data['admin_fee'] < 0 || $data['admin_fee'] > 1000) {
            $errors[] = 'Admin fee must be between 0 and 1000';
        }
    }
    
    if (isset($data['late_fee'])) {
        if (!is_numeric($data['late_fee']) || $data['late_fee'] < 0 || $data['late_fee'] > 500) {
            $errors[] = 'Late fee must be between 0 and 500';
        }
    }
    
    // Validate payment tiers
    if (isset($data['payment_tiers'])) {
        $tiers = json_decode($data['payment_tiers'], true);
        if (!$tiers || !is_array($tiers) || empty($tiers)) {
            $errors[] = 'Invalid payment tiers format';
        } else {
            foreach ($tiers as $tier) {
                if (!isset($tier['amount']) || !isset($tier['tag'])) {
                    $errors[] = 'Each payment tier must have amount and tag';
                    break;
                }
                if (!is_numeric($tier['amount']) || $tier['amount'] <= 0 || $tier['amount'] > 10000) {
                    $errors[] = 'Payment tier amounts must be between 1 and 10,000';
                    break;
                }
                if (empty($tier['tag']) || strlen($tier['tag']) > 20) {
                    $errors[] = 'Payment tier tags must be 1-20 characters';
                    break;
                }
            }
        }
    }
    
    // Validate status
    if (isset($data['status'])) {
        $valid_statuses = ['planning', 'active', 'completed', 'suspended', 'cancelled'];
        if (!in_array($data['status'], $valid_statuses)) {
            $errors[] = 'Invalid status value';
        }
    }
    
    return $errors;
}

/**
 * Calculate total pool amount based on tiers and max members
 */
function calculate_total_pool($payment_tiers, $max_members, $duration_months) {
    $tiers = json_decode($payment_tiers, true);
    if (!$tiers) return 0;
    
    // Use highest tier amount as base calculation
    $max_amount = max(array_column($tiers, 'amount'));
    return $max_amount * $max_members * $duration_months;
}

// Authentication check
if (!is_admin_authenticated()) {
    json_response(false, 'Authentication required', null, 401);
}

$admin_id = $_SESSION['admin_id'];

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
            // Load all equb settings with statistics
            $stmt = $pdo->prepare("
                SELECT 
                    es.*,
                    COUNT(m.id) as current_members,
                    COALESCE(SUM(p.amount), 0) as collected_amount,
                    COALESCE(SUM(po.net_amount), 0) as distributed_amount
                FROM equb_settings es
                LEFT JOIN members m ON m.equb_settings_id = es.id AND m.is_active = 1
                LEFT JOIN payments p ON p.member_id = m.id AND p.status = 'paid'
                LEFT JOIN payouts po ON po.member_id = m.id AND po.status = 'completed'
                GROUP BY es.id
                ORDER BY es.created_at DESC
            ");
            $stmt->execute();
            $equbs = $stmt->fetchAll();
            
            // Calculate statistics
            $stats = [
                'total_equbs' => count($equbs),
                'active_equbs' => count(array_filter($equbs, fn($e) => $e['status'] === 'active')),
                'total_pool' => array_sum(array_column($equbs, 'total_pool_amount')),
                'total_members' => array_sum(array_column($equbs, 'current_members'))
            ];
            
            // Update current members count in database
            foreach ($equbs as &$equb) {
                $stmt = $pdo->prepare("
                    UPDATE equb_settings 
                    SET current_members = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$equb['current_members'], $equb['id']]);
            }
            
            json_response(true, 'Data loaded successfully', [
                'equbs' => $equbs,
                'stats' => $stats
            ]);
            break;
            
        case 'create':
            // Validate input
            $validation_errors = validate_equb_data($data);
            if (!empty($validation_errors)) {
                json_response(false, 'Validation failed', ['errors' => $validation_errors], 400);
            }
            
            // Check for duplicate name
            $stmt = $pdo->prepare("SELECT id FROM equb_settings WHERE equb_name = ?");
            $stmt->execute([$data['equb_name']]);
            if ($stmt->fetch()) {
                json_response(false, 'Equb name already exists', null, 409);
            }
            
            // Generate unique ID
            $equb_id = generate_equb_id($pdo);
            
            // Calculate total pool
            $total_pool = calculate_total_pool(
                $data['payment_tiers'], 
                $data['max_members'], 
                $data['duration_months']
            );
            
            // Create equb
            $stmt = $pdo->prepare("
                INSERT INTO equb_settings (
                    equb_id, equb_name, equb_description, status, max_members, duration_months,
                    start_date, end_date, payment_tiers, payout_day, admin_fee, late_fee,
                    grace_period_days, auto_assign_positions, approval_required, 
                    registration_start_date, registration_end_date, is_public, is_featured,
                    total_pool_amount, notes, created_by_admin_id, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
                )
            ");
            
            $stmt->execute([
                $equb_id,
                $data['equb_name'],
                $data['equb_description'] ?? null,
                $data['status'] ?? 'planning',
                $data['max_members'],
                $data['duration_months'],
                $data['start_date'],
                $data['end_date'],
                $data['payment_tiers'],
                $data['payout_day'] ?? 5,
                $data['admin_fee'] ?? 10.00,
                $data['late_fee'] ?? 20.00,
                $data['grace_period_days'] ?? 2,
                $data['auto_assign_positions'] ?? 1,
                $data['approval_required'] ?? 1,
                $data['registration_start_date'] ?? null,
                $data['registration_end_date'] ?? null,
                $data['is_public'] ?? 1,
                $data['is_featured'] ?? 0,
                $total_pool,
                $data['notes'] ?? null,
                $admin_id
            ]);
            
            $new_id = $pdo->lastInsertId();
            
            json_response(true, 'Equb term created successfully', [
                'id' => $new_id,
                'equb_id' => $equb_id
            ]);
            break;
            
        case 'update':
            if (empty($data['id'])) {
                json_response(false, 'Equb ID is required', null, 400);
            }
            
            // Validate input
            $validation_errors = validate_equb_data($data, true);
            if (!empty($validation_errors)) {
                json_response(false, 'Validation failed', ['errors' => $validation_errors], 400);
            }
            
            // Check if equb exists and get current data
            $stmt = $pdo->prepare("SELECT * FROM equb_settings WHERE id = ?");
            $stmt->execute([$data['id']]);
            $current_equb = $stmt->fetch();
            
            if (!$current_equb) {
                json_response(false, 'Equb not found', null, 404);
            }
            
            // Check for duplicate name (excluding current record)
            if (isset($data['equb_name'])) {
                $stmt = $pdo->prepare("SELECT id FROM equb_settings WHERE equb_name = ? AND id != ?");
                $stmt->execute([$data['equb_name'], $data['id']]);
                if ($stmt->fetch()) {
                    json_response(false, 'Equb name already exists', null, 409);
                }
            }
            
            // Build update query dynamically
            $update_fields = [];
            $update_values = [];
            
            $allowed_fields = [
                'equb_name', 'equb_description', 'status', 'max_members', 'duration_months',
                'start_date', 'end_date', 'payment_tiers', 'payout_day', 'admin_fee', 
                'late_fee', 'grace_period_days', 'auto_assign_positions', 'approval_required',
                'registration_start_date', 'registration_end_date', 'is_public', 'is_featured', 'notes'
            ];
            
            foreach ($allowed_fields as $field) {
                if (isset($data[$field])) {
                    $update_fields[] = "$field = ?";
                    $update_values[] = $data[$field];
                }
            }
            
            // Recalculate total pool if relevant fields changed
            $max_members = $data['max_members'] ?? $current_equb['max_members'];
            $duration = $data['duration_months'] ?? $current_equb['duration_months'];
            $tiers = $data['payment_tiers'] ?? $current_equb['payment_tiers'];
            
            $total_pool = calculate_total_pool($tiers, $max_members, $duration);
            $update_fields[] = "total_pool_amount = ?";
            $update_values[] = $total_pool;
            
            $update_fields[] = "updated_at = NOW()";
            $update_values[] = $data['id'];
            
            $sql = "UPDATE equb_settings SET " . implode(', ', $update_fields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($update_values);
            
            json_response(true, 'Equb term updated successfully');
            break;
            
        case 'delete':
            if (empty($data['id'])) {
                json_response(false, 'Equb ID is required', null, 400);
            }
            
            // Check if equb exists
            $stmt = $pdo->prepare("SELECT * FROM equb_settings WHERE id = ?");
            $stmt->execute([$data['id']]);
            $equb = $stmt->fetch();
            
            if (!$equb) {
                json_response(false, 'Equb not found', null, 404);
            }
            
            // Check if equb has members
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM members WHERE equb_settings_id = ?");
            $stmt->execute([$data['id']]);
            $member_count = $stmt->fetchColumn();
            
            if ($member_count > 0) {
                json_response(false, 'Cannot delete equb with enrolled members', [
                    'member_count' => $member_count
                ], 409);
            }
            
            // Check if equb has payments
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM payments p 
                JOIN members m ON m.id = p.member_id 
                WHERE m.equb_settings_id = ?
            ");
            $stmt->execute([$data['id']]);
            $payment_count = $stmt->fetchColumn();
            
            if ($payment_count > 0) {
                json_response(false, 'Cannot delete equb with payment history', [
                    'payment_count' => $payment_count
                ], 409);
            }
            
            // Soft delete by changing status
            $stmt = $pdo->prepare("
                UPDATE equb_settings 
                SET status = 'cancelled', updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$data['id']]);
            
            json_response(true, 'Equb term deleted successfully');
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
                    AVG(current_members / max_members * 100) as avg_fill_rate,
                    AVG(collected_amount / total_pool_amount * 100) as avg_collection_rate,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_equbs,
                    AVG(duration_months) as avg_duration
                FROM equb_settings
                WHERE total_pool_amount > 0
            ");
            $stats['performance'] = $stmt->fetch();
            
            json_response(true, 'Statistics retrieved successfully', $stats);
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