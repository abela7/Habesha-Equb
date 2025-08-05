<?php
/**
 * HabeshaEqub - SMART EQUB DIAGNOSTICS
 * Fix the Selam EQUB payout calculation errors
 */

require_once '../includes/db.php';
require_once '../includes/smart_pool_calculator.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username();

// Get the active EQUB (Selam equb term)
try {
    $stmt = $pdo->query("
        SELECT * FROM equb_settings 
        WHERE status = 'active' AND equb_name LIKE '%Selam%'
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $active_equb = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$active_equb) {
        // Fallback to any active EQUB
        $stmt = $pdo->query("
            SELECT * FROM equb_settings 
            WHERE status = 'active'
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $active_equb = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $active_equb = null;
    error_log("Error fetching active EQUB: " . $e->getMessage());
}

$csrf_token = generate_csrf_token();

// Auto-analyze the active EQUB
$analysis_result = null;
if ($active_equb) {
    try {
        $calculator = getSmartPoolCalculator();
        $analysis_result = $calculator->generateEqubBreakdown($active_equb['id']);
    } catch (Exception $e) {
        error_log("Auto-analysis error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚨 SMART EQUB DIAGNOSTICS - HabeshaEqub Admin</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .diagnostics-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            background: linear-gradient(135deg, #FF4500 0%, #FF6B35 100%);
            color: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 8px 32px rgba(255, 69, 0, 0.3);
            border: 3px solid #FFD700;
            position: relative;
            overflow: hidden;
        }
        
        .page-header::before {
            content: '🚨';
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 40px;
            animation: pulse 1s infinite;
        }
        
        .page-title-section h1 {
            font-size: 36px;
            font-weight: 800;
            margin: 0 0 8px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .urgent-badge {
            background: #FFD700;
            color: #FF4500;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
            animation: blink 1s infinite;
        }
        
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.5; }
        }
        
        .diagnostic-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
        }
        
        .equb-analysis {
            border-left: 5px solid var(--color-coral);
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.1), rgba(255, 107, 53, 0.05));
        }
        
        .equb-analysis.error {
            border-left-color: #FF4500;
            background: linear-gradient(135deg, rgba(255, 69, 0, 0.15), rgba(255, 69, 0, 0.05));
        }
        
        .equb-analysis.correct {
            border-left-color: #10B981;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
        }
        
        .comparison-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .comparison-table th {
            background: var(--color-purple);
            color: white;
            font-weight: 600;
            padding: 15px;
            border: none;
        }
        
        .comparison-table td {
            padding: 12px 15px;
            border-color: var(--border-color);
        }
        
        .error-value {
            color: #FF4500;
            font-weight: 700;
            background: rgba(255, 69, 0, 0.1);
            padding: 4px 8px;
            border-radius: 6px;
        }
        
        .correct-value {
            color: #10B981;
            font-weight: 700;
            background: rgba(16, 185, 129, 0.1);
            padding: 4px 8px;
            border-radius: 6px;
        }
        
        .fix-button {
            background: linear-gradient(135deg, #10B981, #059669);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .fix-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            color: white;
        }
        
        .danger-button {
            background: linear-gradient(135deg, #FF4500, #FF6B35);
            box-shadow: 0 4px 12px rgba(255, 69, 0, 0.3);
        }
        
        .danger-button:hover {
            box-shadow: 0 6px 20px rgba(255, 69, 0, 0.4);
        }
        
        .member-breakdown {
            background: var(--color-cream);
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .member-item {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid var(--color-teal);
        }
        
        .member-item.joint {
            border-left-color: var(--color-gold);
        }
        
        .impact-summary {
            background: linear-gradient(135deg, rgba(255, 69, 0, 0.1), rgba(255, 107, 53, 0.05));
            border: 2px solid #FF4500;
            border-radius: 16px;
            padding: 25px;
            margin-top: 30px;
        }
        
        .impact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        
        .impact-metric {
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        
        .impact-value {
            font-size: 24px;
            font-weight: 700;
            color: #FF4500;
        }
        
        .impact-label {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    
    <div class="diagnostics-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title-section">
                <h1>
                    🚨 SMART EQUB DIAGNOSTICS
                    <span class="urgent-badge">CRITICAL FIX</span>
                </h1>
                <p class="mb-0" style="font-size: 18px; opacity: 0.9;">
                    Detect and fix fundamental EQUB duration & payout calculation errors
                </p>
                <div style="margin-top: 15px; font-size: 14px; opacity: 0.8;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Issue:</strong> System may incorrectly calculate payouts. Duration is FIXED (9 months), positions should match duration
                </div>
            </div>
        </div>
        
        <!-- Current Active EQUB Analysis -->
        <?php if (!$active_equb): ?>
            <div class="diagnostic-card">
                <div class="alert alert-warning">
                    <h4><i class="fas fa-exclamation-triangle"></i> No Active EQUB Found</h4>
                    <p>Please create and activate an EQUB first.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="diagnostic-card">
                <h3><i class="fas fa-chart-line"></i> Analyzing: <?php echo htmlspecialchars($active_equb['equb_name']); ?></h3>
                <div class="row mb-4">
                    <div class="col-md-8">
                        <p><strong>EQUB ID:</strong> <?php echo htmlspecialchars($active_equb['equb_id']); ?></p>
                        <p><strong>Duration:</strong> <?php echo $active_equb['duration_months']; ?> months</p>
                        <p><strong>Status:</strong> <span class="badge bg-success"><?php echo ucfirst($active_equb['status']); ?></span></p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button id="fixBtn" class="btn fix-button btn-lg">
                            <i class="fas fa-magic"></i> FIX PAYOUT CALCULATIONS
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Analysis Results -->
            <?php if ($analysis_result && $analysis_result['success']): ?>
                <?php $analysis = $analysis_result['breakdown']; ?>
                <div class="diagnostic-card equb-analysis <?php echo $analysis['pool_metrics']['needs_correction'] ? 'error' : 'correct'; ?>">
                    <h3>
                        <i class="fas fa-<?php echo $analysis['pool_metrics']['needs_correction'] ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
                        Analysis Results: <?php echo $analysis['pool_metrics']['needs_correction'] ? 'PAYOUT ERRORS FOUND' : 'CALCULATIONS CORRECT'; ?>
                    </h3>
                    
                    <div class="comparison-table">
                        <table class="table table-borderless mb-0">
                            <thead>
                                <tr>
                                    <th>Metric</th>
                                    <th>Current Value</th>
                                    <th>Analysis</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>EQUB Duration</strong></td>
                                    <td><span class="correct-value"><?php echo $analysis['pool_metrics']['fixed_duration']; ?> months</span></td>
                                    <td>FIXED by admin (correct)</td>
                                    <td><i class="fas fa-check-circle text-success"></i> CORRECT</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Positions</strong></td>
                                    <td><span class="<?php echo $analysis['pool_metrics']['positions_duration_match'] ? 'correct' : 'error'; ?>-value"><?php echo $analysis['pool_metrics']['actual_positions']; ?> positions</span></td>
                                    <td>Individuals + Joint groups</td>
                                    <td>
                                        <?php if ($analysis['pool_metrics']['positions_duration_match']): ?>
                                            <i class="fas fa-check-circle text-success"></i> MATCHES DURATION
                                        <?php else: ?>
                                            <i class="fas fa-exclamation-triangle text-warning"></i> MISMATCH
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Total Monthly Pool</strong></td>
                                    <td><span class="correct-value">£<?php echo number_format($analysis['pool_metrics']['total_monthly_pool']); ?></span></td>
                                    <td>Sum of all contributions</td>
                                    <td><i class="fas fa-info-circle text-info"></i> Pool Amount</td>
                                </tr>
                                <tr>
                                    <td><strong>Gross Payout per Position</strong></td>
                                    <td><span class="correct-value">£<?php echo number_format($analysis['pool_metrics']['gross_payout_per_position']); ?></span></td>
                                    <td>Monthly pool amount (SAME for all)</td>
                                    <td>
                                        <?php if ($analysis['pool_metrics']['needs_correction']): ?>
                                            <i class="fas fa-times-circle text-danger"></i> NEEDS FIX
                                        <?php else: ?>
                                            <i class="fas fa-check-circle text-success"></i> CORRECT
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Member Breakdown -->
                    <div class="member-breakdown">
                        <h5><i class="fas fa-users"></i> Member Breakdown</h5>
                        <?php foreach ($analysis['members'] as $member): ?>
                            <?php $info = $member['member_info']; ?>
                            <?php $calc = $member['payout_calculation']; ?>
                            <?php $isJoint = $info['membership_type'] === 'joint'; ?>
                            <div class="member-item <?php echo $isJoint ? 'joint' : ''; ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo htmlspecialchars($info['first_name'] . ' ' . $info['last_name']); ?></strong>
                                        <?php if ($isJoint): ?>
                                            <span class="badge bg-warning ms-2">Joint: <?php echo htmlspecialchars($info['group_name']); ?></span>
                                        <?php endif; ?>
                                        <div class="small text-muted">
                                            Monthly: £<?php echo number_format($isJoint ? $info['individual_contribution'] : $info['monthly_payment'], 2); ?>
                                            <?php if ($isJoint): ?>
                                                (Group: £<?php echo number_format($info['joint_payment'], 2); ?>)
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold">£<?php echo $calc['success'] ? number_format($calc['gross_payout'], 2) : 'Error'; ?></div>
                                        <div class="small text-muted">Gross Payout</div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="diagnostic-card">
                    <div class="alert alert-danger">
                        <h4><i class="fas fa-exclamation-triangle"></i> Analysis Failed</h4>
                        <p>Unable to analyze the EQUB. Please check the data.</p>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const activeEqubId = <?php echo $active_equb ? $active_equb['id'] : 'null'; ?>;
        const csrfToken = '<?php echo $csrf_token; ?>';
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            const fixBtn = document.getElementById('fixBtn');
            if (fixBtn && activeEqubId) {
                fixBtn.addEventListener('click', fixEqubCalculations);
            }
        });
        
        // Fix EQUB calculations
        async function fixEqubCalculations() {
            if (!activeEqubId) {
                alert('No active EQUB found');
                return;
            }
            
            if (!confirm('⚠️ This will fix the payout calculations for the active EQUB. Continue?')) {
                return;
            }
            
            try {
                const response = await fetch('api/smart-equb-diagnostics.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=fix_equb&equb_id=${activeEqubId}&csrf_token=${csrfToken}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('✅ EQUB payout calculations fixed successfully! Duration: ' + 
                          data.fixed_duration + ' months, Positions: ' + data.actual_positions);
                    location.reload(); // Refresh to show updated results
                } else {
                    alert('❌ Error: ' + data.message);
                }
            } catch (error) {
                alert('❌ Failed to fix EQUB: ' + error.message);
            }
        }
    </script>
</body>
</html>