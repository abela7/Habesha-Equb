<?php
/**
 * HabeshaEqub - Smart EQUB Diagnostics
 * Identifies and fixes the fundamental logical error in EQUB calculations
 */

require_once '../includes/db.php';
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
            max_members, current_members, duration_months,
            regular_payment_tier, calculated_positions
        FROM equb_settings 
        ORDER BY status DESC, created_at DESC
    ");
    $equbs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $equbs = [];
}

$csrf_token = generate_csrf_token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart EQUB Diagnostics - HabeshaEqub Admin</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../Pictures/Icon/favicon-32x32.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .smart-diagnostics-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-lg);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title-section h1 {
            font-size: 32px;
            font-weight: 700;
            color: var(--color-purple);
            margin: 0 0 8px 0;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .page-title-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--color-coral) 0%, #D44638 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .page-subtitle {
            font-size: 18px;
            color: var(--text-secondary);
            margin: 0;
            font-weight: 400;
        }
        
        .diagnostic-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
        }
        
        .equb-selector-card {
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .equb-selector-card:hover {
            border-color: var(--color-teal);
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }
        
        .equb-selector-card.selected {
            border-color: var(--color-coral);
            background: linear-gradient(135deg, rgba(231, 111, 81, 0.05), rgba(212, 70, 56, 0.02));
        }
        
        .critical-error-alert {
            background: linear-gradient(135deg, rgba(231, 111, 81, 0.1), rgba(212, 70, 56, 0.05));
            border: 2px solid var(--color-coral);
            border-radius: var(--radius-md);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .error-icon {
            width: 60px;
            height: 60px;
            background: var(--color-coral);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-bottom: 20px;
        }
        
        .comparison-table {
            background: white;
            border-radius: var(--radius-md);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }
        
        .comparison-table th {
            background: var(--color-purple);
            color: white;
            font-weight: 600;
            padding: 15px;
        }
        
        .comparison-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .wrong-value {
            background: rgba(231, 111, 81, 0.1);
            color: var(--color-coral);
            font-weight: 600;
        }
        
        .correct-value {
            background: rgba(19, 102, 92, 0.1);
            color: var(--color-teal);
            font-weight: 600;
        }
        
        .fix-button {
            background: linear-gradient(135deg, var(--color-teal), var(--btn-primary-hover));
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .fix-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(19, 102, 92, 0.3);
            color: white;
        }
        
        .fix-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .member-analysis-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .member-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .member-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }
        
        .member-card.joint-group {
            border-left: 4px solid var(--color-gold);
        }
        
        .member-card.individual {
            border-left: 4px solid var(--color-teal);
        }
        
        .payout-comparison {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }
        
        .payout-box {
            text-align: center;
            padding: 15px;
            border-radius: var(--radius-sm);
        }
        
        .current-payout {
            background: rgba(231, 111, 81, 0.1);
            border: 1px solid var(--color-coral);
        }
        
        .correct-payout {
            background: rgba(19, 102, 92, 0.1);
            border: 1px solid var(--color-teal);
        }
        
        .loading {
            text-align: center;
            padding: 40px;
        }
        
        .spinner-border {
            color: var(--color-teal);
        }
        
        .no-issues {
            text-align: center;
            padding: 40px;
            color: var(--color-teal);
        }
    </style>
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    
    <div class="smart-diagnostics-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title-section">
                <h1>
                    <div class="page-title-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    Smart EQUB Diagnostics
                </h1>
                <p class="page-subtitle">Identifies and fixes fundamental logical errors in EQUB duration and payout calculations</p>
            </div>
        </div>
        
        <!-- Critical Error Explanation -->
        <div class="critical-error-alert">
            <div class="error-icon">
                <i class="fas fa-bug fa-2x"></i>
            </div>
            <h4 class="text-danger mb-3">🚨 CRITICAL LOGICAL ERROR DETECTED</h4>
            <div class="row">
                <div class="col-md-6">
                    <h6><strong>❌ Current WRONG Logic:</strong></h6>
                    <ul>
                        <li>Duration = Number of members</li>
                        <li>Joint groups counted as 1 position regardless of contribution</li>
                        <li>Michael (£1500) + Koki (£500) = 1 position</li>
                        <li>Everyone gets same payout amount</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6><strong>✅ CORRECT EQUB Logic:</strong></h6>
                    <ul>
                        <li>Duration = Total position coefficients</li>
                        <li>Position coefficient = Contribution ÷ Regular tier</li>
                        <li>Michael (£1500) + Koki (£500) = 2.0 positions</li>
                        <li>Payout proportional to position coefficient</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- EQUB Selection -->
        <div class="diagnostic-card">
            <h3><i class="fas fa-search"></i> Select EQUB to Diagnose</h3>
            <div class="row">
                <div class="col-md-6">
                    <?php if (empty($equbs)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <h5>No EQUBs Found</h5>
                            <p>Create an EQUB first to run diagnostics.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($equbs as $equb): ?>
                            <div class="equb-selector-card" data-equb-id="<?php echo $equb['id']; ?>" 
                                 data-equb='<?php echo htmlspecialchars(json_encode($equb)); ?>'>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1"><?php echo htmlspecialchars($equb['equb_name']); ?></h5>
                                        <p class="mb-0 text-muted">
                                            <span><i class="fas fa-id-badge"></i> <?php echo $equb['equb_id']; ?></span>
                                            <span class="ms-3"><i class="fas fa-users"></i> <?php echo $equb['current_members']; ?> members</span>
                                            <span class="ms-3"><i class="fas fa-calendar"></i> <?php echo $equb['duration_months']; ?> months</span>
                                        </p>
                                    </div>
                                    <span class="badge bg-<?php echo $equb['status'] === 'active' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($equb['status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> What This Diagnoses:</h6>
                        <ul class="mb-0">
                            <li><strong>Duration Logic:</strong> Checks if duration matches total position coefficients</li>
                            <li><strong>Payout Calculations:</strong> Verifies member payouts are proportional to contributions</li>
                            <li><strong>Joint Group Logic:</strong> Ensures joint groups get correct position count</li>
                            <li><strong>Financial Balance:</strong> Validates total pool calculations</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Diagnosis Results (Hidden initially) -->
        <div id="diagnosisResults" style="display: none;">
            <div id="loadingIndicator" class="loading">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Analyzing EQUB...</span>
                </div>
                <p class="mt-3">Running smart diagnostics...</p>
            </div>
            
            <div id="diagnosisContent"></div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let selectedEqub = null;
        const csrfToken = '<?php echo $csrf_token; ?>';
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
        });
        
        // Initialize event listeners
        function initializeEventListeners() {
            // EQUB card selection
            document.querySelectorAll('.equb-selector-card').forEach(card => {
                card.addEventListener('click', function() {
                    selectEqub(this);
                });
            });
        }
        
        // Select EQUB for diagnosis
        function selectEqub(cardElement) {
            // Remove previous selection
            document.querySelectorAll('.equb-selector-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Select new card
            cardElement.classList.add('selected');
            
            // Get EQUB data
            selectedEqub = JSON.parse(cardElement.dataset.equb);
            
            // Show diagnosis section and start analysis
            document.getElementById('diagnosisResults').style.display = 'block';
            document.getElementById('loadingIndicator').style.display = 'block';
            document.getElementById('diagnosisContent').innerHTML = '';
            
            // Run diagnosis
            runSmartDiagnostics(selectedEqub.id);
        }
        
        // Run smart diagnostics
        async function runSmartDiagnostics(equbId) {
            try {
                const formData = new FormData();
                formData.append('action', 'diagnose_equb');
                formData.append('equb_id', equbId);
                formData.append('csrf_token', csrfToken);
                
                const response = await fetch('api/smart-equb-fixer.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                document.getElementById('loadingIndicator').style.display = 'none';
                
                if (data.success) {
                    displayDiagnosisResults(data);
                } else {
                    document.getElementById('diagnosisContent').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error running diagnostics: ${data.message}
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('loadingIndicator').style.display = 'none';
                document.getElementById('diagnosisContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Failed to run diagnostics. Please try again.
                    </div>
                `;
            }
        }
        
        // Display diagnosis results
        function displayDiagnosisResults(data) {
            const diagnosis = data.diagnosis;
            const memberAnalysis = data.member_analysis;
            const issues = data.issues_found;
            
            let html = '';
            
            if (diagnosis.needs_fix) {
                // Show critical issues
                html += `
                    <div class="diagnostic-card">
                        <div class="critical-error-alert">
                            <h4 class="text-danger mb-3">🚨 CRITICAL ISSUE FOUND</h4>
                            <div class="table-responsive">
                                <table class="comparison-table table">
                                    <thead>
                                        <tr>
                                            <th>Parameter</th>
                                            <th>Current (WRONG)</th>
                                            <th>Should Be (CORRECT)</th>
                                            <th>Impact</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Duration</strong></td>
                                            <td class="wrong-value">${diagnosis.current_duration} months</td>
                                            <td class="correct-value">${diagnosis.correct_duration} months</td>
                                            <td>Payout calculations are WRONG</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Positions</strong></td>
                                            <td class="wrong-value">${issues.total_members} (member count)</td>
                                            <td class="correct-value">${diagnosis.total_position_coefficients.toFixed(1)} (coefficient sum)</td>
                                            <td>Position logic is WRONG</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Per-Position Payout</strong></td>
                                            <td class="wrong-value">£${((diagnosis.monthly_pool * diagnosis.current_duration) / diagnosis.current_duration).toLocaleString()}</td>
                                            <td class="correct-value">£${diagnosis.per_position_payout.toLocaleString()}</td>
                                            <td>Payouts are INCORRECT</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-4">
                                <button class="fix-button" onclick="fixEqubLogic(${selectedEqub.id})">
                                    <i class="fas fa-wrench me-2"></i>FIX EQUB LOGIC NOW
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                html += `
                    <div class="diagnostic-card">
                        <div class="no-issues">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <h4>✅ No Critical Issues Found</h4>
                            <p>This EQUB's duration and calculations are correct!</p>
                        </div>
                    </div>
                `;
            }
            
            // Show member analysis
            if (memberAnalysis && memberAnalysis.length > 0) {
                html += `
                    <div class="diagnostic-card">
                        <h3><i class="fas fa-users"></i> Member Payout Analysis</h3>
                        <div class="member-analysis-grid">
                `;
                
                memberAnalysis.forEach(member => {
                    const currentPayout = member.monthly_payment * diagnosis.current_duration;
                    const difference = member.correct_gross_payout - currentPayout;
                    const percentageOff = ((difference / currentPayout) * 100);
                    
                    html += `
                        <div class="member-card ${member.type}">
                            <h6>${member.name}</h6>
                            <p><strong>Monthly:</strong> £${member.monthly_payment.toLocaleString()}</p>
                            <p><strong>Position Coefficient:</strong> ${member.position_coefficient.toFixed(2)}</p>
                            
                            <div class="payout-comparison">
                                <div class="payout-box current-payout">
                                    <div><strong>Current Payout</strong></div>
                                    <div style="font-size: 1.1rem; font-weight: 600;">£${currentPayout.toLocaleString()}</div>
                                    <small>WRONG</small>
                                </div>
                                <div class="payout-box correct-payout">
                                    <div><strong>Correct Payout</strong></div>
                                    <div style="font-size: 1.1rem; font-weight: 600;">£${member.correct_gross_payout.toLocaleString()}</div>
                                    <small>CORRECT</small>
                                </div>
                            </div>
                            
                            <div class="mt-2 text-center">
                                <span class="badge ${difference > 0 ? 'bg-success' : 'bg-danger'}">
                                    ${difference > 0 ? '+' : ''}£${difference.toLocaleString()} 
                                    (${percentageOff > 0 ? '+' : ''}${percentageOff.toFixed(1)}%)
                                </span>
                            </div>
                            
                            ${member.individual_splits ? `
                                <div class="mt-3">
                                    <h6>Individual Splits:</h6>
                                    ${member.individual_splits.map(split => `
                                        <div class="d-flex justify-content-between">
                                            <span>${split.name}</span>
                                            <span>£${split.gross_share.toLocaleString()}</span>
                                        </div>
                                    `).join('')}
                                </div>
                            ` : ''}
                        </div>
                    `;
                });
                
                html += '</div></div>';
            }
            
            document.getElementById('diagnosisContent').innerHTML = html;
        }
        
        // Fix EQUB logic
        async function fixEqubLogic(equbId) {
            const button = event.target;
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Fixing...';
            
            try {
                const formData = new FormData();
                formData.append('action', 'fix_equb_duration');
                formData.append('equb_id', equbId);
                formData.append('csrf_token', csrfToken);
                
                const response = await fetch('api/smart-equb-fixer.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Show success message
                    showAlert(`✅ EQUB Fixed Successfully! Duration updated from ${data.old_duration} to ${data.new_duration} months.`, 'success');
                    
                    // Re-run diagnostics to show the fix
                    setTimeout(() => {
                        runSmartDiagnostics(equbId);
                    }, 2000);
                } else {
                    showAlert('❌ Failed to fix EQUB: ' + data.message, 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('❌ Failed to fix EQUB. Please try again.', 'danger');
            } finally {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }
        
        // Show alert
        function showAlert(message, type) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            const container = document.querySelector('.smart-diagnostics-container');
            container.insertAdjacentHTML('afterbegin', alertHtml);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                const alert = container.querySelector('.alert');
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        }
    </script>
</body>
</html>