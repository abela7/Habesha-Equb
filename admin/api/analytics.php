<?php
// Admin Analytics API - read-only dynamic analytics
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json; charset=utf-8');

require_once '../../includes/db.php';
require_once '../includes/admin_auth_guard.php';

$admin_id = get_current_admin_id();
if (!$admin_id) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }

$action = $_GET['action'] ?? 'summary';
$equb_id = (int)($_GET['equb_id'] ?? 0);
if ($equb_id <= 0) { echo json_encode(['success'=>false,'message'=>'equb_id required']); exit; }

try {
    switch ($action) {
        case 'summary':
            echo json_encode(summary($equb_id));
            break;
        default:
            http_response_code(400);
            echo json_encode(['success'=>false,'message'=>'Invalid action']);
    }
} catch (Throwable $e) {
    error_log('Analytics API error: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Server error']);
}

function summary(int $equb_id): array {
    global $pdo;

    // Expected total based on members’ monthly_payment * duration (for active members in this equb)
    $eq = $pdo->prepare("SELECT duration_months, admin_fee FROM equb_settings WHERE id = ? LIMIT 1");
    $eq->execute([$equb_id]);
    $equb = $eq->fetch(PDO::FETCH_ASSOC) ?: ['duration_months'=>0,'admin_fee'=>0];

    $m = $pdo->prepare("SELECT COUNT(*) AS total_positions,
        SUM(CASE WHEN membership_type='joint' THEN 1 ELSE 1 END) AS positions,
        SUM(monthly_payment) AS sum_monthly
        FROM (
          SELECT CASE WHEN m.membership_type='joint' THEN CONCAT('j',m.joint_group_id) ELSE CONCAT('i',m.id) END AS pos_key,
                 m.membership_type,
                 COALESCE(jmg.total_monthly_payment, m.monthly_payment) AS monthly_payment
          FROM members m
          LEFT JOIN joint_membership_groups jmg ON m.joint_group_id=jmg.joint_group_id
          WHERE m.equb_settings_id = ? AND m.is_active=1
          GROUP BY pos_key
        ) x");
    $m->execute([$equb_id]);
    $mrow = $m->fetch(PDO::FETCH_ASSOC) ?: ['total_positions'=>0,'sum_monthly'=>0];

    $expected_total = (float)($mrow['sum_monthly'] ?? 0) * (int)($equb['duration_months'] ?? 0);

    // Collected contributions to date (paid)
    $col = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status IN ('paid','completed') AND member_id IN (SELECT id FROM members WHERE equb_settings_id = ?)");
    $col->execute([$equb_id]);
    $collected = (float)$col->fetchColumn();

    // Net payouts and admin fees to date
    $p = $pdo->prepare("SELECT COALESCE(SUM(net_amount),0) AS net_total, COALESCE(SUM(admin_fee),0) AS fee_total, COUNT(*) AS cnt
                        FROM payouts WHERE member_id IN (SELECT id FROM members WHERE equb_settings_id = ?) AND status='completed'");
    $p->execute([$equb_id]);
    $prow = $p->fetch(PDO::FETCH_ASSOC) ?: ['net_total'=>0,'fee_total'=>0,'cnt'=>0];

    // Monthly timeline last 12 months based on actual payout_date and payments
    $tl = $pdo->prepare("SELECT DATE_FORMAT(COALESCE(actual_payout_date, created_at), '%Y-%m') ym,
                                COALESCE(SUM(net_amount),0) net_sum,
                                COALESCE(SUM(admin_fee),0) fee_sum
                         FROM payouts 
                         WHERE member_id IN (SELECT id FROM members WHERE equb_settings_id = ?)
                         GROUP BY ym ORDER BY ym ASC");
    $tl->execute([$equb_id]);
    $labels = []; $net = []; $fees = [];
    while ($r = $tl->fetch(PDO::FETCH_ASSOC)) {
        $labels[] = date('M Y', strtotime($r['ym'].'-01'));
        $net[] = (float)$r['net_sum'];
        $fees[] = (float)$r['fee_sum'];
    }

    // Distribution: split net payouts by membership type
    $distInd = $pdo->prepare("SELECT COALESCE(SUM(po.net_amount),0)
                              FROM payouts po
                              JOIN members m ON po.member_id=m.id
                              WHERE m.equb_settings_id = ? AND m.membership_type='individual'");
    $distInd->execute([$equb_id]);
    $indSum = (float)$distInd->fetchColumn();
    $distJoint = $pdo->prepare("SELECT COALESCE(SUM(po.net_amount),0)
                                FROM payouts po
                                JOIN members m ON po.member_id=m.id
                                WHERE m.equb_settings_id = ? AND m.membership_type='joint'");
    $distJoint->execute([$equb_id]);
    $jointSum = (float)$distJoint->fetchColumn();

    $collection_rate = $expected_total > 0 ? ($collected / $expected_total) * 100 : 0;

    return [
        'success' => true,
        'summary' => [
            'total_positions' => (int)($mrow['total_positions'] ?? 0),
            'expected_total' => $expected_total,
            'collection_rate' => $collection_rate,
            'admin_revenue_collected' => (float)$prow['fee_total'],
            'payouts_completed' => (int)$prow['cnt'],
            'net_payouts_total' => (float)$prow['net_total'],
            'monthly_pool' => (float)($mrow['sum_monthly'] ?? 0),
            'total_pool_value' => (float)($mrow['sum_monthly'] ?? 0) * (int)($equb['duration_months'] ?? 0),
            'average_payout' => (float)($mrow['sum_monthly'] ?? 0),
        ],
        'charts' => [
            'payout_distribution' => [
                'individual' => $indSum,
                'joint' => $jointSum,
                'admin_revenue' => (float)$prow['fee_total'],
            ],
            'timeline' => [
                'labels' => $labels,
                'net_payouts' => $net,
                'admin_fees' => $fees,
            ]
        ]
    ];
}

?>

