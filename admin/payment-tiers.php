<?php
/**
 * HabeshaEqub - Payment Tiers Management
 * Advanced payment tier configuration with regular tier selection
 * Rebuilt from scratch with proper logic and design
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username();

// Get all EQUBs for tier management
try {
    $stmt = $pdo->query("
        SELECT 
            id, equb_id, equb_name, status, 
            payment_tiers, regular_payment_tier, calculated_positions, duration_months,
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
    <title>Payment Tiers Management - HabeshaEqub Admin</title>
    
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
        .payment-tiers-container {
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
            background: linear-gradient(135deg, var(--color-gold) 0%, #D4A72C 100%);
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
        
        .tier-management-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
        }
        
        .equb-selector {
            margin-bottom: 30px;
        }
        
        .equb-card {
            background: white;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .equb-card:hover {
            border-color: var(--color-teal);
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }
        
        .equb-card.selected {
            border-color: var(--color-teal);
            background: linear-gradient(135deg, rgba(19, 102, 92, 0.05), rgba(19, 102, 92, 0.02));
        }
        
        .equb-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .equb-details h4 {
            color: var(--color-purple);
            margin: 0 0 5px 0;
        }
        
        .equb-meta {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .equb-status {
            text-align: right;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background: linear-gradient(135deg, var(--color-teal), var(--btn-primary-hover));
            color: white;
        }
        
        .status-planning {
            background: linear-gradient(135deg, var(--color-gold), #D4A72C);
            color: white;
        }
        
        .status-completed {
            background: linear-gradient(135deg, var(--color-light-gold), #B8941C);
            color: white;
        }
        
        .regular-tier-section {
            background: linear-gradient(135deg, rgba(233, 196, 106, 0.1), rgba(205, 175, 86, 0.05));
            border: 2px solid var(--color-gold);
            border-radius: var(--radius-md);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .regular-tier-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .regular-tier-icon {
            width: 40px;
            height: 40px;
            background: var(--color-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .tier-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .tier-card {
            background: white;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 20px;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .tier-card:hover {
            border-color: var(--color-teal);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .tier-card.regular-tier {
            border-color: var(--color-gold);
            background: linear-gradient(135deg, rgba(233, 196, 106, 0.08), rgba(205, 175, 86, 0.02));
        }
        
        .tier-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .tier-amount {
            font-size: 24px;
            font-weight: 700;
            color: var(--color-purple);
        }
        
        .tier-tag {
            background: var(--color-teal);
            color: white;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .tier-card.regular-tier .tier-tag {
            background: var(--color-gold);
        }
        
        .tier-description {
            color: var(--text-secondary);
            margin-bottom: 15px;
        }
        
        .tier-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-tier-action {
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-edit {
            background: var(--color-teal);
            color: white;
        }
        
        .btn-edit:hover {
            background: var(--btn-primary-hover);
            color: white;
        }
        
        .btn-delete {
            background: var(--color-coral);
            color: white;
        }
        
        .btn-delete:hover {
            background: #D44638;
            color: white;
        }
        
        .btn-set-regular {
            background: var(--color-gold);
            color: white;
        }
        
        .btn-set-regular:hover {
            background: #D4A72C;
            color: white;
        }
        
        .add-tier-card {
            border: 2px dashed var(--border-color);
            background: transparent;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 160px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .add-tier-card:hover {
            border-color: var(--color-teal);
            background: rgba(19, 102, 92, 0.02);
        }
        
        .add-tier-icon {
            width: 48px;
            height: 48px;
            background: var(--color-teal);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-bottom: 10px;
        }
        
        .add-tier-text {
            color: var(--color-teal);
            font-weight: 600;
        }
        
        .impact-summary {
            background: linear-gradient(135deg, rgba(19, 102, 92, 0.1), rgba(15, 81, 71, 0.05));
            border: 2px solid var(--color-teal);
            border-radius: var(--radius-md);
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
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
        }
        
        .impact-value {
            font-size: 20px;
            font-weight: 700;
            color: var(--color-teal);
        }
        
        .impact-label {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .modal-content {
            border-radius: var(--radius-lg);
            border: none;
            box-shadow: var(--shadow-lg);
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--color-teal), var(--btn-primary-hover));
            color: white;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            border-bottom: none;
        }
        
        .no-equb-selected {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }
        
        .no-equb-icon {
            width: 80px;
            height: 80px;
            background: var(--border-color);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    
    <div class="payment-tiers-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title-section">
                <h1>
                    <div class="page-title-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    Payment Tiers Management
                </h1>
                <p class="page-subtitle">Configure payment tiers and set regular tier for position calculations</p>
            </div>
        </div>
        
        <!-- EQUB Selection -->
        <div class="tier-management-card">
            <h3><i class="fas fa-list-alt"></i> Select EQUB to Manage Tiers</h3>
            <div class="equb-selector">
                <?php if (empty($equbs)): ?>
                    <div class="no-equb-selected">
                        <div class="no-equb-icon">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <h4>No EQUBs Found</h4>
                        <p>Create an EQUB first to manage payment tiers.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($equbs as $equb): ?>
                        <div class="equb-card" data-equb-id="<?php echo $equb['id']; ?>" 
                             data-equb='<?php echo htmlspecialchars(json_encode($equb)); ?>'>
                            <div class="equb-info">
                                <div class="equb-details">
                                    <h4><?php echo htmlspecialchars($equb['equb_name']); ?></h4>
                                    <div class="equb-meta">
                                        <span><i class="fas fa-id-badge"></i> <?php echo $equb['equb_id']; ?></span>
                                        <span class="ms-3"><i class="fas fa-users"></i> <?php echo $equb['current_members']; ?>/<?php echo $equb['max_members']; ?> members</span>
                                        <span class="ms-3"><i class="fas fa-calendar"></i> <?php echo $equb['duration_months']; ?> months</span>
                                        <?php if ($equb['regular_payment_tier']): ?>
                                            <span class="ms-3"><i class="fas fa-star text-warning"></i> Regular Tier: £<?php echo number_format($equb['regular_payment_tier']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="equb-status">
                                    <span class="status-badge status-<?php echo $equb['status']; ?>">
                                        <?php echo ucfirst($equb['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Tier Management Section (Hidden initially) -->
        <div id="tierManagementSection" style="display: none;">
            <!-- Regular Tier Selection -->
            <div class="regular-tier-section">
                <div class="regular-tier-header">
                    <div class="regular-tier-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div>
                        <h4 class="mb-1">Regular Payment Tier</h4>
                        <p class="mb-0 text-muted">Base tier for position calculations - determines how many positions each member represents</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Select Regular Tier Amount</label>
                        <select id="regularTierSelect" class="form-select form-select-lg">
                            <option value="">Choose regular tier...</option>
                        </select>
                        <div class="form-text">
                            <strong>Example:</strong> If regular tier = £1000, then:
                            <br>• Member paying £500 = 0.5 positions
                            <br>• Member paying £1000 = 1.0 position  
                            <br>• Member paying £1500 = 1.5 positions
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div id="regularTierImpact" class="mt-3"></div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Tiers -->
            <div class="tier-management-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3><i class="fas fa-layer-group"></i> Payment Tiers</h3>
                    <button class="btn btn-primary" id="addTierBtn">
                        <i class="fas fa-plus"></i> Add New Tier
                    </button>
                </div>
                
                <div id="tiersGrid" class="tier-grid">
                    <!-- Tiers will be loaded here -->
                </div>
            </div>
            
            <!-- Impact Summary -->
            <div class="impact-summary">
                <h4><i class="fas fa-chart-line"></i> Position Impact Summary</h4>
                <div id="impactSummaryGrid" class="impact-grid">
                    <!-- Impact metrics will be loaded here -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add/Edit Tier Modal -->
    <div class="modal fade" id="tierModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tierModalTitle">Add Payment Tier</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="tierForm">
                        <input type="hidden" id="editingTierIndex" value="">
                        
                        <div class="mb-3">
                            <label for="tierAmount" class="form-label">Amount (£)</label>
                            <input type="number" class="form-control" id="tierAmount" step="0.01" min="1" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tierTag" class="form-label">Tag</label>
                            <input type="text" class="form-control" id="tierTag" placeholder="e.g., Full, Half, Premium" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tierDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="tierDescription" rows="2" placeholder="Describe this payment tier..."></textarea>
                        </div>
                        
                        <div class="mb-3" id="positionCoefficientInfo">
                            <label class="form-label">Position Coefficient</label>
                            <div id="positionCoefficientDisplay" class="alert alert-info">
                                Will be calculated based on regular tier
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveTierBtn">Save Tier</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let selectedEqub = null;
        let currentTiers = [];
        let currentRegularTier = 0;
        const csrfToken = '<?php echo $csrf_token; ?>';
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
        });
        
        // Initialize event listeners
        function initializeEventListeners() {
            // EQUB card selection
            document.querySelectorAll('.equb-card').forEach(card => {
                card.addEventListener('click', function() {
                    selectEqub(this);
                });
            });
            
            // Regular tier selection
            document.getElementById('regularTierSelect').addEventListener('change', function() {
                updateRegularTier(parseFloat(this.value) || 0);
            });
            
            // Add tier button
            document.getElementById('addTierBtn').addEventListener('click', function() {
                openTierModal();
            });
            
            // Save tier button
            document.getElementById('saveTierBtn').addEventListener('click', function() {
                saveTier();
            });
            
            // Tier amount input for coefficient calculation
            document.getElementById('tierAmount').addEventListener('input', function() {
                updatePositionCoefficientDisplay();
            });
        }
        
        // Select EQUB
        function selectEqub(cardElement) {
            // Remove previous selection
            document.querySelectorAll('.equb-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Select new card
            cardElement.classList.add('selected');
            
            // Get EQUB data
            selectedEqub = JSON.parse(cardElement.dataset.equb);
            currentTiers = selectedEqub.payment_tiers ? JSON.parse(selectedEqub.payment_tiers) : [];
            currentRegularTier = parseFloat(selectedEqub.regular_payment_tier) || 0;
            
            // Show tier management section
            document.getElementById('tierManagementSection').style.display = 'block';
            
            // Load tier data
            loadRegularTierOptions();
            loadTiers();
            updateImpactSummary();
        }
        
        // Load regular tier options
        function loadRegularTierOptions() {
            const select = document.getElementById('regularTierSelect');
            select.innerHTML = '<option value="">Choose regular tier...</option>';
            
            // Add options from existing tiers
            currentTiers.forEach(tier => {
                const option = document.createElement('option');
                option.value = tier.amount;
                option.textContent = `£${tier.amount} - ${tier.tag}`;
                if (tier.amount == currentRegularTier) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }
        
        // Update regular tier
        function updateRegularTier(amount) {
            currentRegularTier = amount;
            
            // Show impact
            const impactDiv = document.getElementById('regularTierImpact');
            if (amount > 0) {
                impactDiv.innerHTML = `
                    <div class="alert alert-success">
                        <strong>Regular Tier Set:</strong> £${amount.toLocaleString()}<br>
                        <small>Position coefficients will be calculated as: Member Amount ÷ £${amount.toLocaleString()}</small>
                    </div>
                `;
            } else {
                impactDiv.innerHTML = `
                    <div class="alert alert-warning">
                        <strong>No Regular Tier Selected</strong><br>
                        <small>Please select a regular tier for position calculations</small>
                    </div>
                `;
            }
            
            // Reload tiers to show updated coefficients
            loadTiers();
            updateImpactSummary();
            
            // Save to database
            saveRegularTier();
        }
        
        // Load tiers display
        function loadTiers() {
            const grid = document.getElementById('tiersGrid');
            grid.innerHTML = '';
            
            // Add existing tiers
            currentTiers.forEach((tier, index) => {
                const positionCoeff = currentRegularTier > 0 ? tier.amount / currentRegularTier : 0;
                const isRegularTier = tier.amount == currentRegularTier;
                
                const tierCard = document.createElement('div');
                tierCard.className = `tier-card ${isRegularTier ? 'regular-tier' : ''}`;
                tierCard.innerHTML = `
                    <div class="tier-header">
                        <div class="tier-amount">£${tier.amount.toLocaleString()}</div>
                        <div class="tier-tag">${tier.tag}</div>
                    </div>
                    <div class="tier-description">${tier.description || 'No description'}</div>
                    <div class="mb-2">
                        <small class="text-muted">Position Coefficient: <strong>${positionCoeff.toFixed(2)}</strong></small>
                    </div>
                    ${isRegularTier ? '<div class="mb-2"><span class="badge bg-warning">Regular Tier</span></div>' : ''}
                    <div class="tier-actions">
                        <button class="btn-tier-action btn-edit" onclick="editTier(${index})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        ${!isRegularTier ? `
                            <button class="btn-tier-action btn-set-regular" onclick="setAsRegularTier(${tier.amount})">
                                <i class="fas fa-star"></i> Set Regular
                            </button>
                        ` : ''}
                        <button class="btn-tier-action btn-delete" onclick="deleteTier(${index})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                `;
                grid.appendChild(tierCard);
            });
            
            // Add "Add Tier" card
            const addCard = document.createElement('div');
            addCard.className = 'tier-card add-tier-card';
            addCard.innerHTML = `
                <div class="add-tier-icon">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="add-tier-text">Add New Tier</div>
            `;
            addCard.addEventListener('click', openTierModal);
            grid.appendChild(addCard);
        }
        
        // Open tier modal
        function openTierModal(editIndex = null) {
            const modal = new bootstrap.Modal(document.getElementById('tierModal'));
            const form = document.getElementById('tierForm');
            form.reset();
            
            if (editIndex !== null) {
                // Editing existing tier
                const tier = currentTiers[editIndex];
                document.getElementById('tierModalTitle').textContent = 'Edit Payment Tier';
                document.getElementById('tierAmount').value = tier.amount;
                document.getElementById('tierTag').value = tier.tag;
                document.getElementById('tierDescription').value = tier.description || '';
                document.getElementById('editingTierIndex').value = editIndex;
            } else {
                // Adding new tier
                document.getElementById('tierModalTitle').textContent = 'Add Payment Tier';
                document.getElementById('editingTierIndex').value = '';
            }
            
            updatePositionCoefficientDisplay();
            modal.show();
        }
        
        // Update position coefficient display in modal
        function updatePositionCoefficientDisplay() {
            const amount = parseFloat(document.getElementById('tierAmount').value) || 0;
            const display = document.getElementById('positionCoefficientDisplay');
            
            if (currentRegularTier > 0 && amount > 0) {
                const coefficient = amount / currentRegularTier;
                display.innerHTML = `
                    <strong>Position Coefficient:</strong> ${coefficient.toFixed(2)} positions<br>
                    <small>Calculation: £${amount.toLocaleString()} ÷ £${currentRegularTier.toLocaleString()} = ${coefficient.toFixed(2)}</small>
                `;
                display.className = 'alert alert-info';
            } else if (currentRegularTier === 0) {
                display.innerHTML = 'Please set a regular tier first';
                display.className = 'alert alert-warning';
            } else {
                display.innerHTML = 'Enter amount to see position coefficient';
                display.className = 'alert alert-light';
            }
        }
        
        // Save tier
        function saveTier() {
            const form = document.getElementById('tierForm');
            const formData = new FormData(form);
            
            const tierData = {
                amount: parseFloat(formData.get('tierAmount')),
                tag: formData.get('tierTag').trim(),
                description: formData.get('tierDescription').trim()
            };
            
            // Validation
            if (!tierData.amount || tierData.amount <= 0) {
                alert('Please enter a valid amount');
                return;
            }
            
            if (!tierData.tag) {
                alert('Please enter a tag');
                return;
            }
            
            const editIndex = document.getElementById('editingTierIndex').value;
            
            if (editIndex !== '') {
                // Edit existing tier
                currentTiers[parseInt(editIndex)] = tierData;
            } else {
                // Add new tier
                // Check for duplicate amounts
                if (currentTiers.some(tier => tier.amount === tierData.amount)) {
                    alert('A tier with this amount already exists');
                    return;
                }
                currentTiers.push(tierData);
            }
            
            // Sort tiers by amount
            currentTiers.sort((a, b) => a.amount - b.amount);
            
            // Save to database and reload
            saveTiersToDatabase();
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('tierModal')).hide();
        }
        
        // Edit tier
        function editTier(index) {
            openTierModal(index);
        }
        
        // Delete tier
        function deleteTier(index) {
            const tier = currentTiers[index];
            if (confirm(`Are you sure you want to delete the "${tier.tag}" tier (£${tier.amount})?`)) {
                // Check if this is the regular tier
                if (tier.amount == currentRegularTier) {
                    alert('Cannot delete the regular tier. Please set a different regular tier first.');
                    return;
                }
                
                currentTiers.splice(index, 1);
                saveTiersToDatabase();
            }
        }
        
        // Set as regular tier
        function setAsRegularTier(amount) {
            document.getElementById('regularTierSelect').value = amount;
            updateRegularTier(amount);
        }
        
        // Save tiers to database
        async function saveTiersToDatabase() {
            if (!selectedEqub) return;
            
            try {
                const response = await fetch('api/payment-tiers.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update_tiers&equb_id=${selectedEqub.id}&tiers=${encodeURIComponent(JSON.stringify(currentTiers))}&csrf_token=${csrfToken}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Reload displays
                    loadRegularTierOptions();
                    loadTiers();
                    updateImpactSummary();
                    showAlert('Payment tiers updated successfully!', 'success');
                } else {
                    showAlert('Error updating tiers: ' + data.message, 'danger');
                }
            } catch (error) {
                showAlert('Failed to save tiers: ' + error.message, 'danger');
            }
        }
        
        // Save regular tier to database
        async function saveRegularTier() {
            if (!selectedEqub || currentRegularTier === 0) return;
            
            try {
                const response = await fetch('api/payment-tiers.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update_regular_tier&equb_id=${selectedEqub.id}&regular_tier=${currentRegularTier}&csrf_token=${csrfToken}`
                });
                
                const data = await response.json();
                
                if (!data.success) {
                    showAlert('Error updating regular tier: ' + data.message, 'warning');
                }
            } catch (error) {
                console.error('Failed to save regular tier:', error);
            }
        }
        
        // Update impact summary
        function updateImpactSummary() {
            const grid = document.getElementById('impactSummaryGrid');
            
            if (!selectedEqub || currentTiers.length === 0) {
                grid.innerHTML = '<p class="text-muted">Select an EQUB and add tiers to see impact summary</p>';
                return;
            }
            
            // Calculate metrics
            const totalTiers = currentTiers.length;
            const minAmount = Math.min(...currentTiers.map(t => t.amount));
            const maxAmount = Math.max(...currentTiers.map(t => t.amount));
            const totalPositions = currentRegularTier > 0 ? 
                currentTiers.reduce((sum, tier) => sum + (tier.amount / currentRegularTier), 0) : 0;
            
            grid.innerHTML = `
                <div class="impact-metric">
                    <div class="impact-value">${totalTiers}</div>
                    <div class="impact-label">Total Tiers</div>
                </div>
                <div class="impact-metric">
                    <div class="impact-value">£${minAmount.toLocaleString()}</div>
                    <div class="impact-label">Minimum Amount</div>
                </div>
                <div class="impact-metric">
                    <div class="impact-value">£${maxAmount.toLocaleString()}</div>
                    <div class="impact-label">Maximum Amount</div>
                </div>
                <div class="impact-metric">
                    <div class="impact-value">${totalPositions.toFixed(1)}</div>
                    <div class="impact-label">Total Positions Available</div>
                </div>
                <div class="impact-metric">
                    <div class="impact-value">£${currentRegularTier.toLocaleString()}</div>
                    <div class="impact-label">Regular Tier</div>
                </div>
            `;
        }
        
        // Show alert
        function showAlert(message, type) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Insert at top of container
            const container = document.querySelector('.payment-tiers-container');
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