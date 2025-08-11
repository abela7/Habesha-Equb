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
    // Resolve token to payment
    $stmt = $pdo->prepare("SELECT p.*, m.first_name, m.last_name, m.member_id AS member_code
                           FROM payment_receipts pr
                           JOIN payments p ON pr.payment_id = p.id
                           LEFT JOIN members m ON p.member_id = m.id
                           WHERE pr.token = ? LIMIT 1");
    $stmt->execute([$token]);
    $pay = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$pay) { http_response_code(404); echo 'Receipt not found or expired.'; exit; }

    $amount = number_format((float)$pay['amount'], 2);
    $date = ($pay['payment_date'] && $pay['payment_date'] !== '0000-00-00') ? date('F j, Y', strtotime($pay['payment_date'])) : date('F j, Y', strtotime($pay['created_at']));
    $month = ($pay['payment_month'] && $pay['payment_month'] !== '0000-00-00') ? date('F Y', strtotime($pay['payment_month'])) : ($date);
    $memberName = trim(($pay['first_name'] ?? '') . ' ' . ($pay['last_name'] ?? ''));
    $memberCode = $pay['member_code'] ?? '';
    $paymentId = $pay['payment_id'] ?? ('PAY-' . $pay['id']);
    $method = $pay['payment_method'] ? ucwords(str_replace('_',' ',$pay['payment_method'])) : 'N/A';
    $lateFee = isset($pay['late_fee']) ? number_format((float)$pay['late_fee'], 2) : '0.00';

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
  <title>Receipt <?php echo htmlspecialchars($paymentId); ?></title>
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
    .btn { display:inline-block; padding:10px 16px; background:#DAA520; color:#301934; border-radius:8px; text-decoration:none; font-weight:700; }
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
          <div class="meta">Payment Receipt • Secure download</div>
        </div>
      </div>
      <div class="meta">Generated on <?php echo date('Y-m-d H:i'); ?></div>
    </div>
    <div class="header">
      <h1 class="title">Payment Receipt</h1>
      <span><?php echo htmlspecialchars($paymentId); ?></span>
    </div>
    <div class="body">
      <div class="row"><div class="label">Member</div><div class="value"><?php echo htmlspecialchars($memberName); ?> (<?php echo htmlspecialchars($memberCode); ?>)</div></div>
      <div class="row"><div class="label">Payment Month</div><div class="value"><?php echo htmlspecialchars($month); ?></div></div>
      <div class="row"><div class="label">Amount</div><div class="value">£<?php echo $amount; ?></div></div>
      <div class="row"><div class="label">Payment Method</div><div class="value"><?php echo htmlspecialchars($method); ?></div></div>
      <div class="row"><div class="label">Late Fee</div><div class="value">£<?php echo $lateFee; ?></div></div>
      <div class="row"><div class="label">Paid On</div><div class="value"><?php echo htmlspecialchars($date); ?></div></div>
      <div class="row"><div class="label">Status</div><div class="value"><?php echo htmlspecialchars(strtoupper($pay['status'])); ?></div></div>
      <div class="no-print" style="margin-top:16px;">
        <a class="btn" href="#" onclick="window.print();return false;">Print / Save</a>
      </div>
    </div>
    <div class="footer">© <?php echo date('Y'); ?> HabeshaEqub • This secure link can be used to view your receipt.</div>
  </div>
</body>
</html>


