<?php
session_start();

// ─────────────────────────────────────────────────────────────────────────────
// 1) Build and verify the path to includes/db.php
$dbPath = __DIR__ . '/includes/db.php';

if (! file_exists($dbPath)) {
    die("❌ Could not find includes/db.php at path: $dbPath");
}

// 2) Load the file
require_once $dbPath;

// 3) Verify $pdo was created successfully
if (! isset($pdo) || ! $pdo instanceof PDO) {
    die("❌ It looks like db.php ran, but \$pdo is not defined or not a PDO instance.");
}
// ─────────────────────────────────────────────────────────────────────────────

// Now the normal signup logic:

// Collect & sanitize inputs
$full_name        = trim($_POST['full_name']        ?? '');
$email            = trim($_POST['email']            ?? '');
$username         = trim($_POST['username']         ?? '');
$password         =            $_POST['password']    ?? '';
$confirm_password =            $_POST['confirm_password'] ?? '';
$phone            = trim($_POST['phone']            ?? '');
$address          = trim($_POST['address']          ?? '');

$errors = [];

// Validate inputs
if ($full_name === '') {
    $errors[] = 'Full name is required.';
}
if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Enter a valid email address.';
}
if ($username === '' || strlen($username) < 3) {
    $errors[] = 'Username must be at least 3 characters.';
}
if (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters.';
}
if ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match.';
}
if ($phone !== '' && !preg_match('/^[\+]?[0-9\(\)\-\s\.]+$/', $phone)) {
    $errors[] = 'Enter a valid phone number.';
}

// If we have validation errors, redirect back with errors
if ($errors) {
    $errorMessage = implode(' ', $errors);
    header('Location: signup.php?error=' . urlencode($errorMessage));
    exit;
}

// Hash the password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // Insert new student into database
    $stmt = $pdo->prepare("
        INSERT INTO students (full_name, email, username, password_hash, phone, address)
        VALUES (:full_name, :email, :username, :password_hash, :phone, :address)
    ");
    $stmt->execute([
        ':full_name'     => $full_name,
        ':email'         => $email,
        ':username'      => $username,
        ':password_hash' => $password_hash,
        ':phone'         => $phone !== '' ? $phone : null,
        ':address'       => $address !== '' ? $address : null,
    ]);

    // Log the user in by setting session vars
    $newUserId = $pdo->lastInsertId();
    $_SESSION['user_id'] = $newUserId;
    $_SESSION['student_id'] = $newUserId; // Keep for backward compatibility
    $_SESSION['full_name'] = $full_name;
    $_SESSION['student_name'] = $full_name; // Keep for backward compatibility
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = 'student'; // New users are students by default
    $_SESSION['student_role'] = 'student'; // Keep for backward compatibility

    // Log signup activity
    try {
        require_once __DIR__ . '/includes/activity_logger.php';
        logStudentActivity($pdo, $newUserId, 'signup', 'Created a new account');
    } catch (Exception $e) {
        // Silent fail for activity logging
    }

    // Redirect to dashboard with success message
    header('Location: student_dashboard.php?success=' . urlencode('Welcome to AUCA, ' . $full_name . '! Your account has been created successfully.'));
    exit;

} catch (PDOException $e) {
    // Handle duplicate email/username (MySQL error code 1062)
    if ($e->errorInfo[1] === 1062) {
        $errorMessage = 'A user with that email or username already exists. Please try different credentials.';
        header('Location: signup.php?error=' . urlencode($errorMessage));
    } else {
        // Other DB errors
        error_log('Signup database error: ' . $e->getMessage());
        $errorMessage = 'A system error occurred during registration. Please try again later or contact support if the problem persists.';
        header('Location: signup.php?error=' . urlencode($errorMessage));
    }
    exit;
}
