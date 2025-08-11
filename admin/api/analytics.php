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

    $sum_monthly = (float)($mrow['sum_monthly'] ?? 0);
    $duration = (int)($equb['duration_months'] ?? 0);
    $expected_total = $sum_monthly * $duration;

    // Collected contributions to date (paid)
    $col = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status IN ('paid','completed') AND member_id IN (SELECT id FROM members WHERE equb_settings_id = ?)");
    $col->execute([$equb_id]);
    $collected = (float)$col->fetchColumn();

    // Current month collected vs target
    $ym = date('Y-m');
    $cm = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status IN ('paid','completed') AND payment_month LIKE CONCAT(?, '%') AND member_id IN (SELECT id FROM members WHERE equb_settings_id = ?)");
    $cm->execute([$ym, $equb_id]);
    $collected_current_month = (float)$cm->fetchColumn();

    // Net payouts and admin fees to date
    $p = $pdo->prepare("SELECT COALESCE(SUM(net_amount),0) AS net_total, COALESCE(SUM(admin_fee),0) AS fee_total, COUNT(*) AS cnt
                        FROM payouts WHERE member_id IN (SELECT id FROM members WHERE equb_settings_id = ?) AND status='completed'");
    $p->execute([$equb_id]);
    $prow = $p->fetch(PDO::FETCH_ASSOC) ?: ['net_total'=>0,'fee_total'=>0,'cnt'=>0];

    // Scheduled payouts count
    $ps = $pdo->prepare("SELECT COUNT(*) FROM payouts WHERE member_id IN (SELECT id FROM members WHERE equb_settings_id = ?) AND status='scheduled'");
    $ps->execute([$equb_id]);
    $scheduledCount = (int)$ps->fetchColumn();

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

    // Outstanding balance
    $outstanding = max($expected_total - $collected, 0);

    // Overdue members (no paid record for current month)
    $ov = $pdo->prepare("SELECT COUNT(*)
                         FROM members m
                         WHERE m.equb_settings_id = ? AND m.is_active=1
                         AND NOT EXISTS (
                           SELECT 1 FROM payments p
                           WHERE p.member_id = m.id AND p.status IN ('paid','completed')
                           AND p.payment_month LIKE CONCAT(?, '%')
                         )");
    $ov->execute([$equb_id, $ym]);
    $overdue_members = (int)$ov->fetchColumn();

    // Top debtors: remaining months = duration - distinct months paid
    $top = $pdo->prepare("SELECT m.id, CONCAT(m.first_name,' ',m.last_name) AS name, m.member_id AS code,
                                 GREATEST(?, 0) - COUNT(DISTINCT DATE_FORMAT(p.payment_month,'%Y-%m')) AS remaining
                          FROM members m
                          LEFT JOIN payments p ON p.member_id = m.id AND p.status IN ('paid','completed')
                          WHERE m.equb_settings_id = ? AND m.is_active=1
                          GROUP BY m.id
                          HAVING remaining > 0
                          ORDER BY remaining DESC, name ASC
                          LIMIT 5");
    $top->execute([$duration, $equb_id]);
    $top_debtors = $top->fetchAll(PDO::FETCH_ASSOC);

    // Payments by month (last 12 months inflow)
    $pbm = $pdo->prepare("SELECT DATE_FORMAT(COALESCE(payment_date, created_at), '%Y-%m') ym, COALESCE(SUM(amount),0) amt
                          FROM payments
                          WHERE status IN ('paid','completed') AND member_id IN (SELECT id FROM members WHERE equb_settings_id = ?)
                          GROUP BY ym ORDER BY ym ASC");
    $pbm->execute([$equb_id]);
    $pay_labels=[]; $pay_values=[];
    while ($r=$pbm->fetch(PDO::FETCH_ASSOC)) { $pay_labels[] = date('M Y', strtotime($r['ym'].'-01')); $pay_values[] = (float)$r['amt']; }

    // Upcoming payouts (next 5)
    $up = $pdo->prepare("SELECT po.id, po.total_amount, po.scheduled_date, CONCAT(m.first_name,' ',m.last_name) as name
                         FROM payouts po
                         JOIN members m ON m.id = po.member_id
                         WHERE m.equb_settings_id = ? AND po.status='scheduled' AND po.scheduled_date >= CURDATE()
                         ORDER BY po.scheduled_date ASC LIMIT 5");
    $up->execute([$equb_id]);
    $upcoming = [];
    while ($r=$up->fetch(PDO::FETCH_ASSOC)) {
        $upcoming[] = [
            'id' => (int)$r['id'],
            'name' => $r['name'],
            'scheduled_date' => date('M j, Y', strtotime($r['scheduled_date'])),
            'amount' => (float)$r['total_amount'],
        ];
    }

    return [
        'success' => true,
        'summary' => [
            'total_positions' => (int)($mrow['total_positions'] ?? 0),
            'expected_total' => $expected_total,
            'collection_rate' => $collection_rate,
            'admin_revenue_collected' => (float)$prow['fee_total'],
            'payouts_completed' => (int)$prow['cnt'],
            'payouts_scheduled' => $scheduledCount,
            'net_payouts_total' => (float)$prow['net_total'],
            'monthly_pool' => $sum_monthly,
            'total_pool_value' => $sum_monthly * $duration,
            'average_payout' => $sum_monthly,
            'collected_total' => $collected,
            'collected_current_month' => $collected_current_month,
            'current_month_target' => $sum_monthly,
            'outstanding_balance' => $outstanding,
            'overdue_members' => $overdue_members,
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
            ],
            'inflows' => [
                'labels' => $pay_labels,
                'payments' => $pay_values,
            ]
        ],
        'tables' => [
            'top_debtors' => $top_debtors,
            'upcoming_payouts' => $upcoming,
        ]
    ];
}

?>

