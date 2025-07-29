<?php
/**
 * HabeshaEqub Terms of Service
 * Legal terms and conditions for admin users
 */

// Include database and start session
require_once '../includes/db.php';

// Include admin auth guard functions (but skip auth check for terms page)
define('SKIP_ADMIN_AUTH_CHECK', true);
require_once 'includes/admin_auth_guard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - HabeshaEqub Admin</title>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="../Pictures/Icon/favicon-32x32.png">
    
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f8f9ff 0%, #f1f3ff 100%);
            margin: 0;
            padding: 0;
            line-height: 1.6;
            color: #2c3e50;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            display: block;
        }
        
        .header h1 {
            font-size: 2.5rem;
            color: #264653;
            margin: 0 0 10px;
            font-weight: 700;
        }
        
        .header p {
            color: #6c757d;
            font-size: 1.1rem;
            margin: 0;
        }
        
        .content {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .content h2 {
            color: #264653;
            font-size: 1.5rem;
            margin: 30px 0 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #E9C46A;
        }
        
        .content h3 {
            color: #2A9D8F;
            font-size: 1.2rem;
            margin: 25px 0 10px;
        }
        
        .content p, .content li {
            margin-bottom: 10px;
            text-align: justify;
        }
        
        .content ul, .content ol {
            padding-left: 25px;
            margin-bottom: 20px;
        }
        
        .highlight {
            background: linear-gradient(135deg, #E9C46A20, #2A9D8F20);
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #E9C46A;
            margin: 20px 0;
        }
        
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #264653, #2A9D8F);
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .last-updated {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="../Pictures/TransparentLogo.png" alt="HabeshaEqub Logo" class="logo">
            <h1>Terms of Service</h1>
            <p>HabeshaEqub Administrative Access Agreement</p>
        </div>
        
        <div class="content">
            <div class="highlight">
                <strong>Important Notice:</strong> By creating an admin account for HabeshaEqub, you agree to these terms and accept full responsibility for the secure and ethical management of the Equb system.
            </div>
            
            <h2>1. Acceptance of Terms</h2>
            <p>
                By accessing and using the HabeshaEqub administrative interface, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service. These terms apply to all administrators and users with elevated privileges within the HabeshaEqub system.
            </p>
            
            <h2>2. Administrative Responsibilities</h2>
            <h3>2.1 Data Protection</h3>
            <ul>
                <li>Maintain the confidentiality of all member information and financial data</li>
                <li>Implement appropriate security measures to protect sensitive information</li>
                <li>Report any data breaches or security incidents immediately</li>
                <li>Ensure compliance with applicable data protection regulations</li>
            </ul>
            
            <h3>2.2 Financial Management</h3>
            <ul>
                <li>Accurately record all member contributions and payments</li>
                <li>Process payouts fairly and according to established rules</li>
                <li>Maintain transparent financial records</li>
                <li>Provide timely reporting to members</li>
            </ul>
            
            <h3>2.3 System Security</h3>
            <ul>
                <li>Keep login credentials secure and confidential</li>
                <li>Use strong, unique passwords</li>
                <li>Log out properly after each session</li>
                <li>Report suspicious activities or unauthorized access attempts</li>
            </ul>
            
            <h2>3. Prohibited Activities</h2>
            <p>As an administrator, you agree NOT to:</p>
            <ol>
                <li>Access or attempt to access areas of the system beyond your authorization</li>
                <li>Share login credentials with unauthorized persons</li>
                <li>Use the system for personal financial gain outside of legitimate Equb operations</li>
                <li>Manipulate or falsify financial records</li>
                <li>Discriminate against members based on personal characteristics</li>
                <li>Use member data for purposes outside of Equb management</li>
                <li>Attempt to bypass security measures or access controls</li>
            </ol>
            
            <h2>4. Ethiopian Equb Traditions</h2>
            <p>
                HabeshaEqub is built to honor and support traditional Ethiopian savings practices. Administrators must:
            </p>
            <ul>
                <li>Respect cultural values and community expectations</li>
                <li>Maintain the spirit of mutual support and trust inherent in Equb traditions</li>
                <li>Facilitate fair and transparent group savings operations</li>
                <li>Resolve disputes with cultural sensitivity and fairness</li>
            </ul>
            
            <h2>5. Limitation of Liability</h2>
            <p>
                While HabeshaEqub provides tools for Equb management, administrators acknowledge that:
            </p>
            <ul>
                <li>The software is provided "as is" without warranty</li>
                <li>Administrators are responsible for the accuracy of data entered</li>
                <li>Financial decisions and member relations remain the responsibility of the Equb group</li>
                <li>Technical issues may occur and should be reported promptly</li>
            </ul>
            
            <h2>6. Privacy and Confidentiality</h2>
            <p>
                Administrators have access to sensitive member information and must:
            </p>
            <ul>
                <li>Treat all member data as strictly confidential</li>
                <li>Only access information necessary for legitimate Equb operations</li>
                <li>Not disclose member information to unauthorized parties</li>
                <li>Follow data retention and deletion policies</li>
            </ul>
            
            <h2>7. Account Termination</h2>
            <p>
                HabeshaEqub reserves the right to terminate admin accounts for:
            </p>
            <ul>
                <li>Violation of these terms of service</li>
                <li>Suspicious or fraudulent activity</li>
                <li>Breach of security protocols</li>
                <li>Misuse of member data or system resources</li>
            </ul>
            
            <h2>8. Updates and Modifications</h2>
            <p>
                These terms may be updated periodically to reflect changes in our services or legal requirements. Administrators will be notified of significant changes and continued use constitutes acceptance of updated terms.
            </p>
            
            <h2>9. Governing Law</h2>
            <p>
                These terms are governed by the laws of Ethiopia and any disputes will be resolved according to Ethiopian legal procedures, with respect for traditional conflict resolution methods where appropriate.
            </p>
            
            <h2>10. Contact Information</h2>
            <p>
                For questions about these terms or to report issues, please contact the HabeshaEqub support team through the appropriate channels within the application.
            </p>
        </div>
        
        <div class="actions">
            <a href="register.php" class="btn btn-primary">Back to Registration</a>
            <a href="privacy-policy.php" class="btn btn-secondary">Privacy Policy</a>
        </div>
        
        <div class="last-updated">
            Last updated: <?php echo date('F j, Y'); ?>
        </div>
    </div>
</body>
</html>