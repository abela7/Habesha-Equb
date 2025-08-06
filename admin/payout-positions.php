<?php
/**
 * HabeshaEqub - COMPLETELY NEW Payout Position Management
 * ROBUST, AUTOMATED, NO HARDCODE
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';
require_once 'includes/admin_auth_guard.php';

$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username();

// Get all equb settings
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
    <title>NEW Payout Position Management - HabeshaEqub Admin</title>
    
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
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
        }
        
        .position-card:hover {
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.15);
            transform: translateY(-2px);
        }
        
        .position-card.sortable-ghost {
            opacity: 0.4;
            background: linear-gradient(135deg, var(--light-purple) 0%, #F8F6FF 100%);
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
        
        .payout-info {
            text-align: right;
            min-width: 120px;
        }
        
        .payout-amount {
            font-weight: 700;
            color: var(--success);
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
        
        .joint-badge {
            background: var(--purple);
            color: var(--white);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-left: 10px;
        }
        
        .individual-badge {
            background: var(--teal);
            color: var(--white);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <?php require_once 'includes/navigation.php'; ?>
    
    <div class="admin-container">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-magic text-gold me-3"></i>NEW Payout Position Management</h1>
                    <p class="mb-0 text-muted">Completely rebuilt system - robust and automated</p>
                </div>
                <div class="col-md-4 text-end">
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
                <div class="col-md-6">
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
                <div class="col-md-6" id="equbStats" style="display: none;">
                    <div class="bg-light p-3 rounded">
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
                                <div class="text-muted small">Positions</div>
                                <div class="fw-bold" id="statPositions">-</div>
                            </div>
                            <div class="col-3">
                                <div class="text-muted small">Pool</div>
                                <div class="fw-bold" id="statPool">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Positions Container -->
        <div class="positions-container">
            <div id="positionsContent">
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-magic fa-3x mb-3"></i>
                    <h5>Select an EQUB term to start managing positions</h5>
                    <p>The new system will automatically handle everything based on your database settings.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let currentEqubId = null;
        let currentMembers = [];
        let currentEqubData = null;
        let sortableInstance = null;
        let hasChanges = false;
        
        // Function to calculate payout month based on position and EQUB start date
        function calculatePayoutMonth(position) {
            if (!currentEqubData || !currentEqubData.start_date) {
                return `Month ${position}`;
            }
            
            const startDate = new Date(currentEqubData.start_date);
            const payoutDay = currentEqubData.payout_day || 5; // Default to 5th
            
            // Calculate payout date: start_date + (position - 1) months
            const payoutDate = new Date(startDate);
            payoutDate.setMonth(startDate.getMonth() + (position - 1));
            payoutDate.setDate(payoutDay);
            
            const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", 
                              "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            
            return `${monthNames[payoutDate.getMonth()]} ${payoutDate.getDate()}, ${payoutDate.getFullYear()}`;
        }
        
        function loadPositions(equbId) {
            if (!equbId) {
                document.getElementById('positionsContent').innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-magic fa-3x mb-3"></i>
                        <h5>Select an EQUB term to start managing positions</h5>
                        <p>The new system will automatically handle everything based on your database settings.</p>
                    </div>
                `;
                document.getElementById('equbStats').style.display = 'none';
                return;
            }

            currentEqubId = equbId;
            
            fetch('api/payout-positions-new.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=get_positions&equb_id=${equbId}`
            })
            .then(response => response.json())
            .then(data => {
                console.log('🆕 NEW API Response:', data);
                if (data.success && data.data) {
                    const positions = data.data.positions || [];
                    currentMembers = data.data.members || [];
                    currentEqubData = data.data.equb || {}; // Store EQUB data for payout month calculations
                    
                    displayPositions(positions);
                    updateStats(data.data.stats || {});
                    document.getElementById('equbStats').style.display = 'block';
                } else {
                    alert('Error loading positions: ' + (data.message || 'Unknown error'));
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
            
            if (!positions || !Array.isArray(positions) || positions.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h5>No positions found</h5>
                        <p>Add members to this EQUB term to manage their payout positions.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5><i class="fas fa-list-ol text-primary me-2"></i>Payout Positions (${positions.length} positions)</h5>
                    <div class="text-muted small">
                        <i class="fas fa-rocket me-1"></i>NEW Automated System
                    </div>
                </div>
                <div id="sortableList">
                    ${positions.map((position, index) => createPositionCard(position)).join('')}
                </div>
            `;

            // Initialize sortable
            if (sortableInstance) {
                sortableInstance.destroy();
            }
            
            sortableInstance = Sortable.create(document.getElementById('sortableList'), {
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function(evt) {
                    console.log('🔄 Position changed - updating...');
                    markAsChanged();
                }
            });
        }

        function createPositionCard(positionData) {
            const members = positionData.members || [];
            const position = positionData.position;
            const isShared = members.length > 1;
            
            // Calculate payout month for this position
            const payoutMonth = calculatePayoutMonth(position);
            
            let memberDisplay = '';
            
            if (isShared) {
                // Multiple members sharing position
                memberDisplay = `
                    <div class="member-name">
                        <i class="fas fa-users me-2 text-primary"></i>Position ${position}
                        <span class="joint-badge">Shared</span>
                    </div>
                    <div class="member-details">
                        ${members.map(member => `
                            <div class="d-flex justify-content-between align-items-center mb-1" data-member-id="${member.id}">
                                <span><i class="fas fa-user me-1"></i>${member.first_name} ${member.last_name}</span>
                                <div>
                                    <span class="badge bg-secondary me-1">${parseFloat(member.calculated_coefficient || 0).toFixed(1)}</span>
                                    <span class="text-muted">£${parseFloat(member.display_payout || 0).toFixed(2)}</span>
                                </div>
                            </div>
                        `).join('')}
                        <div class="mt-2">
                            <i class="fas fa-calendar me-1 text-warning"></i>
                            <span class="badge bg-warning text-dark">Payout: ${payoutMonth}</span>
                        </div>
                    </div>
                `;
            } else {
                // Single member
                const member = members[0] || {};
                memberDisplay = `
                    <div class="member-name" data-member-id="${member.id}">
                        ${member.first_name || 'Unknown'} ${member.last_name || ''}
                        <span class="individual-badge">Individual</span>
                    </div>
                    <div class="member-details">
                        <i class="fas fa-id-card me-1"></i>${member.member_id || 'N/A'}
                        <span class="ms-3">
                            <i class="fas fa-pound-sign me-1"></i>
                            £${parseFloat(member.effective_payment || 0).toFixed(2)}/month
                        </span>
                        <span class="ms-3">
                            <span class="badge bg-info">
                                ${parseFloat(member.calculated_coefficient || 0).toFixed(1)} coefficient
                            </span>
                        </span>
                        <div class="mt-1">
                            <i class="fas fa-calendar me-1 text-warning"></i>
                            <span class="badge bg-warning text-dark">Payout: ${payoutMonth}</span>
                        </div>
                    </div>
                `;
            }
            
            return `
                <div class="position-card" data-position="${position}">
                    <div class="d-flex align-items-center">
                        <div class="position-number">${position}</div>
                        <div class="member-info">
                            ${memberDisplay}
                        </div>
                        <div class="payout-info">
                            <div class="payout-amount">£${positionData.total_payout.toFixed(2)}</div>
                            <div class="small text-muted">Total Payout</div>
                            <div class="small text-warning mt-1">
                                <i class="fas fa-calendar me-1"></i>${payoutMonth}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function updateStats(stats) {
            document.getElementById('statDuration').textContent = (stats.duration_months || 0) + ' months';
            document.getElementById('statMembers').textContent = stats.total_members || 0;
            document.getElementById('statPositions').textContent = stats.total_positions || 0;
            document.getElementById('statPool').textContent = '£' + (stats.total_monthly_pool || 0).toFixed(2);
        }

        function markAsChanged() {
            hasChanges = true;
            document.getElementById('saveBtn').disabled = false;
            document.getElementById('saveBtn').classList.remove('btn-success');
            document.getElementById('saveBtn').classList.add('btn-warning');
        }

        function savePositions() {
            if (!hasChanges) return;
            
            console.log('💾 NEW SAVE SYSTEM - Getting current order...');
            
            // Get current visual order from the DOM
            const cards = document.querySelectorAll('.position-card');
            const updates = [];
            
            cards.forEach((card, index) => {
                const newPosition = index + 1;
                const memberElements = card.querySelectorAll('[data-member-id]');
                
                memberElements.forEach(memberEl => {
                    const memberId = memberEl.getAttribute('data-member-id');
                    if (memberId) {
                        updates.push({
                            member_id: parseInt(memberId),
                            position: newPosition
                        });
                        console.log(`📝 Member ${memberId} → Position ${newPosition}`);
                    }
                });
            });
            
            console.log(`🚀 Sending ${updates.length} updates to NEW API`);
            
            fetch('api/payout-positions-new.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=update_positions&equb_id=${currentEqubId}&positions=${JSON.stringify(updates)}`
            })
            .then(response => response.json())
            .then(data => {
                console.log('📥 NEW Save result:', data);
                if (data.success) {
                    alert('✅ NEW SYSTEM: Positions saved successfully!');
                    hasChanges = false;
                    document.getElementById('saveBtn').disabled = true;
                    document.getElementById('saveBtn').classList.remove('btn-warning');
                    document.getElementById('saveBtn').classList.add('btn-success');
                    loadPositions(currentEqubId); // Reload to verify
                } else {
                    alert('❌ Save failed: ' + data.message);
                }
            })
            .catch(error => {
                console.error('💥 Save error:', error);
                alert('💥 Save failed!');
            });
        }
    </script>
</body>
</html>