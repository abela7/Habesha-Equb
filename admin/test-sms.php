<?php
/**
 * SMS Testing & Configuration Tool
 * Professional SMS testing interface with manual phone number entry
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../includes/db.php';
require_once 'includes/admin_auth_guard.php';
require_once '../includes/sms/SmsService.php';

$admin_id = get_current_admin_id();
if (!$admin_id) { header('Location: login.php'); exit; }

// Get SMS configuration
$stmt = $pdo->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_category = 'sms'");
$stmt->execute();
$config = [];
while ($row = $stmt->fetch()) {
    $config[$row['setting_key']] = $row['setting_value'];
}

$result = null;
$test_performed = false;

// Handle SMS test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_sms'])) {
    $test_performed = true;
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? 'Test SMS from HabeshaEqub');
    
    if (!empty($phone) && !empty($message)) {
        try {
            $smsService = new SmsService($pdo);
            $result = $smsService->sendSMS($phone, $message);
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    } else {
        $result = [
            'success' => false,
            'message' => 'Phone number and message are required'
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Testing Tool - HabeshaEqub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --color-teal: #13665C;
            --color-coral: #E07856;
            --color-gold: #DAA520;
        }
        body { background: #f8f9fa; padding: 20px; }
        .test-container { max-width: 900px; margin: 0 auto; }
        .card { border-radius: 12px; border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card-header { background: linear-gradient(135deg, var(--color-teal), #0F5147); color: white; padding: 20px; border-radius: 12px 12px 0 0 !important; }
        .badge-status { padding: 8px 12px; border-radius: 20px; font-size: 13px; }
        .config-item { padding: 12px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .config-label { font-weight: 600; color: #333; }
        .config-value { color: #666; font-family: monospace; }
        .alert-ip { background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 15px; }
        .test-form { background: white; padding: 25px; border-radius: 12px; }
        .result-box { padding: 20px; border-radius: 8px; margin-top: 20px; }
        .result-success { background: #d4edda; border: 1px solid #28a745; color: #155724; }
        .result-error { background: #f8d7da; border: 1px solid #dc3545; color: #721c24; }
        .info-box { background: #e7f3ff; border-left: 4px solid #0066cc; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
<?php include 'includes/navigation.php'; ?>

<div class="test-container">
    <!-- Header -->
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0"><i class="fas fa-mobile-alt me-2"></i>SMS Testing & Configuration Tool</h4>
        </div>
        <div class="card-body">
            <p class="mb-0">Test your SMS configuration and send test messages to verify everything is working correctly.</p>
        </div>
    </div>

    <!-- IP Whitelist Alert -->
    <?php if (!empty($config['sms_api_key'])): ?>
    <div class="alert-ip">
        <h6><i class="fas fa-shield-alt me-2"></i><strong>Security Notice: IP Whitelist Required</strong></h6>
        <p class="mb-2">Brevo requires your server IP to be whitelisted for security. Your server IP is:</p>
        <div class="config-item mb-2">
            <span class="config-label">Server IP Address:</span>
            <code class="config-value fs-5"><strong><?php echo $_SERVER['SERVER_ADDR'] ?? 'Unable to detect'; ?></strong></code>
        </div>
        <p class="mb-2"><strong>To whitelist this IP:</strong></p>
        <ol class="mb-2">
            <li>Go to: <a href="https://app.brevo.com/security/authorised_ips" target="_blank">https://app.brevo.com/security/authorised_ips</a></li>
            <li>Click <strong>"Add an IP address"</strong></li>
            <li>Enter: <code><?php echo $_SERVER['SERVER_ADDR'] ?? 'N/A'; ?></code></li>
            <li>Click Save</li>
            <li>Wait 2-3 minutes for it to activate</li>
            <li>Come back here and test again!</li>
        </ol>
        <p class="mb-0"><i class="fas fa-info-circle"></i> <small>This is a one-time setup for security.</small></p>
    </div>
    <?php endif; ?>

    <!-- Current Configuration -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Current SMS Configuration</h5>
        </div>
        <div class="card-body">
            <div class="config-item">
                <span class="config-label">SMS Enabled</span>
                <span>
                    <?php if (($config['sms_enabled'] ?? '0') == '1'): ?>
                        <span class="badge bg-success"><i class="fas fa-check"></i> Enabled</span>
                    <?php else: ?>
                        <span class="badge bg-danger"><i class="fas fa-times"></i> Disabled</span>
                    <?php endif; ?>
                </span>
            </div>

            <div class="config-item">
                <span class="config-label">API Key Status</span>
                <span>
                    <?php if (!empty($config['sms_api_key'])): ?>
                        <span class="badge bg-success"><i class="fas fa-check"></i> Configured</span>
                        <small class="ms-2 text-muted">(<?php echo substr($config['sms_api_key'], 0, 15); ?>...)</small>
                    <?php else: ?>
                        <span class="badge bg-danger"><i class="fas fa-times"></i> Not Set</span>
                    <?php endif; ?>
                </span>
            </div>

            <div class="config-item">
                <span class="config-label">Sender Name</span>
                <span class="config-value"><?php echo htmlspecialchars($config['sms_sender_name'] ?? 'Not Set'); ?></span>
            </div>

            <div class="config-item">
                <span class="config-label">Test Mode</span>
                <span>
                    <?php if (($config['sms_test_mode'] ?? '0') == '1'): ?>
                        <span class="badge bg-warning text-dark"><i class="fas fa-flask"></i> Test Mode ON</span>
                        <small class="ms-2 text-muted">(SMS won't actually send)</small>
                    <?php else: ?>
                        <span class="badge bg-info"><i class="fas fa-paper-plane"></i> Live Mode</span>
                    <?php endif; ?>
                </span>
            </div>

            <div class="mt-3">
                <a href="system-configuration.php" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit"></i> Edit Configuration
                </a>
            </div>
        </div>
    </div>

    <!-- Test Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-vial me-2"></i>Send Test SMS</h5>
        </div>
        <div class="card-body">
            <?php if (empty($config['sms_api_key'])): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>API Key Required!</strong> Please configure your Brevo API key in 
                    <a href="system-configuration.php">System Configuration</a> first.
                </div>
            <?php else: ?>
                <div class="info-box">
                    <i class="fas fa-lightbulb me-2"></i>
                    <strong>Tip:</strong> Phone numbers should be in E.164 format (e.g., +447123456789). 
                    UK numbers starting with 0 will be auto-converted.
                </div>

                <form method="POST" class="test-form">
                    <div class="mb-3">
                        <label class="form-label"><strong>Phone Number *</strong></label>
                        <input type="text" name="phone" class="form-control form-control-lg" 
                               placeholder="+447360436171" 
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                               required>
                        <small class="text-muted">Format: +44XXXXXXXXXX (UK) or +251XXXXXXXXX (Ethiopia)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Test Message *</strong></label>
                        <textarea name="message" class="form-control" rows="4" required 
                                  placeholder="Enter your test message here..."><?php echo htmlspecialchars($_POST['message'] ?? 'This is a test SMS from HabeshaEqub. If you receive this, your SMS configuration is working correctly! 🎉'); ?></textarea>
                        <small class="text-muted">
                            <strong>Character limit:</strong> 160 chars (English) or 70 chars (Amharic/Unicode)
                        </small>
                    </div>

                    <button type="submit" name="test_sms" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-paper-plane me-2"></i>Send Test SMS
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Test Result -->
    <?php if ($test_performed && $result): ?>
    <div class="card">
        <div class="card-body">
            <div class="result-box <?php echo $result['success'] ? 'result-success' : 'result-error'; ?>">
                <?php if ($result['success']): ?>
                    <h5><i class="fas fa-check-circle me-2"></i>Success!</h5>
                    <p class="mb-2"><strong>Message:</strong> <?php echo htmlspecialchars($result['message']); ?></p>
                    <?php if (isset($result['message_id'])): ?>
                        <p class="mb-2"><strong>Message ID:</strong> <code><?php echo htmlspecialchars($result['message_id']); ?></code></p>
                    <?php endif; ?>
                    <?php if (isset($result['delivery_time'])): ?>
                        <p class="mb-2"><strong>Delivery Time:</strong> <?php echo $result['delivery_time']; ?>ms</p>
                    <?php endif; ?>
                    <?php if (isset($result['credits_remaining'])): ?>
                        <p class="mb-0"><strong>Credits Remaining:</strong> <?php echo $result['credits_remaining']; ?></p>
                    <?php endif; ?>
                    <hr>
                    <p class="mb-0"><i class="fas fa-mobile-alt me-2"></i>Check your phone for the SMS!</p>
                <?php else: ?>
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Failed</h5>
                    <p class="mb-2"><strong>Error:</strong> <?php echo htmlspecialchars($result['message']); ?></p>
                    <?php if (isset($result['http_code'])): ?>
                        <p class="mb-2"><strong>HTTP Code:</strong> <?php echo $result['http_code']; ?></p>
                    <?php endif; ?>
                    <?php if (isset($result['error_code'])): ?>
                        <p class="mb-2"><strong>Error Code:</strong> <?php echo $result['error_code']; ?></p>
                    <?php endif; ?>
                    
                    <?php if (isset($result['response'])): ?>
                        <details class="mt-3">
                            <summary style="cursor: pointer;"><strong>Show Full Response</strong></summary>
                            <pre class="mt-2 p-2 bg-white border rounded" style="max-height: 200px; overflow-y: auto;"><?php print_r($result['response']); ?></pre>
                        </details>
                    <?php endif; ?>

                    <hr>
                    <p class="mb-2"><strong>Common Solutions:</strong></p>
                    <ul class="mb-0">
                        <?php if (isset($result['http_code']) && $result['http_code'] == 401): ?>
                            <li><strong>HTTP 401 (Unauthorized):</strong> 
                                <?php if (strpos($result['message'], 'IP address') !== false): ?>
                                    Your server IP needs to be whitelisted (see instructions above)
                                <?php else: ?>
                                    Check your API key is correct in System Configuration
                                <?php endif; ?>
                            </li>
                        <?php elseif (isset($result['http_code']) && $result['http_code'] == 403): ?>
                            <li><strong>HTTP 403 (Forbidden):</strong> Sender name "<?php echo htmlspecialchars($config['sms_sender_name'] ?? 'N/A'); ?>" needs approval from Brevo (takes 24-48 hours)</li>
                        <?php elseif (isset($result['http_code']) && $result['http_code'] == 402): ?>
                            <li><strong>HTTP 402 (Payment Required):</strong> You're out of SMS credits! Purchase more at <a href="https://app.brevo.com" target="_blank">Brevo</a></li>
                        <?php elseif (isset($result['http_code']) && $result['http_code'] == 400): ?>
                            <li><strong>HTTP 400 (Bad Request):</strong> Check phone number format or sender name</li>
                        <?php else: ?>
                            <li>Check that your API key is correct</li>
                            <li>Verify sender name is approved by Brevo</li>
                            <li>Ensure phone number is in E.164 format (+44...)</li>
                            <li>Check you have SMS credits remaining</li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-body text-center">
            <a href="notifications.php" class="btn btn-outline-primary me-2">
                <i class="fas fa-bell me-1"></i>Back to Notifications
            </a>
            <a href="system-configuration.php" class="btn btn-outline-secondary">
                <i class="fas fa-cog me-1"></i>SMS Settings
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

