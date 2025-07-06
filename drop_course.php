<?php
// drop_course.php — Handle course dropping
session_start();
require __DIR__ . '/includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

// Check if course ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: student_courses.php?error=Invalid course ID');
    exit;
}

$course_id = (int)$_GET['id'];
$student_id = $_SESSION['student_id'];

try {
    // Check if student is enrolled in this course
    $stmt = $pdo->prepare("
        SELECT e.*, c.course_name 
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        WHERE e.student_id = :student_id AND e.course_id = :course_id AND e.status = 'enrolled'
    ");
    $stmt->execute([':student_id' => $student_id, ':course_id' => $course_id]);
    $enrollment = $stmt->fetch();
    
    if (!$enrollment) {
        header('Location: student_courses.php?error=You are not enrolled in this course');
        exit;
    }
    
    // Update enrollment status to dropped
    $stmt = $pdo->prepare("
        UPDATE enrollments 
        SET status = 'dropped' 
        WHERE student_id = :student_id AND course_id = :course_id
    ");
    $stmt->execute([':student_id' => $student_id, ':course_id' => $course_id]);
    
    header('Location: student_courses.php?success=Successfully dropped ' . urlencode($enrollment['course_name']));
    exit;
    
} catch (PDOException $e) {
    header('Location: student_courses.php?error=Database error: ' . urlencode($e->getMessage()));
    exit;
}
?> 