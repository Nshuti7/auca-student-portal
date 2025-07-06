<?php
// process_reset.php — Process password reset
session_start();
require __DIR__ . '/includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['student_id'])) {
    header('Location: profile.php');
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot_password.php?error=Invalid request method');
    exit;
}

// Validate input
$token = trim($_POST['token'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($token) || empty($password) || empty($confirm_password)) {
    header('Location: reset_password.php?token=' . urlencode($token) . '&error=All fields are required');
    exit;
}

// Validate password match
if ($password !== $confirm_password) {
    header('Location: reset_password.php?token=' . urlencode($token) . '&error=Passwords do not match');
    exit;
}

// Validate password strength
if (strlen($password) < 8) {
    header('Location: reset_password.php?token=' . urlencode($token) . '&error=Password must be at least 8 characters long');
    exit;
}

try {
    // Validate token and get user information
    $stmt = $pdo->prepare("
        SELECT pr.email, pr.expires_at, s.id, s.full_name 
        FROM password_resets pr
        JOIN students s ON pr.email = s.email
        WHERE pr.token = :token AND pr.used = FALSE AND pr.expires_at > :current_time
    ");
    $stmt->execute([
        ':token' => $token,
        ':current_time' => date('Y-m-d H:i:s')
    ]);
    $reset_data = $stmt->fetch();
    
    if (!$reset_data) {
        header('Location: forgot_password.php?error=Invalid or expired reset token. Please request a new password reset');
        exit;
    }
    
    // Hash the new password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Update user's password
        $stmt = $pdo->prepare("UPDATE students SET password_hash = :password_hash WHERE email = :email");
        $stmt->execute([
            ':password_hash' => $password_hash,
            ':email' => $reset_data['email']
        ]);
        
        // Mark the reset token as used
        $stmt = $pdo->prepare("UPDATE password_resets SET used = TRUE WHERE token = :token");
        $stmt->execute([':token' => $token]);
        
        // Clean up old expired tokens
        $pdo->exec("DELETE FROM password_resets WHERE expires_at < NOW() OR used = TRUE");
        
        // Commit transaction
        $pdo->commit();
        
        // Log the successful reset
        $log_dir = __DIR__ . '/logs';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $log_entry = date('Y-m-d H:i:s') . " - Password reset completed for: {$reset_data['email']} (User: {$reset_data['full_name']})\n";
        file_put_contents($log_dir . '/password_resets.log', $log_entry, FILE_APPEND | LOCK_EX);
        
        // Auto-login the user
        $_SESSION['student_id'] = $reset_data['id'];
        $_SESSION['student_role'] = 'student'; // Will be updated by login logic if admin
        
        // Get user role for session
        $stmt = $pdo->prepare("SELECT role FROM students WHERE id = :id");
        $stmt->execute([':id' => $reset_data['id']]);
        $user_role = $stmt->fetchColumn();
        $_SESSION['student_role'] = $user_role;
        
        // Redirect to success page
        header('Location: password_reset_success.php');
        exit;
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollback();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log('Password reset processing error: ' . $e->getMessage());
    header('Location: reset_password.php?token=' . urlencode($token) . '&error=A system error occurred. Please try again');
    exit;
}
?> 