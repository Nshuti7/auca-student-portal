<?php
// send_reset_email.php — Process password reset request and send email
session_start();
require __DIR__ . '/includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['student_id'])) {
    header('Location: profile.php');
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['email'])) {
    header('Location: forgot_password.php?error=Please provide an email address');
    exit;
}

$email = trim($_POST['email']);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: forgot_password.php?error=Please provide a valid email address&email=' . urlencode($email));
    exit;
}

try {
    // Check if user exists with this email
    $stmt = $pdo->prepare("SELECT id, full_name FROM students WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Don't reveal whether email exists or not for security
        header('Location: forgot_password.php?success=If an account with that email exists, you will receive password reset instructions');
        exit;
    }
    
    // Generate secure reset token
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 hour from now
    
    // Clean up old tokens for this email
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = :email");
    $stmt->execute([':email' => $email]);
    
    // Insert new reset token
    $stmt = $pdo->prepare("
        INSERT INTO password_resets (email, token, expires_at) 
        VALUES (:email, :token, :expires_at)
    ");
    $stmt->execute([
        ':email' => $email,
        ':token' => $token,
        ':expires_at' => $expires_at
    ]);
    
    // Create reset link
    $reset_link = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . 
                  dirname($_SERVER['REQUEST_URI']) . '/reset_password.php?token=' . $token;
    
    // Log reset attempt
    $log_dir = __DIR__ . '/logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_entry = date('Y-m-d H:i:s') . " - Password reset requested for: {$email} (User: {$user['full_name']}) - Token: {$token}\n";
    file_put_contents($log_dir . '/password_resets.log', $log_entry, FILE_APPEND | LOCK_EX);
    
    // Detect if running on localhost for development mode
    $is_localhost = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1']) || 
                    strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0;
    
    if ($is_localhost) {
        // Development mode - display reset link directly
        session_start();
        $_SESSION['dev_reset_link'] = $reset_link;
        $_SESSION['dev_reset_email'] = $email;
        $_SESSION['dev_reset_user'] = $user['full_name'];
        $_SESSION['dev_reset_expires'] = $expires_at;
        
        header('Location: password_reset_sent.php?dev=1');
        exit;
    } else {
        // Production mode - send actual email
        $subject = 'Password Reset - Student Portal';
        $message = "
Dear {$user['full_name']},

You have requested to reset your password for your Student Portal account.

Click the following link to reset your password:
{$reset_link}

This link will expire in 1 hour for security reasons.

If you did not request this password reset, please ignore this email.

Best regards,
Student Portal Team
        ";
        
        $headers = "From: noreply@studentportal.com\r\n";
        $headers .= "Reply-To: support@studentportal.com\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        if (mail($email, $subject, $message, $headers)) {
            header('Location: forgot_password.php?success=If an account with that email exists, you will receive password reset instructions');
        } else {
            header('Location: forgot_password.php?error=Unable to send email. Please try again later');
        }
        exit;
    }
    
} catch (PDOException $e) {
    error_log('Password reset error: ' . $e->getMessage());
    header('Location: forgot_password.php?error=A system error occurred. Please try again later');
    exit;
}
?> 