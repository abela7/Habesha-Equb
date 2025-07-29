<?php
/**
 * HabeshaEqub Terms of Service
 * UK-based legal terms for member users
 */

// Include database and start session
require_once '../includes/db.php';

// Include user auth guard functions (but skip auth check for terms page)
define('SKIP_AUTH_CHECK', true);
require_once 'includes/auth_guard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - HabeshaEqub</title>
    
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
        
        .legal-notice {
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
            <h1>Terms of Service</h1>
            <p>HabeshaEqub Member Agreement</p>
        </div>
        
        <div class="content">
            <div class="highlight">
                <strong>Welcome to HabeshaEqub!</strong> By using our platform, you agree to these terms and commit to maintaining the trust and integrity that are fundamental to Equb savings practices.
            </div>
            
            <div class="legal-notice">
                <h3>🇬🇧 UK Legal Framework</h3>
                <p>These terms are governed by the laws of England and Wales. Any disputes will be resolved in accordance with UK law and regulations.</p>
            </div>
            
            <h2>1. About HabeshaEqub</h2>
            <p>
                HabeshaEqub is a digital platform designed to support and modernize traditional savings groups (Equb). Our mission is to preserve the cultural significance of Equb while providing modern tools for transparency, security, and efficiency, operating within UK legal and regulatory frameworks.
            </p>
            
            <h2>2. Member Responsibilities</h2>
            
            <h3>2.1 Account Management</h3>
            <ul>
                <li>Provide accurate and truthful personal information</li>
                <li>Maintain the confidentiality of your login credentials</li>
                <li>Update your information promptly if it changes</li>
                <li>Use the platform in accordance with UK laws and regulations</li>
                <li>Report any suspicious activities or security concerns</li>
            </ul>
            
            <h3>2.2 Financial Obligations</h3>
            <ul>
                <li>Make contributions according to agreed schedules and amounts</li>
                <li>Participate actively and responsibly in Equb activities</li>
                <li>Communicate any changes to financial circumstances</li>
                <li>Honor commitment to the Equb duration and rules</li>
                <li>Maintain transparency in all financial dealings</li>
            </ul>
            
            <h3>2.3 Community Conduct</h3>
            <ul>
                <li>Respect other members and maintain group harmony</li>
                <li>Participate in group decisions and meetings</li>
                <li>Support the collective success of the Equb group</li>
                <li>Resolve disputes through respectful dialogue</li>
                <li>Maintain the dignity and reputation of the Equb tradition</li>
            </ul>
            
            <h2>3. Financial Services and Regulations</h2>
            
            <h3>3.1 UK Financial Conduct Authority (FCA) Compliance</h3>
            <p>
                HabeshaEqub operates as a technology platform facilitating traditional savings practices. While we are not a regulated financial institution, we ensure compliance with relevant UK financial services regulations where applicable.
            </p>
            
            <h3>3.2 Contributions and Payouts</h3>
            <ul>
                <li>All contributions are made voluntarily by group agreement</li>
                <li>Payout schedules and amounts are determined by Equb rules</li>
                <li>Financial transactions are recorded transparently</li>
                <li>Late payments may result in penalties as per group rules</li>
                <li>No interest is paid on contributions (traditional Equb practice)</li>
            </ul>
            
            <h3>3.3 Tax Obligations</h3>
            <p>
                Members are responsible for their own tax obligations related to Equb participation. We recommend consulting with a qualified tax advisor regarding any tax implications of your Equb activities.
            </p>
            
            <h2>4. Prohibited Activities</h2>
            <p>Members are prohibited from:</p>
            <ol>
                <li>Using the platform for money laundering or terrorist financing</li>
                <li>Attempting to manipulate or falsify financial records</li>
                <li>Sharing login credentials or accessing unauthorized accounts</li>
                <li>Using the platform to harm other members or the Equb group</li>
                <li>Violating UK laws, including financial services regulations</li>
                <li>Attempting to breach system security</li>
                <li>Using member data for unauthorized purposes</li>
                <li>Engaging in fraudulent activities or misrepresentation</li>
            </ol>
            
            <h2>5. Data Protection and Privacy</h2>
            
            <h3>5.1 UK GDPR Compliance</h3>
            <p>
                HabeshaEqub complies with the UK General Data Protection Regulation (UK GDPR) and the Data Protection Act 2018. Your personal data is processed in accordance with our Privacy Policy and UK data protection laws.
            </p>
            
            <h3>5.2 Your Data Rights</h3>
            <ul>
                <li>Right to access your personal data</li>
                <li>Right to rectification of inaccurate data</li>
                <li>Right to erasure ("right to be forgotten")</li>
                <li>Right to restrict processing</li>
                <li>Right to data portability</li>
                <li>Right to object to processing</li>
                <li>Rights related to automated decision making</li>
            </ul>
            
            <h2>6. Platform Availability and Technical Issues</h2>
            <p>
                While we strive to maintain continuous service, HabeshaEqub may experience:
            </p>
            <ul>
                <li>Scheduled maintenance periods</li>
                <li>Unexpected technical difficulties</li>
                <li>Internet connectivity issues</li>
                <li>System updates and improvements</li>
                <li>Third-party service interruptions</li>
            </ul>
            <p>
                Members acknowledge that such issues may occur and agree to be patient while problems are resolved.
            </p>
            
            <h2>7. Dispute Resolution</h2>
            <p>
                Disputes will be resolved through the following hierarchy:
            </p>
            <ol>
                <li>Direct communication between involved parties</li>
                <li>Mediation by Equb group leaders or administrators</li>
                <li>Alternative dispute resolution (ADR) procedures</li>
                <li>Legal proceedings under English law if necessary</li>
            </ol>
            
            <h2>8. Account Termination</h2>
            <p>
                Accounts may be terminated for:
            </p>
            <ul>
                <li>Violation of these terms of service</li>
                <li>Fraudulent or suspicious activity</li>
                <li>Breach of Equb group rules</li>
                <li>Non-payment of contributions</li>
                <li>Request by the member</li>
                <li>Legal or regulatory requirements</li>
            </ul>
            
            <h2>9. Limitation of Liability</h2>
            <p>
                HabeshaEqub provides tools and platform services but is not responsible for:
            </p>
            <ul>
                <li>Individual financial decisions or outcomes</li>
                <li>Disputes between Equb members</li>
                <li>External economic factors affecting savings</li>
                <li>Force majeure events beyond our control</li>
                <li>Third-party service interruptions</li>
                <li>Tax implications of Equb participation</li>
            </ul>
            
            <h2>10. Intellectual Property</h2>
            <p>
                The HabeshaEqub platform, including its design, functionality, and content, is protected by UK and international intellectual property laws. Members may use the platform for its intended purpose but may not copy, modify, or distribute the platform without permission.
            </p>
            
            <h2>11. Updates to Terms</h2>
            <p>
                These terms may be updated to reflect:
            </p>
            <ul>
                <li>Changes in UK law or regulations</li>
                <li>Platform improvements and new features</li>
                <li>Enhanced security measures</li>
                <li>User feedback and community needs</li>
                <li>Regulatory requirements</li>
            </ul>
            <p>
                Members will be notified of significant changes and continued use constitutes acceptance of updated terms.
            </p>
            
            <h2>12. Contact Information</h2>
            <p>
                For questions, concerns, or support regarding these terms, please contact us through the appropriate channels within the HabeshaEqub platform or reach out to your Equb group administrator.
            </p>
            
            <div class="highlight">
                <strong>Remember:</strong> HabeshaEqub is more than just software - it's a tool to strengthen community bonds and preserve cultural savings traditions while operating within UK legal frameworks. Use it wisely and with respect for all members.
            </div>
        </div>
        
        <div class="actions">
            <a href="login.php" class="btn btn-primary">Back to Registration</a>
            <a href="privacy-policy.php" class="btn btn-secondary">Privacy Policy</a>
        </div>
        
        <div class="last-updated">
            Last updated: <?php echo date('F j, Y'); ?>
        </div>
    </div>
</body>
</html>