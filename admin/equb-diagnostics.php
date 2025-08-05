<?php
/**
 * HabeshaEqub - EQUB Diagnostics & Position Fixer
 * Advanced admin tool to analyze and fix EQUB logical issues
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username();

// Get all EQUBs for selection
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
    <title>EQUB Diagnostics & Position Fixer - HabeshaEqub Admin</title>
    
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
        .diagnostics-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            background: linear-gradient(135deg, #FF6B6B 0%, #4ECDC4 100%);
            color: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        
        .diagnostic-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        }
        
        .issue-card {
            background: #FFF3CD;
            border: 1px solid #FFE69C;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .issue-card.error {
            background: #F8D7DA;
            border-color: #F5C2C7;
        }
        
        .issue-card.success {
            background: #D1E7DD;
            border-color: #BADBCC;
        }
        
        .fix-button {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .fix-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
            color: white;
        }
        
        .member-row {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
            border-left: 4px solid #007bff;
        }
        
        .member-row.joint {
            border-left-color: #28a745;
        }
        
        .member-row.issue {
            border-left-color: #dc3545;
        }
        
        .position-coefficient {
            background: #007bff;
            color: white;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
        }
        
        .spinner-border {
            color: #007bff;
        }
    </style>
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    
    <div class="diagnostics-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-diagnoses"></i> EQUB Diagnostics & Position Fixer</h1>
            <p class="mb-0">Advanced tool to analyze and fix EQUB logical issues, position calculations, and member assignments</p>
        </div>
        
        <!-- EQUB Selection -->
        <div class="diagnostic-card">
            <h3><i class="fas fa-search"></i> Select EQUB to Analyze</h3>
            <div class="row">
                <div class="col-md-6">
                    <select id="equbSelector" class="form-select form-select-lg">
                        <option value="">Choose an EQUB to analyze...</option>
                        <?php foreach ($equbs as $equb): ?>
                            <option value="<?php echo $equb['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($equb['equb_name']); ?>"
                                    data-status="<?php echo $equb['status']; ?>">
                                <?php echo htmlspecialchars($equb['equb_name']); ?> 
                                (<?php echo $equb['equb_id']; ?>) - <?php echo ucfirst($equb['status']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <button id="analyzeBtn" class="btn btn-primary btn-lg" disabled>
                        <i class="fas fa-search"></i> Analyze EQUB
                    </button>
                    <button id="autoFixBtn" class="btn btn-success btn-lg" disabled>
                        <i class="fas fa-magic"></i> Auto-Fix Issues
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Loading indicator -->
        <div id="loadingIndicator" class="diagnostic-card loading" style="display: none;">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3">Analyzing EQUB positions and calculations...</p>
        </div>
        
        <!-- Analysis Results -->
        <div id="analysisResults" style="display: none;">
            <!-- EQUB Overview -->
            <div class="diagnostic-card">
                <h3><i class="fas fa-info-circle"></i> EQUB Overview</h3>
                <div id="equbOverview"></div>
            </div>
            
            <!-- Issues Found -->
            <div class="diagnostic-card">
                <h3><i class="fas fa-exclamation-triangle text-warning"></i> Issues Found</h3>
                <div id="issuesContainer"></div>
            </div>
            
            <!-- Position Analysis -->
            <div class="diagnostic-card">
                <h3><i class="fas fa-users"></i> Member Position Analysis</h3>
                <div id="positionAnalysis"></div>
            </div>
            
            <!-- Financial Summary -->
            <div class="diagnostic-card">
                <h3><i class="fas fa-calculator"></i> Financial Summary</h3>
                <div id="financialSummary"></div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const csrfToken = '<?php echo $csrf_token; ?>';
        let currentEqubId = null;
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            const equbSelector = document.getElementById('equbSelector');
            const analyzeBtn = document.getElementById('analyzeBtn');
            const autoFixBtn = document.getElementById('autoFixBtn');
            
            equbSelector.addEventListener('change', function() {
                currentEqubId = this.value;
                analyzeBtn.disabled = !currentEqubId;
                autoFixBtn.disabled = !currentEqubId;
                
                if (!currentEqubId) {
                    document.getElementById('analysisResults').style.display = 'none';
                }
            });
            
            analyzeBtn.addEventListener('click', analyzeEqub);
            autoFixBtn.addEventListener('click', autoFixEqub);
        });
        
        // Analyze EQUB function
        async function analyzeEqub() {
            if (!currentEqubId) return;
            
            showLoading(true);
            hideResults();
            
            try {
                const response = await fetch('api/equb-position-fixer.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=get_equb_analysis&equb_id=${currentEqubId}&csrf_token=${csrfToken}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    displayAnalysisResults(data);
                } else {
                    showAlert('Error: ' + data.message, 'danger');
                }
            } catch (error) {
                showAlert('Failed to analyze EQUB: ' + error.message, 'danger');
            } finally {
                showLoading(false);
            }
        }
        
        // Auto-fix EQUB function
        async function autoFixEqub() {
            if (!currentEqubId) return;
            
            if (!confirm('This will automatically fix position calculations and update the database. Continue?')) {
                return;
            }
            
            showLoading(true);
            
            try {
                const response = await fetch('api/equb-position-fixer.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=auto_fix_positions&equb_id=${currentEqubId}&csrf_token=${csrfToken}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('EQUB positions fixed successfully!', 'success');
                    // Re-analyze to show updated results
                    setTimeout(() => analyzeEqub(), 1000);
                } else {
                    showAlert('Error fixing EQUB: ' + data.message, 'danger');
                }
            } catch (error) {
                showAlert('Failed to fix EQUB: ' + error.message, 'danger');
            } finally {
                showLoading(false);
            }
        }
        
        // Display analysis results
        function displayAnalysisResults(data) {
            // EQUB Overview
            const equbOverview = document.getElementById('equbOverview');
            equbOverview.innerHTML = `
                <div class="row">
                    <div class="col-md-3">
                        <strong>EQUB Name:</strong><br>
                        ${data.equb_details.equb_name}
                    </div>
                    <div class="col-md-3">
                        <strong>Regular Tier:</strong><br>
                        £${data.equb_details.regular_payment_tier}
                    </div>
                    <div class="col-md-3">
                        <strong>Duration:</strong><br>
                        ${data.equb_details.duration_months} months
                    </div>
                    <div class="col-md-3">
                        <strong>Total Members:</strong><br>
                        ${data.equb_details.actual_member_count}
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-3">
                        <strong>Position Coefficients:</strong><br>
                        ${data.equb_details.total_position_coefficients}
                    </div>
                    <div class="col-md-3">
                        <strong>Monthly Pool:</strong><br>
                        £${data.financial_summary.total_monthly_pool}
                    </div>
                    <div class="col-md-3">
                        <strong>Projected Total:</strong><br>
                        £${data.financial_summary.projected_total}
                    </div>
                    <div class="col-md-3">
                        <strong>Status:</strong><br>
                        <span class="badge bg-${data.equb_details.status === 'active' ? 'success' : 'secondary'}">
                            ${data.equb_details.status.toUpperCase()}
                        </span>
                    </div>
                </div>
            `;
            
            // Issues Found
            const issuesContainer = document.getElementById('issuesContainer');
            if (data.issues.length === 0) {
                issuesContainer.innerHTML = `
                    <div class="issue-card success">
                        <i class="fas fa-check-circle"></i> No issues found! This EQUB is properly configured.
                    </div>
                `;
            } else {
                issuesContainer.innerHTML = data.issues.map(issue => `
                    <div class="issue-card error">
                        <i class="fas fa-exclamation-triangle"></i> ${issue}
                    </div>
                `).join('') + 
                '<div class="mt-3"><h5>Recommendations:</h5>' +
                data.recommendations.map(rec => `<li>${rec}</li>`).join('') + '</div>';
            }
            
            // Position Analysis
            const positionAnalysis = document.getElementById('positionAnalysis');
            positionAnalysis.innerHTML = data.members.map(member => `
                <div class="member-row ${member.membership_type} ${member.position_coefficient > 1.5 ? 'issue' : ''}">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <strong>${member.first_name} ${member.last_name}</strong>
                            ${member.membership_type === 'joint' ? `<br><small class="text-muted">${member.group_name || 'Joint Group'}</small>` : ''}
                        </div>
                        <div class="col-md-2">
                            Position ${member.payout_position}
                        </div>
                        <div class="col-md-2">
                            £${member.effective_contribution}/month
                        </div>
                        <div class="col-md-2">
                            <span class="position-coefficient">${member.position_coefficient} positions</span>
                        </div>
                        <div class="col-md-2">
                            <span class="badge bg-${member.membership_type === 'joint' ? 'success' : 'primary'}">
                                ${member.membership_type.toUpperCase()}
                            </span>
                        </div>
                    </div>
                </div>
            `).join('');
            
            // Financial Summary
            const financialSummary = document.getElementById('financialSummary');
            financialSummary.innerHTML = `
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4 class="text-primary">£${data.financial_summary.total_monthly_pool}</h4>
                            <small>Monthly Pool</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4 class="text-success">£${data.financial_summary.projected_total}</h4>
                            <small>Total Over Duration</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4 class="text-warning">£${data.financial_summary.admin_fee_total}</h4>
                            <small>Total Admin Fees</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4 class="text-info">£${data.financial_summary.regular_tier}</h4>
                            <small>Regular Payment Tier</small>
                        </div>
                    </div>
                </div>
            `;
            
            showResults();
        }
        
        // Utility functions
        function showLoading(show) {
            document.getElementById('loadingIndicator').style.display = show ? 'block' : 'none';
        }
        
        function hideResults() {
            document.getElementById('analysisResults').style.display = 'none';
        }
        
        function showResults() {
            document.getElementById('analysisResults').style.display = 'block';
        }
        
        function showAlert(message, type) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Insert at top of container
            const container = document.querySelector('.diagnostics-container');
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