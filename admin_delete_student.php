<?php
// admin_delete_student.php — Delete student functionality
session_start();
require __DIR__ . '/includes/db.php';

// Check if user is logged in and is admin
if (empty($_SESSION['student_id']) || $_SESSION['student_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$student_id = $_GET['id'] ?? 0;

if (!$student_id) {
    header('Location: admin_dashboard.php');
    exit;
}

// Prevent admin from deleting themselves
if ($student_id == $_SESSION['student_id']) {
    header('Location: admin_dashboard.php?error=cannot_delete_self');
    exit;
}

try {
    // Get student info before deletion
    $stmt = $pdo->prepare("SELECT full_name, email, username FROM students WHERE id = :id");
    $stmt->execute([':id' => $student_id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        header('Location: admin_dashboard.php?error=student_not_found');
        exit;
    }
    
    // Delete the student
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = :id");
    $stmt->execute([':id' => $student_id]);
    
    // Redirect back to admin dashboard with success message
    header('Location: admin_dashboard.php?success=student_deleted&name=' . urlencode($student['full_name']));
    exit;
    
} catch (PDOException $e) {
    // Redirect back with error
    header('Location: admin_dashboard.php?error=database_error');
    exit;
}
?> 