<?php
/**
 * HabeshaEqub - Admin Management Page
 * Manage admin accounts, permissions, and access controls
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username() ?? 'Admin';

// Get all admin accounts
try {
    $stmt = $pdo->query("
        SELECT id, username, email, phone, is_active, language_preference, created_at, updated_at
        FROM admins 
        ORDER BY created_at DESC
    ");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching admins: " . $e->getMessage());
    $admins = [];
}

$total_admins = count($admins);
$active_admins = count(array_filter($admins, fn($a) => $a['is_active']));
$inactive_admins = $total_admins - $active_admins;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management - HabeshaEqub Admin</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../Pictures/Icon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../Pictures/Icon/favicon-16x16.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        /* === ADMIN MANAGEMENT PAGE DESIGN === */
        
        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            border: 1px solid var(--border-light);
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.08);
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
            background: linear-gradient(135deg, var(--color-coral) 0%, #D63447 100%);
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

        .page-actions .btn {
            padding: 12px 24px;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            border: none;
            box-shadow: 0 4px 12px rgba(48, 25, 67, 0.15);
        }

        .btn-add-admin {
            background: linear-gradient(135deg, var(--color-coral) 0%, #D63447 100%);
            color: white;
            font-size: 16px;
        }

        .btn-add-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(214, 52, 71, 0.3);
            color: white;
        }

        /* Statistics Dashboard */
        .stats-dashboard {
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
            transition: all 0.3s ease;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(48, 25, 67, 0.12);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .stat-icon.total { background: linear-gradient(135deg, var(--color-teal) 0%, #0F5147 100%); }
        .stat-icon.active { background: linear-gradient(135deg, #10B981 0%, #059669 100%); }
        .stat-icon.inactive { background: linear-gradient(135deg, var(--color-coral) 0%, #DC2626 100%); }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--color-purple);
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* Admin Table */
        .admin-table-container {
            background: white;
            border-radius: 16px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
            overflow: hidden;
        }

        .table-header {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-light);
        }

        .table-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--color-purple);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-table {
            margin: 0;
        }

        .admin-table th {
            background: none;
            border: none;
            padding: 16px 24px;
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-light);
        }

        .admin-table td {
            padding: 20px 24px;
            border: none;
            border-bottom: 1px solid var(--border-light);
            color: var(--text-primary);
            vertical-align: middle;
        }

        .admin-table tbody tr:hover {
            background: var(--color-cream);
        }

        .admin-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Admin Card */
        .admin-card {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .admin-avatar {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--color-teal) 0%, #0F5147 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 18px;
        }

        .admin-info h6 {
            margin: 0 0 4px 0;
            font-weight: 600;
            color: var(--color-purple);
            font-size: 16px;
        }

        .admin-meta {
            font-size: 14px;
            color: var(--text-secondary);
            margin: 0;
        }

        /* Status Badge */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.active {
            background: linear-gradient(135deg, #D1FAE5, #A7F3D0);
            color: #065F46;
        }

        .status-badge.inactive {
            background: linear-gradient(135deg, #FEE2E2, #FECACA);
            color: #991B1B;
        }

        /* Language Badge */
        .language-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            background: var(--color-cream);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .btn-action {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .btn-edit {
            background: linear-gradient(135deg, var(--color-gold) 0%, var(--color-light-gold) 100%);
            color: white;
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(233, 196, 106, 0.4);
            color: white;
        }

        .btn-toggle {
            background: linear-gradient(135deg, var(--color-teal) 0%, #0F5147 100%);
            color: white;
        }

        .btn-toggle:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(19, 102, 92, 0.4);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--color-coral) 0%, #DC2626 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(214, 52, 71, 0.4);
            color: white;
        }

        /* Modal Styles */
        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(48, 25, 67, 0.2);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            border-bottom: 1px solid var(--border-light);
            border-radius: 16px 16px 0 0;
            padding: 20px 24px;
        }

        .modal-title {
            color: var(--color-purple);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .modal-body {
            padding: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: var(--color-purple);
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--color-teal);
            box-shadow: 0 0 0 3px rgba(19, 102, 92, 0.1);
        }

        .form-select {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-select:focus {
            border-color: var(--color-teal);
            box-shadow: 0 0 0 3px rgba(19, 102, 92, 0.1);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
                padding: 24px;
            }

            .page-title-section h1 {
                font-size: 24px;
                justify-content: center;
            }

            .stats-dashboard .row {
                gap: 16px;
            }

            .stat-card {
                padding: 20px;
            }

            .admin-table-container {
                overflow-x: auto;
            }

            .admin-table {
                min-width: 600px;
            }

            .admin-table th,
            .admin-table td {
                padding: 12px 16px;
            }

            .admin-card {
                gap: 12px;
            }

            .admin-avatar {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 4px;
            }

            .btn-action {
                width: 32px;
                height: 32px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <div class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title-section">
                <h1>
                    <div class="page-title-icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    Admin Management
                </h1>
                <p class="page-subtitle">Manage admin accounts, permissions, and access controls</p>
            </div>
            <div class="page-actions">
                <button class="btn btn-add-admin" onclick="openAddAdminModal()">
                    <i class="fas fa-user-plus"></i>
                    Add New Admin
                </button>
            </div>
        </div>

        <!-- Statistics Dashboard -->
        <div class="stats-dashboard">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon total">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo $total_admins; ?></div>
                        <div class="stat-label">Total Admins</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon active">
                                <i class="fas fa-user-check"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo $active_admins; ?></div>
                        <div class="stat-label">Active Admins</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon inactive">
                                <i class="fas fa-user-times"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo $inactive_admins; ?></div>
                        <div class="stat-label">Inactive Admins</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Table -->
        <div class="admin-table-container">
            <div class="table-header">
                <h3 class="table-title">
                    <i class="fas fa-list"></i>
                    Admin Accounts
                </h3>
            </div>
            <div class="table-responsive">
                <table class="table admin-table">
                    <thead>
                        <tr>
                            <th>Admin</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Language</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $admin): ?>
                        <tr>
                            <td>
                                <div class="admin-card">
                                    <div class="admin-avatar">
                                        <?php echo strtoupper(substr($admin['username'], 0, 2)); ?>
                                    </div>
                                    <div class="admin-info">
                                        <h6><?php echo htmlspecialchars($admin['username']); ?></h6>
                                        <p class="admin-meta">ID: <?php echo $admin['id']; ?></p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <?php if ($admin['email']): ?>
                                        <div><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($admin['email']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($admin['phone']): ?>
                                        <div><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($admin['phone']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!$admin['email'] && !$admin['phone']): ?>
                                        <span class="text-muted">No contact info</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $admin['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $admin['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="language-badge">
                                    <?php echo $admin['language_preference'] ? 'አማ' : 'EN'; ?>
                                </span>
                            </td>
                            <td>
                                <div>
                                    <?php echo date('M j, Y', strtotime($admin['created_at'])); ?>
                                    <div class="text-muted" style="font-size: 12px;">
                                        <?php echo date('H:i', strtotime($admin['created_at'])); ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-action btn-edit" 
                                            onclick="editAdmin(<?php echo $admin['id']; ?>)"
                                            title="Edit Admin">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-action btn-toggle" 
                                            onclick="toggleAdminStatus(<?php echo $admin['id']; ?>, <?php echo $admin['is_active'] ? 'false' : 'true'; ?>)"
                                            title="<?php echo $admin['is_active'] ? 'Deactivate' : 'Activate'; ?> Admin">
                                        <i class="fas fa-<?php echo $admin['is_active'] ? 'user-slash' : 'user-check'; ?>"></i>
                                    </button>
                                    <?php if ($admin['id'] != $admin_id): ?>
                                    <button class="btn btn-action btn-danger" 
                                            onclick="deleteAdmin(<?php echo $admin['id']; ?>)"
                                            title="Delete Admin">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($admins)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                    No admin accounts found
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Admin Modal -->
    <div class="modal fade" id="adminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="adminModalTitle">
                        <i class="fas fa-user-plus"></i>
                        Add New Admin
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="adminForm">
                        <input type="hidden" id="adminId" name="admin_id">
                        
                        <div class="form-group">
                            <label class="form-label">Username *</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                        
                        <div class="form-group" id="passwordGroup">
                            <label class="form-label">Password *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Language Preference</label>
                            <select class="form-select" id="languagePreference" name="language_preference">
                                <option value="0">English</option>
                                <option value="1">አማርኛ (Amharic)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="isActive" name="is_active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveAdminBtn" onclick="saveAdmin()">
                        <i class="fas fa-save me-2"></i>
                        Save Admin
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let adminModal;
        let currentAdminId = null;

        document.addEventListener('DOMContentLoaded', function() {
            adminModal = new bootstrap.Modal(document.getElementById('adminModal'));
        });

        function openAddAdminModal() {
            currentAdminId = null;
            document.getElementById('adminModalTitle').innerHTML = '<i class="fas fa-user-plus"></i> Add New Admin';
            document.getElementById('adminForm').reset();
            document.getElementById('adminId').value = '';
            document.getElementById('passwordGroup').style.display = 'block';
            document.getElementById('password').required = true;
            adminModal.show();
        }

        function editAdmin(adminId) {
            currentAdminId = adminId;
            document.getElementById('adminModalTitle').innerHTML = '<i class="fas fa-user-edit"></i> Edit Admin';
            document.getElementById('passwordGroup').style.display = 'none';
            document.getElementById('password').required = false;
            
            // Fetch admin data and populate form
            fetch(`api/admin-management.php?action=get&id=${adminId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const admin = data.admin;
                        document.getElementById('adminId').value = admin.id;
                        document.getElementById('username').value = admin.username;
                        document.getElementById('email').value = admin.email || '';
                        document.getElementById('phone').value = admin.phone || '';
                        document.getElementById('languagePreference').value = admin.language_preference;
                        document.getElementById('isActive').value = admin.is_active;
                        adminModal.show();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load admin data');
                });
        }

        function saveAdmin() {
            const form = document.getElementById('adminForm');
            const formData = new FormData(form);
            
            const action = currentAdminId ? 'update' : 'create';
            formData.append('action', action);
            
            const saveBtn = document.getElementById('saveAdminBtn');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';

            fetch('api/admin-management.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    adminModal.hide();
                    location.reload(); // Refresh page to show changes
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to save admin');
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save me-2"></i>Save Admin';
            });
        }

        function toggleAdminStatus(adminId, newStatus) {
            const action = newStatus === 'true' ? 'activate' : 'deactivate';
            const confirmMessage = `Are you sure you want to ${action} this admin?`;
            
            if (confirm(confirmMessage)) {
                const formData = new FormData();
                formData.append('action', 'toggle_status');
                formData.append('admin_id', adminId);
                formData.append('is_active', newStatus);

                fetch('api/admin-management.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to update admin status');
                });
            }
        }

        function deleteAdmin(adminId) {
            if (confirm('Are you sure you want to delete this admin? This action cannot be undone.')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('admin_id', adminId);

                fetch('api/admin-management.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete admin');
                });
            }
        }
    </script>
</body>
</html> 