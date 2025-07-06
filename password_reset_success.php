<?php
// password_reset_success.php — Password reset success page
session_start();
require __DIR__ . '/includes/db.php';

// Check if user is logged in (should be auto-logged in after reset)
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php?error=Session expired. Please log in with your new password');
    exit;
}

// Get user information
try {
    $stmt = $pdo->prepare('SELECT full_name, email, role FROM students WHERE id = :id');
    $stmt->execute([':id' => $_SESSION['student_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        session_destroy();
        header('Location: login.php?error=User not found. Please log in again');
        exit;
    }
} catch (PDOException $e) {
    session_destroy();
    header('Location: login.php?error=A system error occurred. Please log in again');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Successful - Student Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <img src="assets/images/logo.png" alt="Student Portal Logo" class="logo">
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="courses.php">Courses</a></li>
                <li><a href="profile.php">Profile</a></li>
                <?php if ($user['role'] === 'admin'): ?>
                    <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="success-section">
            <div class="success-icon">🎉</div>
            <h1>Password Reset Successful!</h1>
            <p>Welcome back, <strong><?= htmlspecialchars($user['full_name']) ?></strong>!</p>
            
            <div class="success-details">
                <h3>✅ What Happened:</h3>
                <ul>
                    <li>Your password has been successfully updated</li>
                    <li>You have been automatically logged in</li>
                    <li>The reset link has been invalidated for security</li>
                    <li>Your account is now secure with the new password</li>
                </ul>
            </div>
            
            <div class="next-steps">
                <h3>🚀 What's Next?</h3>
                <div class="step-cards">
                    <div class="step-card">
                        <h4>📋 Update Your Profile</h4>
                        <p>Make sure your account information is up to date</p>
                        <a href="profile.php" class="btn btn-outline">Go to Profile</a>
                    </div>
                    
                    <div class="step-card">
                        <h4>📚 Browse Courses</h4>
                        <p>Explore available courses and continue your learning</p>
                        <a href="courses.php" class="btn btn-outline">View Courses</a>
                    </div>
                    
                    <?php if ($user['role'] === 'admin'): ?>
                    <div class="step-card">
                        <h4>🔧 Admin Dashboard</h4>
                        <p>Manage the portal and oversee student activities</p>
                        <a href="admin_dashboard.php" class="btn btn-outline">Admin Panel</a>
                    </div>
                    <?php else: ?>
                    <div class="step-card">
                        <h4>🎓 My Courses</h4>
                        <p>Check your enrolled courses and academic progress</p>
                        <a href="student_courses.php" class="btn btn-outline">My Courses</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="security-recommendations">
                <h3>🔒 Security Recommendations</h3>
                <div class="recommendations-grid">
                    <div class="recommendation">
                        <h4>🔐 Strong Passwords</h4>
                        <p>Use unique passwords for different accounts and consider using a password manager</p>
                    </div>
                    
                    <div class="recommendation">
                        <h4>📱 Account Security</h4>
                        <p>Regularly review your account activity and update your password if needed</p>
                    </div>
                    
                    <div class="recommendation">
                        <h4>🚨 Stay Alert</h4>
                        <p>Never share your password and be cautious of phishing attempts</p>
                    </div>
                    
                    <div class="recommendation">
                        <h4>💾 Keep Information Updated</h4>
                        <p>Ensure your email address is current for important account notifications</p>
                    </div>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="profile.php" class="btn btn-primary">Continue to Profile</a>
                <a href="index.php" class="btn btn-secondary">Back to Home</a>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> Student Portal. All rights reserved.</p>
    </footer>
</body>
</html>

<style>
.success-section {
    max-width: 800px;
    margin: 40px auto;
    padding: 20px;
    text-align: center;
}

.success-icon {
    font-size: 5em;
    margin-bottom: 20px;
}

.success-section h1 {
    color: #28a745;
    margin-bottom: 10px;
}

.success-section p {
    color: #666;
    margin-bottom: 30px;
    font-size: 18px;
}

.success-details {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
    text-align: left;
}

.success-details h3 {
    margin-top: 0;
    color: #155724;
}

.success-details ul {
    margin: 15px 0;
    padding-left: 20px;
}

.success-details li {
    margin: 10px 0;
    color: #155724;
}

.next-steps {
    margin: 30px 0;
}

.next-steps h3 {
    color: #333;
    margin-bottom: 20px;
}

.step-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.step-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
}

.step-card h4 {
    margin-top: 0;
    color: #007cba;
}

.step-card p {
    margin: 15px 0;
    color: #666;
    font-size: 14px;
}

.security-recommendations {
    margin: 30px 0;
    text-align: left;
}

.security-recommendations h3 {
    text-align: center;
    color: #333;
    margin-bottom: 20px;
}

.recommendations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.recommendation {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    padding: 15px;
    border-radius: 8px;
}

.recommendation h4 {
    margin-top: 0;
    color: #856404;
    font-size: 16px;
}

.recommendation p {
    margin: 10px 0;
    color: #856404;
    font-size: 14px;
}

.action-buttons {
    margin-top: 30px;
}

.btn {
    display: inline-block;
    padding: 12px 24px;
    margin: 5px 10px;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.2s;
}

.btn-primary {
    background: #007cba;
    color: white;
}

.btn-primary:hover {
    background: #005a87;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.btn-outline {
    background: transparent;
    color: #007cba;
    border: 1px solid #007cba;
}

.btn-outline:hover {
    background: #007cba;
    color: white;
}

@media (max-width: 768px) {
    .success-section {
        margin: 20px auto;
        padding: 15px;
    }
    
    .step-cards {
        grid-template-columns: 1fr;
    }
    
    .recommendations-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .btn {
        width: 100%;
        max-width: 250px;
        margin: 5px 0;
    }
}
</style> 