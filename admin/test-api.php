<?php
/**
 * API Test Page - Diagnose Network Errors
 */

require_once '../includes/db.php';
require_once 'includes/admin_auth_guard.php';

$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Test - HabeshaEqub Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-cogs me-2"></i>API Diagnostic Test</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h5>Current Session Status:</h5>
                            <p><strong>Admin ID:</strong> <?php echo $admin_id; ?></p>
                            <p><strong>Admin Username:</strong> <?php echo $admin_username; ?></p>
                        </div>
                        
                        <div class="mb-4">
                            <button class="btn btn-primary me-2" onclick="testAPI('test-connection')">Test Basic Connection</button>
                            <button class="btn btn-info me-2" onclick="testAPI('payout-positions')">Test Payout Positions</button>
                            <button class="btn btn-success me-2" onclick="testAPI('joint-membership')">Test Joint Groups</button>
                            <button class="btn btn-warning" onclick="testAPI('payment-tiers')">Test Payment Tiers</button>
                        </div>
                        
                        <div id="results" class="mt-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function testAPI(apiType) {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Testing...</div>';
            
            let url, body;
            
            switch(apiType) {
                case 'test-connection':
                    url = 'api/test-connection.php';
                    body = '';
                    break;
                case 'payout-positions':
                    url = 'api/payout-positions.php';
                    body = 'action=get_positions&equb_id=2';
                    break;
                case 'joint-membership':
                    url = 'api/joint-membership.php';
                    body = 'action=get_existing_joint_groups&equb_term_id=2';
                    break;
                case 'payment-tiers':
                    url = 'api/payment-tiers.php';
                    body = 'action=get_tiers&equb_id=2';
                    break;
            }
            
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    displayResult(apiType, 'success', data);
                } catch (e) {
                    displayResult(apiType, 'parse_error', {error: 'Invalid JSON', raw_response: text});
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                displayResult(apiType, 'network_error', {error: error.message});
            });
        }
        
        function displayResult(apiType, status, data) {
            const resultsDiv = document.getElementById('results');
            
            let alertClass = 'alert-info';
            let icon = 'fas fa-info-circle';
            
            if (status === 'success' && data.success) {
                alertClass = 'alert-success';
                icon = 'fas fa-check-circle';
            } else if (status === 'network_error' || status === 'parse_error' || !data.success) {
                alertClass = 'alert-danger';
                icon = 'fas fa-exclamation-triangle';
            }
            
            resultsDiv.innerHTML = `
                <div class="alert ${alertClass}">
                    <h6><i class="${icon} me-2"></i>Test Results for: ${apiType}</h6>
                    <strong>Status:</strong> ${status}<br>
                    <strong>Response:</strong><br>
                    <pre style="font-size: 12px; max-height: 300px; overflow-y: auto;">${JSON.stringify(data, null, 2)}</pre>
                </div>
            `;
        }
    </script>
</body>
</html>