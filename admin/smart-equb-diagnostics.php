<?php
/**
 * HabeshaEqub - SMART EQUB DIAGNOSTICS
 * Critical tool to fix fundamental EQUB duration and payout logic errors
 */

require_once '../includes/db.php';
require_once '../includes/smart_pool_calculator.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username();

// Get all EQUBs for analysis
try {
    $stmt = $pdo->query("
        SELECT 
            id, equb_id, equb_name, status, 
            duration_months, regular_payment_tier,
            max_members, current_members
        FROM equb_settings 
        ORDER BY status DESC, created_at DESC
    ");
    $equbs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $equbs = [];
    error_log("Error fetching EQUBs: " . $e->getMessage());
}

$csrf_token = generate_csrf_token();
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
        
        <!-- EQUB Selection and Analysis -->
        <div class="diagnostic-card">
            <h3><i class="fas fa-search"></i> Select EQUB for Smart Analysis</h3>
            <div class="row">
                <div class="col-md-6">
                    <select id="equbSelector" class="form-select form-select-lg">
                        <option value="">Choose EQUB to analyze...</option>
                        <?php foreach ($equbs as $equb): ?>
                            <option value="<?php echo $equb['id']; ?>" 
                                    data-equb='<?php echo htmlspecialchars(json_encode($equb)); ?>'>
                                <?php echo htmlspecialchars($equb['equb_name']); ?> 
                                (<?php echo $equb['equb_id']; ?>) - 
                                <?php echo $equb['current_members']; ?> members, 
                                <?php echo $equb['duration_months']; ?> months
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <button id="analyzeBtn" class="btn danger-button btn-lg w-100" disabled>
                        <i class="fas fa-diagnoses"></i> ANALYZE & DETECT ERRORS
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Analysis Results -->
        <div id="analysisResults" style="display: none;">
            <!-- Comparison will be loaded here -->
        </div>
        
        <!-- Impact Summary -->
        <div id="impactSummary" style="display: none;" class="impact-summary">
            <!-- Impact metrics will be loaded here -->
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let selectedEqubId = null;
        const csrfToken = '<?php echo $csrf_token; ?>';
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('equbSelector').addEventListener('change', function() {
                selectedEqubId = this.value;
                document.getElementById('analyzeBtn').disabled = !selectedEqubId;
                
                if (!selectedEqubId) {
                    hideResults();
                }
            });
            
            document.getElementById('analyzeBtn').addEventListener('click', analyzeEqub);
        });
        
        // Analyze EQUB for errors
        async function analyzeEqub() {
            if (!selectedEqubId) return;
            
            showLoading();
            
            try {
                const response = await fetch('api/smart-equb-diagnostics.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=analyze_equb&equb_id=${selectedEqubId}&csrf_token=${csrfToken}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    displayAnalysisResults(data.analysis);
                    displayImpactSummary(data.analysis);
                } else {
                    showError(data.message);
                }
            } catch (error) {
                showError('Failed to analyze EQUB: ' + error.message);
            }
        }
        
        // Display analysis results
        function displayAnalysisResults(analysis) {
            const resultsDiv = document.getElementById('analysisResults');
            const hasErrors = analysis.pool_metrics.needs_correction;
            
            resultsDiv.innerHTML = `
                <div class="diagnostic-card equb-analysis ${hasErrors ? 'error' : 'correct'}">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3>
                            <i class="fas fa-${hasErrors ? 'exclamation-triangle' : 'check-circle'}"></i>
                            Analysis Results: ${analysis.pool_metrics.needs_correction ? 'PAYOUT CALCULATION ERRORS FOUND' : 'CALCULATIONS CORRECT'}
                        </h3>
                        ${hasErrors ? `
                            <button class="btn fix-button" onclick="fixEqubCalculations()">
                                <i class="fas fa-magic"></i> SMART FIX PAYOUTS
                            </button>
                        ` : ''}
                    </div>
                    
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
                                    <td><span class="correct-value">${analysis.pool_metrics.fixed_duration} months</span></td>
                                    <td>FIXED by admin (correct)</td>
                                    <td><i class="fas fa-check-circle text-success"></i> CORRECT</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Positions</strong></td>
                                    <td><span class="${analysis.pool_metrics.positions_duration_match ? 'correct' : 'error'}-value">${analysis.pool_metrics.actual_positions} positions</span></td>
                                    <td>7 individuals + 2 joint groups</td>
                                    <td>
                                        ${analysis.pool_metrics.positions_duration_match ? 
                                            '<i class="fas fa-check-circle text-success"></i> MATCHES DURATION' : 
                                            '<i class="fas fa-exclamation-triangle text-warning"></i> MISMATCH'
                                        }
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Total Monthly Pool</strong></td>
                                    <td><span class="correct-value">£${analysis.pool_metrics.total_monthly_pool.toLocaleString()}</span></td>
                                    <td>Sum of all contributions</td>
                                    <td><i class="fas fa-info-circle text-info"></i> Pool Amount</td>
                                </tr>
                                <tr>
                                    <td><strong>Gross Payout per Position</strong></td>
                                    <td><span class="correct-value">£${analysis.pool_metrics.gross_payout_per_position.toLocaleString()}</span></td>
                                    <td>Monthly pool amount (SAME for all)</td>
                                    <td>
                                        ${hasErrors ? 
                                            '<i class="fas fa-times-circle text-danger"></i> NEEDS FIX' : 
                                            '<i class="fas fa-check-circle text-success"></i> CORRECT'
                                        }
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    ${hasErrors ? `
                        <div class="alert alert-danger mt-4">
                            <h5><i class="fas fa-exclamation-triangle"></i> Payout Calculation Issues:</h5>
                            <ul class="mb-0">
                                <li>Current system may be calculating payouts incorrectly</li>
                                <li>Should be £${analysis.pool_metrics.gross_payout_per_position.toLocaleString()} gross for each position</li>
                                <li>Joint groups get multiple position coefficients but same gross base</li>
                                <li>Duration is FIXED at ${analysis.pool_metrics.fixed_duration} months (${analysis.pool_metrics.actual_positions} positions)</li>
                            </ul>
                        </div>
                    ` : `
                        <div class="alert alert-success mt-4">
                            <h5><i class="fas fa-check-circle"></i> All Calculations Correct!</h5>
                            <p class="mb-0">Duration: ${analysis.pool_metrics.fixed_duration} months | Positions: ${analysis.pool_metrics.actual_positions} | Pool: £${analysis.pool_metrics.total_monthly_pool.toLocaleString()}</p>
                        </div>
                    `}
                    
                    <div class="member-breakdown">
                        <h5><i class="fas fa-users"></i> Member Breakdown</h5>
                        ${generateMemberBreakdown(analysis.breakdown.members)}
                    </div>
                </div>
            `;
            
            resultsDiv.style.display = 'block';
        }
        
        // Generate member breakdown HTML
        function generateMemberBreakdown(members) {
            return members.map(member => {
                const info = member.member_info;
                const calc = member.payout_calculation;
                const isJoint = info.membership_type === 'joint';
                
                return `
                    <div class="member-item ${isJoint ? 'joint' : ''}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${info.first_name} ${info.last_name}</strong>
                                ${isJoint ? `<span class="badge bg-warning ms-2">Joint: ${info.group_name}</span>` : ''}
                                <div class="small text-muted">
                                    Monthly: £${parseFloat(isJoint ? info.individual_contribution : info.monthly_payment).toFixed(2)}
                                    ${isJoint ? ` (Group: £${parseFloat(info.joint_payment).toFixed(2)})` : ''}
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">£${calc.success ? parseFloat(calc.gross_payout).toFixed(2) : 'Error'}</div>
                                <div class="small text-muted">Gross Payout</div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        // Display impact summary
        function displayImpactSummary(analysis) {
            const summaryDiv = document.getElementById('impactSummary');
            const summary = analysis.breakdown.summary;
            
            summaryDiv.innerHTML = `
                <h4><i class="fas fa-chart-line"></i> Financial Impact Summary</h4>
                <div class="impact-grid">
                    <div class="impact-metric">
                        <div class="impact-value">${summary.total_individual_members}</div>
                        <div class="impact-label">Individual Members</div>
                    </div>
                    <div class="impact-metric">
                        <div class="impact-value">${summary.total_joint_groups}</div>
                        <div class="impact-label">Joint Groups</div>
                    </div>
                    <div class="impact-metric">
                        <div class="impact-value">${summary.total_positions}</div>
                        <div class="impact-label">Total Positions</div>
                    </div>
                    <div class="impact-metric">
                        <div class="impact-value">£${analysis.pool_metrics.total_monthly_pool.toLocaleString()}</div>
                        <div class="impact-label">Monthly Pool</div>
                    </div>
                    <div class="impact-metric">
                        <div class="impact-value">${analysis.pool_metrics.fixed_duration}</div>
                        <div class="impact-label">Fixed Duration (Months)</div>
                    </div>
                    <div class="impact-metric">
                        <div class="impact-value">£${analysis.pool_metrics.gross_payout_per_position.toLocaleString()}</div>
                        <div class="impact-label">Gross per Position</div>
                    </div>
                </div>
            `;
            
            summaryDiv.style.display = 'block';
        }
        
        // Fix EQUB calculations
        async function fixEqubCalculations() {
            if (!confirm('⚠️ This will permanently fix the EQUB duration and recalculate all member payouts. Continue?')) {
                return;
            }
            
            showLoading();
            
            try {
                const response = await fetch('api/smart-equb-diagnostics.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=fix_equb&equb_id=${selectedEqubId}&csrf_token=${csrfToken}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showSuccess('EQUB payout calculations fixed successfully! Duration remains ' + 
                               data.fixed_duration + ' months with ' + data.actual_positions + ' positions.');
                    // Re-analyze to show updated results
                    setTimeout(analyzeEqub, 1000);
                } else {
                    showError(data.message);
                }
            } catch (error) {
                showError('Failed to fix EQUB: ' + error.message);
            }
        }
        
        // Utility functions
        function showLoading() {
            document.getElementById('analysisResults').innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Analyzing EQUB calculations...</p>
                </div>
            `;
            document.getElementById('analysisResults').style.display = 'block';
        }
        
        function hideResults() {
            document.getElementById('analysisResults').style.display = 'none';
            document.getElementById('impactSummary').style.display = 'none';
        }
        
        function showError(message) {
            document.getElementById('analysisResults').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Error: ${message}
                </div>
            `;
        }
        
        function showSuccess(message) {
            const alertHtml = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            document.querySelector('.diagnostics-container').insertAdjacentHTML('afterbegin', alertHtml);
        }
    </script>
</body>
</html>