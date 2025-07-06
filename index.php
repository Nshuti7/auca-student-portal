<?php
// index.php — public homepage
session_start();
require_once 'includes/db.php';

// Get some basic statistics for the homepage
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM students");
    $totalStudents = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM courses");
    $totalCourses = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM enrollments");
    $totalEnrollments = $stmt->fetchColumn();
    
    // Get recent courses
    $stmt = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC LIMIT 3");
    $recentCourses = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $totalStudents = 0;
    $totalCourses = 0;
    $totalEnrollments = 0;
    $recentCourses = [];
}

// Check if user is logged in
if (isset($_SESSION['student_id'])) {
    $stmt = $pdo->prepare('SELECT full_name, role, profile_picture FROM students WHERE id = :id');
    $stmt->execute([':id' => $_SESSION['student_id']]);
    $user = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Adventist University of Central Africa - Student Portal</title>
  <meta name="description" content="Official Student Portal for Adventist University of Central Africa. Access courses, manage your academic journey, and connect with our vibrant learning community.">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <?php require __DIR__ . '/includes/navigation.php'; ?>

  <!-- Hero Section -->
  <section class="hero-section">
    <div class="hero-background">
      <img src="assets/images/building.jpg" alt="Adventist University of Central Africa Campus" class="hero-image">
      <div class="hero-overlay"></div>
    </div>
    <div class="hero-content">
      <div class="container">
        <div class="hero-text">
          <h1 class="hero-title">
            <span class="university-name">Adventist University of Central Africa</span>
            <span class="portal-subtitle">Student Portal</span>
          </h1>
          <p class="hero-description">
            Empowering minds, transforming lives, and building leaders for tomorrow. 
            Join our vibrant academic community where faith meets excellence in education.
          </p>
                              <div class="hero-actions">
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <a href="signup.php" class="btn btn-primary btn-lg">
                                <div class="icon icon-user-plus btn-icon"></div>
                                <span>Join Our Community</span>
                            </a>
                            <a href="login.php" class="btn btn-secondary btn-lg">
                                <div class="icon icon-login btn-icon"></div>
                                <span>Student Login</span>
                            </a>
                        <?php elseif ($_SESSION['role'] === 'admin'): ?>
                            <a href="admin_dashboard.php" class="btn btn-primary btn-lg">
                                <div class="icon icon-admin btn-icon"></div>
                                <span>Admin Dashboard</span>
                            </a>
                            <a href="admin_courses.php" class="btn btn-secondary btn-lg">
                                <div class="icon icon-courses btn-icon"></div>
                                <span>Manage Courses</span>
                            </a>
                            <a href="admin_user_roles.php" class="btn btn-outline-primary btn-lg">
                                <div class="icon icon-users btn-icon"></div>
                                <span>Manage Users</span>
                            </a>
                        <?php else: ?>
                            <a href="student_dashboard.php" class="btn btn-primary btn-lg">
                                <div class="icon icon-dashboard btn-icon"></div>
                                <span>My Dashboard</span>
                            </a>
                            <a href="courses.php" class="btn btn-secondary btn-lg">
                                <div class="icon icon-courses btn-icon"></div>
                                <span>Browse Courses</span>
                            </a>
                            <a href="student_courses.php" class="btn btn-outline-primary btn-lg">
                                <div class="icon icon-graduation btn-icon"></div>
                                <span>My Courses</span>
                            </a>
                        <?php endif; ?>
                    </div>
        </div>
        <div class="hero-stats">
          <div class="stat-item">
            <div class="stat-number"><?= number_format($totalStudents) ?>+</div>
            <div class="stat-label">Active Students</div>
          </div>
          <div class="stat-item">
            <div class="stat-number"><?= number_format($totalCourses) ?>+</div>
            <div class="stat-label">Courses Offered</div>
          </div>
          <div class="stat-item">
            <div class="stat-number"><?= number_format($totalEnrollments) ?>+</div>
            <div class="stat-label">Total Enrollments</div>
          </div>
          <div class="stat-item">
            <div class="stat-number">50+</div>
            <div class="stat-label">Years of Excellence</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- University Overview -->
  <section class="university-overview">
    <div class="container">
      <div class="section-header text-center">
        <h2>About Adventist University of Central Africa</h2>
        <p class="section-description">
          A leading institution of higher learning in Central Africa, committed to academic excellence, 
          spiritual growth, and service to humanity.
        </p>
      </div>
      <div class="overview-grid">
        <div class="overview-card">
          <div class="card-icon">
            <div class="icon icon-graduation text-primary"></div>
          </div>
          <div class="card-content">
            <h3>Academic Excellence</h3>
            <p>Offering world-class education across multiple disciplines with internationally recognized faculty and cutting-edge research facilities.</p>
          </div>
        </div>
        <div class="overview-card">
          <div class="card-icon">
            <div class="icon icon-users text-success"></div>
          </div>
          <div class="card-content">
            <h3>Vibrant Community</h3>
            <p>A diverse student body from across Central Africa, fostering cultural exchange and lifelong friendships in a supportive environment.</p>
          </div>
        </div>
        <div class="overview-card">
          <div class="card-icon">
            <div class="icon icon-star text-warning"></div>
          </div>
          <div class="card-content">
            <h3>Holistic Development</h3>
            <p>Nurturing not just academic growth but also spiritual, physical, and social development to produce well-rounded graduates.</p>
          </div>
        </div>
        <div class="overview-card">
          <div class="card-icon">
            <div class="icon icon-heart text-error"></div>
          </div>
          <div class="card-content">
            <h3>Service Leadership</h3>
            <p>Preparing students to be servant leaders who make positive impacts in their communities and contribute to global development.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Portal Features -->
  <section class="features-section">
    <div class="container">
      <div class="section-header text-center">
        <h2>Student Portal Features</h2>
        <p class="section-description">
          Your gateway to academic success with comprehensive tools and resources
        </p>
      </div>
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">
            <div class="icon icon-dashboard text-primary"></div>
          </div>
          <div class="feature-content">
            <h3>Personal Dashboard</h3>
            <p>Track your academic progress, view upcoming deadlines, and access personalized recommendations all in one place.</p>
            <ul class="feature-list">
              <li>Real-time progress tracking</li>
              <li>Assignment deadlines</li>
              <li>Grade notifications</li>
              <li>Academic calendar</li>
            </ul>
          </div>
        </div>
        <div class="feature-card">
          <div class="feature-icon">
            <div class="icon icon-courses text-success"></div>
          </div>
          <div class="feature-content">
            <h3>Course Management</h3>
            <p>Browse available courses, enroll in new programs, and manage your academic schedule with ease.</p>
            <ul class="feature-list">
              <li>Course catalog browsing</li>
              <li>Online enrollment</li>
              <li>Schedule management</li>
              <li>Prerequisites tracking</li>
            </ul>
          </div>
        </div>
        <div class="feature-card">
          <div class="feature-icon">
            <div class="icon icon-profile text-warning"></div>
          </div>
          <div class="feature-content">
            <h3>Profile & Records</h3>
            <p>Maintain your personal information, academic records, and connect with fellow students and faculty.</p>
            <ul class="feature-list">
              <li>Personal information</li>
              <li>Academic transcripts</li>
              <li>Contact management</li>
              <li>Privacy settings</li>
            </ul>
          </div>
        </div>
        <div class="feature-card">
          <div class="feature-icon">
            <div class="icon icon-admin text-error"></div>
          </div>
          <div class="feature-content">
            <h3>Administrative Tools</h3>
            <p>Comprehensive administration features for faculty and staff to manage students, courses, and institutional data.</p>
            <ul class="feature-list">
              <li>Student management</li>
              <li>Course administration</li>
              <li>Enrollment tracking</li>
              <li>Report generation</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

      <!-- Recent Courses (Only for Students and Guests) -->
    <?php if (!empty($recentCourses) && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')): ?>
    <section class="recent-courses">
        <div class="container">
            <div class="section-header text-center">
                <h2><?= isset($_SESSION['user_id']) ? 'Latest Course Offerings' : 'Recently Added Courses' ?></h2>
                <p class="section-description">
                    <?= isset($_SESSION['user_id']) ? 'Discover new courses to enhance your academic journey' : 'Explore our latest course offerings and expand your knowledge' ?>
                </p>
            </div>
            <div class="courses-grid">
                <?php foreach ($recentCourses as $course): ?>
                <div class="course-card">
                    <div class="course-header">
                        <div class="course-badge">
                            <?= htmlspecialchars($course['course_code']) ?>
                        </div>
                        <div class="course-credits">
                            <?= htmlspecialchars($course['credits']) ?> Credits
                        </div>
                    </div>
                    <div class="course-content">
                        <h3><?= htmlspecialchars($course['course_name']) ?></h3>
                        <p class="course-description">
                            <?= htmlspecialchars(strlen($course['description']) > 120 ? substr($course['description'], 0, 120) . '...' : $course['description']) ?>
                        </p>
                        <div class="course-details">
                            <div class="course-detail">
                                <div class="icon icon-calendar text-primary"></div>
                                <span><?= htmlspecialchars($course['semester']) ?></span>
                            </div>
                            <div class="course-detail">
                                <div class="icon icon-profile text-success"></div>
                                <span><?= htmlspecialchars($course['instructor']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="course-actions">
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'student'): ?>
                            <?php
                            // Check if student is already enrolled
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id = ? AND course_id = ?");
                            $stmt->execute([$_SESSION['user_id'], $course['id']]);
                            $isEnrolled = $stmt->fetchColumn() > 0;
                            ?>
                            <?php if ($isEnrolled): ?>
                                <a href="student_courses.php" class="btn btn-success btn-sm">
                                    <div class="icon icon-check btn-icon"></div>
                                    <span>Enrolled</span>
                                </a>
                            <?php else: ?>
                                <a href="courses.php" class="btn btn-primary btn-sm">
                                    <div class="icon icon-plus btn-icon"></div>
                                    <span>Enroll Now</span>
                                </a>
                            <?php endif; ?>
                        <?php elseif (isset($_SESSION['user_id'])): ?>
                            <a href="courses.php" class="btn btn-primary btn-sm">
                                <div class="icon icon-eye btn-icon"></div>
                                <span>View Details</span>
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-secondary btn-sm">
                                <div class="icon icon-login btn-icon"></div>
                                <span>Login to Enroll</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-8">
                <a href="courses.php" class="btn btn-outline-primary">
                    <div class="icon icon-courses btn-icon"></div>
                    <span><?= isset($_SESSION['user_id']) ? 'View All Courses' : 'Browse Course Catalog' ?></span>
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

  <!-- Testimonials -->
  <section class="testimonials-section">
    <div class="container">
      <div class="section-header text-center">
        <h2>What Our Students Say</h2>
        <p class="section-description">
          Hear from our students about their experience at AUCA
        </p>
      </div>
      <div class="testimonials-grid">
        <div class="testimonial-card">
          <div class="testimonial-content">
            <div class="testimonial-quote">
              <div class="icon icon-quote text-primary"></div>
              <p>"The Student Portal has revolutionized how I manage my academic life. Everything I need is right at my fingertips, from course enrollment to tracking my progress."</p>
            </div>
            <div class="testimonial-author">
              <div class="author-avatar">
                <div class="icon icon-profile"></div>
              </div>
              <div class="author-info">
                <h4>Sarah Mukamana</h4>
                <span>Business Administration, Class of 2024</span>
              </div>
            </div>
          </div>
        </div>
        <div class="testimonial-card">
          <div class="testimonial-content">
            <div class="testimonial-quote">
              <div class="icon icon-quote text-success"></div>
              <p>"AUCA has provided me with not just academic knowledge, but also spiritual growth and leadership skills that I'll carry throughout my career."</p>
            </div>
            <div class="testimonial-author">
              <div class="author-avatar">
                <div class="icon icon-profile"></div>
              </div>
              <div class="author-info">
                <h4>Jean-Claude Niyomugabo</h4>
                <span>Theology, Class of 2023</span>
              </div>
            </div>
          </div>
        </div>
        <div class="testimonial-card">
          <div class="testimonial-content">
            <div class="testimonial-quote">
              <div class="icon icon-quote text-warning"></div>
              <p>"The online portal makes it easy to stay connected with my professors and classmates. I can access my grades, assignments, and communicate effectively."</p>
            </div>
            <div class="testimonial-author">
              <div class="author-avatar">
                <div class="icon icon-profile"></div>
              </div>
              <div class="author-info">
                <h4>Grace Nkurunziza</h4>
                <span>Nursing, Class of 2025</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Quick Access -->
  <section class="quick-access">
    <div class="container">
      <div class="section-header text-center">
        <h2>Quick Access</h2>
        <p class="section-description">
          <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
              Administrative tools and management features for efficient portal administration
            <?php else: ?>
              Your personalized dashboard and academic tools for success
            <?php endif; ?>
          <?php else: ?>
            Common tasks and important links for students, faculty, and visitors
          <?php endif; ?>
        </p>
      </div>
      
      <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
        <!-- Admin Quick Access -->
        <div class="admin-quick-access">
          <div class="admin-stats-grid">
            <div class="admin-stat-card">
              <div class="stat-icon">
                <div class="icon icon-users text-primary"></div>
              </div>
              <div class="stat-content">
                <h3><?= number_format($totalStudents) ?></h3>
                <p>Total Students</p>
              </div>
            </div>
            <div class="admin-stat-card">
              <div class="stat-icon">
                <div class="icon icon-courses text-success"></div>
              </div>
              <div class="stat-content">
                <h3><?= number_format($totalCourses) ?></h3>
                <p>Active Courses</p>
              </div>
            </div>
            <div class="admin-stat-card">
              <div class="stat-icon">
                <div class="icon icon-graduation text-warning"></div>
              </div>
              <div class="stat-content">
                <h3><?= number_format($totalEnrollments) ?></h3>
                <p>Total Enrollments</p>
              </div>
            </div>
          </div>
          
          <div class="admin-actions-grid">
            <div class="admin-action-card">
              <div class="action-icon">
                <div class="icon icon-admin text-primary"></div>
              </div>
              <div class="action-content">
                <h3>Administration</h3>
                <p>Complete portal management and oversight</p>
                <a href="admin_dashboard.php" class="btn btn-primary">
                  <div class="icon icon-dashboard btn-icon"></div>
                  <span>Admin Dashboard</span>
                </a>
              </div>
            </div>
            <div class="admin-action-card">
              <div class="action-icon">
                <div class="icon icon-courses text-success"></div>
              </div>
              <div class="action-content">
                <h3>Course Management</h3>
                <p>Create, modify, and oversee academic programs</p>
                <a href="admin_courses.php" class="btn btn-success">
                  <div class="icon icon-courses btn-icon"></div>
                  <span>Manage Courses</span>
                </a>
              </div>
            </div>
            <div class="admin-action-card">
              <div class="action-icon">
                <div class="icon icon-users text-warning"></div>
              </div>
              <div class="action-content">
                <h3>User Management</h3>
                <p>Manage student accounts and permissions</p>
                <a href="admin_user_roles.php" class="btn btn-warning">
                  <div class="icon icon-users btn-icon"></div>
                  <span>Manage Users</span>
                </a>
              </div>
            </div>
          </div>
        </div>
        
      <?php elseif (isset($_SESSION['user_id'])): ?>
        <!-- Student Quick Access -->
        <div class="student-quick-access">
          <div class="student-welcome">
            <h3>Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h3>
            <p>Continue your academic journey with these quick actions</p>
          </div>
          
          <div class="student-actions-grid">
            <div class="student-action-card">
              <div class="action-icon">
                <div class="icon icon-dashboard text-primary"></div>
              </div>
              <div class="action-content">
                <h3>My Dashboard</h3>
                <p>View your academic progress and recent activities</p>
                <div class="action-meta">
                  <span class="status-badge status-active">Active</span>
                </div>
              </div>
              <a href="student_dashboard.php" class="btn btn-primary">
                <div class="icon icon-dashboard btn-icon"></div>
                <span>Go to Dashboard</span>
              </a>
            </div>
            
            <div class="student-action-card">
              <div class="action-icon">
                <div class="icon icon-courses text-success"></div>
              </div>
              <div class="action-content">
                <h3>Course Catalog</h3>
                <p>Browse and enroll in available courses</p>
                <div class="action-meta">
                  <span class="status-badge status-info"><?= $totalCourses ?> Available</span>
                </div>
              </div>
              <a href="courses.php" class="btn btn-success">
                <div class="icon icon-courses btn-icon"></div>
                <span>Browse Courses</span>
              </a>
            </div>
            
            <div class="student-action-card">
              <div class="action-icon">
                <div class="icon icon-graduation text-warning"></div>
              </div>
              <div class="action-content">
                <h3>My Courses</h3>
                <p>Access your enrolled courses and materials</p>
                <div class="action-meta">
                  <?php
                  $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id = ?");
                  $stmt->execute([$_SESSION['user_id']]);
                  $enrolledCount = $stmt->fetchColumn();
                  ?>
                  <span class="status-badge status-warning"><?= $enrolledCount ?> Enrolled</span>
                </div>
              </div>
              <a href="student_courses.php" class="btn btn-warning">
                <div class="icon icon-graduation btn-icon"></div>
                <span>View My Courses</span>
              </a>
            </div>
            
            <div class="student-action-card">
              <div class="action-icon">
                <div class="icon icon-profile text-info"></div>
              </div>
              <div class="action-content">
                <h3>My Profile</h3>
                <p>Update your personal information and settings</p>
                <div class="action-meta">
                  <span class="status-badge status-info">Personal</span>
                </div>
              </div>
              <a href="profile.php" class="btn btn-info">
                <div class="icon icon-profile btn-icon"></div>
                <span>Edit Profile</span>
              </a>
            </div>
          </div>
        </div>
        
      <?php else: ?>
        <!-- Guest Quick Access -->
        <div class="quick-access-grid">
          <div class="access-category">
            <h3>For Prospective Students</h3>
            <div class="access-links">
              <a href="signup.php" class="access-link">
                <div class="icon icon-user-plus"></div>
                <span>Apply for Admission</span>
              </a>
              <a href="courses.php" class="access-link">
                <div class="icon icon-courses"></div>
                <span>Browse Academic Programs</span>
              </a>
              <a href="#" class="access-link">
                <div class="icon icon-info"></div>
                <span>Admission Requirements</span>
              </a>
              <a href="#" class="access-link">
                <div class="icon icon-calendar"></div>
                <span>Academic Calendar</span>
              </a>
            </div>
          </div>
          
          <div class="access-category">
            <h3>For Current Students</h3>
            <div class="access-links">
              <a href="login.php" class="access-link">
                <div class="icon icon-login"></div>
                <span>Student Portal Login</span>
              </a>
              <a href="forgot_password.php" class="access-link">
                <div class="icon icon-lock"></div>
                <span>Reset Password</span>
              </a>
              <a href="#" class="access-link">
                <div class="icon icon-mail"></div>
                <span>Student Support</span>
              </a>
              <a href="#" class="access-link">
                <div class="icon icon-book"></div>
                <span>Library Resources</span>
              </a>
            </div>
          </div>
          
          <div class="access-category">
            <h3>For Faculty & Staff</h3>
            <div class="access-links">
              <a href="login.php" class="access-link">
                <div class="icon icon-admin"></div>
                <span>Faculty Portal</span>
              </a>
              <a href="#" class="access-link">
                <div class="icon icon-chart"></div>
                <span>Academic Reports</span>
              </a>
              <a href="#" class="access-link">
                <div class="icon icon-settings"></div>
                <span>System Administration</span>
              </a>
              <a href="#" class="access-link">
                <div class="icon icon-help"></div>
                <span>Faculty Resources</span>
              </a>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <div class="footer-content">
        <div class="footer-section">
          <div class="footer-logo">
            <img src="assets/images/logo.png" alt="AUCA Logo" class="footer-logo-img">
            <div class="footer-logo-text">
              <h3>Adventist University of Central Africa</h3>
              <p>Empowering minds, transforming lives</p>
            </div>
          </div>
          <div class="footer-description">
            <p>A leading institution of higher learning in Central Africa, committed to academic excellence, spiritual growth, and service to humanity.</p>
          </div>
        </div>
        <div class="footer-section">
          <h4>Quick Links</h4>
          <ul class="footer-links">
            <li><a href="#">About AUCA</a></li>
            <li><a href="#">Academic Programs</a></li>
            <li><a href="#">Admissions</a></li>
            <li><a href="#">Student Life</a></li>
            <li><a href="#">Research</a></li>
          </ul>
        </div>
        <div class="footer-section">
          <h4>Student Services</h4>
          <ul class="footer-links">
            <li><a href="login.php">Student Portal</a></li>
            <li><a href="#">Library</a></li>
            <li><a href="#">Academic Support</a></li>
            <li><a href="#">Career Services</a></li>
            <li><a href="#">Health Services</a></li>
          </ul>
        </div>
        <div class="footer-section">
          <h4>Contact Information</h4>
          <div class="contact-info">
            <div class="contact-item">
              <div class="icon icon-location"></div>
              <span>Kigali, Rwanda</span>
            </div>
            <div class="contact-item">
              <div class="icon icon-phone"></div>
              <span>+250 788 123 456</span>
            </div>
            <div class="contact-item">
              <div class="icon icon-mail"></div>
              <span>info@auca.ac.rw</span>
            </div>
            <div class="contact-item">
              <div class="icon icon-globe"></div>
              <span>www.auca.ac.rw</span>
            </div>
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        <div class="footer-bottom-content">
          <p>&copy; <?= date('Y') ?> Adventist University of Central Africa. All rights reserved.</p>
          <div class="footer-bottom-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
            <a href="#">Accessibility</a>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <script src="assets/js/app.js"></script>
  <script>
    // Homepage specific JavaScript
    document.addEventListener('DOMContentLoaded', function() {
      // Animate hero stats on scroll
      const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const stats = entry.target.querySelectorAll('.stat-number');
            stats.forEach(stat => {
              const finalValue = parseInt(stat.textContent.replace(/[^\d]/g, ''));
              animateCounter(stat, finalValue);
            });
          }
        });
      });

      const heroStats = document.querySelector('.hero-stats');
      if (heroStats) {
        statsObserver.observe(heroStats);
      }

      // Animate feature cards on scroll
      const featureObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
          }
        });
      }, { threshold: 0.1 });

      document.querySelectorAll('.feature-card, .overview-card, .testimonial-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'all 0.6s ease';
        featureObserver.observe(card);
      });

      // Parallax effect for hero background
      window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const heroImage = document.querySelector('.hero-image');
        if (heroImage) {
          heroImage.style.transform = `translateY(${scrolled * 0.5}px)`;
        }
      });
    });

    // Counter animation function
    function animateCounter(element, target) {
      let current = 0;
      const increment = target / 50;
      const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
          element.textContent = target.toLocaleString() + '+';
          clearInterval(timer);
        } else {
          element.textContent = Math.floor(current).toLocaleString() + '+';
        }
      }, 50);
    }
  </script>
</body>
</html>

<style>
/* Homepage Specific Styles */
.hero-section {
  position: relative;
  height: 100vh;
  min-height: 700px;
  display: flex;
  align-items: center;
  overflow: hidden;
}

.hero-background {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: -1;
}

.hero-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  will-change: transform;
}

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    135deg,
    rgba(var(--primary-rgb), 0.8) 0%,
    rgba(var(--primary-rgb), 0.6) 50%,
    rgba(var(--primary-rgb), 0.4) 100%
  );
}

.hero-content {
  position: relative;
  z-index: 1;
  width: 100%;
  color: white;
}

.hero-text {
  text-align: center;
  margin-bottom: var(--spacing-12);
}

.hero-title {
  font-size: clamp(2rem, 5vw, 4rem);
  font-weight: 700;
  margin-bottom: var(--spacing-6);
  line-height: 1.1;
}

.university-name {
  display: block;
  font-size: 0.8em;
  margin-bottom: var(--spacing-2);
}

.portal-subtitle {
  display: block;
  font-size: 0.6em;
  font-weight: 400;
  opacity: 0.9;
}

.hero-description {
  font-size: 1.25rem;
  margin-bottom: var(--spacing-8);
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
  opacity: 0.95;
}

.hero-actions {
  display: flex;
  gap: var(--spacing-4);
  justify-content: center;
  flex-wrap: wrap;
}

.hero-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: var(--spacing-6);
  margin-top: var(--spacing-12);
}

.stat-item {
  text-align: center;
  padding: var(--spacing-4);
  background: rgba(255, 255, 255, 0.1);
  border-radius: var(--border-radius);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.stat-number {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: var(--spacing-2);
  color: white;
}

.stat-label {
  font-size: 0.9rem;
  opacity: 0.9;
  color: white;
}

.university-overview {
  padding: var(--spacing-16) 0;
  background: var(--gray-50);
}

.overview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: var(--spacing-8);
  margin-top: var(--spacing-12);
}

.overview-card {
  background: white;
  padding: var(--spacing-8);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-sm);
  transition: transform var(--transition-fast), box-shadow var(--transition-fast);
}

.overview-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-lg);
}

.overview-card .card-icon {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: var(--gray-100);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: var(--spacing-6);
}

.overview-card .card-icon .icon {
  font-size: 24px;
}

.features-section {
  padding: var(--spacing-16) 0;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: var(--spacing-8);
  margin-top: var(--spacing-12);
}

.feature-card {
  background: white;
  padding: var(--spacing-8);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--gray-200);
  transition: all var(--transition-fast);
}

.feature-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-lg);
  border-color: var(--primary-color);
}

.feature-icon {
  width: 70px;
  height: 70px;
  border-radius: var(--border-radius-lg);
  background: var(--gray-100);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: var(--spacing-6);
}

.feature-icon .icon {
  font-size: 28px;
}

.feature-list {
  list-style: none;
  padding: 0;
  margin: var(--spacing-4) 0 0;
}

.feature-list li {
  padding: var(--spacing-2) 0;
  color: var(--gray-600);
  position: relative;
  padding-left: var(--spacing-6);
}

.feature-list li:before {
  content: "✓";
  position: absolute;
  left: 0;
  color: var(--success-color);
  font-weight: bold;
}

.recent-courses {
  padding: var(--spacing-16) 0;
  background: var(--gray-50);
}

.courses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: var(--spacing-6);
  margin-top: var(--spacing-12);
}

.course-card {
  background: white;
  border-radius: var(--border-radius-lg);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  transition: transform var(--transition-fast), box-shadow var(--transition-fast);
}

.course-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-lg);
}

.course-header {
  padding: var(--spacing-4);
  background: var(--primary-color);
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.course-badge {
  background: rgba(255, 255, 255, 0.2);
  padding: var(--spacing-1) var(--spacing-3);
  border-radius: var(--border-radius);
  font-size: 0.8rem;
  font-weight: 600;
}

.course-credits {
  font-size: 0.9rem;
  opacity: 0.9;
}

.course-content {
  padding: var(--spacing-6);
}

.course-description {
  color: var(--gray-600);
  margin: var(--spacing-4) 0;
}

.course-details {
  display: flex;
  gap: var(--spacing-4);
  margin: var(--spacing-4) 0;
}

.course-detail {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
  font-size: 0.9rem;
  color: var(--gray-600);
}

.course-actions {
  padding: var(--spacing-4) var(--spacing-6);
  border-top: 1px solid var(--gray-200);
}

.testimonials-section {
  padding: var(--spacing-16) 0;
  background: var(--primary-color);
  color: white;
}

.testimonials-section .section-header h2,
.testimonials-section .section-description {
  color: white;
}

.testimonials-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: var(--spacing-8);
  margin-top: var(--spacing-12);
}

.testimonial-card {
  background: rgba(255, 255, 255, 0.1);
  padding: var(--spacing-8);
  border-radius: var(--border-radius-lg);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.testimonial-quote {
  margin-bottom: var(--spacing-6);
}

.testimonial-quote .icon {
  font-size: 24px;
  margin-bottom: var(--spacing-4);
}

.testimonial-author {
  display: flex;
  align-items: center;
  gap: var(--spacing-4);
}

.author-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
}

.author-info h4 {
  margin: 0;
  font-weight: 600;
}

.author-info span {
  font-size: 0.9rem;
  opacity: 0.8;
}

.quick-access {
  padding: var(--spacing-16) 0;
  background: var(--gray-50);
}

.quick-access-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: var(--spacing-8);
  margin-top: var(--spacing-12);
}

.access-category {
  background: white;
  padding: var(--spacing-8);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-sm);
}

.access-category h3 {
  color: var(--primary-color);
  margin-bottom: var(--spacing-6);
  font-size: 1.25rem;
}

.access-links {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-3);
}

.access-link {
  display: flex;
  align-items: center;
  gap: var(--spacing-3);
  padding: var(--spacing-3);
  text-decoration: none;
  color: var(--gray-700);
  border-radius: var(--border-radius);
  transition: all var(--transition-fast);
}

.access-link:hover {
  background: var(--gray-100);
  color: var(--primary-color);
}

/* Admin Quick Access Styles */
.admin-quick-access {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-12);
}

.admin-stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: var(--spacing-6);
}

.admin-stat-card {
  background: white;
  padding: var(--spacing-6);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-sm);
  display: flex;
  align-items: center;
  gap: var(--spacing-4);
  transition: transform var(--transition-fast), box-shadow var(--transition-fast);
}

.admin-stat-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.admin-stat-card .stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: var(--gray-100);
  display: flex;
  align-items: center;
  justify-content: center;
}

.admin-stat-card .stat-icon .icon {
  font-size: 24px;
}

.admin-stat-card .stat-content h3 {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
  color: var(--gray-900);
}

.admin-stat-card .stat-content p {
  margin: 0;
  color: var(--gray-600);
  font-size: 0.9rem;
}

.admin-actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: var(--spacing-8);
}

.admin-action-card {
  background: white;
  padding: var(--spacing-8);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-sm);
  display: flex;
  flex-direction: column;
  gap: var(--spacing-6);
  transition: transform var(--transition-fast), box-shadow var(--transition-fast);
}

.admin-action-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-lg);
}

.admin-action-card .action-icon {
  width: 70px;
  height: 70px;
  border-radius: var(--border-radius-lg);
  background: var(--gray-100);
  display: flex;
  align-items: center;
  justify-content: center;
}

.admin-action-card .action-icon .icon {
  font-size: 28px;
}

.admin-action-card .action-content h3 {
  font-size: 1.5rem;
  margin: 0;
  color: var(--gray-900);
}

.admin-action-card .action-content p {
  margin: 0;
  color: var(--gray-600);
  line-height: 1.5;
}

/* Student Quick Access Styles */
.student-quick-access {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-12);
}

.student-welcome {
  text-align: center;
  padding: var(--spacing-8);
  background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
  color: white;
  border-radius: var(--border-radius-lg);
  margin-bottom: var(--spacing-8);
}

.student-welcome h3 {
  font-size: 1.75rem;
  margin: 0 0 var(--spacing-4) 0;
  color: white;
}

.student-welcome p {
  margin: 0;
  opacity: 0.9;
  font-size: 1.1rem;
}

.student-actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: var(--spacing-6);
}

.student-action-card {
  background: white;
  padding: var(--spacing-6);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-sm);
  display: flex;
  flex-direction: column;
  gap: var(--spacing-4);
  transition: transform var(--transition-fast), box-shadow var(--transition-fast);
}

.student-action-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-lg);
}

.student-action-card .action-icon {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: var(--gray-100);
  display: flex;
  align-items: center;
  justify-content: center;
}

.student-action-card .action-icon .icon {
  font-size: 24px;
}

.student-action-card .action-content h3 {
  font-size: 1.25rem;
  margin: 0;
  color: var(--gray-900);
}

.student-action-card .action-content p {
  margin: 0;
  color: var(--gray-600);
  font-size: 0.9rem;
  line-height: 1.4;
}

.student-action-card .action-meta {
  margin-top: var(--spacing-2);
}

.status-badge {
  display: inline-block;
  padding: var(--spacing-1) var(--spacing-2);
  border-radius: var(--border-radius);
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.status-badge.status-active {
  background: var(--success-light);
  color: var(--success-dark);
}

.status-badge.status-info {
  background: var(--info-light);
  color: var(--info-dark);
}

.status-badge.status-warning {
  background: var(--warning-light);
  color: var(--warning-dark);
}

.footer {
  background: var(--gray-900);
  color: white;
  padding: var(--spacing-16) 0 var(--spacing-8);
}

.footer-content {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: var(--spacing-8);
  margin-bottom: var(--spacing-8);
}

.footer-logo {
  display: flex;
  align-items: center;
  gap: var(--spacing-4);
  margin-bottom: var(--spacing-6);
}

.footer-logo-img {
  width: 50px;
  height: 50px;
}

.footer-logo-text h3 {
  margin: 0;
  font-size: 1.1rem;
}

.footer-logo-text p {
  margin: 0;
  font-size: 0.9rem;
  opacity: 0.8;
}

.footer-description {
  color: var(--gray-400);
  line-height: 1.6;
}

.footer-section h4 {
  color: white;
  margin-bottom: var(--spacing-4);
  font-size: 1.1rem;
}

.footer-links {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-links li {
  margin-bottom: var(--spacing-2);
}

.footer-links a {
  color: var(--gray-400);
  text-decoration: none;
  transition: color var(--transition-fast);
}

.footer-links a:hover {
  color: white;
}

.contact-info {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-3);
}

.contact-item {
  display: flex;
  align-items: center;
  gap: var(--spacing-3);
  color: var(--gray-400);
}

.contact-item .icon {
  color: var(--primary-color);
}

.footer-bottom {
  border-top: 1px solid var(--gray-800);
  padding-top: var(--spacing-8);
}

.footer-bottom-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: var(--spacing-4);
}

.footer-bottom-links {
  display: flex;
  gap: var(--spacing-6);
}

.footer-bottom-links a {
  color: var(--gray-400);
  text-decoration: none;
  font-size: 0.9rem;
  transition: color var(--transition-fast);
}

.footer-bottom-links a:hover {
  color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
  .hero-title {
    font-size: 2.5rem;
  }
  
  .hero-description {
    font-size: 1.1rem;
  }
  
  .hero-actions {
    flex-direction: column;
    align-items: center;
  }
  
  .hero-stats {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .stat-number {
    font-size: 2rem;
  }
  
  .overview-grid,
  .features-grid,
  .courses-grid,
  .testimonials-grid {
    grid-template-columns: 1fr;
  }
  
  .footer-bottom-content {
    flex-direction: column;
    text-align: center;
  }
}
</style>
