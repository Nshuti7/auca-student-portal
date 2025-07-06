<?php
// courses.php — Course catalog page
session_start();
require __DIR__ . '/includes/db.php';

// Get all courses
try {
    $stmt = $pdo->query("
        SELECT c.*, 
               COUNT(e.id) as enrolled_count
        FROM courses c
        LEFT JOIN enrollments e ON c.id = e.course_id AND e.status = 'enrolled'
        GROUP BY c.id
        ORDER BY c.semester, c.course_code
    ");
    $courses = $stmt->fetchAll();
} catch (PDOException $e) {
    $courses = [];
    $error = 'Error loading courses: ' . $e->getMessage();
}

// Get student's enrolled courses if logged in
$enrolled_courses = [];
if (isset($_SESSION['student_id'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT course_id 
            FROM enrollments 
            WHERE student_id = :student_id AND status = 'enrolled'
        ");
        $stmt->execute([':student_id' => $_SESSION['student_id']]);
        $enrolled_courses = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        // Handle error silently
    }
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
    <title>Course Catalog - Student Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/icons.css">
</head>
<body>
    <?php require __DIR__ . '/includes/navigation.php'; ?>

    <main>
        <div class="container">
            <section class="courses-section">
                <div class="courses-header">
                    <h1>
                    
                            <span>Course Catalog</span>
                  
                    </h1>
                    <p>Browse and enroll in available courses to expand your knowledge</p>
                </div>
                
                <?php if (!isset($_SESSION['student_id'])): ?>
                    <div class="alert alert-info">
                        <div class="alert-icon">
                            <div class="icon icon-info"></div>
                        </div>
                        <div class="alert-content">
                            <p>
                                <a href="login.php" class="alert-link">Log in</a> to enroll in courses and track your progress
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="courses-grid">
                    <?php if (empty($courses)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <div class="icon icon-courses icon-xl"></div>
                            </div>
                            <h3>No courses available</h3>
                            <p>There are no courses available at this time. Please check back later.</p>
                            <?php if (isset($_SESSION['student_role']) && $_SESSION['student_role'] === 'admin'): ?>
                                <a href="admin_add_course.php" class="btn btn-primary">
                                    <div class="icon icon-plus btn-icon"></div>
                                    <span>Add First Course</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($courses as $course): ?>
                            <div class="course-card">
                                <div class="course-card-header">
                                    <div class="course-title">
                                        <h3><?= htmlspecialchars($course['course_name']) ?></h3>
                                        <span class="course-code"><?= htmlspecialchars($course['course_code']) ?></span>
                                    </div>
                                    <div class="course-status">
                                        <?php if (isset($_SESSION['student_id']) && in_array($course['id'], $enrolled_courses)): ?>
                                            <span class="status-badge enrolled">
                                                <div class="icon icon-check"></div>
                                                <span>Enrolled</span>
                                            </span>
                                        <?php elseif ($course['enrolled_count'] >= $course['max_students']): ?>
                                            <span class="status-badge full">
                                                <div class="icon icon-x"></div>
                                                <span>Full</span>
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge available">
                                                <div class="icon icon-check"></div>
                                                <span>Available</span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
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
                                                <span class="info-value"><?= htmlspecialchars($course['credits']) ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="info-item">
                                            <div class="info-icon">
                                                <div class="icon icon-users"></div>
                                            </div>
                                            <div class="info-content">
                                                <span class="info-label">Enrollment</span>
                                                <span class="info-value">
                                                    <?= $course['enrolled_count'] ?>/<?= $course['max_students'] ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="enrollment-progress">
                                        <div class="progress-bar">
                                            <?php 
                                            $progress = $course['max_students'] > 0 ? 
                                                ($course['enrolled_count'] / $course['max_students']) * 100 : 0;
                                            ?>
                                            <div class="progress-fill" style="width: <?= min($progress, 100) ?>%" 
                                                 data-width="<?= min($progress, 100) ?>%"></div>
                                        </div>
                                        <span class="progress-text">
                                            <?= number_format($progress, 1) ?>% full
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="course-card-footer">
                                    <?php if (isset($_SESSION['student_id'])): ?>
                                        <?php if (in_array($course['id'], $enrolled_courses)): ?>
                                            <button class="btn btn-success" disabled>
                                                <div class="icon icon-check btn-icon"></div>
                                                <span>Enrolled</span>
                                            </button>
                                            <a href="student_courses.php" class="btn btn-secondary">
                                                <div class="icon icon-graduation btn-icon"></div>
                                                <span>View Course</span>
                                            </a>
                                        <?php elseif ($course['enrolled_count'] >= $course['max_students']): ?>
                                            <button class="btn btn-error" disabled>
                                                <div class="icon icon-x btn-icon"></div>
                                                <span>Course Full</span>
                                            </button>
                                            <a href="#" class="btn btn-secondary" onclick="joinWaitlist(<?= $course['id'] ?>)">
                                                <div class="icon icon-clock btn-icon"></div>
                                                <span>Join Waitlist</span>
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-primary" onclick="enrollInCourse(<?= $course['id'] ?>, '<?= htmlspecialchars($course['course_name']) ?>')">
                                                <div class="icon icon-plus btn-icon"></div>
                                                <span>Enroll Now</span>
                                            </button>
                                            <a href="#" class="btn btn-secondary" onclick="showCourseDetails(<?= $course['id'] ?>)">
                                                <div class="icon icon-eye btn-icon"></div>
                                                <span>View Details</span>
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-primary">
                                            <div class="icon icon-login btn-icon"></div>
                                            <span>Login to Enroll</span>
                                        </a>
                                        <a href="#" class="btn btn-secondary" onclick="showCourseDetails(<?= $course['id'] ?>)">
                                            <div class="icon icon-eye btn-icon"></div>
                                            <span>View Details</span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> Student Portal. All rights reserved.</p>
        </div>
    </footer>

    <!-- Course Details Modal -->
    <div id="courseModal" class="modal-overlay">
        <div class="modal modal-lg">
            <div class="modal-header">
                <h3 class="modal-title" id="courseModalTitle">Course Details</h3>
                <button class="modal-close" onclick="closeCourseModal()">
                    <div class="icon icon-x"></div>
                </button>
            </div>
            <div class="modal-body">
                <div class="course-modal-content">
                    <!-- Course Header -->
                    <div class="course-modal-header">
                        <div class="course-header-info">
                            <h2 id="modalCourseName">Loading...</h2>
                            <div class="course-header-meta">
                                <span class="course-code" id="modalCourseCode">-</span>
                                <span class="course-credits" id="modalCourseCredits">
                                    <div class="icon icon-star"></div>
                                    <span>- Credits</span>
                                </span>
                                <span class="course-semester" id="modalCourseSemester">-</span>
                            </div>
                        </div>
                        <div class="course-status-badge" id="modalCourseStatus">
                            <div class="icon icon-info"></div>
                            <span>Status</span>
                        </div>
                    </div>

                    <!-- Course Tabs -->
                    <div class="course-tabs">
                        <button class="tab-button active" onclick="switchCourseTab('overview')">
                            <div class="icon icon-info"></div>
                            <span>Overview</span>
                        </button>
                        <button class="tab-button" onclick="switchCourseTab('instructor')">
                            <div class="icon icon-profile"></div>
                            <span>Instructor</span>
                        </button>
                        <button class="tab-button" onclick="switchCourseTab('enrollment')">
                            <div class="icon icon-users"></div>
                            <span>Enrollment</span>
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="course-tab-content">
                        <!-- Overview Tab -->
                        <div id="overviewTab" class="tab-pane active">
                            <div class="course-description">
                                <h4>Course Description</h4>
                                <p id="modalCourseDescription">Loading course description...</p>
                            </div>
                            
                            <div class="course-details-grid">
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <div class="icon icon-calendar"></div>
                                    </div>
                                    <div class="detail-content">
                                        <span class="detail-label">Semester</span>
                                        <span class="detail-value" id="modalDetailSemester">-</span>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <div class="icon icon-star"></div>
                                    </div>
                                    <div class="detail-content">
                                        <span class="detail-label">Credits</span>
                                        <span class="detail-value" id="modalDetailCredits">-</span>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <div class="icon icon-profile"></div>
                                    </div>
                                    <div class="detail-content">
                                        <span class="detail-label">Instructor</span>
                                        <span class="detail-value" id="modalDetailInstructor">-</span>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <div class="icon icon-users"></div>
                                    </div>
                                    <div class="detail-content">
                                        <span class="detail-label">Class Size</span>
                                        <span class="detail-value" id="modalDetailSize">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Instructor Tab -->
                        <div id="instructorTab" class="tab-pane">
                            <div class="instructor-info">
                                <div class="instructor-avatar">
                                    <div class="icon icon-profile icon-xl"></div>
                                </div>
                                <div class="instructor-details">
                                    <h4 id="modalInstructorName">Loading...</h4>
                                    <p class="instructor-title">Course Instructor</p>
                                    <div class="instructor-contact">
                                        <div class="contact-item">
                                            <div class="icon icon-mail"></div>
                                            <span id="modalInstructorEmail">Contact information available in class</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="instructor-bio">
                                <h4>About the Instructor</h4>
                                <p id="modalInstructorBio">Instructor information will be provided during course enrollment.</p>
                            </div>
                        </div>

                        <!-- Enrollment Tab -->
                        <div id="enrollmentTab" class="tab-pane">
                            <div class="enrollment-stats">
                                <div class="stats-grid">
                                    <div class="stat-item">
                                        <div class="stat-icon">
                                            <div class="icon icon-users"></div>
                                        </div>
                                        <div class="stat-content">
                                            <span class="stat-label">Total Enrolled</span>
                                            <span class="stat-value" id="modalEnrolledCount">-</span>
                                        </div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-icon">
                                            <div class="icon icon-star"></div>
                                        </div>
                                        <div class="stat-content">
                                            <span class="stat-label">Max Students</span>
                                            <span class="stat-value" id="modalMaxStudents">-</span>
                                        </div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-icon">
                                            <div class="icon icon-check"></div>
                                        </div>
                                        <div class="stat-content">
                                            <span class="stat-label">Available Spots</span>
                                            <span class="stat-value" id="modalAvailableSpots">-</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="enrollment-progress">
                                    <div class="progress-header">
                                        <span>Course Capacity</span>
                                        <span id="modalProgressPercent">0%</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" id="modalProgressBar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeCourseModal()">Close</button>
                <div class="modal-actions" id="modalActions">
                    <!-- Dynamic buttons will be inserted here -->
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
    <script>
        // Courses-specific JavaScript
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
                    bar.style.width = '0%';
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 100);
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
        });
        
        // Course enrollment function
        function enrollInCourse(courseId, courseName) {
            modal.confirm(
                `Are you sure you want to enroll in "${courseName}"?`,
                'Confirm Enrollment',
                'Enroll',
                'Cancel'
            ).then(confirmed => {
                if (confirmed) {
                    // Show loading state
                    const btn = event.target.closest('button');
                    btn.innerHTML = `
                        <div class="icon icon-spinning btn-icon"></div>
                        <span>Enrolling...</span>
                    `;
                    btn.disabled = true;
                    
                    // Redirect to enrollment
                    window.location.href = `enroll_course.php?id=${courseId}`;
                }
            });
        }
        
        // Join waitlist function
        function joinWaitlist(courseId) {
            modal.confirm(
                'This course is currently full. Would you like to join the waitlist?',
                'Join Waitlist',
                'Join Waitlist',
                'Cancel'
            ).then(confirmed => {
                if (confirmed) {
                    toast.info('Waitlist functionality coming soon!');
                }
            });
        }
        
        // Show course details
        function showCourseDetails(courseId) {
            openCourseModal(courseId);
        }

        // Course Modal Functions
        function openCourseModal(courseId) {
            const modal = document.getElementById('courseModal');
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Load course data
            loadCourseData(courseId);
        }

        function closeCourseModal() {
            const modal = document.getElementById('courseModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }

        function switchCourseTab(tabName) {
            // Remove active from all tabs and panes
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
            
            // Add active to selected tab and pane
            event.target.closest('.tab-button').classList.add('active');
            document.getElementById(tabName + 'Tab').classList.add('active');
        }

        function loadCourseData(courseId) {
            // Show loading state
            document.getElementById('modalCourseName').textContent = 'Loading...';
            
            // Fetch course data
            fetch(`get_course_details.php?id=${courseId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateCourseModal(data.course);
                    } else {
                        toastManager.error('Failed to load course details');
                        closeCourseModal();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastManager.error('Failed to load course details');
                    closeCourseModal();
                });
        }

        function populateCourseModal(course) {
            // Header information
            document.getElementById('modalCourseName').textContent = course.course_name;
            document.getElementById('modalCourseCode').textContent = course.course_code;
            document.getElementById('modalCourseCredits').innerHTML = `
                <div class="icon icon-star"></div>
                <span>${course.credits} Credits</span>
            `;
            document.getElementById('modalCourseSemester').textContent = course.semester;
            
            // Status badge
            const statusBadge = document.getElementById('modalCourseStatus');
            const isEnrolled = course.is_enrolled;
            const isFull = course.is_full;
            
            if (isEnrolled) {
                statusBadge.className = 'course-status-badge enrolled';
                statusBadge.innerHTML = '<div class="icon icon-check"></div><span>Enrolled</span>';
            } else if (isFull) {
                statusBadge.className = 'course-status-badge full';
                statusBadge.innerHTML = '<div class="icon icon-x"></div><span>Full</span>';
            } else {
                statusBadge.className = 'course-status-badge available';
                statusBadge.innerHTML = '<div class="icon icon-check"></div><span>Available</span>';
            }
            
            // Overview tab
            document.getElementById('modalCourseDescription').textContent = course.description;
            document.getElementById('modalDetailSemester').textContent = course.semester;
            document.getElementById('modalDetailCredits').textContent = course.credits;
            document.getElementById('modalDetailInstructor').textContent = course.instructor;
            document.getElementById('modalDetailSize').textContent = `${course.enrolled_count}/${course.max_students}`;
            
            // Instructor tab
            document.getElementById('modalInstructorName').textContent = course.instructor;
            
            // Enrollment tab
            document.getElementById('modalEnrolledCount').textContent = course.enrolled_count;
            document.getElementById('modalMaxStudents').textContent = course.max_students;
            document.getElementById('modalAvailableSpots').textContent = course.available_spots;
            
            const progress = course.max_students > 0 ? (course.enrolled_count / course.max_students) * 100 : 0;
            document.getElementById('modalProgressPercent').textContent = Math.round(progress) + '%';
            document.getElementById('modalProgressBar').style.width = progress + '%';
            
            // Update modal actions
            updateModalActions(course);
        }

        function updateModalActions(course) {
            const actionsContainer = document.getElementById('modalActions');
            const isLoggedIn = <?= isset($_SESSION['student_id']) ? 'true' : 'false' ?>;
            const isEnrolled = course.is_enrolled;
            const isFull = course.is_full;
            
            if (!isLoggedIn) {
                actionsContainer.innerHTML = `
                    <a href="login.php" class="btn btn-primary">
                        <div class="icon icon-login btn-icon"></div>
                        <span>Login to Enroll</span>
                    </a>
                `;
            } else if (isEnrolled) {
                actionsContainer.innerHTML = `
                    <button class="btn btn-success" disabled>
                        <div class="icon icon-check btn-icon"></div>
                        <span>Already Enrolled</span>
                    </button>
                    <button class="btn btn-outline-warning" onclick="confirmDropCourse(${course.id}, '${course.course_name}')">
                        <div class="icon icon-x btn-icon"></div>
                        <span>Drop Course</span>
                    </button>
                `;
            } else if (isFull) {
                actionsContainer.innerHTML = `
                    <button class="btn btn-error" disabled>
                        <div class="icon icon-x btn-icon"></div>
                        <span>Course Full</span>
                    </button>
                `;
            } else {
                actionsContainer.innerHTML = `
                    <button class="btn btn-primary" onclick="confirmEnrollCourse(${course.id}, '${course.course_name}')">
                        <div class="icon icon-plus btn-icon"></div>
                        <span>Enroll Now</span>
                    </button>
                `;
            }
        }

        function confirmEnrollCourse(courseId, courseName) {
            modalManager.confirm(
                `Are you sure you want to enroll in "${courseName}"?`,
                'Confirm Enrollment'
            ).then(confirmed => {
                if (confirmed) {
                    window.location.href = `enroll_course.php?id=${courseId}`;
                }
            });
        }

        function confirmDropCourse(courseId, courseName) {
            modalManager.confirm(
                `Are you sure you want to drop "${courseName}"? This action cannot be undone.`,
                'Confirm Drop Course'
            ).then(confirmed => {
                if (confirmed) {
                    window.location.href = `drop_course.php?id=${courseId}`;
                }
            });
        }

        // Close modal on overlay click
        document.getElementById('courseModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCourseModal();
            }
        });

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('courseModal').classList.contains('active')) {
                closeCourseModal();
            }
        });
    </script>
</body>
</html>

<style>
/* Courses-specific modern styles */
.courses-section {
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

.alert {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    padding: var(--spacing-4);
    border-radius: var(--radius-md);
    margin-bottom: var(--spacing-6);
}

.alert-info {
    background: var(--info-light);
    color: var(--info-color);
    border: 1px solid var(--info-color);
}

.alert-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--info-color);
    border-radius: var(--radius-full);
    color: var(--white);
}

.alert-content {
    flex: 1;
}

.alert-link {
    color: var(--info-color);
    text-decoration: underline;
    font-weight: 600;
}

.courses-grid {
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

.course-card-header {
    padding: var(--spacing-6);
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.course-title h3 {
    margin: 0 0 var(--spacing-2) 0;
    color: var(--gray-900);
    font-size: var(--font-size-xl);
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
    background: var(--success-light);
    color: var(--success-color);
}

.status-badge.full {
    background: var(--error-light);
    color: var(--error-color);
}

.status-badge.available {
    background: var(--info-light);
    color: var(--info-color);
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
    margin-bottom: var(--spacing-6);
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

.enrollment-progress {
    margin-bottom: var(--spacing-4);
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: var(--gray-200);
    border-radius: var(--radius-full);
    overflow: hidden;
    margin-bottom: var(--spacing-2);
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
    border-radius: var(--radius-full);
    transition: width 1s ease-out;
}

.progress-text {
    font-size: var(--font-size-sm);
    color: var(--gray-600);
    font-weight: 500;
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
    grid-column: 1 / -1;
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

/* Responsive Design */
@media (max-width: 768px) {
    .courses-grid {
        grid-template-columns: 1fr;
        gap: var(--spacing-4);
    }
    
    .course-card-header {
        flex-direction: column;
        gap: var(--spacing-3);
    }
    
    .course-info {
        grid-template-columns: 1fr;
    }
    
    .course-card-footer {
        flex-direction: column;
    }
    
    .course-card-footer .btn {
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .courses-grid {
        grid-template-columns: 1fr;
    }
    
    .course-card-header,
    .course-card-body {
        padding: var(--spacing-4);
    }
    
    .course-card-footer {
        padding: var(--spacing-3) var(--spacing-4);
    }
}
</style> 