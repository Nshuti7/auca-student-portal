<?php
// includes/navigation.php — Unified navigation component for role-based navigation

// Determine current page for active state
$current_page = basename($_SERVER['PHP_SELF']);

// Check if user is logged in and get their role
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $_SESSION['role'] ?? 'student';
$user_info = null;

if ($is_logged_in) {
    try {
        $stmt = $pdo->prepare('SELECT full_name, role, profile_picture FROM students WHERE id = :id');
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $user_info = $stmt->fetch();
    } catch (PDOException $e) {
        // Silent fail - user info will be null
    }
}
?>

<header class="header">
    <div class="container">
        <div class="header-content">
            <div class="logo-section">
                <img src="assets/images/logo.png" alt="Student Portal Logo" class="logo">
                <div class="logo-text">
                    <h1>AUCA Portal</h1>
                    <span class="portal-tagline">
                        <?php if ($is_logged_in): ?>
                            <?= $user_role === 'admin' ? 'Admin Dashboard' : 'Student Portal' ?>
                        <?php else: ?>
                            Adventist University of Central Africa
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            
            <nav class="nav">
                <ul class="nav-list">
                    <!-- Home - Always visible -->
                    <li class="nav-item">
                        <a href="index.php" class="nav-link <?= $current_page === 'index.php' ? 'active' : '' ?>">
                            <div class="icon icon-home nav-icon"></div>
                            <span>Home</span>
                        </a>
                    </li>

                    <!-- Course Catalog - Always visible -->
                    <li class="nav-item">
                        <a href="courses.php" class="nav-link <?= $current_page === 'courses.php' ? 'active' : '' ?>">
                            <div class="icon icon-courses nav-icon"></div>
                            <span>Courses</span>
                        </a>
                    </li>

                    <?php if ($is_logged_in): ?>
                        <?php if ($user_role === 'student'): ?>
                            <!-- Student Navigation -->
                            <li class="nav-item">
                                <a href="student_dashboard.php" class="nav-link <?= $current_page === 'student_dashboard.php' ? 'active' : '' ?>">
                                    <div class="icon icon-dashboard nav-icon"></div>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="student_courses.php" class="nav-link <?= $current_page === 'student_courses.php' ? 'active' : '' ?>">
                                    <div class="icon icon-graduation nav-icon"></div>
                                    <span>My Courses</span>
                                </a>
                            </li>
                        <?php elseif ($user_role === 'admin'): ?>
                            <!-- Admin Navigation -->
                            <li class="nav-item">
                                <a href="admin_dashboard.php" class="nav-link <?= $current_page === 'admin_dashboard.php' ? 'active' : '' ?>">
                                    <div class="icon icon-dashboard nav-icon"></div>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="admin_courses.php" class="nav-link <?= $current_page === 'admin_courses.php' ? 'active' : '' ?>">
                                    <div class="icon icon-settings nav-icon"></div>
                                    <span>Manage Courses</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="admin_user_roles.php" class="nav-link <?= $current_page === 'admin_user_roles.php' ? 'active' : '' ?>">
                                    <div class="icon icon-admin nav-icon"></div>
                                    <span>User Roles</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Profile - Always visible for logged in users -->
                        <li class="nav-item">
                            <a href="profile.php" class="nav-link <?= $current_page === 'profile.php' ? 'active' : '' ?>">
                                <div class="icon icon-profile nav-icon"></div>
                                <span>Profile</span>
                            </a>
                        </li>

                        <!-- Logout - Always visible for logged in users -->
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link">
                                <div class="icon icon-logout nav-icon"></div>
                                <span>Logout</span>
                            </a>
                        </li>
                    <?php else: ?>
                        <!-- Guest Navigation -->
                        <li class="nav-item">
                            <a href="signup.php" class="nav-link btn btn-primary btn-sm <?= $current_page === 'signup.php' ? 'active' : '' ?>">
                                <div class="icon icon-user-plus btn-icon"></div>
                                <span>Sign Up</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="login.php" class="nav-link btn btn-secondary btn-sm <?= $current_page === 'login.php' ? 'active' : '' ?>">
                                <div class="icon icon-login btn-icon"></div>
                                <span>Login</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <?php if ($is_logged_in && $user_info): ?>
                <div class="user-info">
                    <?php
                    $profile_picture = $user_info['profile_picture'];
                    if ($profile_picture && file_exists(__DIR__ . '/../assets/images/profiles/' . $profile_picture)) {
                        $picture_url = 'assets/images/profiles/' . htmlspecialchars($profile_picture);
                    } else {
                        $picture_url = 'assets/images/default-avatar.svg';
                    }
                    ?>
                    <img src="<?= $picture_url ?>" alt="Profile" class="profile-pic-small">
                    <div class="user-details">
                        <span class="user-name"><?= htmlspecialchars($user_info['full_name']) ?></span>
                        <span class="user-role"><?= ucfirst($user_info['role']) ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<style>
/* Unified Navigation Styles */
.header {
    background: var(--white);
    border-bottom: 1px solid var(--gray-200);
    box-shadow: var(--shadow-sm);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--spacing-4) 0;
    gap: var(--spacing-6);
}

.logo-section {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    flex-shrink: 0;
}

.logo {
    height: 40px;
    width: auto;
}

.logo-text h1 {
    margin: 0;
    font-size: var(--font-size-lg);
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1.2;
}

.portal-tagline {
    font-size: var(--font-size-xs);
    color: var(--gray-600);
    font-weight: 500;
}

.nav {
    flex: 1;
    display: flex;
    justify-content: center;
}

.nav-list {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: var(--spacing-1);
    align-items: center;
}

.nav-item {
    display: flex;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    padding: var(--spacing-2) var(--spacing-3);
    border-radius: var(--radius-md);
    text-decoration: none;
    color: var(--gray-700);
    font-weight: 500;
    font-size: var(--font-size-sm);
    transition: all var(--transition-normal);
    white-space: nowrap;
}

.nav-link:hover {
    background: var(--gray-100);
    color: var(--gray-900);
    text-decoration: none;
}

.nav-link.active {
    background: var(--primary-light);
    color: var(--primary-dark);
}

.nav-icon {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.btn-icon {
    width: 16px;
    height: 16px;
}

.user-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    flex-shrink: 0;
}

.profile-pic-small {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-full);
    object-fit: cover;
    border: 2px solid var(--gray-200);
}

.user-details {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 1px;
}

.user-name {
    font-size: var(--font-size-sm);
    font-weight: 600;
    color: var(--gray-900);
    line-height: 1.2;
}

.user-role {
    font-size: var(--font-size-xs);
    color: var(--gray-600);
    text-transform: uppercase;
    font-weight: 500;
    letter-spacing: 0.5px;
}

/* Button styles for navigation buttons */
.nav-link.btn {
    border: 1px solid transparent;
}

.nav-link.btn-primary {
    background: var(--primary-color);
    color: var(--white);
    border-color: var(--primary-color);
}

.nav-link.btn-primary:hover {
    background: var(--primary-dark);
    border-color: var(--primary-dark);
    color: var(--white);
}

.nav-link.btn-secondary {
    background: var(--gray-600);
    color: var(--white);
    border-color: var(--gray-600);
}

.nav-link.btn-secondary:hover {
    background: var(--gray-700);
    border-color: var(--gray-700);
    color: var(--white);
}

.btn-sm {
    padding: var(--spacing-2) var(--spacing-3);
    font-size: var(--font-size-xs);
}

/* Mobile Navigation */
@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        gap: var(--spacing-3);
        padding: var(--spacing-3) 0;
    }
    
    .nav {
        width: 100%;
        justify-content: center;
    }
    
    .nav-list {
        flex-wrap: wrap;
        justify-content: center;
        gap: var(--spacing-1);
    }
    
    .nav-link {
        padding: var(--spacing-2);
        font-size: var(--font-size-xs);
    }
    
    .nav-link span {
        display: none;
    }
    
    .nav-icon, .btn-icon {
        width: 20px;
        height: 20px;
    }
    
    .user-info {
        justify-content: center;
    }
    
    .user-details {
        align-items: center;
    }
}

@media (max-width: 480px) {
    .logo-text h1 {
        font-size: var(--font-size-base);
    }
    
    .portal-tagline {
        display: none;
    }
    
    .nav-list {
        gap: 2px;
    }
    
    .nav-link {
        padding: var(--spacing-1);
    }
}
</style> 