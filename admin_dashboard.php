<?php
// admin_dashboard.php — Modern Admin Dashboard
session_start();
require __DIR__ . '/includes/db.php';

// Check if user is logged in and is admin
if (empty($_SESSION['student_id']) || $_SESSION['student_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Get statistics
try {
    // Total students
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM students WHERE role = 'student'");
    $totalStudents = $stmt->fetch()['total'];
    
    // Total admins
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM students WHERE role = 'admin'");
    $totalAdmins = $stmt->fetch()['total'];
    
    // Total courses
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM courses");
    $totalCourses = $stmt->fetch()['total'];
    
    // Recent registrations (last 30 days)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM students WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $recentRegistrations = $stmt->fetch()['total'];
    
    // Get recent activities
    $stmt = $pdo->query("SELECT s.full_name, s.email, s.created_at FROM students s ORDER BY s.created_at DESC LIMIT 5");
    $recentActivities = $stmt->fetchAll();
    
    // Get all students for management
    $stmt = $pdo->query("SELECT id, full_name, email, username, role, created_at, profile_picture FROM students ORDER BY created_at DESC");
    $allStudents = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Get current admin info
try {
    $stmt = $pdo->prepare('SELECT * FROM students WHERE id = :id');
    $stmt->execute([':id' => $_SESSION['student_id']]);
    $admin = $stmt->fetch();
} catch (PDOException $e) {
    $admin = null;
}

// Get the current hour to determine greeting
$hour = date('G');
$greeting = '';
if ($hour < 12) {
    $greeting = 'Good morning';
} elseif ($hour < 17) {
    $greeting = 'Good afternoon';
} else {
    $greeting = 'Good evening';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AUCA Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/icons.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require __DIR__ . '/includes/navigation.php'; ?>

    <main class="dashboard-main">
        <div class="dashboard-container">
            <!-- Admin Header -->
            <header class="dashboard-header">
                <div class="welcome-section">
                    <div class="welcome-text">
                        <h1 class="dashboard-title">
                            <?= $greeting ?>, <?= $admin ? htmlspecialchars(explode(' ', $admin['full_name'])[0]) : 'Administrator' ?>! 👋
                        </h1>
                        <p class="dashboard-subtitle">
                            Welcome to your admin dashboard. Manage students, courses, and system settings.
                        </p>
                    </div>
                    <div class="dashboard-actions">
                        <a href="signup.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i>
                            Add Student
                        </a>
                        <a href="admin_courses.php" class="btn btn-secondary">
                            <i class="fas fa-book"></i>
                            Manage Courses
                        </a>
                    </div>
                </div>
            </header>

            <!-- Statistics Cards -->
            <section class="stats-section">
                <div class="stats-grid">
                    <div class="stat-card students">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?= $totalStudents ?></h3>
                            <p class="stat-label">Total Students</p>
                            <span class="stat-badge">Active Users</span>
                        </div>
                    </div>
                    
                    <div class="stat-card admins">
                        <div class="stat-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?= $totalAdmins ?></h3>
                            <p class="stat-label">Total Admins</p>
                            <span class="stat-badge">System Administrators</span>
                        </div>
                    </div>
                    
                    <div class="stat-card courses">
                        <div class="stat-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?= $totalCourses ?></h3>
                            <p class="stat-label">Total Courses</p>
                            <span class="stat-badge">Available Courses</span>
                        </div>
                    </div>
                    
                    <div class="stat-card recent">
                        <div class="stat-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?= $recentRegistrations ?></h3>
                            <p class="stat-label">New Registrations</p>
                            <span class="stat-badge">Last 30 Days</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main Content Grid -->
            <div class="dashboard-content">
                <div class="content-left">
                    <!-- Student Management -->
                    <div class="dashboard-card students-card">
                        <div class="card-header">
                            <h2>
                                <i class="fas fa-users"></i>
                                Student Management
                            </h2>
                            <div class="header-actions">
                                <button class="btn btn-sm btn-secondary" onclick="exportStudents()">
                                    <i class="fas fa-download"></i>
                                    Export
                                </button>
                            </div>
                        </div>
                        <div class="card-content">
                            <!-- Table Filters -->
                            <div class="table-filters">
                                <div class="filter-group">
                                    <label for="roleFilter">Role:</label>
                                    <select id="roleFilter" onchange="filterStudents()">
                                        <option value="">All Roles</option>
                                        <option value="student">Students</option>
                                        <option value="admin">Admins</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="searchFilter">Search:</label>
                                    <input type="text" id="searchFilter" placeholder="Search by name or email" onkeyup="filterStudents()">
                                </div>
                            </div>
                            
                            <!-- Students Table -->
                            <div class="table-wrapper">
                                <table class="students-table">
                                    <thead>
                                        <tr>
                                            <th>Profile</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Joined</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="studentsTableBody">
                                        <?php foreach ($allStudents as $student): ?>
                                        <tr data-role="<?= $student['role'] ?>" data-name="<?= htmlspecialchars($student['full_name']) ?>" data-email="<?= htmlspecialchars($student['email']) ?>">
                                            <td>
                                                <div class="user-profile">
                                                    <?php
                                                    $profile_picture = $student['profile_picture'];
                                                    if ($profile_picture && file_exists(__DIR__ . '/assets/images/profiles/' . $profile_picture)) {
                                                        $picture_url = 'assets/images/profiles/' . htmlspecialchars($profile_picture);
                                                    } else {
                                                        $picture_url = 'assets/images/default-avatar.svg';
                                                    }
                                                    ?>
                                                    <img src="<?= $picture_url ?>" alt="Profile" class="profile-avatar">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="user-info">
                                                    <div class="user-name"><?= htmlspecialchars($student['full_name']) ?></div>
                                                    <div class="user-username">@<?= htmlspecialchars($student['username']) ?></div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($student['email']) ?></td>
                                            <td>
                                                <span class="role-badge <?= $student['role'] ?>">
                                                    <i class="fas fa-<?= $student['role'] === 'admin' ? 'user-shield' : 'user' ?>"></i>
                                                    <?= ucfirst(htmlspecialchars($student['role'])) ?>
                                                </span>
                                            </td>
                                            <td><?= date('M j, Y', strtotime($student['created_at'])) ?></td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="admin_edit_student.php?id=<?= $student['id'] ?>" class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($student['id'] != $_SESSION['student_id']): ?>
                                                        <button class="btn btn-sm btn-danger" onclick="confirmDeleteStudent(<?= $student['id'] ?>, '<?= htmlspecialchars($student['full_name']) ?>')" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-right">
                    <!-- Recent Activity -->
                    <div class="dashboard-card activity-card">
                        <div class="card-header">
                            <h2>
                                <i class="fas fa-clock"></i>
                                Recent Activity
                            </h2>
                        </div>
                        <div class="card-content">
                            <?php if (empty($recentActivities)): ?>
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <h3>No Recent Activity</h3>
                                    <p>Recent user activities will appear here</p>
                                </div>
                            <?php else: ?>
                                <div class="activity-timeline">
                                    <?php foreach ($recentActivities as $activity): ?>
                                        <div class="activity-item">
                                            <div class="activity-icon">
                                                <i class="fas fa-user-plus"></i>
                                            </div>
                                            <div class="activity-content">
                                                <p class="activity-description">
                                                    <?= htmlspecialchars($activity['full_name']) ?> joined the platform
                                                </p>
                                                <span class="activity-time"><?= date('M j, g:i A', strtotime($activity['created_at'])) ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="dashboard-card actions-card">
                        <div class="card-header">
                            <h2>
                                <i class="fas fa-bolt"></i>
                                Quick Actions
                            </h2>
                        </div>
                        <div class="card-content">
                            <div class="quick-actions">
                                <a href="admin_courses.php" class="action-item">
                                    <div class="action-icon">
                                        <i class="fas fa-book-open"></i>
                                    </div>
                                    <div class="action-content">
                                        <h4>Manage Courses</h4>
                                        <p>Add, edit, and organize courses</p>
                                    </div>
                                </a>
                                <a href="admin_user_roles.php" class="action-item">
                                    <div class="action-icon">
                                        <i class="fas fa-user-shield"></i>
                                    </div>
                                    <div class="action-content">
                                        <h4>User Roles</h4>
                                        <p>Manage user permissions</p>
                                    </div>
                                </a>
                                <a href="signup.php" class="action-item">
                                    <div class="action-icon">
                                        <i class="fas fa-user-plus"></i>
                                    </div>
                                    <div class="action-content">
                                        <h4>Add Student</h4>
                                        <p>Create new user accounts</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- System Overview -->
                    <div class="dashboard-card overview-card">
                        <div class="card-header">
                            <h2>
                                <i class="fas fa-chart-pie"></i>
                                System Overview
                            </h2>
                        </div>
                        <div class="card-content">
                            <div class="overview-stats">
                                <div class="overview-item">
                                    <div class="overview-label">Total Users</div>
                                    <div class="overview-value"><?= $totalStudents + $totalAdmins ?></div>
                                </div>
                                <div class="overview-item">
                                    <div class="overview-label">Active Courses</div>
                                    <div class="overview-value"><?= $totalCourses ?></div>
                                </div>
                                <div class="overview-item">
                                    <div class="overview-label">New This Month</div>
                                    <div class="overview-value"><?= $recentRegistrations ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> AUCA Portal. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/app.js"></script>
    <script>
        // Admin dashboard JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Handle success/error messages
            const urlParams = new URLSearchParams(window.location.search);
            
            if (urlParams.has('success')) {
                const successType = urlParams.get('success');
                const name = urlParams.get('name');
                
                switch(successType) {
                    case 'student_deleted':
                        showAlert(`Successfully deleted ${name || 'student'}.`, 'success');
                        break;
                    case 'student_added':
                        showAlert(`Successfully added ${name || 'student'}.`, 'success');
                        break;
                    case 'student_updated':
                        showAlert(`Successfully updated ${name || 'student'}.`, 'success');
                        break;
                    default:
                        showAlert('Operation completed successfully.', 'success');
                }
                
                // Clean URL
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            if (urlParams.has('error')) {
                const errorType = urlParams.get('error');
                let errorMessage = '';
                
                switch(errorType) {
                    case 'cannot_delete_self':
                        errorMessage = 'You cannot delete your own account.';
                        break;
                    case 'student_not_found':
                        errorMessage = 'Student not found.';
                        break;
                    case 'database_error':
                        errorMessage = 'Database error occurred.';
                        break;
                    default:
                        errorMessage = 'An error occurred.';
                }
                
                showAlert(errorMessage, 'error');
                
                // Clean URL
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
        
        // Filter students function
        function filterStudents() {
            const roleFilter = document.getElementById('roleFilter').value;
            const searchFilter = document.getElementById('searchFilter').value.toLowerCase();
            const rows = document.querySelectorAll('#studentsTableBody tr');
            
            rows.forEach(row => {
                const role = row.getAttribute('data-role');
                const name = row.getAttribute('data-name').toLowerCase();
                const email = row.getAttribute('data-email').toLowerCase();
                
                let showRow = true;
                
                // Role filter
                if (roleFilter && role !== roleFilter) {
                    showRow = false;
                }
                
                // Search filter
                if (searchFilter && !name.includes(searchFilter) && !email.includes(searchFilter)) {
                    showRow = false;
                }
                
                row.style.display = showRow ? '' : 'none';
            });
        }
        
        // Confirm delete student function
        function confirmDeleteStudent(studentId, studentName) {
            if (confirm(`Are you sure you want to delete "${studentName}"? This action cannot be undone.`)) {
                // Show loading state
                const btn = event.target.closest('button');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                btn.disabled = true;
                
                // Redirect to delete
                window.location.href = `admin_delete_student.php?id=${studentId}`;
            }
        }
        
        // Export students function
        function exportStudents() {
            showAlert('Export students feature coming soon!', 'info');
        }
        
        // Simple alert function
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                ${message}
            `;
            
            document.querySelector('.dashboard-header').appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    </script>
</body>
</html> 