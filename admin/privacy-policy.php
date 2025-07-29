<?php
/**
 * HabeshaEqub Privacy Policy
 * Data protection and privacy information for admin users
 */

// Include database and start session
require_once '../includes/db.php';

// Include admin auth guard functions (but skip auth check for privacy page)
define('SKIP_ADMIN_AUTH_CHECK', true);
require_once 'includes/admin_auth_guard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - HabeshaEqub Admin</title>
    
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
            border-left: 4px solid #2A9D8F;
            margin: 20px 0;
        }
        
        .security-box {
            background: linear-gradient(135deg, #26465320, #264653);
            color: white;
            padding: 25px;
            border-radius: 10px;
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
            <h1>Privacy Policy</h1>
            <p>How we protect and handle your data</p>
        </div>
        
        <div class="content">
            <div class="highlight">
                <strong>Our Commitment:</strong> HabeshaEqub is designed with privacy and security at its core. We respect the sensitive nature of Equb financial data and implement robust protections for all user information.
            </div>
            
            <h2>1. Information We Collect</h2>
            
            <h3>1.1 Administrator Information</h3>
            <p>When you create an admin account, we collect:</p>
            <ul>
                <li><strong>Account credentials:</strong> Username and encrypted password</li>
                <li><strong>Activity logs:</strong> Login times, page access, and administrative actions</li>
                <li><strong>Technical data:</strong> IP addresses, browser information, and session data</li>
                <li><strong>System interactions:</strong> Records of data modifications and system usage</li>
            </ul>
            
            <h3>1.2 Member Information (Managed by Admins)</h3>
            <p>As an administrator, you will handle sensitive member data including:</p>
            <ul>
                <li>Personal identification information</li>
                <li>Financial contribution records</li>
                <li>Payment history and payout schedules</li>
                <li>Contact information and communication preferences</li>
            </ul>
            
            <h2>2. How We Use Information</h2>
            
            <h3>2.1 System Operation</h3>
            <ul>
                <li>Authenticate admin access and maintain security</li>
                <li>Process Equb financial transactions and calculations</li>
                <li>Generate reports and maintain accurate records</li>
                <li>Facilitate communication between admins and members</li>
            </ul>
            
            <h3>2.2 Security and Monitoring</h3>
            <ul>
                <li>Monitor for unauthorized access attempts</li>
                <li>Audit administrative actions for accountability</li>
                <li>Detect and prevent fraudulent activities</li>
                <li>Ensure compliance with Equb rules and policies</li>
            </ul>
            
            <h2>3. Data Protection Measures</h2>
            
            <div class="security-box">
                <h3>🔒 Security Implementation</h3>
                <ul>
                    <li><strong>Encryption:</strong> All passwords are encrypted using industry-standard bcrypt hashing</li>
                    <li><strong>Session Security:</strong> Secure session management with automatic timeouts</li>
                    <li><strong>Access Control:</strong> Role-based permissions and authentication requirements</li>
                    <li><strong>Audit Trails:</strong> Comprehensive logging of all administrative actions</li>
                    <li><strong>Data Validation:</strong> Input sanitization and CSRF protection</li>
                </ul>
            </div>
            
            <h2>4. Data Sharing and Disclosure</h2>
            
            <h3>4.1 Within Equb Operations</h3>
            <p>Member data is shared only as necessary for legitimate Equb operations:</p>
            <ul>
                <li>Financial status and contribution records for payout calculations</li>
                <li>Contact information for official Equb communications</li>
                <li>Payment history for transparency and record-keeping</li>
            </ul>
            
            <h3>4.2 External Disclosure</h3>
            <p>We do NOT share personal data with third parties except:</p>
            <ul>
                <li>When required by Ethiopian law or legal process</li>
                <li>To protect the rights and safety of Equb members</li>
                <li>With explicit consent from the data subject</li>
                <li>For authorized auditing or regulatory compliance</li>
            </ul>
            
            <h2>5. Data Retention</h2>
            
            <h3>5.1 Administrative Data</h3>
            <ul>
                <li><strong>Account information:</strong> Retained while admin account is active</li>
                <li><strong>Activity logs:</strong> Maintained for security auditing (minimum 1 year)</li>
                <li><strong>Session data:</strong> Automatically deleted upon logout or timeout</li>
            </ul>
            
            <h3>5.2 Member Data</h3>
            <ul>
                <li><strong>Financial records:</strong> Retained as required for Equb operations and legal compliance</li>
                <li><strong>Personal information:</strong> Maintained only while member is active in the Equb</li>
                <li><strong>Historical data:</strong> Preserved for transparency and dispute resolution</li>
            </ul>
            
            <h2>6. Your Rights and Controls</h2>
            
            <h3>6.1 As an Administrator</h3>
            <ul>
                <li>Access and update your admin account information</li>
                <li>Review your activity logs and system usage</li>
                <li>Request account deactivation or deletion</li>
                <li>Report privacy concerns or data breaches</li>
            </ul>
            
            <h3>6.2 Member Data Management</h3>
            <p>You are responsible for ensuring members can:</p>
            <ul>
                <li>Access their personal financial information</li>
                <li>Request corrections to their data</li>
                <li>Understand how their information is used</li>
                <li>Withdraw from the Equb with proper data handling</li>
            </ul>
            
            <h2>7. Ethiopian Data Protection Compliance</h2>
            <p>
                HabeshaEqub is designed to comply with Ethiopian data protection principles and respects local cultural values regarding privacy and community financial practices. We recognize the importance of trust in traditional Equb systems and strive to maintain that trust through responsible data handling.
            </p>
            
            <h2>8. Data Breach Response</h2>
            <p>In the event of a data security incident:</p>
            <ol>
                <li>We will investigate and contain the breach immediately</li>
                <li>Affected users will be notified within 72 hours when possible</li>
                <li>Appropriate authorities will be contacted as required by law</li>
                <li>System security will be enhanced to prevent similar incidents</li>
                <li>A full incident report will be documented for transparency</li>
            </ol>
            
            <h2>9. Children's Privacy</h2>
            <p>
                HabeshaEqub is intended for use by adults only. We do not knowingly collect personal information from individuals under 18 years of age. Admin accounts should only be created by adults with legal capacity to enter into financial agreements.
            </p>
            
            <h2>10. Policy Updates</h2>
            <p>
                This privacy policy may be updated to reflect changes in our data handling practices or legal requirements. Material changes will be communicated to administrators through the system, and continued use of the admin interface constitutes acceptance of the updated policy.
            </p>
            
            <h2>11. Contact for Privacy Concerns</h2>
            <p>
                For questions about this privacy policy, data handling practices, or to report privacy concerns, please contact the system administrator through the appropriate channels within the HabeshaEqub application.
            </p>
            
            <div class="highlight">
                <strong>Remember:</strong> As an administrator, you play a crucial role in protecting member privacy. Always handle personal and financial data with the utmost care and respect.
            </div>
        </div>
        
        <div class="actions">
            <a href="register.php" class="btn btn-primary">Back to Registration</a>
            <a href="terms-of-service.php" class="btn btn-secondary">Terms of Service</a>
        </div>
        
        <div class="last-updated">
            Last updated: <?php echo date('F j, Y'); ?>
        </div>
    </div>
</body>
</html>