<?php
// student_dashboard.php — Modern Enhanced Student Dashboard
session_start();
require __DIR__ . '/includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

// Redirect admin to admin dashboard
if (isset($_SESSION['student_role']) && $_SESSION['student_role'] === 'admin') {
    header('Location: admin_dashboard.php');
    exit;
}

// Log dashboard access
try {
    $stmt = $pdo->prepare("
        INSERT INTO student_activity (student_id, activity_type, activity_description)
        VALUES (:student_id, 'dashboard_access', 'Accessed student dashboard')
    ");
    $stmt->execute([':student_id' => $_SESSION['student_id']]);
} catch (PDOException $e) {
    // Silent fail for activity logging
}

// Get student information
try {
    $stmt = $pdo->prepare('SELECT * FROM students WHERE id = :id');
    $stmt->execute([':id' => $_SESSION['student_id']]);
    $student = $stmt->fetch();
    
    if (!$student) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}

// Get dashboard statistics
try {
    // Enrolled courses count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as enrolled_count
        FROM enrollments 
        WHERE student_id = :student_id AND status = 'enrolled'
    ");
    $stmt->execute([':student_id' => $_SESSION['student_id']]);
    $enrolled_count = $stmt->fetchColumn();
    
    // Completed courses count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as completed_count
        FROM enrollments 
        WHERE student_id = :student_id AND status = 'completed'
    ");
    $stmt->execute([':student_id' => $_SESSION['student_id']]);
    $completed_count = $stmt->fetchColumn();
    
    // Total credits enrolled
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(c.credits), 0) as total_credits
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        WHERE e.student_id = :student_id AND e.status = 'enrolled'
    ");
    $stmt->execute([':student_id' => $_SESSION['student_id']]);
    $total_credits = $stmt->fetchColumn();
    
    // Average progress
    $stmt = $pdo->prepare("
        SELECT COALESCE(AVG(progress_percentage), 0) as avg_progress
        FROM student_progress
        WHERE student_id = :student_id
    ");
    $stmt->execute([':student_id' => $_SESSION['student_id']]);
    $avg_progress = $stmt->fetchColumn();
    
    // Get current courses with progress
    $stmt = $pdo->prepare("
        SELECT c.*, e.enrollment_date, e.grade, e.status,
               COALESCE(sp.progress_percentage, 0) as progress,
               COALESCE(sp.last_accessed, e.enrollment_date) as last_accessed,
               COALESCE(sp.study_hours, 0) as study_hours
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        LEFT JOIN student_progress sp ON sp.student_id = e.student_id AND sp.course_id = c.id
        WHERE e.student_id = :student_id AND e.status = 'enrolled'
        ORDER BY e.enrollment_date DESC
        LIMIT 6
    ");
    $stmt->execute([':student_id' => $_SESSION['student_id']]);
    $current_courses = $stmt->fetchAll();
    
    // Get recent activities
    $stmt = $pdo->prepare("
        SELECT sa.*, c.course_name, c.course_code
        FROM student_activity sa
        LEFT JOIN courses c ON sa.related_course_id = c.id
        WHERE sa.student_id = :student_id
        ORDER BY sa.created_at DESC
        LIMIT 8
    ");
    $stmt->execute([':student_id' => $_SESSION['student_id']]);
    $recent_activities = $stmt->fetchAll();
    
    // Get course recommendations (courses not enrolled in)
    $stmt = $pdo->prepare("
        SELECT c.*, COUNT(e.student_id) as enrollment_count
        FROM courses c
        LEFT JOIN enrollments e ON c.id = e.course_id
        WHERE c.id NOT IN (
            SELECT course_id FROM enrollments 
            WHERE student_id = :student_id
        )
        GROUP BY c.id
        ORDER BY enrollment_count DESC, c.course_name
        LIMIT 3
    ");
    $stmt->execute([':student_id' => $_SESSION['student_id']]);
    $recommended_courses = $stmt->fetchAll();
    
    // Profile completion score
    $profile_score = 0;
    $profile_items = [
        'full_name' => !empty($student['full_name']) ? 20 : 0,
        'email' => !empty($student['email']) ? 20 : 0,
        'phone' => !empty($student['phone'] ?? '') ? 15 : 0,
        'address' => !empty($student['address'] ?? '') ? 15 : 0,
        'profile_picture' => !empty($student['profile_picture']) ? 30 : 0
    ];
    $profile_score = array_sum($profile_items);
    
} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}

// Handle quick actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_progress' && isset($_POST['course_id'])) {
        try {
            $course_id = (int)$_POST['course_id'];
            $progress = min(100, max(0, (float)$_POST['progress']));
            
            $stmt = $pdo->prepare("
                INSERT INTO student_progress (student_id, course_id, progress_percentage, last_accessed)
                VALUES (:student_id, :course_id, :progress, NOW())
                ON DUPLICATE KEY UPDATE 
                progress_percentage = :progress2, 
                last_accessed = NOW()
            ");
            $stmt->execute([
                ':student_id' => $_SESSION['student_id'],
                ':course_id' => $course_id,
                ':progress' => $progress,
                ':progress2' => $progress
            ]);
            
            // Log activity
            $stmt = $pdo->prepare("
                INSERT INTO student_activity (student_id, activity_type, activity_description, related_course_id)
                VALUES (:student_id, 'progress_update', 'Updated course progress', :course_id)
            ");
            $stmt->execute([
                ':student_id' => $_SESSION['student_id'],
                ':course_id' => $course_id
            ]);
            
            header('Location: student_dashboard.php?updated=1');
            exit;
        } catch (PDOException $e) {
            $error_message = 'Error updating progress: ' . $e->getMessage();
        }
    }
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
    <title>Student Dashboard - AUCA Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/icons.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require __DIR__ . '/includes/navigation.php'; ?>

    <main class="dashboard-main">
        <div class="dashboard-container">
            <!-- Dashboard Header -->
            <header class="dashboard-header">
                <div class="welcome-section">
                    <div class="welcome-text">
                        <h1 class="dashboard-title">
                            <?= $greeting ?>, <?= htmlspecialchars(explode(' ', $student['full_name'])[0]) ?>! 👋
                        </h1>
                        <p class="dashboard-subtitle">
                            Welcome back to your learning journey. Here's your academic overview.
                        </p>
                    </div>
                    <div class="dashboard-actions">
                        <a href="courses.php" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            Browse Courses
                        </a>
                        <a href="profile.php" class="btn btn-secondary">
                            <i class="fas fa-user"></i>
                            Profile
                        </a>
                    </div>
                </div>
                
                <?php if (isset($_GET['updated'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Progress updated successfully!
                </div>
                <?php endif; ?>
            </header>

            <!-- Statistics Cards -->
            <section class="stats-section">
                <div class="stats-grid">
                    <div class="stat-card enrolled">
                        <div class="stat-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?= $enrolled_count ?></h3>
                            <p class="stat-label">Enrolled Courses</p>
                            <span class="stat-badge">Currently Active</span>
                        </div>
                    </div>
                    
                    <div class="stat-card completed">
                        <div class="stat-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?= $completed_count ?></h3>
                            <p class="stat-label">Completed Courses</p>
                            <span class="stat-badge">Certificates Earned</span>
                        </div>
                    </div>
                    
                    <div class="stat-card credits">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?= $total_credits ?></h3>
                            <p class="stat-label">Credit Hours</p>
                            <span class="stat-badge">Total Enrolled</span>
                        </div>
                    </div>
                    
                    <div class="stat-card progress">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?= number_format($avg_progress, 1) ?>%</h3>
                            <p class="stat-label">Average Progress</p>
                            <span class="stat-badge">Across All Courses</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main Content Grid -->
            <div class="dashboard-content">
                <div class="content-left">
                    <!-- Current Courses -->
                    <div class="dashboard-card courses-card">
                        <div class="card-header">
                            <h2>
                                <i class="fas fa-book-open"></i>
                                Current Courses
                            </h2>
                            <a href="student_courses.php" class="view-all-btn">
                                View All <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        <div class="card-content">
                            <?php if (empty($current_courses)): ?>
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-book-open"></i>
                                    </div>
                                    <h3>No Courses Yet</h3>
                                    <p>Start your learning journey by enrolling in courses</p>
                                    <a href="courses.php" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                        Browse Courses
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="courses-grid">
                                    <?php foreach ($current_courses as $course): ?>
                                        <div class="course-card">
                                            <div class="course-header">
                                                <h4><?= htmlspecialchars($course['course_name']) ?></h4>
                                                <span class="course-code"><?= htmlspecialchars($course['course_code']) ?></span>
                                            </div>
                                            <div class="course-meta">
                                                <span class="instructor">
                                                    <i class="fas fa-user"></i>
                                                    <?= htmlspecialchars($course['instructor']) ?>
                                                </span>
                                                <span class="credits">
                                                    <i class="fas fa-star"></i>
                                                    <?= $course['credits'] ?> Credits
                                                </span>
                                            </div>
                                            <div class="course-progress">
                                                <div class="progress-header">
                                                    <span class="progress-label">Progress</span>
                                                    <span class="progress-percentage"><?= number_format($course['progress'], 1) ?>%</span>
                                                </div>
                                                <div class="progress-bar">
                                                    <div class="progress-fill" style="width: <?= $course['progress'] ?>%"></div>
                                                </div>
                                            </div>
                                            <div class="course-actions">
                                                <form method="post" class="progress-form">
                                                    <input type="hidden" name="action" value="update_progress">
                                                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                                    <div class="progress-slider-container">
                                                        <input type="range" 
                                                               name="progress" 
                                                               min="0" 
                                                               max="100" 
                                                               value="<?= $course['progress'] ?>" 
                                                               class="progress-slider"
                                                               id="slider-<?= $course['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-primary update-btn">
                                                            <i class="fas fa-check"></i>
                                                            Update
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="dashboard-card activity-card">
                        <div class="card-header">
                            <h2>
                                <i class="fas fa-clock"></i>
                                Recent Activity
                            </h2>
                        </div>
                        <div class="card-content">
                            <?php if (empty($recent_activities)): ?>
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <h3>No Recent Activity</h3>
                                    <p>Your activities will appear here as you use the portal</p>
                                </div>
                            <?php else: ?>
                                <div class="activity-timeline">
                                    <?php foreach ($recent_activities as $activity): ?>
                                        <div class="activity-item">
                                            <div class="activity-icon">
                                                <?php
                                                $icons = [
                                                    'login' => 'fas fa-sign-in-alt',
                                                    'course_view' => 'fas fa-eye',
                                                    'enrollment' => 'fas fa-book-open',
                                                    'profile_update' => 'fas fa-user-edit',
                                                    'progress_update' => 'fas fa-chart-line',
                                                    'dashboard_access' => 'fas fa-tachometer-alt'
                                                ];
                                                $icon = $icons[$activity['activity_type']] ?? 'fas fa-info-circle';
                                                ?>
                                                <i class="<?= $icon ?>"></i>
                                            </div>
                                            <div class="activity-content">
                                                <p class="activity-description"><?= htmlspecialchars($activity['activity_description']) ?></p>
                                                <?php if ($activity['course_name']): ?>
                                                    <span class="activity-course"><?= htmlspecialchars($activity['course_code']) ?></span>
                                                <?php endif; ?>
                                                <span class="activity-time"><?= date('M j, g:i A', strtotime($activity['created_at'])) ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="content-right">
                    <!-- Profile Completion -->
                    <div class="dashboard-card profile-card">
                        <div class="card-header">
                            <h2>
                                <i class="fas fa-user-circle"></i>
                                Profile Completion
                            </h2>
                        </div>
                        <div class="card-content">
                            <div class="profile-completion">
                                <div class="completion-circle">
                                    <div class="circle-progress" data-percentage="<?= $profile_score ?>">
                                        <span class="percentage"><?= $profile_score ?>%</span>
                                    </div>
                                </div>
                                <div class="completion-details">
                                    <div class="completion-item <?= $profile_items['full_name'] ? 'completed' : 'incomplete' ?>">
                                        <i class="fas fa-<?= $profile_items['full_name'] ? 'check' : 'times' ?>"></i>
                                        <span>Full Name</span>
                                    </div>
                                    <div class="completion-item <?= $profile_items['email'] ? 'completed' : 'incomplete' ?>">
                                        <i class="fas fa-<?= $profile_items['email'] ? 'check' : 'times' ?>"></i>
                                        <span>Email</span>
                                    </div>
                                    <div class="completion-item <?= $profile_items['phone'] ? 'completed' : 'incomplete' ?>">
                                        <i class="fas fa-<?= $profile_items['phone'] ? 'check' : 'times' ?>"></i>
                                        <span>Phone</span>
                                    </div>
                                    <div class="completion-item <?= $profile_items['address'] ? 'completed' : 'incomplete' ?>">
                                        <i class="fas fa-<?= $profile_items['address'] ? 'check' : 'times' ?>"></i>
                                        <span>Address</span>
                                    </div>
                                    <div class="completion-item <?= $profile_items['profile_picture'] ? 'completed' : 'incomplete' ?>">
                                        <i class="fas fa-<?= $profile_items['profile_picture'] ? 'check' : 'times' ?>"></i>
                                        <span>Profile Picture</span>
                                    </div>
                                </div>
                                <?php if ($profile_score < 100): ?>
                                    <a href="profile.php" class="btn btn-primary btn-block">
                                        <i class="fas fa-edit"></i>
                                        Complete Profile
                                    </a>
                                <?php endif; ?>
                            </div>
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
                                <a href="courses.php" class="action-item">
                                    <div class="action-icon">
                                        <i class="fas fa-search"></i>
                                    </div>
                                    <div class="action-content">
                                        <h4>Browse Courses</h4>
                                        <p>Discover new learning opportunities</p>
                                    </div>
                                </a>
                                <a href="student_courses.php" class="action-item">
                                    <div class="action-icon">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <div class="action-content">
                                        <h4>My Courses</h4>
                                        <p>View and manage enrolled courses</p>
                                    </div>
                                </a>
                                <a href="profile.php" class="action-item">
                                    <div class="action-icon">
                                        <i class="fas fa-user-edit"></i>
                                    </div>
                                    <div class="action-content">
                                        <h4>Edit Profile</h4>
                                        <p>Update your information</p>
                                    </div>
                                </a>
                                <a href="upload_profile_picture.php" class="action-item">
                                    <div class="action-icon">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                    <div class="action-content">
                                        <h4>Upload Photo</h4>
                                        <p>Add your profile picture</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Course Recommendations -->
                    <div class="dashboard-card recommendations-card">
                        <div class="card-header">
                            <h2>
                                <i class="fas fa-lightbulb"></i>
                                Recommended Courses
                            </h2>
                        </div>
                        <div class="card-content">
                            <?php if (empty($recommended_courses)): ?>
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-lightbulb"></i>
                                    </div>
                                    <h3>No Recommendations</h3>
                                    <p>Complete your profile to get personalized recommendations</p>
                                </div>
                            <?php else: ?>
                                <div class="recommendations-list">
                                    <?php foreach ($recommended_courses as $course): ?>
                                        <div class="recommendation-item">
                                            <div class="recommendation-content">
                                                <h4><?= htmlspecialchars($course['course_name']) ?></h4>
                                                <span class="course-code"><?= htmlspecialchars($course['course_code']) ?></span>
                                                <p class="instructor">
                                                    <i class="fas fa-user"></i>
                                                    <?= htmlspecialchars($course['instructor']) ?>
                                                </p>
                                                <div class="popularity">
                                                    <i class="fas fa-users"></i>
                                                    <span><?= $course['enrollment_count'] ?> students enrolled</span>
                                                </div>
                                            </div>
                                            <a href="courses.php#course-<?= $course['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                                View
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
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
        // Modern Dashboard JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Animate progress circles
            const progressCircles = document.querySelectorAll('.circle-progress');
            progressCircles.forEach(circle => {
                const percentage = circle.getAttribute('data-percentage');
                animateProgressCircle(circle, percentage);
            });
            
            // Handle progress slider interactions
            const sliders = document.querySelectorAll('.progress-slider');
            sliders.forEach(slider => {
                slider.addEventListener('input', function() {
                    const value = this.value;
                    const progressFill = this.closest('.course-card').querySelector('.progress-fill');
                    const progressPercentage = this.closest('.course-card').querySelector('.progress-percentage');
                    
                    progressFill.style.width = value + '%';
                    progressPercentage.textContent = value + '%';
                });
            });
            
            // Add smooth animations to stat cards
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-in');
                    }
                });
            });
            
            document.querySelectorAll('.stat-card').forEach(card => {
                observer.observe(card);
            });
        });
        
        function animateProgressCircle(circle, percentage) {
            const circumference = 2 * Math.PI * 45; // radius is 45
            const strokeDasharray = `${circumference} ${circumference}`;
            const strokeDashoffset = circumference - (percentage / 100) * circumference;
            
            // Create SVG circle if not exists
            if (!circle.querySelector('svg')) {
                circle.innerHTML = `
                    <svg class="progress-ring" width="100" height="100">
                        <circle class="progress-ring__circle" stroke="currentColor" stroke-width="8" fill="transparent" r="45" cx="50" cy="50" style="stroke-dasharray: ${strokeDasharray}; stroke-dashoffset: ${strokeDashoffset}; transition: stroke-dashoffset 1s ease-in-out;"/>
                    </svg>
                    <span class="percentage">${percentage}%</span>
                `;
            }
        }
    </script>
</body>
</html> 