<?php
// admin_courses.php — Modern Course Management Dashboard
session_start();
require __DIR__ . '/includes/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['student_id']) || $_SESSION['student_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Handle success/error messages
$message = '';
$error = '';
if (isset($_GET['success'])) {
    $message = htmlspecialchars($_GET['success']);
}
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}

// Get all courses with enrollment statistics
try {
    $stmt = $pdo->query("
        SELECT c.*, 
               COUNT(e.id) as total_enrollments,
               SUM(CASE WHEN e.status = 'enrolled' THEN 1 ELSE 0 END) as active_enrollments,
               SUM(CASE WHEN e.status = 'completed' THEN 1 ELSE 0 END) as completed_enrollments,
               SUM(CASE WHEN e.status = 'dropped' THEN 1 ELSE 0 END) as dropped_enrollments
        FROM courses c
        LEFT JOIN enrollments e ON c.id = e.course_id
        GROUP BY c.id
        ORDER BY c.created_at DESC
    ");
    $courses = $stmt->fetchAll();
    
    // Get overall statistics
    $stmt = $pdo->query("SELECT COUNT(*) as total_courses FROM courses");
    $totalCourses = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as total_enrollments FROM enrollments");
    $totalEnrollments = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as active_enrollments FROM enrollments WHERE status = 'enrolled'");
    $activeEnrollments = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(DISTINCT semester) as total_semesters FROM courses");
    $totalSemesters = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    $courses = [];
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management - AUCA Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/icons.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require __DIR__ . '/includes/navigation.php'; ?>

    <main class="dashboard-main">
        <div class="dashboard-container">
            <!-- Course Management Header -->
            <header class="dashboard-header">
                <div class="welcome-section">
                    <div class="welcome-text">
                        <h1 class="dashboard-title">
                            Course Management 📚
                        </h1>
                        <p class="dashboard-subtitle">
                            Manage courses, enrollments, and academic programs with comprehensive administrative tools.
                        </p>
                    </div>
                    <div class="dashboard-actions">
                        <a href="admin_add_course.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Add New Course
                        </a>
                    </div>
                </div>
                
                <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= $message ?>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $error ?>
                </div>
                <?php endif; ?>
            </header>

            <!-- Statistics Cards -->
            <section class="stats-section">
                <div class="stats-grid">
                    <div class="stat-card courses">
                        <div class="stat-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?= number_format($totalCourses) ?></h3>
                            <p class="stat-label">Total Courses</p>
                            <span class="stat-badge">Active Programs</span>
                        </div>
                    </div>
                    
                    <div class="stat-card enrollments">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?= number_format($totalEnrollments) ?></h3>
                            <p class="stat-label">Total Enrollments</p>
                            <span class="stat-badge">All-Time Registrations</span>
                        </div>
                    </div>
                    
                    <div class="stat-card active">
                        <div class="stat-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?= number_format($activeEnrollments) ?></h3>
                            <p class="stat-label">Active Enrollments</p>
                            <span class="stat-badge">Current Semester</span>
                        </div>
                    </div>
                    
                    <div class="stat-card semesters">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?= number_format($totalSemesters) ?></h3>
                            <p class="stat-label">Academic Terms</p>
                            <span class="stat-badge">Available Semesters</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Course Management Section -->
            <div class="dashboard-card courses-management-card">
                <div class="card-header">
                    <h2>
                        <i class="fas fa-graduation-cap"></i>
                        Course Management
                    </h2>
                    <div class="header-actions">
                        <div class="search-container">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="courseSearch" placeholder="Search courses..." class="search-input">
                        </div>
                        <select id="filterSemester" class="filter-select">
                            <option value="">All Semesters</option>
                            <?php
                            // Get unique semesters for filter
                            $stmt = $pdo->query("SELECT DISTINCT semester FROM courses ORDER BY semester");
                            $semesters = $stmt->fetchAll();
                            foreach ($semesters as $semester) {
                                echo "<option value='" . htmlspecialchars($semester['semester']) . "'>" . htmlspecialchars($semester['semester']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="card-content">
                    <?php if (empty($courses)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-book-open"></i>
                            </div>
                            <h3>No Courses Found</h3>
                            <p>Get started by adding your first course to the system.</p>
                            <a href="admin_add_course.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                Add First Course
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="courses-grid">
                            <?php foreach ($courses as $course): ?>
                            <div class="course-item" data-semester="<?= htmlspecialchars($course['semester']) ?>">
                                <div class="course-header">
                                    <div class="course-code"><?= htmlspecialchars($course['course_code']) ?></div>
                                    <div class="course-actions">
                                                                        <button class="btn btn-sm btn-secondary" onclick="viewEnrollments(<?= $course['id'] ?>)" title="View Enrollments">
                                    <i class="fas fa-users"></i>
                                </button>
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="confirmDelete(<?= $course['id'] ?>, '<?= htmlspecialchars($course['course_name']) ?>')"
                                                title="Delete Course">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="course-body">
                                    <h3 class="course-title"><?= htmlspecialchars($course['course_name']) ?></h3>
                                    <p class="course-description">
                                        <?= htmlspecialchars(strlen($course['description']) > 120 ? substr($course['description'], 0, 120) . '...' : $course['description']) ?>
                                    </p>
                                    
                                    <div class="course-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-user"></i>
                                            <span><?= htmlspecialchars($course['instructor']) ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-calendar"></i>
                                            <span><?= htmlspecialchars($course['semester']) ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-star"></i>
                                            <span><?= $course['credits'] ?> Credits</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="course-footer">
                                    <div class="enrollment-status">
                                        <div class="enrollment-info">
                                            <span class="enrolled-count"><?= $course['active_enrollments'] ?></span>
                                            <span class="separator">/</span>
                                            <span class="max-capacity"><?= $course['max_students'] ?></span>
                                            <span class="label">Students</span>
                                        </div>
                                        <div class="enrollment-bar">
                                            <div class="enrollment-progress" style="width: <?= $course['max_students'] > 0 ? ($course['active_enrollments'] / $course['max_students']) * 100 : 0 ?>%"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="course-status">
                                        <?php if ($course['active_enrollments'] >= $course['max_students']): ?>
                                            <span class="status-badge status-full">
                                                <i class="fas fa-times-circle"></i>
                                                Full
                                            </span>
                                        <?php elseif ($course['active_enrollments'] > 0): ?>
                                            <span class="status-badge status-active">
                                                <i class="fas fa-check-circle"></i>
                                                Active
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge status-empty">
                                                <i class="fas fa-exclamation-circle"></i>
                                                Empty
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Course search functionality
            const searchInput = document.getElementById('courseSearch');
            const semesterFilter = document.getElementById('filterSemester');
            const courseItems = document.querySelectorAll('.course-item');
            
            function filterCourses() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedSemester = semesterFilter.value;
                
                courseItems.forEach(item => {
                    const courseText = item.textContent.toLowerCase();
                    const itemSemester = item.getAttribute('data-semester');
                    
                    const matchesSearch = courseText.includes(searchTerm);
                    const matchesSemester = !selectedSemester || itemSemester === selectedSemester;
                    
                    item.style.display = matchesSearch && matchesSemester ? '' : 'none';
                });
            }
            
            searchInput.addEventListener('input', filterCourses);
            semesterFilter.addEventListener('change', filterCourses);
        });
        
        // Confirm delete function
        function confirmDelete(courseId, courseName) {
            if (confirm(`Are you sure you want to delete "${courseName}"? This will also remove all enrollments and cannot be undone.`)) {
                // Show loading state
                const btn = event.target.closest('button');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                btn.disabled = true;
                
                alert('Delete course feature coming soon!');
            }
        }
        
        // View enrollments function
        function viewEnrollments(courseId) {
            alert('View enrollments feature coming soon!');
        }
    </script>
</body>
</html> 