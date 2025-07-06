<?php
// admin_user_roles.php — Manage user roles
session_start();
require __DIR__ . '/includes/db.php';

// Check if user is logged in and is admin
if (empty($_SESSION['student_id']) || $_SESSION['student_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Handle role change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
    $student_id = $_POST['student_id'] ?? 0;
    $new_role = $_POST['new_role'] ?? '';
    
    if ($student_id && in_array($new_role, ['student', 'admin'])) {
        try {
            // Get current student info
            $stmt = $pdo->prepare("SELECT full_name, role FROM students WHERE id = :id");
            $stmt->execute([':id' => $student_id]);
            $student = $stmt->fetch();
            
            if ($student) {
                // Update role
                $stmt = $pdo->prepare("UPDATE students SET role = :role WHERE id = :id");
                $stmt->execute([':role' => $new_role, ':id' => $student_id]);
                
                $message = "Successfully changed {$student['full_name']}'s role from {$student['role']} to {$new_role}.";
                
                // Update session if changing own role
                if ($student_id == $_SESSION['student_id']) {
                    $_SESSION['student_role'] = $new_role;
                }
            } else {
                $error = 'Student not found.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error = 'Invalid role or student ID.';
    }
}

// Get all users with statistics
try {
    $stmt = $pdo->query("SELECT id, full_name, email, username, role, created_at FROM students ORDER BY role DESC, full_name ASC");
    $users = $stmt->fetchAll();
    
    // Get user statistics
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM students");
    $totalUsers = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as total_admins FROM students WHERE role = 'admin'");
    $totalAdmins = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as total_students FROM students WHERE role = 'student'");
    $totalStudents = $stmt->fetchColumn();
    
    // Get recent registrations
    $stmt = $pdo->query("SELECT COUNT(*) as recent_registrations FROM students WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $recentRegistrations = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Role Management - Admin Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/icons.css">
</head>
<body>
    <?php require __DIR__ . '/includes/navigation.php'; ?>

    <main class="main">
        <div class="container">
            <div class="page-header">
                <h1>
            
                        <span>User Role Management</span>
                  
                </h1>
                <p class="page-description">Manage user permissions and administrative privileges across the portal</p>
                
                <div class="breadcrumb">
                    <a href="admin_dashboard.php" class="breadcrumb-link">
                        <div class="icon icon-dashboard"></div>
                        <span>Dashboard</span>
                    </a>
                    <div class="breadcrumb-separator">/</div>
                    <span class="breadcrumb-current">User Roles</span>
                </div>
            </div>

            <!-- User Statistics -->
            <section class="statistics-section">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="icon icon-users stat-icon text-primary"></div>
                            <div class="stat-info">
                                <h3>Total Users</h3>
                                <p class="stat-number"><?= number_format($totalUsers) ?></p>
                            </div>
                        </div>
                        <div class="stat-footer">
                            <small>Registered accounts</small>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="icon icon-admin stat-icon text-error"></div>
                            <div class="stat-info">
                                <h3>Administrators</h3>
                                <p class="stat-number"><?= number_format($totalAdmins) ?></p>
                            </div>
                        </div>
                        <div class="stat-footer">
                            <small>Admin privileges</small>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="icon icon-graduation stat-icon text-success"></div>
                            <div class="stat-info">
                                <h3>Students</h3>
                                <p class="stat-number"><?= number_format($totalStudents) ?></p>
                            </div>
                        </div>
                        <div class="stat-footer">
                            <small>Student accounts</small>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="icon icon-calendar stat-icon text-info"></div>
                            <div class="stat-info">
                                <h3>Recent Registrations</h3>
                                <p class="stat-number"><?= number_format($recentRegistrations) ?></p>
                            </div>
                        </div>
                        <div class="stat-footer">
                            <small>Last 30 days</small>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Users Management -->
            <section class="users-section">
                <div class="section-header">
                    <h2>
                        <div class="icon icon-users icon-text">
                            <span>User Management</span>
                        </div>
                    </h2>
                    <div class="section-actions">
                        <div class="search-container">
                            <div class="icon icon-search"></div>
                            <input type="text" id="userSearch" placeholder="Search users..." class="search-input">
                        </div>
                        <select id="roleFilter" class="filter-select">
                            <option value="">All Roles</option>
                            <option value="admin">Administrators</option>
                            <option value="student">Students</option>
                        </select>
                    </div>
                </div>
                
                <div class="users-grid" id="usersGrid">
                    <?php foreach ($users as $user): ?>
                        <div class="user-card" data-role="<?= $user['role'] ?>">
                            <div class="user-header">
                                <div class="user-avatar">
                                    <div class="icon icon-profile"></div>
                                </div>
                                <div class="user-info">
                                    <h3 class="user-name"><?= htmlspecialchars($user['full_name']) ?></h3>
                                    <p class="user-username">@<?= htmlspecialchars($user['username']) ?></p>
                                </div>
                                <div class="role-badge role-<?= $user['role'] ?>">
                                    <div class="icon icon-<?= $user['role'] === 'admin' ? 'admin' : 'graduation' ?>"></div>
                                    <span><?= ucfirst($user['role']) ?></span>
                                </div>
                            </div>
                            
                            <div class="user-details">
                                <div class="detail-item">
                                    <div class="icon icon-email"></div>
                                    <span><?= htmlspecialchars($user['email']) ?></span>
                                </div>
                                <div class="detail-item">
                                    <div class="icon icon-calendar"></div>
                                    <span>Joined <?= date('M j, Y', strtotime($user['created_at'])) ?></span>
                                </div>
                            </div>
                            
                            <div class="user-actions">
                                <?php if ($user['id'] != $_SESSION['student_id']): ?>
                                    <form method="post" class="role-change-form">
                                        <input type="hidden" name="student_id" value="<?= $user['id'] ?>">
                                        <input type="hidden" name="change_role" value="1">
                                        
                                        <?php if ($user['role'] === 'student'): ?>
                                            <input type="hidden" name="new_role" value="admin">
                                            <button type="button" class="btn btn-promote" 
                                                    onclick="confirmRoleChange(this, 'promote', '<?= htmlspecialchars($user['full_name']) ?>')">
                                                <div class="icon icon-admin btn-icon"></div>
                                                <span>Promote to Admin</span>
                                            </button>
                                        <?php else: ?>
                                            <input type="hidden" name="new_role" value="student">
                                            <button type="button" class="btn btn-demote" 
                                                    onclick="confirmRoleChange(this, 'demote', '<?= htmlspecialchars($user['full_name']) ?>')">
                                                <div class="icon icon-graduation btn-icon"></div>
                                                <span>Demote to Student</span>
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                <?php else: ?>
                                    <div class="current-user-badge">
                                        <div class="icon icon-check"></div>
                                        <span>Current User</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Role Information -->
            <section class="role-info-section">
                <div class="section-header">
                    <h2>
                        <div class="icon icon-info icon-text">
                            <span>Role Information</span>
                        </div>
                    </h2>
                </div>
                
                <div class="role-cards">
                    <div class="role-card">
                        <div class="role-header">
                            <div class="icon icon-graduation role-icon text-success"></div>
                            <h3>Student Role</h3>
                        </div>
                        <div class="role-description">
                            <p>Standard user privileges for students accessing the portal</p>
                        </div>
                        <div class="role-permissions">
                            <h4>Permissions Include:</h4>
                            <ul>
                                <li>
                                    <div class="icon icon-check text-success"></div>
                                    <span>Access personal profile and settings</span>
                                </li>
                                <li>
                                    <div class="icon icon-check text-success"></div>
                                    <span>Update personal information</span>
                                </li>
                                <li>
                                    <div class="icon icon-check text-success"></div>
                                    <span>View and enroll in courses</span>
                                </li>
                                <li>
                                    <div class="icon icon-check text-success"></div>
                                    <span>Access student dashboard</span>
                                </li>
                                <li>
                                    <div class="icon icon-check text-success"></div>
                                    <span>Upload profile pictures</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="role-card">
                        <div class="role-header">
                            <div class="icon icon-admin role-icon text-error"></div>
                            <h3>Administrator Role</h3>
                        </div>
                        <div class="role-description">
                            <p>Full administrative privileges with system management capabilities</p>
                        </div>
                        <div class="role-permissions">
                            <h4>Additional Permissions:</h4>
                            <ul>
                                <li>
                                    <div class="icon icon-check text-success"></div>
                                    <span>All student permissions</span>
                                </li>
                                <li>
                                    <div class="icon icon-check text-success"></div>
                                    <span>Access admin dashboard</span>
                                </li>
                                <li>
                                    <div class="icon icon-check text-success"></div>
                                    <span>Manage all users and roles</span>
                                </li>
                                <li>
                                    <div class="icon icon-check text-success"></div>
                                    <span>Add, edit, and delete courses</span>
                                </li>
                                <li>
                                    <div class="icon icon-check text-success"></div>
                                    <span>View system statistics</span>
                                </li>
                                <li>
                                    <div class="icon icon-check text-success"></div>
                                    <span>Generate reports and analytics</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Student Portal. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/app.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show messages as toasts
            <?php if ($message): ?>
                toast.success('<?= addslashes($message) ?>');
            <?php endif; ?>
            
            <?php if ($error): ?>
                toast.error('<?= addslashes($error) ?>');
            <?php endif; ?>
            
            // User search and filter functionality
            const searchInput = document.getElementById('userSearch');
            const roleFilter = document.getElementById('roleFilter');
            const userCards = document.querySelectorAll('.user-card');
            
            function filterUsers() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedRole = roleFilter.value;
                
                userCards.forEach(card => {
                    const cardText = card.textContent.toLowerCase();
                    const cardRole = card.getAttribute('data-role');
                    
                    const matchesSearch = cardText.includes(searchTerm);
                    const matchesRole = !selectedRole || cardRole === selectedRole;
                    
                    card.style.display = matchesSearch && matchesRole ? 'block' : 'none';
                });
            }
            
            searchInput.addEventListener('input', filterUsers);
            roleFilter.addEventListener('change', filterUsers);
        });
        
        // Confirm role change function
        async function confirmRoleChange(button, action, userName) {
            const actionText = action === 'promote' ? 'promote' : 'demote';
            const newRole = action === 'promote' ? 'administrator' : 'student';
            
            const confirmed = await modal.confirm(
                `Are you sure you want to ${actionText} ${userName} to ${newRole}? This will change their access permissions.`,
                `${action === 'promote' ? 'Promote' : 'Demote'} User`
            );
            
            if (confirmed) {
                // Show loading state
                button.disabled = true;
                button.innerHTML = `
                    <div class="icon icon-spinning btn-icon"></div>
                    <span>Processing...</span>
                `;
                
                // Submit the form
                button.closest('form').submit();
            }
        }
    </script>
</body>
</html>

<style>
/* User Roles Page Styles */
.page-header {
    text-align: center;
    margin-bottom: var(--spacing-8);
}

.page-header h1 {
    font-size: var(--font-size-3xl);
    margin-bottom: var(--spacing-2);
    color: var(--gray-900);
}

.page-description {
    font-size: var(--font-size-lg);
    color: var(--gray-600);
    margin: 0 0 var(--spacing-4) 0;
}

.breadcrumb {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-2);
    margin-top: var(--spacing-4);
}

.breadcrumb-link {
    display: flex;
    align-items: center;
    gap: var(--spacing-1);
    color: var(--primary-color);
    text-decoration: none;
    font-size: var(--font-size-sm);
}

.breadcrumb-link:hover {
    color: var(--primary-dark);
}

.breadcrumb-separator {
    color: var(--gray-400);
}

.breadcrumb-current {
    color: var(--gray-600);
    font-size: var(--font-size-sm);
}

.statistics-section {
    margin-bottom: var(--spacing-8);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-4);
}

.stat-card {
    background: var(--white);
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg);
    padding: var(--spacing-6);
    box-shadow: var(--shadow-sm);
    transition: all var(--transition-normal);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.stat-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    margin-bottom: var(--spacing-2);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gray-100);
}

.stat-info h3 {
    margin: 0;
    font-size: var(--font-size-sm);
    font-weight: 600;
    color: var(--gray-700);
}

.stat-number {
    font-size: var(--font-size-2xl);
    font-weight: 700;
    color: var(--gray-900);
    margin: 0;
}

.stat-footer {
    color: var(--gray-500);
    font-size: var(--font-size-xs);
}

.users-section {
    margin-bottom: var(--spacing-8);
}

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: var(--spacing-6);
}

.section-header h2 {
    margin: 0;
    font-size: var(--font-size-xl);
    color: var(--gray-900);
}

.section-actions {
    display: flex;
    gap: var(--spacing-3);
    align-items: center;
}

.search-container {
    position: relative;
    display: flex;
    align-items: center;
}

.search-container .icon {
    position: absolute;
    left: 12px;
    color: var(--gray-500);
    z-index: 1;
}

.search-input {
    padding: var(--spacing-2) var(--spacing-2) var(--spacing-2) 40px;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    min-width: 250px;
}

.filter-select {
    padding: var(--spacing-2) var(--spacing-3);
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    background: var(--white);
}

.users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: var(--spacing-6);
}

.user-card {
    background: var(--white);
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg);
    padding: var(--spacing-6);
    box-shadow: var(--shadow-sm);
    transition: all var(--transition-normal);
}

.user-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.user-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    margin-bottom: var(--spacing-4);
}

.user-avatar {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-full);
    background: var(--gray-100);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.user-info {
    flex: 1;
}

.user-name {
    margin: 0;
    font-size: var(--font-size-base);
    font-weight: 600;
    color: var(--gray-900);
}

.user-username {
    margin: 0;
    font-size: var(--font-size-sm);
    color: var(--gray-600);
}

.role-badge {
    display: flex;
    align-items: center;
    gap: var(--spacing-1);
    padding: var(--spacing-1) var(--spacing-2);
    border-radius: var(--radius-sm);
    font-size: var(--font-size-xs);
    font-weight: 500;
    flex-shrink: 0;
}

.role-badge.role-admin {
    background: var(--error-light);
    color: var(--error-dark);
}

.role-badge.role-student {
    background: var(--success-light);
    color: var(--success-dark);
}

.role-badge .icon {
    width: 12px;
    height: 12px;
}

.user-details {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
    margin-bottom: var(--spacing-4);
}

.detail-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-sm);
    color: var(--gray-600);
}

.detail-item .icon {
    width: 16px;
    height: 16px;
    color: var(--gray-400);
}

.user-actions {
    display: flex;
    justify-content: center;
}

.role-change-form {
    width: 100%;
}

.btn-promote {
    background: var(--success-color);
    color: var(--white);
    border: 1px solid var(--success-color);
    width: 100%;
    padding: var(--spacing-3);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    font-weight: 500;
    cursor: pointer;
    transition: all var(--transition-normal);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-2);
}

.btn-promote:hover {
    background: var(--success-dark);
    border-color: var(--success-dark);
}

.btn-demote {
    background: var(--warning-color);
    color: var(--white);
    border: 1px solid var(--warning-color);
    width: 100%;
    padding: var(--spacing-3);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    font-weight: 500;
    cursor: pointer;
    transition: all var(--transition-normal);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-2);
}

.btn-demote:hover {
    background: var(--warning-dark);
    border-color: var(--warning-dark);
}

.current-user-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-2);
    padding: var(--spacing-3);
    background: var(--gray-100);
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    font-weight: 500;
    color: var(--gray-700);
}

.role-info-section {
    margin-bottom: var(--spacing-8);
}

.role-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: var(--spacing-6);
}

.role-card {
    background: var(--white);
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg);
    padding: var(--spacing-6);
    box-shadow: var(--shadow-sm);
}

.role-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    margin-bottom: var(--spacing-4);
}

.role-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gray-100);
}

.role-header h3 {
    margin: 0;
    font-size: var(--font-size-lg);
    color: var(--gray-900);
}

.role-description {
    margin-bottom: var(--spacing-4);
}

.role-description p {
    margin: 0;
    color: var(--gray-600);
}

.role-permissions h4 {
    margin: 0 0 var(--spacing-3) 0;
    font-size: var(--font-size-base);
    color: var(--gray-900);
}

.role-permissions ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.role-permissions li {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    margin-bottom: var(--spacing-2);
    font-size: var(--font-size-sm);
    color: var(--gray-700);
}

.role-permissions li .icon {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-3);
    }
    
    .section-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .search-input {
        min-width: 100%;
    }
    
    .users-grid {
        grid-template-columns: 1fr;
    }
    
    .role-cards {
        grid-template-columns: 1fr;
    }
    
    .user-header {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-2);
    }
    
    .role-badge {
        align-self: flex-start;
    }
}
</style> 