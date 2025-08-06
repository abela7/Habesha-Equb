<?php
/**
 * HabeshaEqub - Payout Position Management System
 * Professional drag-and-drop interface for customizing member payout positions
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username();

// Get all equb settings - using stored current_members field
try {
    $stmt = $pdo->query("
        SELECT 
            id, equb_id, equb_name, status, max_members, current_members,
            duration_months, admin_fee
        FROM equb_settings
        WHERE status IN ('planning', 'active') 
        ORDER BY 
            CASE WHEN status = 'active' THEN 1 WHEN status = 'planning' THEN 2 ELSE 3 END,
            created_at DESC
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
    <title>Payout Position Management - HabeshaEqub Admin</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SortableJS for drag and drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .position-card {
            background: var(--white);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 16px rgba(48, 25, 67, 0.08);
            cursor: move;
            transition: all 0.3s ease;
            user-select: none;
        }
        
        .position-card:hover {
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.15);
            transform: translateY(-2px);
        }
        
        .position-card.sortable-ghost {
            opacity: 0.4;
            background: linear-gradient(135deg, var(--light-purple) 0%, #F8F6FF 100%);
        }
        
        .position-card.sortable-drag {
            transform: rotate(5deg);
            box-shadow: 0 20px 40px rgba(48, 25, 67, 0.3);
        }
        
        .position-number {
            background: linear-gradient(135deg, var(--gold) 0%, var(--light-gold) 100%);
            color: var(--white);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 700;
            margin-right: 20px;
            flex-shrink: 0;
        }
        
        .member-info {
            flex-grow: 1;
        }
        
        .member-name {
            font-weight: 600;
            color: var(--darker-purple);
            margin-bottom: 5px;
        }
        
        .member-details {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        
        .joint-badge {
            background: var(--purple);
            color: var(--white);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-left: 10px;
        }
        
        .payout-info {
            text-align: right;
            min-width: 120px;
        }
        
        .payout-amount {
            font-weight: 700;
            color: var(--success);
        }
        
        .payout-date {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        
        .equb-selector {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid var(--border-light);
        }
        
        .positions-container {
            background: var(--white);
            border-radius: 16px;
            padding: 30px;
            border: 1px solid var(--border-light);
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.08);
        }
        
        .drag-instructions {
            background: linear-gradient(135deg, var(--light-gold) 0%, #FFF9E6 100%);
            border: 1px solid var(--gold);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .auto-sort-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .stats-row {
            background: var(--white);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid var(--border-light);
        }
        
        /* ========================================= */
        /* MOBILE RESPONSIVENESS */
        /* ========================================= */
        
        @media (max-width: 768px) {
            .position-card {
                padding: 15px;
                margin-bottom: 10px;
            }
            
            .position-card .d-flex {
                flex-direction: column;
                gap: 15px;
            }
            
            .position-number {
                width: 40px;
                height: 40px;
                font-size: 1rem;
                margin-right: 0;
                align-self: flex-start;
            }
            
            .member-info {
                order: 1;
            }
            
            .payout-info {
                order: 2;
                text-align: left;
                min-width: auto;
                background: linear-gradient(135deg, var(--light-gold) 0%, #FFF9E6 100%);
                padding: 10px;
                border-radius: 8px;
            }
            
            .member-details {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }
            
            .joint-badge {
                margin-left: 0;
                margin-top: 5px;
                display: inline-block;
                width: fit-content;
            }
            
            .equb-selector {
                padding: 20px;
            }
            
            .positions-container {
                padding: 20px;
            }
            
            .auto-sort-buttons {
                flex-direction: column;
            }
            
            .auto-sort-buttons .btn {
                width: 100%;
            }
            
            .page-header .col-md-4 {
                text-align: left !important;
                margin-top: 15px;
            }
            
            .page-header .btn {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .drag-instructions {
                padding: 15px;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 480px) {
            .position-number {
                width: 35px;
                height: 35px;
                font-size: 0.9rem;
            }
            
            .member-name {
                font-size: 1rem;
            }
            
            .member-details {
                font-size: 0.8rem;
            }
            
            .payout-amount {
                font-size: 1.1rem;
            }
            
            .admin-container {
                padding: 10px;
            }
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
                    <h1><i class="fas fa-sort-numeric-down text-gold me-3"></i>Payout Position Management</h1>
                    <p class="mb-0 text-muted">Customize the order in which members receive their payouts</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="equb-management.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to EQUB Management
                    </a>
                    <button class="btn btn-success" onclick="savePositions()" id="saveBtn" disabled>
                        <i class="fas fa-save me-1"></i>
                        Save Changes
                    </button>
                </div>
            </div>
        </div>

        <!-- EQUB Term Selector -->
        <div class="equb-selector">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <label class="form-label"><strong>Select EQUB Term:</strong></label>
                    <select class="form-select" id="equbTermSelector" onchange="loadPositions(this.value)">
                        <option value="">Choose an EQUB term...</option>
                        <?php foreach ($equb_terms as $term): ?>
                            <option value="<?php echo $term['id']; ?>">
                                <?php echo htmlspecialchars($term['equb_name'] . ' (' . $term['equb_id'] . ')'); ?>
                                - <?php echo ucfirst($term['status']); ?>
                                (<?php echo $term['current_members']; ?>/<?php echo $term['max_members']; ?> members)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-8" id="equbStats" style="display: none;">
                    <div class="stats-row">
                        <div class="row text-center">
                            <div class="col-3">
                                <div class="text-muted small">Duration</div>
                                <div class="fw-bold" id="statDuration">-</div>
                            </div>
                            <div class="col-3">
                                <div class="text-muted small">Members</div>
                                <div class="fw-bold" id="statMembers">-</div>
                            </div>
                            <div class="col-3">
                                <div class="text-muted small">Individual</div>
                                <div class="fw-bold" id="statIndividual">-</div>
                            </div>
                            <div class="col-3">
                                <div class="text-muted small">Joint Groups</div>
                                <div class="fw-bold" id="statJoint">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Auto-Sort Options -->
        <div id="autoSortSection" style="display: none;">
            <div class="drag-instructions">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-2"><i class="fas fa-magic text-gold me-2"></i>Quick Sort Options</h5>
                        <p class="mb-0 small">Use these buttons to automatically arrange positions, or drag & drop manually below.</p>
                    </div>
                    <div class="col-md-6">
                        <div class="auto-sort-buttons">
                            <button class="btn btn-outline-primary btn-sm" onclick="autoSort('random')">
                                <i class="fas fa-random me-1"></i>Random
                            </button>
                            <button class="btn btn-outline-info btn-sm" onclick="autoSort('alphabetical')">
                                <i class="fas fa-sort-alpha-down me-1"></i>Alphabetical
                            </button>
                            <button class="btn btn-outline-success btn-sm" onclick="autoSort('payment_amount')">
                                <i class="fas fa-pound-sign me-1"></i>By Payment
                            </button>
                            <button class="btn btn-outline-warning btn-sm" onclick="autoSort('join_date')">
                                <i class="fas fa-calendar me-1"></i>By Join Date
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="resetPositions()">
                                <i class="fas fa-undo me-1"></i>Reset
                            </button>
                            <button class="btn btn-outline-danger btn-sm" onclick="factoryReset()">
                                <i class="fas fa-trash me-1"></i>Factory Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Positions Container -->
        <div class="positions-container">
            <div id="positionsContent">
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-sort-numeric-down fa-3x mb-3"></i>
                    <h5>Select an EQUB term to manage payout positions</h5>
                    <p>Choose an EQUB term from the dropdown above to start customizing payout positions.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let currentEqubId = null;
        let currentMembers = [];
        let currentEqubData = null;
        let sortableInstance = null;
        let hasChanges = false;
        
        function getPayoutMonthDisplay(positionNumber) {
            if (!currentEqubData || !currentEqubData.start_date) {
                return `Month ${positionNumber}`;
            }
            
            // Calculate actual payout month based on start_date and position
            const startDate = new Date(currentEqubData.start_date);
            const payoutDate = new Date(startDate);
            payoutDate.setMonth(startDate.getMonth() + positionNumber - 1);
            
            const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", 
                              "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            
            return `${monthNames[payoutDate.getMonth()]} ${payoutDate.getFullYear()}`;
        }

        function loadPositions(equbId) {
            if (!equbId) {
                document.getElementById('positionsContent').innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-sort-numeric-down fa-3x mb-3"></i>
                        <h5>Select an EQUB term to manage payout positions</h5>
                        <p>Choose an EQUB term from the dropdown above to start customizing payout positions.</p>
                    </div>
                `;
                document.getElementById('equbStats').style.display = 'none';
                document.getElementById('autoSortSection').style.display = 'none';
                return;
            }

            currentEqubId = equbId;
            
            fetch('api/payout-positions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=get_positions&equb_id=${equbId}`
            })
            .then(response => response.json())
            .then(data => {
                console.log('📥 API Response:', data); // Debug log
                if (data.success && data.data) {
                    // Store all data for reference
                    const positions = data.data.positions || [];
                    currentMembers = data.data.members || [];
                    currentEqubData = data.data.equb || {}; // Store equb data for date calculations
                    
                    console.log('👥 Current Members:', currentMembers.map(m => ({
                        id: m.id, 
                        name: m.first_name + ' ' + m.last_name, 
                        position: m.payout_position
                    })));
                    console.log('🎯 Grouped Positions:', positions);
                    console.log('📅 Equb Data:', currentEqubData);
                    
                    displayPositions(positions);
                    updateStats(data.data.stats || {});
                    document.getElementById('equbStats').style.display = 'block';
                    document.getElementById('autoSortSection').style.display = 'block';
                } else {
                    alert('Error loading positions: ' + (data.message || 'Unknown error'));
                    currentMembers = [];
                    currentEqubData = null;
                    displayPositions([]);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error. Please try again.');
            });
        }

        function displayPositions(positions) {
            const container = document.getElementById('positionsContent');
            
            // Ensure positions is an array
            if (!positions || !Array.isArray(positions) || positions.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h5>No positions found</h5>
                        <p>Add members to this EQUB term to manage their payout positions.</p>
                        <a href="members.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Add Members
                        </a>
                    </div>
                `;
                return;
            }

            container.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5><i class="fas fa-list-ol text-primary me-2"></i>Payout Positions (${positions.length} positions)</h5>
                    <div class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i>Position-based view (coefficient logic)
                    </div>
                </div>
                <div id="sortableList">
                    ${positions.map((position, index) => createPositionCard(position, position.position)).join('')}
                </div>
            `;

            // Initialize sortable for position reordering
            if (sortableInstance) {
                sortableInstance.destroy();
            }
            
            sortableInstance = Sortable.create(document.getElementById('sortableList'), {
                animation: 150,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onUpdate: function(evt) {
                    console.log('🔄 Drag detected - updating position numbers');
                    updatePositionNumbers();
                    markAsChanged();
                },
                onEnd: function(evt) {
                    console.log('🎯 Drag ended - from:', evt.oldIndex, 'to:', evt.newIndex);
                    if (evt.oldIndex !== evt.newIndex) {
                        console.log('✅ Position changed - marking as changed');
                        markAsChanged();
                    }
                }
            });
        }

        function createPositionCard(positionData, positionNumber) {
            // positionData now contains: position, members[], total_coefficient, total_payout, position_type
            const members = positionData.members || [];
            const isJoint = positionData.position_type === 'joint' || members.length > 1;
            const coefficient = positionData.total_coefficient || 1.0;
            const totalPayout = positionData.total_payout || 0;
            
            // Create member display
            let memberDisplay = '';
            if (isJoint) {
                memberDisplay = `
                    <div class="member-name">
                        <i class="fas fa-users me-2 text-primary"></i>Position ${positionNumber} (Joint)
                        <span class="joint-badge">Shared Position</span>
                    </div>
                    <div class="member-details">
                        ${members.map(member => `
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span><i class="fas fa-user me-1"></i>${member.first_name} ${member.last_name}</span>
                                <div>
                                    <span class="badge bg-secondary me-1">${parseFloat(member.position_coefficient || 0).toFixed(1)}</span>
                                    <span class="text-muted">£${parseFloat(member.expected_payout || 0).toFixed(2)}</span>
                                </div>
                            </div>
                        `).join('')}
                        <div class="mt-2">
                            <i class="fas fa-calculator me-1"></i>
                            <span class="badge" style="background: var(--color-gold); color: white;">
                                ${coefficient.toFixed(1)} coefficient total
                            </span>
                        </div>
                    </div>
                `;
            } else {
                const member = members[0] || {};
                memberDisplay = `
                    <div class="member-name">
                        ${member.first_name || 'Unknown'} ${member.last_name || ''}
                    </div>
                    <div class="member-details">
                        <i class="fas fa-id-card me-1"></i>${member.member_id || 'N/A'}
                        <span class="ms-3">
                            <i class="fas fa-pound-sign me-1"></i>
                            £${parseFloat(member.effective_payment || member.monthly_payment || 0).toFixed(2)}/month
                        </span>
                        <span class="ms-3">
                            <i class="fas fa-calculator me-1"></i>
                            <span class="badge" style="background: var(--color-gold); color: white;">
                                ${coefficient.toFixed(1)} coefficient
                            </span>
                        </span>
                    </div>
                `;
            }
            
            return `
                <div class="position-card" data-position="${positionNumber}">
                    <div class="d-flex align-items-center">
                        <div class="position-number">${positionNumber}</div>
                        <div class="member-info">
                            ${memberDisplay}
                        </div>
                        <div class="payout-info">
                            <div class="payout-amount">£${totalPayout.toFixed(2)}</div>
                            <div class="payout-date">${getPayoutMonthDisplay(positionNumber)}</div>
                            ${isJoint ? '<div class="small text-primary">Split among members</div>' : ''}
                        </div>
                        <div class="ms-3 text-muted">
                            <i class="fas fa-info-circle" title="Position ${positionNumber} details"></i>
                        </div>
                    </div>
                </div>
            `;
        }

        function updatePositionNumbers() {
            const cards = document.querySelectorAll('.position-card');
            cards.forEach((card, index) => {
                const newPosition = index + 1;
                card.querySelector('.position-number').textContent = newPosition;
                card.dataset.position = newPosition;
            });
        }

        function updateStats(stats) {
            document.getElementById('statDuration').textContent = (stats.duration_months || stats.duration || 0) + ' months';
            document.getElementById('statMembers').textContent = (stats.total_positions || 0) + ' positions (' + (stats.total_members || 0) + ' people)';
            document.getElementById('statIndividual').textContent = stats.individual_positions || 'N/A';
            document.getElementById('statJoint').textContent = stats.joint_positions || 'N/A';
            
            // Add coefficient validation display
            if (stats.position_balance !== undefined) {
                const balanceText = stats.position_balance ? 'Balanced ✅' : 'Unbalanced ⚠️';
                const balanceColor = stats.position_balance ? 'text-success' : 'text-warning';
                
                // Add balance indicator to stats if not exists
                let balanceElement = document.getElementById('statBalance');
                if (!balanceElement) {
                    const container = document.querySelector('.stats-row .col-md-3:last-child');
                    if (container) {
                        container.insertAdjacentHTML('afterend', `
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="stat-icon"><i class="fas fa-balance-scale"></i></div>
                                    <div class="stat-info">
                                        <div class="stat-value ${balanceColor}" id="statBalance">${balanceText}</div>
                                        <div class="stat-label">Position Balance</div>
                                    </div>
                                </div>
                            </div>
                        `);
                    }
                } else {
                    balanceElement.textContent = balanceText;
                    balanceElement.className = `stat-value ${balanceColor}`;
                }
            }
        }

        function markAsChanged() {
            hasChanges = true;
            document.getElementById('saveBtn').disabled = false;
            document.getElementById('saveBtn').classList.remove('btn-success');
            document.getElementById('saveBtn').classList.add('btn-warning');
        }

        function autoSort(method) {
            if (!currentMembers.length) return;
            
            let sortedMembers = [...currentMembers];
            
            switch (method) {
                case 'random':
                    sortedMembers.sort(() => Math.random() - 0.5);
                    break;
                case 'alphabetical':
                    sortedMembers.sort((a, b) => (a.first_name + ' ' + a.last_name).localeCompare(b.first_name + ' ' + b.last_name));
                    break;
                case 'payment_amount':
                    sortedMembers.sort((a, b) => b.monthly_payment - a.monthly_payment);
                    break;
                case 'join_date':
                    sortedMembers.sort((a, b) => new Date(a.join_date) - new Date(b.join_date));
                    break;
            }
            
            currentMembers = sortedMembers;
            displayPositions(currentMembers);
            markAsChanged();
        }

        function resetPositions() {
            if (confirm('Reset all positions to original order?')) {
                loadPositions(currentEqubId);
            }
        }

        function factoryReset() {
            if (!currentEqubId) {
                alert('Please select an EQUB term first.');
                return;
            }
            
            if (confirm('⚠️ FACTORY RESET WARNING ⚠️\n\nThis will:\n• Clear ALL payout positions (set to 0)\n• Remove all position assignments\n• Cannot be undone\n\nAre you absolutely sure?')) {
                if (confirm('Final confirmation: Clear all positions and start from zero?')) {
                    performFactoryReset();
                }
            }
        }
        
        function performFactoryReset() {
            fetch('api/payout-positions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=factory_reset&equb_id=${currentEqubId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Factory reset completed!\nAll positions have been cleared.');
                    loadPositions(currentEqubId); // Reload to show updated data
                } else {
                    alert('❌ Error during factory reset: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error during factory reset. Please try again.');
            });
        }

        function savePositions() {
            if (!hasChanges) return;
            
            console.log('💾 SAVING POSITIONS - Getting current card order');
            
            const cards = document.querySelectorAll('.position-card');
            const updates = [];
            
            // Get the CURRENT visual order of cards and map members to new positions
            Array.from(cards).forEach((card, visualIndex) => {
                const newPosition = visualIndex + 1; // First card = position 1, second = position 2, etc.
                const originalPosition = parseInt(card.dataset.position);
                
                console.log(`📍 Visual position ${visualIndex + 1}: Card with original position ${originalPosition}`);
                
                // Find ALL members from this original position
                const membersInThisPosition = currentMembers.filter(member => 
                    member.payout_position === originalPosition
                );
                
                membersInThisPosition.forEach(member => {
                    updates.push({
                        member_id: member.id,
                        position: newPosition
                    });
                    
                    console.log(`👤 ${member.first_name} ${member.last_name} (ID: ${member.id}): ${originalPosition} → ${newPosition}`);
                });
            });
            
            if (updates.length === 0) {
                alert('No changes to save!');
                return;
            }
            
            console.log(`📤 Sending ${updates.length} position updates to database`);
            
            fetch('api/payout-positions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=update_positions&equb_id=${currentEqubId}&positions=${JSON.stringify(updates)}`
            })
            .then(response => response.json())
            .then(data => {
                console.log('📥 Database update result:', data);
                if (data.success) {
                    alert('✅ Positions saved successfully!');
                    hasChanges = false;
                    document.getElementById('saveBtn').disabled = true;
                    document.getElementById('saveBtn').classList.remove('btn-warning');
                    document.getElementById('saveBtn').classList.add('btn-success');
                    
                    // Reload to show updated positions
                    loadPositions(currentEqubId);
                } else {
                    alert('❌ Save failed: ' + data.message);
                }
            })
            .catch(error => {
                console.error('💥 Network error:', error);
                alert('💥 Network error - please try again');
            });
        }

        // Warn user about unsaved changes
        window.addEventListener('beforeunload', function(e) {
            if (hasChanges) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                return e.returnValue;
            }
        });
    </script>
</body>
</html>