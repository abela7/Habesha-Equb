<?php
/**
 * HabeshaEqub - Member Testing Tool (Admin Only)
 * Allows admins to impersonate members for testing purposes
 * SECURITY: Only accessible by verified admins
 */

// FORCE NO CACHING
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Start session and include necessary files
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db.php';
require_once '../languages/translator.php';

// STRICT ADMIN AUTHENTICATION CHECK
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();

// Get admin info for security verification
try {
    $stmt = $db->prepare("SELECT username, email FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        die("❌ SECURITY ERROR: Invalid admin session");
    }
} catch (PDOException $e) {
    die("❌ DATABASE ERROR: " . $e->getMessage());
}

// Get all active members for testing
try {
    $stmt = $db->query("
        SELECT 
            m.id,
            m.member_id,
            m.first_name,
            m.last_name,
            m.email,
            m.phone,
            m.monthly_payment,
            m.payout_position,
            m.is_approved,
            m.status,
            m.created_at
        FROM members m 
        WHERE m.is_active = 1 
        ORDER BY m.payout_position ASC, m.first_name ASC
    ");
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("❌ DATABASE ERROR: " . $e->getMessage());
}

$cache_buster = time() . '_' . rand(1000, 9999);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🧪 Member Testing Tool - HabeshaEqub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
    :root {
        --color-primary: #2A9D8F;
        --color-secondary: #E76F51;
        --color-accent: #F4A261;
        --color-gold: #DAA520;
        --color-deep-purple: #301934;
        --color-white: #FFFFFF;
        --color-light: #F8F9FA;
        --color-border: #E0E0E0;
    }

    body {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
    }

    .testing-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px 20px;
    }

    .testing-header {
        background: linear-gradient(135deg, var(--color-deep-purple) 0%, #4a2c4a 100%);
        color: white;
        padding: 30px;
        border-radius: 20px;
        margin-bottom: 30px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(48, 25, 52, 0.3);
    }

    .testing-header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 700;
    }

    .testing-header p {
        margin: 10px 0 0 0;
        opacity: 0.9;
        font-size: 1.1rem;
    }

    .admin-info {
        background: var(--color-gold);
        color: var(--color-deep-purple);
        padding: 15px 20px;
        border-radius: 15px;
        margin-bottom: 30px;
        font-weight: 600;
        text-align: center;
    }

    .member-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .member-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        border-color: var(--color-gold);
    }

    .member-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .member-details h3 {
        margin: 0 0 5px 0;
        color: var(--color-deep-purple);
        font-size: 1.4rem;
        font-weight: 600;
    }

    .member-meta {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .meta-item {
        font-size: 0.9rem;
        color: #666;
    }

    .meta-item i {
        color: var(--color-gold);
        margin-right: 5px;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-approved {
        background: #d4edda;
        color: #155724;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .login-btn {
        background: linear-gradient(135deg, var(--color-primary) 0%, #228B22 100%);
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .login-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(42, 157, 143, 0.4);
        color: white;
        text-decoration: none;
    }

    .warning-box {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
        color: white;
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 30px;
        text-align: center;
    }

    .back-btn {
        background: var(--color-deep-purple);
        color: white;
        padding: 12px 25px;
        border-radius: 10px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }

    .back-btn:hover {
        background: #4a2c4a;
        color: white;
        text-decoration: none;
        transform: translateX(-3px);
    }

    @media (max-width: 768px) {
        .member-info {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .member-meta {
            flex-direction: column;
            gap: 10px;
        }
        
        .testing-header h1 {
            font-size: 2rem;
        }
    }
    </style>
</head>
<body>
    <div class="testing-container">
        <!-- Back to Admin Dashboard -->
        <a href="dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Admin Dashboard
        </a>

        <!-- Testing Header -->
        <div class="testing-header">
            <h1><i class="fas fa-vial"></i> Member Testing Tool</h1>
            <p>Login as any member to test the member-side functionality</p>
        </div>

        <!-- Admin Info -->
        <div class="admin-info">
            <i class="fas fa-shield-alt"></i>
            <strong>Admin:</strong> <?php echo htmlspecialchars($admin['username']); ?> 
            (<?php echo htmlspecialchars($admin['email']); ?>)
        </div>

        <!-- Security Warning -->
        <div class="warning-box">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>TESTING ONLY:</strong> This tool is for development and testing purposes only. 
            Member sessions created here bypass normal authentication.
        </div>

        <!-- Members List -->
        <div class="row">
            <?php if (empty($members)): ?>
                <div class="col-12">
                    <div class="text-center p-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h3>No Members Found</h3>
                        <p class="text-muted">No active members available for testing.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($members as $member): ?>
                    <div class="col-lg-6 col-xl-4">
                        <div class="member-card">
                            <div class="member-info">
                                <div class="member-details">
                                    <h3><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></h3>
                                    <div class="member-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-id-card"></i>
                                            <?php echo htmlspecialchars($member['member_id']); ?>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-envelope"></i>
                                            <?php echo htmlspecialchars($member['email']); ?>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-pound-sign"></i>
                                            £<?php echo number_format($member['monthly_payment'], 0); ?>/month
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-sort-numeric-up"></i>
                                            Position <?php echo $member['payout_position']; ?>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <span class="status-badge <?php echo $member['is_approved'] ? 'status-approved' : 'status-pending'; ?>">
                                            <?php echo $member['is_approved'] ? 'Approved' : 'Pending'; ?>
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <a href="api/impersonate-member.php?member_id=<?php echo $member['id']; ?>&admin_id=<?php echo $admin_id; ?>" 
                                       class="login-btn"
                                       onclick="return confirm('Login as <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>?')">
                                        <i class="fas fa-sign-in-alt"></i>
                                        Login as Member
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Instructions -->
        <div class="mt-5 p-4 bg-light rounded">
            <h4><i class="fas fa-info-circle text-primary"></i> How to Use:</h4>
            <ol class="mb-0">
                <li><strong>Select a Member:</strong> Click "Login as Member" for any member above</li>
                <li><strong>Test Functionality:</strong> You'll be logged in as that member and redirected to their dashboard</li>
                <li><strong>Return to Admin:</strong> Use the logout button or navigate back to admin area</li>
                <li><strong>Security:</strong> This tool only works for admins and doesn't affect normal login security</li>
            </ol>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
