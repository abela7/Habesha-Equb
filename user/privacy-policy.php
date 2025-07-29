<?php
/**
 * HabeshaEqub Privacy Policy
 * UK GDPR compliant privacy policy for member users
 */

// Include database and start session
require_once '../includes/db.php';

// Include user auth guard functions (but skip auth check for privacy page)
define('SKIP_AUTH_CHECK', true);
require_once 'includes/auth_guard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - HabeshaEqub</title>
    
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
        
        .gdpr-box {
            background: linear-gradient(135deg, #26465320, #264653);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .security-box {
            background: linear-gradient(135deg, #2A9D8F20, #2A9D8F);
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
                <strong>Our Commitment:</strong> HabeshaEqub is designed with privacy and security at its core. We respect the sensitive nature of Equb financial data and implement robust protections for all user information in compliance with UK data protection laws.
            </div>
            
            <div class="gdpr-box">
                <h3>🇬🇧 UK GDPR Compliance</h3>
                <p>This privacy policy is compliant with the UK General Data Protection Regulation (UK GDPR) and the Data Protection Act 2018. We are committed to protecting your personal data and ensuring your privacy rights are respected.</p>
            </div>
            
            <h2>1. Information We Collect</h2>
            
            <h3>1.1 Personal Information</h3>
            <p>When you register as a member, we collect:</p>
            <ul>
                <li><strong>Identity data:</strong> Full name, username, and contact information</li>
                <li><strong>Contact data:</strong> Email address and phone number</li>
                <li><strong>Account data:</strong> Login credentials and account preferences</li>
                <li><strong>Financial data:</strong> Contribution records and payment history</li>
                <li><strong>Technical data:</strong> IP addresses, browser information, and device details</li>
                <li><strong>Usage data:</strong> How you interact with our platform</li>
            </ul>
            
            <h3>1.2 Special Category Data</h3>
            <p>We may process limited special category data where necessary for:</p>
            <ul>
                <li>Financial transaction monitoring (for fraud prevention)</li>
                <li>Regulatory compliance requirements</li>
                <li>Legal obligations under UK financial services regulations</li>
            </ul>
            
            <h2>2. How We Use Your Information</h2>
            
            <h3>2.1 Primary Purposes</h3>
            <ul>
                <li>Provide and maintain the HabeshaEqub platform</li>
                <li>Process Equb contributions and payouts</li>
                <li>Generate financial reports and statements</li>
                <li>Facilitate communication between members</li>
                <li>Ensure platform security and prevent fraud</li>
            </ul>
            
            <h3>2.2 Legal Basis for Processing</h3>
            <p>We process your personal data based on the following legal grounds:</p>
            <ul>
                <li><strong>Contract:</strong> To provide the services you've requested</li>
                <li><strong>Legitimate Interest:</strong> To improve our services and prevent fraud</li>
                <li><strong>Legal Obligation:</strong> To comply with UK laws and regulations</li>
                <li><strong>Consent:</strong> For optional communications and features</li>
            </ul>
            
            <h2>3. Data Protection Measures</h2>
            
            <div class="security-box">
                <h3>🔒 Security Implementation</h3>
                <ul>
                    <li><strong>Encryption:</strong> All passwords encrypted using bcrypt with cost factor 12</li>
                    <li><strong>Session Security:</strong> Secure session management with automatic timeouts</li>
                    <li><strong>Access Control:</strong> Role-based permissions and authentication requirements</li>
                    <li><strong>Data Validation:</strong> Input sanitization and CSRF protection</li>
                    <li><strong>Audit Trails:</strong> Comprehensive logging of all user actions</li>
                    <li><strong>Secure Transmission:</strong> HTTPS encryption for all data transfers</li>
                </ul>
            </div>
            
            <h2>4. Data Sharing and Disclosure</h2>
            
            <h3>4.1 Within Equb Operations</h3>
            <p>Your data may be shared with other Equb members for legitimate group operations:</p>
            <ul>
                <li>Financial status and contribution records for transparency</li>
                <li>Contact information for official Equb communications</li>
                <li>Payment history for group accountability</li>
            </ul>
            
            <h3>4.2 External Disclosure</h3>
            <p>We do NOT share your personal data with third parties except:</p>
            <ul>
                <li>When required by UK law or legal process</li>
                <li>To protect the rights and safety of Equb members</li>
                <li>With your explicit consent</li>
                <li>For authorized auditing or regulatory compliance</li>
                <li>To prevent fraud or financial crime</li>
            </ul>
            
            <h2>5. Your Data Rights (UK GDPR)</h2>
            
            <h3>5.1 Your Rights</h3>
            <p>Under UK GDPR, you have the following rights:</p>
            <ul>
                <li><strong>Right of Access:</strong> Request a copy of your personal data</li>
                <li><strong>Right to Rectification:</strong> Correct inaccurate or incomplete data</li>
                <li><strong>Right to Erasure:</strong> Request deletion of your data ("right to be forgotten")</li>
                <li><strong>Right to Restrict Processing:</strong> Limit how we use your data</li>
                <li><strong>Right to Data Portability:</strong> Receive your data in a portable format</li>
                <li><strong>Right to Object:</strong> Object to certain types of processing</li>
                <li><strong>Rights Related to Automated Decision Making:</strong> Challenge automated decisions</li>
            </ul>
            
            <h3>5.2 Exercising Your Rights</h3>
            <p>To exercise any of these rights, please contact us through the platform or your Equb administrator. We will respond to your request within one month.</p>
            
            <h2>6. Data Retention</h2>
            
            <h3>6.1 Retention Periods</h3>
            <ul>
                <li><strong>Account data:</strong> Retained while your account is active</li>
                <li><strong>Financial records:</strong> Retained for 7 years (UK tax law requirement)</li>
                <li><strong>Transaction logs:</strong> Retained for 6 years (fraud prevention)</li>
                <li><strong>Activity logs:</strong> Retained for 2 years (security auditing)</li>
                <li><strong>Session data:</strong> Automatically deleted upon logout</li>
            </ul>
            
            <h3>6.2 Data Deletion</h3>
            <p>When you request account deletion, we will:</p>
            <ul>
                <li>Remove your personal data from active systems</li>
                <li>Retain financial records as required by law</li>
                <li>Anonymize data used for analytics</li>
                <li>Confirm deletion within 30 days</li>
            </ul>
            
            <h2>7. International Data Transfers</h2>
            <p>
                HabeshaEqub operates primarily within the UK. If data is transferred outside the UK, we ensure adequate protection through:
            </p>
            <ul>
                <li>Adequacy decisions by the UK government</li>
                <li>Standard contractual clauses</li>
                <li>Binding corporate rules</li>
                <li>Other approved transfer mechanisms</li>
            </ul>
            
            <h2>8. Cookies and Tracking</h2>
            
            <h3>8.1 Essential Cookies</h3>
            <p>We use essential cookies for:</p>
            <ul>
                <li>Session management and security</li>
                <li>Authentication and login status</li>
                <li>CSRF protection</li>
                <li>Language preferences</li>
            </ul>
            
            <h3>8.2 Analytics and Performance</h3>
            <p>We may use analytics cookies to:</p>
            <ul>
                <li>Improve platform performance</li>
                <li>Understand user behavior</li>
                <li>Identify and fix technical issues</li>
            </ul>
            
            <h2>9. Data Breach Response</h2>
            <p>In the event of a data security incident:</p>
            <ol>
                <li>We will investigate and contain the breach immediately</li>
                <li>Affected users will be notified within 72 hours</li>
                <li>The Information Commissioner's Office (ICO) will be notified if required</li>
                <li>System security will be enhanced to prevent similar incidents</li>
                <li>A full incident report will be documented</li>
            </ol>
            
            <h2>10. Children's Privacy</h2>
            <p>
                HabeshaEqub is intended for use by adults (18+) only. We do not knowingly collect personal information from individuals under 18 years of age. If we become aware that we have collected data from a child, we will take steps to delete it promptly.
            </p>
            
            <h2>11. Third-Party Services</h2>
            <p>
                Our platform may integrate with third-party services for specific functionalities. These services have their own privacy policies, and we recommend reviewing them. We only share data with third parties as described in this policy.
            </p>
            
            <h2>12. Policy Updates</h2>
            <p>
                This privacy policy may be updated to reflect changes in our data handling practices, UK law, or regulatory requirements. Material changes will be communicated to users through the platform, and continued use constitutes acceptance of the updated policy.
            </p>
            
            <h2>13. Contact Information</h2>
            <p>
                For questions about this privacy policy, data handling practices, or to exercise your data rights, please contact us through the appropriate channels within the HabeshaEqub platform or reach out to your Equb group administrator.
            </p>
            
            <div class="highlight">
                <strong>Remember:</strong> Your privacy is fundamental to the trust that makes Equb groups successful. We are committed to protecting your data and maintaining the highest standards of privacy and security.
            </div>
        </div>
        
        <div class="actions">
            <a href="login.php" class="btn btn-primary">Back to Registration</a>
            <a href="terms-of-service.php" class="btn btn-secondary">Terms of Service</a>
        </div>
        
        <div class="last-updated">
            Last updated: <?php echo date('F j, Y'); ?>
        </div>
    </div>
</body>
</html>