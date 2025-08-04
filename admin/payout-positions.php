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
        let sortableInstance = null;
        let hasChanges = false;

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
                if (data.success && data.data && data.data.members) {
                    currentMembers = data.data.members || [];
                    displayPositions(currentMembers);
                    updateStats(data.data.stats || {});
                    document.getElementById('equbStats').style.display = 'block';
                    document.getElementById('autoSortSection').style.display = 'block';
                } else {
                    alert('Error loading positions: ' + (data.message || 'Unknown error'));
                    currentMembers = [];
                    displayPositions([]);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error. Please try again.');
            });
        }

        function displayPositions(members) {
            const container = document.getElementById('positionsContent');
            
            // Ensure members is an array
            if (!members || !Array.isArray(members) || members.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h5>No members found</h5>
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
                    <h5><i class="fas fa-list-ol text-primary me-2"></i>Payout Order (Drag to reorder)</h5>
                    <div class="text-muted small">
                        <i class="fas fa-hand-paper me-1"></i>Drag and drop to change positions
                    </div>
                </div>
                <div id="sortableList">
                    ${members.map((member, index) => createPositionCard(member, index + 1)).join('')}
                </div>
            `;

            // Initialize sortable
            if (sortableInstance) {
                sortableInstance.destroy();
            }
            
            sortableInstance = Sortable.create(document.getElementById('sortableList'), {
                animation: 150,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onUpdate: function() {
                    updatePositionNumbers();
                    markAsChanged();
                }
            });
        }

        function createPositionCard(member, position) {
            const payoutAmount = member.monthly_payment * member.duration_months;
            const isJoint = member.membership_type === 'joint';
            
            return `
                <div class="position-card" data-member-id="${member.id}">
                    <div class="d-flex align-items-center">
                        <div class="position-number">${position}</div>
                        <div class="member-info">
                            <div class="member-name">
                                ${member.first_name} ${member.last_name}
                                ${isJoint ? `<span class="joint-badge">Joint</span>` : ''}
                            </div>
                            <div class="member-details">
                                <i class="fas fa-id-card me-1"></i>${member.member_id}
                                <span class="ms-3"><i class="fas fa-pound-sign me-1"></i>£${parseFloat(member.monthly_payment).toFixed(2)}/month</span>
                                ${isJoint ? `<span class="ms-3"><i class="fas fa-users me-1"></i>${member.joint_group_id}</span>` : ''}
                            </div>
                        </div>
                        <div class="payout-info">
                            <div class="payout-amount">£${payoutAmount.toFixed(2)}</div>
                            <div class="payout-date">${member.estimated_payout_date || 'TBD'}</div>
                        </div>
                        <div class="ms-3 text-muted">
                            <i class="fas fa-grip-vertical"></i>
                        </div>
                    </div>
                </div>
            `;
        }

        function updatePositionNumbers() {
            const cards = document.querySelectorAll('.position-card');
            cards.forEach((card, index) => {
                card.querySelector('.position-number').textContent = index + 1;
            });
        }

        function updateStats(stats) {
            document.getElementById('statDuration').textContent = stats.duration + ' months';
            document.getElementById('statMembers').textContent = stats.total_members;
            document.getElementById('statIndividual').textContent = stats.individual_members;
            document.getElementById('statJoint').textContent = stats.joint_groups;
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

        function savePositions() {
            if (!hasChanges) return;
            
            const cards = document.querySelectorAll('.position-card');
            const positions = Array.from(cards).map((card, index) => ({
                member_id: parseInt(card.dataset.memberId),
                position: index + 1
            }));
            
            fetch('api/payout-positions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'save_positions',
                    equb_id: currentEqubId,
                    positions: positions
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Payout positions saved successfully!');
                    hasChanges = false;
                    document.getElementById('saveBtn').disabled = true;
                    document.getElementById('saveBtn').classList.remove('btn-warning');
                    document.getElementById('saveBtn').classList.add('btn-success');
                    loadPositions(currentEqubId); // Reload to get updated data
                } else {
                    alert('Error saving positions: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error. Please try again.');
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