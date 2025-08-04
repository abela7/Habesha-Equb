<?php
/**
 * HabeshaEqub - Advanced Payment Tiers Management
 * Professional payment tier configuration with real-time preview
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username();

// Get all equb settings with payment tiers
try {
    $stmt = $pdo->query("
        SELECT 
            es.id, es.equb_id, es.equb_name, es.status, es.payment_tiers, es.admin_fee, 
            es.max_members, es.duration_months,
            COUNT(DISTINCT m.id) as current_members
        FROM equb_settings es
        LEFT JOIN members m ON m.equb_settings_id = es.id AND m.is_active = 1
        WHERE es.status IN ('planning', 'active') 
        GROUP BY es.id
        ORDER BY 
            CASE WHEN es.status = 'active' THEN 1 WHEN es.status = 'planning' THEN 2 ELSE 3 END,
            es.created_at DESC
    ");
    $equb_terms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching equb terms: " . $e->getMessage());
    $equb_terms = [];
}

$csrf_token = generate_csrf_token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Tiers Management - HabeshaEqub Admin</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .tier-card {
            background: var(--white);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid var(--border-light);
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.08);
            transition: all 0.3s ease;
        }
        
        .tier-card:hover {
            box-shadow: 0 12px 48px rgba(48, 25, 67, 0.12);
            transform: translateY(-2px);
        }
        
        .tier-amount {
            background: linear-gradient(135deg, var(--gold) 0%, var(--light-gold) 100%);
            color: var(--white);
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .tier-preview {
            background: linear-gradient(135deg, var(--light-purple) 0%, #F8F6FF 100%);
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .add-tier-btn {
            border: 2px dashed var(--border-light);
            background: transparent;
            color: var(--text-muted);
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .add-tier-btn:hover {
            border-color: var(--gold);
            color: var(--gold);
            background: rgba(218, 165, 32, 0.05);
        }
        
        .equb-selector {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid var(--border-light);
        }
    </style>
</head>
<body>
    <!-- Include Navigation -->
    <?php require_once 'includes/navigation.php'; ?>
    
    <div class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-coins text-gold me-3"></i>Payment Tiers Management</h1>
                    <p class="mb-0 text-muted">Configure and manage payment tiers for each EQUB term</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="equb-management.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to EQUB Management
                    </a>
                    <button class="btn btn-primary" onclick="saveAllTiers()">
                        <i class="fas fa-save me-1"></i>
                        Save All Changes
                    </button>
                </div>
            </div>
        </div>

        <!-- EQUB Term Selector -->
        <div class="equb-selector">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <label class="form-label"><strong>Select EQUB Term to Configure:</strong></label>
                    <select class="form-select" id="equbTermSelector" onchange="loadPaymentTiers(this.value)">
                        <option value="">Choose an EQUB term...</option>
                        <?php foreach ($equb_terms as $term): ?>
                            <option value="<?php echo $term['id']; ?>" 
                                    data-tiers='<?php echo htmlspecialchars($term['payment_tiers']); ?>'
                                    data-admin-fee="<?php echo $term['admin_fee']; ?>"
                                    data-duration="<?php echo $term['duration_months']; ?>">
                                <?php echo htmlspecialchars($term['equb_name'] . ' (' . $term['equb_id'] . ')'); ?>
                                - <?php echo ucfirst($term['status']); ?>
                                (<?php echo $term['current_members']; ?>/<?php echo $term['max_members']; ?> members)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <div id="equbInfo" style="display: none;">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="text-muted small">Duration</div>
                                <div class="fw-bold" id="equbDuration">-</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small">Admin Fee</div>
                                <div class="fw-bold" id="equbAdminFee">-</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small">Members</div>
                                <div class="fw-bold" id="equbMembers">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Tiers Container -->
        <div id="tiersContainer">
            <div class="text-center py-5 text-muted">
                <i class="fas fa-coins fa-3x mb-3"></i>
                <h5>Select an EQUB term to configure payment tiers</h5>
                <p>Choose an EQUB term from the dropdown above to start configuring payment tiers.</p>
            </div>
        </div>
    </div>

    <!-- Tier Template (Hidden) -->
    <div id="tierTemplate" style="display: none;">
        <div class="tier-card" data-tier-id="">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h5 class="mb-0">Payment Tier</h5>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeTier(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Amount (£) *</label>
                    <input type="number" class="form-control tier-amount-input" step="0.01" min="0" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tag *</label>
                    <input type="text" class="form-control tier-tag-input" placeholder="e.g., full, half" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-control tier-description-input" placeholder="e.g., Full Member">
                </div>
            </div>
            
            <div class="tier-preview">
                <div class="row">
                    <div class="col-md-6">
                        <div class="tier-amount" id="tierAmountPreview">£0.00</div>
                        <div class="text-center">
                            <strong class="tier-tag-preview">-</strong><br>
                            <small class="tier-description-preview text-muted">-</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Calculation Preview:</h6>
                        <div class="small">
                            <div class="d-flex justify-content-between">
                                <span>Monthly Payment:</span>
                                <span class="tier-monthly">£0.00</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Total Over <span class="equb-duration">12</span> months:</span>
                                <span class="tier-total">£0.00</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Admin Fee:</span>
                                <span class="tier-admin-fee">£20.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Net Payout:</span>
                                <span class="tier-net-payout text-success">£0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let currentEqubId = null;
        let tierCounter = 0;

        function loadPaymentTiers(equbId) {
            if (!equbId) {
                document.getElementById('tiersContainer').innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-coins fa-3x mb-3"></i>
                        <h5>Select an EQUB term to configure payment tiers</h5>
                        <p>Choose an EQUB term from the dropdown above to start configuring payment tiers.</p>
                    </div>
                `;
                document.getElementById('equbInfo').style.display = 'none';
                return;
            }

            currentEqubId = equbId;
            const option = document.querySelector(`option[value="${equbId}"]`);
            const tiers = JSON.parse(option.dataset.tiers || '[]');
            const adminFee = option.dataset.adminFee;
            const duration = option.dataset.duration;

            // Update EQUB info
            document.getElementById('equbDuration').textContent = duration + ' months';
            document.getElementById('equbAdminFee').textContent = '£' + parseFloat(adminFee).toFixed(2);
            document.getElementById('equbMembers').textContent = option.textContent.match(/\((\d+)\/(\d+) members\)/)[1] + '/' + option.textContent.match(/\((\d+)\/(\d+) members\)/)[2];
            document.getElementById('equbInfo').style.display = 'block';

            // Load tiers
            const container = document.getElementById('tiersContainer');
            container.innerHTML = '';
            tierCounter = 0;

            if (tiers.length > 0) {
                tiers.forEach(tier => {
                    addTier(tier.amount, tier.tag, tier.description);
                });
            } else {
                addTier(); // Add empty tier
            }

            // Add "Add Tier" button
            container.innerHTML += `
                <div class="add-tier-btn" onclick="addTier()">
                    <i class="fas fa-plus fa-2x mb-2"></i>
                    <div><strong>Add New Payment Tier</strong></div>
                    <small>Click to add another payment option</small>
                </div>
            `;
        }

        function addTier(amount = '', tag = '', description = '') {
            tierCounter++;
            const template = document.getElementById('tierTemplate').innerHTML;
            const container = document.getElementById('tiersContainer');
            
            // Remove existing add button
            const addBtn = container.querySelector('.add-tier-btn');
            if (addBtn) addBtn.remove();

            const tierDiv = document.createElement('div');
            tierDiv.innerHTML = template;
            tierDiv.querySelector('.tier-card').dataset.tierId = tierCounter;
            
            // Set values
            if (amount) tierDiv.querySelector('.tier-amount-input').value = amount;
            if (tag) tierDiv.querySelector('.tier-tag-input').value = tag;
            if (description) tierDiv.querySelector('.tier-description-input').value = description;

            container.appendChild(tierDiv.firstElementChild);

            // Add event listeners
            const card = container.lastElementChild;
            card.querySelector('.tier-amount-input').addEventListener('input', updatePreview);
            card.querySelector('.tier-tag-input').addEventListener('input', updatePreview);
            card.querySelector('.tier-description-input').addEventListener('input', updatePreview);

            // Update preview
            updatePreview.call(card.querySelector('.tier-amount-input'));

            // Re-add "Add Tier" button
            container.innerHTML += `
                <div class="add-tier-btn" onclick="addTier()">
                    <i class="fas fa-plus fa-2x mb-2"></i>
                    <div><strong>Add New Payment Tier</strong></div>
                    <small>Click to add another payment option</small>
                </div>
            `;
        }

        function removeTier(button) {
            const card = button.closest('.tier-card');
            const container = document.getElementById('tiersContainer');
            const tierCards = container.querySelectorAll('.tier-card');
            
            if (tierCards.length > 1) {
                card.remove();
            } else {
                alert('At least one payment tier is required.');
            }
        }

        function updatePreview() {
            const card = this.closest('.tier-card');
            const amount = parseFloat(card.querySelector('.tier-amount-input').value) || 0;
            const tag = card.querySelector('.tier-tag-input').value || '-';
            const description = card.querySelector('.tier-description-input').value || '-';
            
            const option = document.querySelector(`option[value="${currentEqubId}"]`);
            const adminFee = parseFloat(option?.dataset.adminFee || 20);
            const duration = parseInt(option?.dataset.duration || 12);

            // Update preview
            card.querySelector('#tierAmountPreview').textContent = '£' + amount.toFixed(2);
            card.querySelector('.tier-tag-preview').textContent = tag;
            card.querySelector('.tier-description-preview').textContent = description;
            card.querySelector('.tier-monthly').textContent = '£' + amount.toFixed(2);
            card.querySelector('.tier-total').textContent = '£' + (amount * duration).toFixed(2);
            card.querySelector('.tier-admin-fee').textContent = '£' + adminFee.toFixed(2);
            card.querySelector('.tier-net-payout').textContent = '£' + ((amount * duration) - adminFee).toFixed(2);
            card.querySelector('.equb-duration').textContent = duration;
        }

        function saveAllTiers() {
            if (!currentEqubId) {
                alert('Please select an EQUB term first.');
                return;
            }

            const tiers = [];
            document.querySelectorAll('.tier-card').forEach(card => {
                const amount = parseFloat(card.querySelector('.tier-amount-input').value);
                const tag = card.querySelector('.tier-tag-input').value.trim();
                const description = card.querySelector('.tier-description-input').value.trim();

                if (amount > 0 && tag) {
                    tiers.push({ amount, tag, description });
                }
            });

            if (tiers.length === 0) {
                alert('Please add at least one valid payment tier.');
                return;
            }

            // Save via API
            fetch('api/payment-tiers.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=update_tiers&equb_id=${currentEqubId}&tiers=${encodeURIComponent(JSON.stringify(tiers))}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Payment tiers updated successfully!');
                    // Refresh the page
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error. Please try again.');
            });
        }
    </script>
</body>
</html>