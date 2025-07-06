<?php
// login_process.php — handles login submissions
session_start();
require __DIR__ . '/includes/db.php';

// Collect inputs
$identifier = trim($_POST['username_or_email'] ?? '');
$password   = $_POST['password'] ?? '';

$errors = [];

// Validate inputs
if ($identifier === '') {
    $errors[] = 'Please enter your username or email.';
}
if ($password === '') {
    $errors[] = 'Please enter your password.';
}

if ($errors) {
    $errorMessage = implode(' ', $errors);
    header('Location: login.php?error=' . urlencode($errorMessage));
    exit;
}

try {
    // Fetch user by username OR email
    $stmt = $pdo->prepare('
        SELECT id, full_name, username, email, password_hash, role
        FROM students
        WHERE username = :ident OR email = :ident
        LIMIT 1
    ');
    $stmt->execute([':ident' => $identifier]);
    $student = $stmt->fetch();

    if ($student && password_verify($password, $student['password_hash'])) {
        // Credentials valid - set session variables
        $_SESSION['user_id']   = $student['id'];
        $_SESSION['student_id'] = $student['id']; // Keep for backward compatibility
        $_SESSION['full_name'] = $student['full_name'];
        $_SESSION['student_name'] = $student['full_name']; // Keep for backward compatibility
        $_SESSION['username'] = $student['username'];
        $_SESSION['email'] = $student['email'];
        $_SESSION['role'] = $student['role'];
        $_SESSION['student_role'] = $student['role']; // Keep for backward compatibility
        
        // Log login activity
        try {
            require_once __DIR__ . '/includes/activity_logger.php';
            logStudentActivity($pdo, $student['id'], 'login', 'Logged into the Student Portal');
        } catch (Exception $e) {
            // Silent fail for activity logging
        }
        
        // Redirect based on role
        if ($student['role'] === 'admin') {
            header('Location: admin_dashboard.php?success=' . urlencode('Welcome back, ' . $student['full_name'] . '!'));
        } else {
            header('Location: student_dashboard.php?success=' . urlencode('Welcome back, ' . $student['full_name'] . '!'));
        }
        exit;
    } else {
        // Invalid credentials
        $errorMessage = 'Invalid username/email or password. Please check your credentials and try again.';
        header('Location: login.php?error=' . urlencode($errorMessage));
        exit;
    }

} catch (PDOException $e) {
    error_log('Login database error: ' . $e->getMessage());
    $errorMessage = 'A system error occurred. Please try again later or contact support if the problem persists.';
    header('Location: login.php?error=' . urlencode($errorMessage));
    exit;
}
