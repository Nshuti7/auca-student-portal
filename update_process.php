<?php
// update_process.php — handle edit-profile submission
session_start();
require __DIR__ . '/includes/db.php';

if (empty($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_SESSION['student_id'];
$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email']     ?? '');
$phone     = trim($_POST['phone']     ?? '');
$address   = trim($_POST['address']   ?? '');
$password  = $_POST['password']       ?? '';
$confirm   = $_POST['confirm_password'] ?? '';

$errors = [];

// Validation
if ($full_name === '') {
    $errors[] = 'Full name cannot be empty.';
}
if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Enter a valid email address.';
}
if ($phone !== '' && !preg_match('/^[\+]?[0-9\(\)\-\s\.]+$/', $phone)) {
    $errors[] = 'Enter a valid phone number.';
}
if ($password !== '' && strlen($password) < 6) {
    $errors[] = 'New password must be at least 6 characters.';
}
if ($password !== $confirm) {
    $errors[] = 'New passwords do not match.';
}

if ($errors) {
    echo '<h2>Errors:</h2><ul>';
    foreach ($errors as $e) {
        echo '<li>' . htmlspecialchars($e) . '</li>';
    }
    echo '</ul><p><a href="update.php">Go back</a></p>';
    exit;
}

try {
    // Start building the SQL
    $sql = 'UPDATE students SET full_name = :full_name, email = :email, phone = :phone, address = :address';
    $params = [
        ':full_name' => $full_name,
        ':email'     => $email,
        ':phone'     => $phone !== '' ? $phone : null,
        ':address'   => $address !== '' ? $address : null,
        ':id'        => $id,
    ];

    // If password provided, include it
    if ($password !== '') {
        $sql .= ', password_hash = :password_hash';
        $params[':password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    }
    $sql .= ' WHERE id = :id';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Log profile update activity
    try {
        require_once __DIR__ . '/includes/activity_logger.php';
        logStudentActivity($pdo, $id, 'profile_update', 'Updated profile information');
    } catch (Exception $e) {
        // Silent fail for activity logging
    }

    // Optionally refresh session name
    $_SESSION['student_name'] = $full_name;

    header('Location: profile.php?success=Profile updated successfully!');
    exit;

} catch (PDOException $e) {
    if ($e->errorInfo[1] === 1062) {
        echo '<p>Email already in use by another account.</p>';
        echo '<p><a href="update.php">Try again</a></p>';
    } else {
        die('Database error: ' . htmlspecialchars($e->getMessage()));
    }
}
