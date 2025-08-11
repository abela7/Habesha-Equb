<?php
// Public receipt download by token (no login required)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/includes/db.php';

$token = $_GET['rt'] ?? '';
if ($token === '') {
    http_response_code(400);
    echo 'Invalid receipt token';
    exit;
}

try {
    // First try resolve as payment receipt
    $stmt = $pdo->prepare("SELECT p.*, m.first_name, m.last_name, m.member_id AS member_code, 'payment' AS receipt_kind
                           FROM payment_receipts pr
                           JOIN payments p ON pr.payment_id = p.id
                           LEFT JOIN members m ON p.member_id = m.id
                           WHERE pr.token = ? LIMIT 1");
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        // Fallback: resolve as payout receipt
        $stmt2 = $pdo->prepare("SELECT po.*, m.first_name, m.last_name, m.member_id AS member_code, 'payout' AS receipt_kind
                                FROM payout_receipts pr
                                JOIN payouts po ON pr.payout_id = po.id
                                LEFT JOIN members m ON po.member_id = m.id
                                WHERE pr.token = ? LIMIT 1");
        $stmt2->execute([$token]);
        $row = $stmt2->fetch(PDO::FETCH_ASSOC);
    }
    if (!$row) { http_response_code(404); echo 'Receipt not found or expired.'; exit; }

    $kind = $row['receipt_kind'];
    $memberName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
    $memberCode = $row['member_code'] ?? '';
    if ($kind === 'payment') {
        $amount = number_format((float)$row['amount'], 2);
        $date = ($row['payment_date'] && $row['payment_date'] !== '0000-00-00') ? date('F j, Y', strtotime($row['payment_date'])) : date('F j, Y', strtotime($row['created_at']));
        $month = ($row['payment_month'] && $row['payment_month'] !== '0000-00-00') ? date('F Y', strtotime($row['payment_month'])) : ($date);
        $method = $row['payment_method'] ? ucwords(str_replace('_',' ', $row['payment_method'])) : 'N/A';
        $lateFee = isset($row['late_fee']) ? number_format((float)$row['late_fee'], 2) : '0.00';
    } else {
        // payout
        $amount = number_format((float)$row['total_amount'], 2);
        $date = ($row['actual_payout_date'] && $row['actual_payout_date'] !== '0000-00-00') ? date('F j, Y', strtotime($row['actual_payout_date'])) : date('F j, Y', strtotime($row['created_at']));
        $month = date('F Y', strtotime($date));
        $method = $row['payout_method'] ? ucwords(str_replace('_',' ', $row['payout_method'])) : 'N/A';
        $lateFee = null; // not applicable for payout
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo 'Server error.';
    exit;
}

// Choose branding logo
$brandLogo = '/assets/img/logo.png';
if (!file_exists(__DIR__ . $brandLogo)) {
    $fallback = '/Pictures/Icon/apple-icon-180x180.png';
    if (file_exists(__DIR__ . $fallback)) { $brandLogo = $fallback; }
}

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo ($kind === 'payment') ? 'Payment Receipt' : 'Payout Receipt'; ?></title>
  <style>
    body { font-family: Arial, sans-serif; background:#f6f8fb; color:#301934; margin:0; padding:20px; }
    .card { max-width: 680px; margin: 0 auto; background:#fff; border:1px solid #ececec; border-radius:14px; box-shadow:0 8px 24px rgba(48,25,52,.08); overflow:hidden; }
    .brand { padding:16px 24px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f0f0f0; background:#ffffff; }
    .brand .left { display:flex; align-items:center; gap:12px; }
    .brand .logo { width:44px; height:44px; border-radius:8px; object-fit:contain; border:1px solid #ececec; }
    .brand .name { font-weight:800; font-size:18px; letter-spacing:.2px; }
    .brand .meta { font-size:12px; color:#6b7280; }
    .header { padding:16px 24px; background:linear-gradient(135deg,#F8F7F4 0%,#FFFFFF 100%); border-bottom:1px solid #f0f0f0; display:flex; justify-content:space-between; align-items:center; }
    .title { font-size:18px; font-weight:800; margin:0; letter-spacing:.2px; }
    .body { padding:24px; }
    .row { display:flex; justify-content:space-between; margin-bottom:10px; }
    .label { color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:.5px; }
    .value { font-weight:700; }
    .footer { padding:16px 24px; border-top:1px solid #f0f0f0; background:#fafbfc; font-size:12px; color:#6b7280; text-align:center; }
    .btn { display:inline-block; padding:10px 16px; background:#DAA520; color:#301934; border-radius:8px; text-decoration:none; font-weight:700; border:0; }
    .btn-secondary { background:#ffffff; color:#DAA520; border:1px solid #DAA520; }
    .btns { display:flex; gap:10px; flex-wrap:wrap; }
    .btns .btn { flex:1 1 auto; text-align:center; }
    @media print { .no-print { display:none; } body{background:#fff;} .card{box-shadow:none; border:none;} }
  </style>
</head>
<body>
  <div class="card">
    <div class="brand">
      <div class="left">
        <img class="logo" src="<?php echo htmlspecialchars($brandLogo); ?>" alt="HabeshaEqub" />
        <div>
          <div class="name">HabeshaEqub</div>
          <div class="meta"><?php echo ($kind === 'payment') ? 'Payment Receipt' : 'Payout Receipt'; ?></div>
        </div>
      </div>
      <div class="meta">Generated on <?php echo date('Y-m-d H:i'); ?></div>
    </div>
    <div class="header">
      <h1 class="title"><?php echo ($kind === 'payment') ? 'Payment Receipt' : 'Payout Receipt'; ?></h1>
    </div>
    <div class="body">
      <div class="row"><div class="label">Member</div><div class="value"><?php echo htmlspecialchars($memberName); ?> (<?php echo htmlspecialchars($memberCode); ?>)</div></div>
      <?php if ($kind === 'payment'): ?>
        <div class="row"><div class="label">Payment Month</div><div class="value"><?php echo htmlspecialchars($month); ?></div></div>
        <div class="row"><div class="label">Amount</div><div class="value">£<?php echo $amount; ?></div></div>
        <div class="row"><div class="label">Payment Method</div><div class="value"><?php echo htmlspecialchars($method); ?></div></div>
        <?php if ((float)$lateFee > 0): ?>
        <div class="row"><div class="label">Late Fee</div><div class="value">£<?php echo $lateFee; ?></div></div>
        <?php endif; ?>
        <div class="row"><div class="label">Paid On</div><div class="value"><?php echo htmlspecialchars($date); ?></div></div>
        <?php
          $verificationText = '';
          if (isset($row['verified_by_admin']) && (int)$row['verified_by_admin'] === 1) {
              $verificationText = 'Verified';
          } elseif (in_array(strtolower((string)($row['status'] ?? '')), ['paid','completed'], true)) {
              $verificationText = 'Pending Verification';
          } else {
              $verificationText = 'Not Verified';
          }
          $statusText = ucfirst(strtolower((string)($row['status'] ?? '')));
        ?>
        <div class="row"><div class="label">Status</div><div class="value"><?php echo htmlspecialchars($statusText); ?></div></div>
        <div class="row"><div class="label">Verification</div><div class="value"><?php echo htmlspecialchars($verificationText); ?></div></div>
      <?php else: ?>
        <div class="row"><div class="label">Payout Amount</div><div class="value">£<?php echo $amount; ?></div></div>
        <div class="row"><div class="label">Payout Method</div><div class="value"><?php echo htmlspecialchars($method); ?></div></div>
        <div class="row"><div class="label">Payout Date</div><div class="value"><?php echo htmlspecialchars($date); ?></div></div>
        <?php $statusText = ucfirst(strtolower((string)($row['status'] ?? ''))); ?>
        <div class="row"><div class="label">Status</div><div class="value"><?php echo htmlspecialchars($statusText); ?></div></div>
      <?php endif; ?>
      <div class="no-print" style="margin-top:16px;">
        <div class="btns">
          <a class="btn" href="#" onclick="window.print();return false;">Print / Save</a>
          <a class="btn btn-secondary" href="/user/dashboard.php">Back to Dashboard</a>
        </div>
      </div>
    </div>
    <div class="footer">© <?php echo date('Y'); ?> HabeshaEqub • This secure link can be used to view your receipt.</div>
  </div>
</body>
</html>


