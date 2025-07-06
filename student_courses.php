<?php
// student_courses.php — Student course dashboard
session_start();
require __DIR__ . '/includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

$student_id = $_SESSION['student_id'];

// Get student's enrolled courses
try {
    $stmt = $pdo->prepare("
        SELECT c.*, e.enrollment_date, e.grade, e.status,
               COALESCE(sp.progress_percentage, 0) as progress,
               COALESCE(sp.last_accessed, e.enrollment_date) as last_accessed,
               COALESCE(sp.study_hours, 0) as study_hours
        FROM courses c
        JOIN enrollments e ON c.id = e.course_id
        LEFT JOIN student_progress sp ON sp.student_id = e.student_id AND sp.course_id = c.id
        WHERE e.student_id = :student_id
        ORDER BY e.enrollment_date DESC
    ");
    $stmt->execute([':student_id' => $student_id]);
    $enrolledCourses = $stmt->fetchAll();
    
    // Get course statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_courses,
            SUM(CASE WHEN e.status = 'enrolled' THEN 1 ELSE 0 END) as active_courses,
            SUM(CASE WHEN e.status = 'completed' THEN 1 ELSE 0 END) as completed_courses,
            SUM(CASE WHEN e.status = 'enrolled' THEN c.credits ELSE 0 END) as current_credits,
            SUM(CASE WHEN e.status = 'completed' THEN c.credits ELSE 0 END) as completed_credits
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        WHERE e.student_id = :student_id
    ");
    $stmt->execute([':student_id' => $student_id]);
    $stats = $stmt->fetch();
    
} catch (PDOException $e) {
    $enrolledCourses = [];
    $stats = [
        'total_courses' => 0,
        'active_courses' => 0,
        'completed_courses' => 0,
        'current_credits' => 0,
        'completed_credits' => 0
    ];
    $error = 'Error loading courses: ' . $e->getMessage();
}

// Handle success/error messages
$message = '';
$error_msg = '';
if (isset($_GET['success'])) {
    $message = htmlspecialchars($_GET['success']);
}
if (isset($_GET['error'])) {
    $error_msg = htmlspecialchars($_GET['error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Student Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/icons.css">
</head>
<body>
    <?php require __DIR__ . '/includes/navigation.php'; ?>

    <main>
        <div class="container">
            <section class="student-courses-section">
                <div class="courses-header">
                    <h1>
                        My Courses
                    </h1>
                    <p>Track your academic progress and manage your course enrollments</p>
                </div>
                
                <!-- Course Statistics -->
                <div class="course-stats-section">
                    <div class="section-header">
                        <h2>
                            Academic Overview
                           
                        </h2>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <div class="icon icon-courses icon-xl"></div>
                            </div>
                            <div class="stat-content">
                                <h3><?= $stats['total_courses'] ?></h3>
                                <p>Total Courses</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <div class="icon icon-graduation icon-xl"></div>
                            </div>
                            <div class="stat-content">
                                <h3><?= $stats['active_courses'] ?></h3>
                                <p>Active Courses</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <div class="icon icon-check icon-xl"></div>
                            </div>
                            <div class="stat-content">
                                <h3><?= $stats['completed_courses'] ?></h3>
                                <p>Completed Courses</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <div class="icon icon-star icon-xl"></div>
                            </div>
                            <div class="stat-content">
                                <h3><?= $stats['current_credits'] ?></h3>
                                <p>Current Credits</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <div class="icon icon-star icon-xl"></div>
                            </div>
                            <div class="stat-content">
                                <h3><?= $stats['completed_credits'] ?></h3>
                                <p>Completed Credits</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Course List -->
                <div class="course-list-section">
                    <div class="section-header">
                        <h2>
                            Enrolled Courses
                           
                        </h2>
                        <a href="courses.php" class="btn btn-primary btn-sm">
                            <div class="icon icon-plus btn-icon"></div>
                            <span>Browse More Courses</span>
                        </a>
                    </div>
                    
                    <?php if (empty($enrolledCourses)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <div class="icon icon-graduation icon-xl"></div>
                            </div>
                            <h3>No courses enrolled yet</h3>
                            <p>Start your learning journey by browsing and enrolling in courses</p>
                            <a href="courses.php" class="btn btn-primary">
                                <div class="icon icon-search btn-icon"></div>
                                <span>Browse Course Catalog</span>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="courses-container">
                            <?php foreach ($enrolledCourses as $course): ?>
                                <div class="course-card <?= $course['status'] ?>">
                                    <div class="course-card-header">
                                        <div class="course-title">
                                            <h3><?= htmlspecialchars($course['course_name']) ?></h3>
                                            <div class="course-badges">
                                                <span class="course-code"><?= htmlspecialchars($course['course_code']) ?></span>
                                                <span class="status-badge <?= $course['status'] ?>">
                                                    <div class="icon <?= $course['status'] === 'completed' ? 'icon-check' : 'icon-graduation' ?>"></div>
                                                    <span><?= ucfirst($course['status']) ?></span>
                                                </span>
                                            </div>
                                        </div>
                                        <?php if ($course['status'] === 'enrolled' && $course['progress'] > 0): ?>
                                            <div class="course-progress">
                                                <div class="progress-info">
                                                    <span class="progress-label">Progress</span>
                                                    <span class="progress-text"><?= number_format($course['progress'], 1) ?>%</span>
                                                </div>
                                                <div class="progress-bar">
                                                    <div class="progress-fill" style="width: <?= $course['progress'] ?>%" data-width="<?= $course['progress'] ?>%"></div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="course-card-body">
                                        <p class="course-description"><?= htmlspecialchars($course['description']) ?></p>
                                        
                                        <div class="course-info">
                                            <div class="info-item">
                                                <div class="info-icon">
                                                    <div class="icon icon-profile"></div>
                                                </div>
                                                <div class="info-content">
                                                    <span class="info-label">Instructor</span>
                                                    <span class="info-value"><?= htmlspecialchars($course['instructor']) ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="info-item">
                                                <div class="info-icon">
                                                    <div class="icon icon-calendar"></div>
                                                </div>
                                                <div class="info-content">
                                                    <span class="info-label">Semester</span>
                                                    <span class="info-value"><?= htmlspecialchars($course['semester']) ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="info-item">
                                                <div class="info-icon">
                                                    <div class="icon icon-star"></div>
                                                </div>
                                                <div class="info-content">
                                                    <span class="info-label">Credits</span>
                                                    <span class="info-value"><?= $course['credits'] ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="info-item">
                                                <div class="info-icon">
                                                    <div class="icon icon-calendar"></div>
                                                </div>
                                                <div class="info-content">
                                                    <span class="info-label">Enrolled</span>
                                                    <span class="info-value"><?= date('M j, Y', strtotime($course['enrollment_date'])) ?></span>
                                                </div>
                                            </div>
                                            
                                            <?php if ($course['grade']): ?>
                                                <div class="info-item">
                                                    <div class="info-icon">
                                                        <div class="icon icon-star"></div>
                                                    </div>
                                                    <div class="info-content">
                                                        <span class="info-label">Grade</span>
                                                        <span class="info-value grade-<?= strtolower($course['grade']) ?>"><?= htmlspecialchars($course['grade']) ?></span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($course['last_accessed'] !== $course['enrollment_date']): ?>
                                                <div class="info-item">
                                                    <div class="info-icon">
                                                        <div class="icon icon-clock"></div>
                                                    </div>
                                                    <div class="info-content">
                                                        <span class="info-label">Last Accessed</span>
                                                        <span class="info-value"><?= date('M j, Y', strtotime($course['last_accessed'])) ?></span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="course-card-footer">
                                        <a href="student_dashboard.php" class="btn btn-secondary">
                                            <div class="icon icon-dashboard btn-icon"></div>
                                            <span>View Progress</span>
                                        </a>
                                        <?php if ($course['status'] === 'enrolled'): ?>
                                            <button class="btn btn-error" onclick="confirmDropCourse(<?= $course['id'] ?>, '<?= htmlspecialchars($course['course_name']) ?>')">
                                                <div class="icon icon-x btn-icon"></div>
                                                <span>Drop Course</span>
                                            </button>
                                        <?php else: ?>
                                            <span class="btn btn-success" disabled>
                                                <div class="icon icon-check btn-icon"></div>
                                                <span>Completed</span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Quick Actions -->
                <div class="quick-actions-section">
                    <div class="section-header">
                        <h2>
                            <div class="icon icon-settings icon-text">
                                <span>Quick Actions</span>
                            </div>
                        </h2>
                    </div>
                    <div class="actions-grid">
                        <a href="courses.php" class="action-card">
                            <div class="action-icon">
                                <div class="icon icon-search"></div>
                            </div>
                            <div class="action-content">
                                <h3>Browse Courses</h3>
                                <p>Find and enroll in new courses to expand your knowledge</p>
                            </div>
                        </a>
                        <a href="student_dashboard.php" class="action-card">
                            <div class="action-icon">
                                <div class="icon icon-dashboard"></div>
                            </div>
                            <div class="action-content">
                                <h3>View Dashboard</h3>
                                <p>Track your overall academic progress and activity</p>
                            </div>
                        </a>
                        <a href="profile.php" class="action-card">
                            <div class="action-icon">
                                <div class="icon icon-edit"></div>
                            </div>
                            <div class="action-content">
                                <h3>Update Profile</h3>
                                <p>Keep your personal information current</p>
                            </div>
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> Student Portal. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/app.js"></script>
    <script>
        // Student courses specific JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Handle success/error messages
            <?php if ($message): ?>
                toast.success('<?= addslashes($message) ?>');
            <?php endif; ?>
            
            <?php if ($error_msg): ?>
                toast.error('<?= addslashes($error_msg) ?>');
            <?php endif; ?>
            
            // Initialize progress bar animations
            setTimeout(() => {
                const progressBars = document.querySelectorAll('.progress-fill');
                progressBars.forEach(bar => {
                    const width = bar.getAttribute('data-width');
                    if (width) {
                        bar.style.width = '0%';
                        setTimeout(() => {
                            bar.style.width = width;
                        }, 100);
                    }
                });
            }, 500);
            
            // Add hover effects to course cards
            const courseCards = document.querySelectorAll('.course-card');
            courseCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-4px)';
                    this.style.boxShadow = 'var(--shadow-xl)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = 'var(--shadow-md)';
                });
            });
            
            // Add hover effects to action cards
            const actionCards = document.querySelectorAll('.action-card');
            actionCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                    this.style.boxShadow = 'var(--shadow-lg)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = 'var(--shadow-md)';
                });
            });
        });
        
        // Confirm drop course function
        function confirmDropCourse(courseId, courseName) {
            modal.confirm(
                `Are you sure you want to drop "${courseName}"? This action cannot be undone.`,
                'Drop Course',
                'Drop Course',
                'Cancel'
            ).then(confirmed => {
                if (confirmed) {
                    // Show loading state
                    const btn = event.target.closest('button');
                    btn.innerHTML = `
                        <div class="icon icon-spinning btn-icon"></div>
                        <span>Dropping...</span>
                    `;
                    btn.disabled = true;
                    
                    // Redirect to drop course
                    window.location.href = `drop_course.php?id=${courseId}`;
                }
            });
        }
    </script>
</body>
</html>

<style>
/* Student courses specific modern styles */
.student-courses-section {
    padding: var(--spacing-6) 0;
}

.courses-header {
    text-align: center;
    margin-bottom: var(--spacing-8);
}

.courses-header h1 {
    color: var(--primary-color);
    margin-bottom: var(--spacing-2);
}

.courses-header p {
    color: var(--gray-600);
    font-size: var(--font-size-lg);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-6);
    flex-wrap: wrap;
    gap: var(--spacing-3);
}

.section-header h2 {
    color: var(--gray-900);
    margin: 0;
}

.course-stats-section {
    margin-bottom: var(--spacing-10);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: var(--spacing-4);
}

.stat-card {
    background: var(--white);
    padding: var(--spacing-6);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--gray-200);
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
    transition: all var(--transition-normal);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: var(--primary-light);
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
}

.stat-content h3 {
    font-size: var(--font-size-3xl);
    font-weight: 700;
    color: var(--primary-color);
    margin: 0;
}

.stat-content p {
    margin: var(--spacing-1) 0 0 0;
    color: var(--gray-600);
    font-size: var(--font-size-sm);
}

.course-list-section {
    margin-bottom: var(--spacing-10);
}

.courses-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: var(--spacing-6);
}

.course-card {
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--gray-200);
    overflow: hidden;
    transition: all var(--transition-normal);
}

.course-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-xl);
}

.course-card.completed {
    border-color: var(--success-color);
    background: linear-gradient(135deg, var(--white) 0%, var(--success-light) 100%);
}

.course-card-header {
    padding: var(--spacing-6);
    border-bottom: 1px solid var(--gray-200);
}

.course-title h3 {
    margin: 0 0 var(--spacing-3) 0;
    color: var(--gray-900);
    font-size: var(--font-size-xl);
}

.course-badges {
    display: flex;
    gap: var(--spacing-2);
    align-items: center;
    flex-wrap: wrap;
}

.course-code {
    background: var(--primary-color);
    color: var(--white);
    padding: var(--spacing-1) var(--spacing-2);
    border-radius: var(--radius-sm);
    font-size: var(--font-size-sm);
    font-weight: 600;
}

.status-badge {
    display: flex;
    align-items: center;
    gap: var(--spacing-1);
    padding: var(--spacing-1) var(--spacing-2);
    border-radius: var(--radius-sm);
    font-size: var(--font-size-sm);
    font-weight: 600;
}

.status-badge.enrolled {
    background: var(--info-light);
    color: var(--info-color);
}

.status-badge.completed {
    background: var(--success-light);
    color: var(--success-color);
}

.course-progress {
    margin-top: var(--spacing-4);
}

.progress-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: var(--spacing-1);
}

.progress-label {
    font-size: var(--font-size-sm);
    color: var(--gray-600);
}

.progress-text {
    font-size: var(--font-size-sm);
    font-weight: 600;
    color: var(--primary-color);
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: var(--gray-200);
    border-radius: var(--radius-full);
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
    border-radius: var(--radius-full);
    transition: width 1s ease-out;
}

.course-card-body {
    padding: var(--spacing-6);
}

.course-description {
    color: var(--gray-600);
    line-height: 1.6;
    margin-bottom: var(--spacing-6);
}

.course-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-4);
}

.info-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
}

.info-icon {
    width: 32px;
    height: 32px;
    background: var(--gray-100);
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gray-600);
}

.info-content {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-size: var(--font-size-sm);
    color: var(--gray-500);
    font-weight: 500;
}

.info-value {
    font-size: var(--font-size-sm);
    color: var(--gray-900);
    font-weight: 600;
}

.info-value.grade-a {
    color: var(--success-color);
}

.info-value.grade-b {
    color: var(--info-color);
}

.info-value.grade-c {
    color: var(--warning-color);
}

.info-value.grade-d,
.info-value.grade-f {
    color: var(--error-color);
}

.course-card-footer {
    padding: var(--spacing-4) var(--spacing-6);
    background: var(--gray-50);
    border-top: 1px solid var(--gray-200);
    display: flex;
    gap: var(--spacing-3);
    justify-content: space-between;
}

.course-card-footer .btn {
    flex: 1;
    justify-content: center;
}

.empty-state {
    text-align: center;
    padding: var(--spacing-12) var(--spacing-6);
    color: var(--gray-500);
}

.empty-icon {
    width: 120px;
    height: 120px;
    background: var(--gray-100);
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto var(--spacing-6);
    color: var(--gray-400);
}

.empty-state h3 {
    color: var(--gray-700);
    margin-bottom: var(--spacing-2);
}

.empty-state p {
    margin-bottom: var(--spacing-6);
}

.quick-actions-section {
    margin-bottom: var(--spacing-8);
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--spacing-6);
}

.action-card {
    background: var(--white);
    padding: var(--spacing-6);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--gray-200);
    text-decoration: none;
    color: inherit;
    transition: all var(--transition-normal);
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
}

.action-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary-color);
}

.action-icon {
    width: 50px;
    height: 50px;
    background: var(--primary-color);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
}

.action-content h3 {
    margin: 0 0 var(--spacing-1) 0;
    color: var(--gray-900);
    font-size: var(--font-size-lg);
}

.action-content p {
    margin: 0;
    color: var(--gray-600);
    font-size: var(--font-size-sm);
}

/* Responsive Design */
@media (max-width: 768px) {
    .courses-container {
        grid-template-columns: 1fr;
        gap: var(--spacing-4);
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: var(--spacing-3);
    }
    
    .course-info {
        grid-template-columns: 1fr;
    }
    
    .course-card-footer {
        flex-direction: column;
    }
    
    .actions-grid {
        grid-template-columns: 1fr;
    }
    
    .action-card {
        flex-direction: column;
        text-align: center;
    }
    
    .section-header {
        flex-direction: column;
        align-items: stretch;
    }
}

@media (max-width: 480px) {
    .course-card-header,
    .course-card-body {
        padding: var(--spacing-4);
    }
    
    .course-card-footer {
        padding: var(--spacing-3) var(--spacing-4);
    }
    
    .stat-card {
        padding: var(--spacing-4);
        flex-direction: column;
        text-align: center;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
    }
}
</style> 