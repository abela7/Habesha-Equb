<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple admin check
if (!isset($_SESSION['admin_id'])) {
    die('⛔ Please login as admin first - <a href="login.php">Login Here</a>');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Setup Guide - HabeshaEqub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .card { box-shadow: 0 15px 35px rgba(0,0,0,0.1); border: none; border-radius: 15px; }
        .step { background: white; border-radius: 10px; padding: 20px; margin-bottom: 20px; border-left: 5px solid #667eea; }
        .btn-action { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; }
    </style>
</head>
<body>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white text-center">
                    <h3 class="mb-0"><i class="fas fa-rocket"></i> Email Setup Guide</h3>
                    <p class="mb-0">Fresh start - Simple & Working</p>
                </div>
                <div class="card-body">
                    
                    <div class="step">
                        <h5><i class="fas fa-user-cog text-primary"></i> Step 1: Get Your Brevo SMTP Settings</h5>
                        <p>Login to your Brevo account and get these details:</p>
                        <div class="row">
                            <div class="col-md-6">
                                <ul>
                                    <li><strong>SMTP Host:</strong> smtp-relay.brevo.com</li>
                                    <li><strong>Port:</strong> 587</li>
                                    <li><strong>Encryption:</strong> TLS</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul>
                                    <li><strong>Username:</strong> Your Brevo login email</li>
                                    <li><strong>SMTP Key:</strong> From SMTP & API → SMTP</li>
                                    <li><strong>From Email:</strong> Verified sender in Brevo</li>
                                </ul>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Where to find SMTP Key:</strong> Brevo Dashboard → SMTP & API → SMTP → SMTP Key (starts with "xkeysib-...")
                        </div>
                    </div>

                    <div class="step">
                        <h5><i class="fas fa-cog text-success"></i> Step 2: Configure Email Settings</h5>
                        <p>Use our simple configuration tool to save your Brevo settings:</p>
                        <a href="email-config-working.php" class="btn btn-action">
                            <i class="fas fa-cog"></i> Configure Email Settings
                        </a>
                        <small class="text-muted d-block mt-2">This is a fresh, simple interface that works independently</small>
                    </div>

                    <div class="step">
                        <h5><i class="fas fa-paper-plane text-warning"></i> Step 3: Test Your Configuration</h5>
                        <p>Send a test email to verify everything is working:</p>
                        <a href="email-test-simple.php" class="btn btn-action">
                            <i class="fas fa-paper-plane"></i> Test Email Now
                        </a>
                        <small class="text-muted d-block mt-2">This will test your Brevo SMTP connection directly</small>
                    </div>

                    <div class="step">
                        <h5><i class="fas fa-bug text-info"></i> Step 4: Debug (If Needed)</h5>
                        <p>If something's not working, use our debug tool:</p>
                        <a href="debug-email-config.php" class="btn btn-action">
                            <i class="fas fa-bug"></i> Debug Configuration
                        </a>
                        <small class="text-muted d-block mt-2">Shows exactly what's in your database</small>
                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-exclamation-triangle text-warning"></i> Common Issues:</h6>
                            <ul class="small">
                                <li>Wrong SMTP Key (use app password, not account password)</li>
                                <li>From email not verified in Brevo</li>
                                <li>Firewall blocking port 587</li>
                                <li>TLS/SSL configuration issues</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-lightbulb text-success"></i> Pro Tips:</h6>
                            <ul class="small">
                                <li>Verify your sender email in Brevo first</li>
                                <li>Use TLS encryption with port 587</li>
                                <li>Test with a working email address</li>
                                <li>Check spam folder for test emails</li>
                            </ul>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                        <a href="system-configuration.php" class="btn btn-outline-primary">
                            <i class="fas fa-cogs"></i> Original System Config
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>