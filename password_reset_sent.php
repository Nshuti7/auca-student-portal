<?php
// password_reset_sent.php — Confirmation page for password reset request
session_start();

// Check if this is development mode
$is_dev_mode = isset($_GET['dev']) && isset($_SESSION['dev_reset_link']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Sent - Student Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <img src="assets/images/logo.png" alt="Student Portal Logo" class="logo">
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="courses.php">Courses</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php">Sign Up</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="reset-sent-section">
            <div class="success-icon">📧</div>
            <h1>Password Reset Instructions Sent</h1>
            
            <?php if ($is_dev_mode): ?>
                <!-- Development Mode Display -->
                <div class="dev-mode-notice">
                    <h2>🔧 Development Mode</h2>
                    <p>Since you're running on localhost, the reset email was not sent. Instead, use the link below:</p>
                </div>
                
                <div class="reset-details">
                    <h3>Reset Details:</h3>
                    <ul>
                        <li><strong>Email:</strong> <?= htmlspecialchars($_SESSION['dev_reset_email']) ?></li>
                        <li><strong>User:</strong> <?= htmlspecialchars($_SESSION['dev_reset_user']) ?></li>
                        <li><strong>Expires:</strong> <?= htmlspecialchars($_SESSION['dev_reset_expires']) ?></li>
                    </ul>
                </div>
                
                <div class="reset-link-section">
                    <h3>🔗 Your Reset Link:</h3>
                    <div class="reset-link">
                        <a href="<?= htmlspecialchars($_SESSION['dev_reset_link']) ?>" class="btn btn-primary">
                            Reset Your Password
                        </a>
                    </div>
                    <p><small>This link expires in 1 hour</small></p>
                </div>
                
                <?php
                // Clear the session variables after displaying
                unset($_SESSION['dev_reset_link']);
                unset($_SESSION['dev_reset_email']);
                unset($_SESSION['dev_reset_user']);
                unset($_SESSION['dev_reset_expires']);
                ?>
                
            <?php else: ?>
                <!-- Production Mode Display -->
                <div class="instructions">
                    <p>We've sent password reset instructions to your email address.</p>
                    <p>Please check your email and click the reset link to create a new password.</p>
                </div>
                
                <div class="next-steps">
                    <h3>What's Next?</h3>
                    <ol>
                        <li>Check your email inbox (and spam folder)</li>
                        <li>Click the password reset link in the email</li>
                        <li>Create a new password</li>
                        <li>Log in with your new password</li>
                    </ol>
                </div>
            <?php endif; ?>
            
            <div class="help-section">
                <h3>❓ Didn't receive the email?</h3>
                <ul>
                    <li>Check your spam/junk folder</li>
                    <li>Make sure you entered the correct email address</li>
                    <li>Wait a few minutes for the email to arrive</li>
                    <li>Try requesting another reset</li>
                </ul>
                
                <div class="action-buttons">
                    <a href="forgot_password.php" class="btn btn-secondary">Request Another Reset</a>
                    <a href="login.php" class="btn btn-outline">Back to Login</a>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> Student Portal. All rights reserved.</p>
    </footer>
</body>
</html>

<style>
.reset-sent-section {
    max-width: 600px;
    margin: 40px auto;
    padding: 20px;
    text-align: center;
}

.success-icon {
    font-size: 4em;
    margin-bottom: 20px;
}

.reset-sent-section h1 {
    color: #333;
    margin-bottom: 30px;
}

.dev-mode-notice {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
}

.dev-mode-notice h2 {
    margin-top: 0;
    color: #856404;
}

.reset-details {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
    text-align: left;
}

.reset-details ul {
    list-style: none;
    padding: 0;
}

.reset-details li {
    margin: 10px 0;
    padding: 8px;
    background: white;
    border-radius: 4px;
}

.reset-link-section {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
}

.reset-link {
    margin: 15px 0;
}

.instructions {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
}

.next-steps {
    text-align: left;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
}

.next-steps ol {
    padding-left: 20px;
}

.next-steps li {
    margin: 10px 0;
    color: #666;
}

.help-section {
    text-align: left;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
}

.help-section h3 {
    margin-top: 0;
    text-align: center;
}

.help-section ul {
    padding-left: 20px;
}

.help-section li {
    margin: 8px 0;
    color: #666;
}

.action-buttons {
    text-align: center;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #dee2e6;
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
    .reset-sent-section {
        margin: 20px auto;
        padding: 15px;
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