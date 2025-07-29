<?php
/**
 * HabeshaEqub Terms of Service
 * Universal terms for all users (admin and members)
 */

// Include database and start session
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - HabeshaEqub</title>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="Pictures/Icon/favicon-32x32.png">
    
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
            <img src="Pictures/TransparentLogo.png" alt="HabeshaEqub Logo" class="logo">
            <h1>Terms of Service</h1>
            <p>HabeshaEqub User Agreement</p>
        </div>
        
        <div class="content">
            <div class="highlight">
                <strong>Welcome to HabeshaEqub!</strong> By using our platform, you agree to these terms and commit to maintaining the trust and integrity that are fundamental to Ethiopian Equb traditions.
            </div>
            
            <h2>1. About HabeshaEqub</h2>
            <p>
                HabeshaEqub is a digital platform designed to support and modernize traditional Ethiopian savings groups (Equb). Our mission is to preserve the cultural significance of Equb while providing modern tools for transparency, security, and efficiency.
            </p>
            
            <h2>2. User Responsibilities</h2>
            
            <h3>2.1 For All Users</h3>
            <ul>
                <li>Provide accurate and truthful information</li>
                <li>Maintain the confidentiality of your login credentials</li>
                <li>Use the platform in accordance with Ethiopian laws and regulations</li>
                <li>Respect the cultural values and traditions of Equb practices</li>
                <li>Report any suspicious activities or security concerns</li>
            </ul>
            
            <h3>2.2 For Administrators</h3>
            <ul>
                <li>Manage member data with utmost care and confidentiality</li>
                <li>Maintain accurate financial records and transparent operations</li>
                <li>Process payments and payouts fairly and timely</li>
                <li>Ensure equal treatment of all Equb members</li>
                <li>Follow established Equb rules and procedures</li>
            </ul>
            
            <h3>2.3 For Members</h3>
            <ul>
                <li>Make contributions according to agreed schedules</li>
                <li>Participate actively and responsibly in Equb activities</li>
                <li>Communicate any changes to personal or financial information</li>
                <li>Respect other members and maintain group harmony</li>
                <li>Honor commitment to the Equb duration and rules</li>
            </ul>
            
            <h2>3. Financial Responsibilities</h2>
            
            <h3>3.1 Contributions</h3>
            <p>
                All users agree to make financial contributions as agreed upon by the Equb group. Late payments or defaults may result in penalties as determined by group rules.
            </p>
            
            <h3>3.2 Payouts</h3>
            <p>
                Payout schedules and amounts are determined by Equb rules and member agreements. HabeshaEqub facilitates the process but does not guarantee individual financial outcomes.
            </p>
            
            <h3>3.3 Transparency</h3>
            <p>
                All financial transactions will be recorded transparently and made available to relevant parties according to Equb practices and privacy policies.
            </p>
            
            <h2>4. Prohibited Activities</h2>
            <p>Users are prohibited from:</p>
            <ol>
                <li>Using the platform for fraudulent or illegal activities</li>
                <li>Attempting to manipulate or falsify financial records</li>
                <li>Sharing login credentials or accessing unauthorized accounts</li>
                <li>Using the platform to harm other members or the Equb group</li>
                <li>Violating Ethiopian laws or cultural norms</li>
                <li>Attempting to breach system security</li>
                <li>Using member data for unauthorized purposes</li>
            </ol>
            
            <h2>5. Cultural Respect and Traditional Values</h2>
            <p>
                HabeshaEqub is built upon the foundation of Ethiopian cultural values. All users must:
            </p>
            <ul>
                <li>Honor the spirit of mutual assistance and community support</li>
                <li>Maintain trust and integrity in all interactions</li>
                <li>Resolve disputes through respectful dialogue and cultural practices</li>
                <li>Support the collective success of the Equb group</li>
                <li>Preserve the dignity and reputation of the Equb tradition</li>
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
            </ul>
            <p>
                Users acknowledge that such issues may occur and agree to be patient while problems are resolved.
            </p>
            
            <h2>7. Data Protection and Privacy</h2>
            <p>
                HabeshaEqub takes data protection seriously. All personal and financial information is handled according to our Privacy Policy and applicable Ethiopian data protection principles.
            </p>
            
            <h2>8. Dispute Resolution</h2>
            <p>
                Disputes will be resolved through:
            </p>
            <ol>
                <li>Direct communication between involved parties</li>
                <li>Mediation by Equb group leaders or administrators</li>
                <li>Traditional Ethiopian conflict resolution methods</li>
                <li>Legal procedures under Ethiopian law if necessary</li>
            </ol>
            
            <h2>9. Account Termination</h2>
            <p>
                Accounts may be terminated for:
            </p>
            <ul>
                <li>Violation of these terms of service</li>
                <li>Fraudulent or suspicious activity</li>
                <li>Breach of Equb group rules</li>
                <li>Non-payment of contributions</li>
                <li>Request by the user</li>
            </ul>
            
            <h2>10. Limitation of Liability</h2>
            <p>
                HabeshaEqub provides tools and platform services but is not responsible for:
            </p>
            <ul>
                <li>Individual financial decisions or outcomes</li>
                <li>Disputes between Equb members</li>
                <li>External economic factors affecting savings</li>
                <li>Force majeure events beyond our control</li>
                <li>Third-party service interruptions</li>
            </ul>
            
            <h2>11. Updates to Terms</h2>
            <p>
                These terms may be updated to reflect:
            </p>
            <ul>
                <li>Changes in Ethiopian law or regulations</li>
                <li>Platform improvements and new features</li>
                <li>Enhanced security measures</li>
                <li>User feedback and community needs</li>
            </ul>
            <p>
                Users will be notified of significant changes and continued use constitutes acceptance of updated terms.
            </p>
            
            <h2>12. Contact Information</h2>
            <p>
                For questions, concerns, or support regarding these terms, please contact us through the appropriate channels within the HabeshaEqub platform or reach out to your Equb group administrator.
            </p>
            
            <div class="highlight">
                <strong>Remember:</strong> HabeshaEqub is more than just software - it's a tool to strengthen our community bonds and preserve our cultural heritage. Use it wisely and with respect for all members.
            </div>
        </div>
        
        <div class="actions">
            <a href="index.php" class="btn btn-primary">Back to Home</a>
            <a href="privacy-policy.php" class="btn btn-secondary">Privacy Policy</a>
        </div>
        
        <div class="last-updated">
            Last updated: <?php echo date('F j, Y'); ?>
        </div>
    </div>
</body>
</html>