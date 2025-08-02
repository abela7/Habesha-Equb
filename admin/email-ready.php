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
    <title>Email System Ready - HabeshaEqub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
            font-family: 'Arial', sans-serif;
        }
        .card { 
            box-shadow: 0 20px 40px rgba(0,0,0,0.1); 
            border: none; 
            border-radius: 20px; 
            overflow: hidden;
        }
        .hero-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .step-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 5px solid #28a745;
            transition: transform 0.2s;
        }
        .step-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .btn-action {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
            color: white;
        }
        .settings-display {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="hero-header">
                    <h1 class="mb-3">
                        <i class="fas fa-rocket fa-2x"></i><br>
                        🎉 Your Email System is Ready!
                    </h1>
                    <p class="lead mb-0">
                        I have your exact Brevo SMTP settings. Let's test and activate your email system!
                    </p>
                </div>
                
                <div class="card-body p-4">
                    
                    <!-- Your Settings Display -->
                    <div class="step-card">
                        <h5><i class="fas fa-cog text-success"></i> Your Brevo Configuration</h5>
                        <div class="settings-display">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>SMTP Server:</strong> smtp-relay.brevo.com<br>
                                    <strong>Port:</strong> 587<br>
                                    <strong>Encryption:</strong> TLS (STARTTLS)<br>
                                </div>
                                <div class="col-md-6">
                                    <strong>Login:</strong> 92bed1001@smtp-brevo.com<br>
                                    <strong>Password:</strong> 8VgfHCdmsZX0whkx<br>
                                    <strong>Status:</strong> <span class="text-success">✅ Ready to test</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 1: Test -->
                    <div class="step-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-2">
                                    <span class="badge bg-primary rounded-pill me-2">1</span>
                                    <i class="fas fa-paper-plane text-primary"></i> 
                                    Test Your Configuration
                                </h5>
                                <p class="mb-0 text-muted">
                                    Send a test email using your exact Brevo settings to verify everything works.
                                </p>
                            </div>
                            <div>
                                <a href="test-your-brevo.php" class="btn-action">
                                    <i class="fas fa-paper-plane"></i> Test Now
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Save -->
                    <div class="step-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-2">
                                    <span class="badge bg-warning rounded-pill me-2">2</span>
                                    <i class="fas fa-database text-warning"></i> 
                                    Save to Database
                                </h5>
                                <p class="mb-0 text-muted">
                                    Save your working configuration to the database for permanent use.
                                </p>
                            </div>
                            <div>
                                <a href="email-config-working.php" class="btn-action">
                                    <i class="fas fa-save"></i> Configure
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Activate -->
                    <div class="step-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-2">
                                    <span class="badge bg-success rounded-pill me-2">3</span>
                                    <i class="fas fa-bell text-success"></i> 
                                    Activate Notifications
                                </h5>
                                <p class="mb-0 text-muted">
                                    Enable automatic email notifications for your HabeshaEqub system.
                                </p>
                                <small class="text-muted">
                                    (Welcome emails, payment reminders, payout alerts, etc.)
                                </small>
                            </div>
                            <div>
                                <a href="#" class="btn-action" style="opacity: 0.6;">
                                    <i class="fas fa-bell"></i> Coming Next
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Important Notes -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="alert alert-info border-0">
                                <h6><i class="fas fa-info-circle"></i> Important:</h6>
                                <ul class="mb-0 small">
                                    <li>Make sure you have a verified sender email in your Brevo account</li>
                                    <li>Check spam folder for test emails</li>
                                    <li>Your SMTP login is different from your regular Brevo login</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-success border-0">
                                <h6><i class="fas fa-lightbulb"></i> Next Features:</h6>
                                <ul class="mb-0 small">
                                    <li>✅ Welcome emails for new members</li>
                                    <li>✅ Payment reminder notifications</li>
                                    <li>✅ Payout confirmation emails</li>
                                    <li>✅ Admin alert notifications</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="text-center mt-4 pt-3 border-top">
                        <h6 class="text-muted mb-3">Quick Access</h6>
                        <a href="test-your-brevo.php" class="btn btn-outline-success me-2 mb-2">
                            <i class="fas fa-paper-plane"></i> Test Email
                        </a>
                        <a href="brevo-test-advanced.php" class="btn btn-outline-primary me-2 mb-2">
                            <i class="fas fa-cogs"></i> Advanced Test
                        </a>
                        <a href="debug-email-config.php" class="btn btn-outline-info me-2 mb-2">
                            <i class="fas fa-bug"></i> Debug
                        </a>
                        <a href="dashboard.php" class="btn btn-outline-secondary mb-2">
                            <i class="fas fa-arrow-left"></i> Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>