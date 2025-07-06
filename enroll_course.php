<?php
// enroll_course.php — Handle course enrollment
session_start();
require __DIR__ . '/includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

// Check if course ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: courses.php?error=Invalid course ID');
    exit;
}

$course_id = (int)$_GET['id'];
$student_id = $_SESSION['student_id'];

try {
    // Check if course exists
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = :id");
    $stmt->execute([':id' => $course_id]);
    $course = $stmt->fetch();
    
    if (!$course) {
        header('Location: courses.php?error=Course not found');
        exit;
    }
    
    // Check if student is already enrolled
    $stmt = $pdo->prepare("
        SELECT * FROM enrollments 
        WHERE student_id = :student_id AND course_id = :course_id
    ");
    $stmt->execute([':student_id' => $student_id, ':course_id' => $course_id]);
    $existing_enrollment = $stmt->fetch();
    
    if ($existing_enrollment) {
        if ($existing_enrollment['status'] === 'enrolled') {
            header('Location: courses.php?error=You are already enrolled in this course');
            exit;
        } elseif ($existing_enrollment['status'] === 'dropped') {
            // Re-enroll dropped student
            $stmt = $pdo->prepare("
                UPDATE enrollments 
                SET status = 'enrolled', enrollment_date = CURRENT_TIMESTAMP 
                WHERE student_id = :student_id AND course_id = :course_id
            ");
            $stmt->execute([':student_id' => $student_id, ':course_id' => $course_id]);
            
            header('Location: courses.php?success=Successfully re-enrolled in ' . urlencode($course['course_name']));
            exit;
        }
    }
    
    // Check if course is full
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as enrolled_count 
        FROM enrollments 
        WHERE course_id = :course_id AND status = 'enrolled'
    ");
    $stmt->execute([':course_id' => $course_id]);
    $enrolled_count = $stmt->fetchColumn();
    
    if ($enrolled_count >= $course['max_students']) {
        header('Location: courses.php?error=Course is full');
        exit;
    }
    
    // Enroll the student
    $stmt = $pdo->prepare("
        INSERT INTO enrollments (student_id, course_id, status) 
        VALUES (:student_id, :course_id, 'enrolled')
    ");
    $stmt->execute([
        ':student_id' => $student_id,
        ':course_id' => $course_id
    ]);
    
    header('Location: courses.php?success=Successfully enrolled in ' . urlencode($course['course_name']));
    exit;
    
} catch (PDOException $e) {
    header('Location: courses.php?error=Database error: ' . urlencode($e->getMessage()));
    exit;
}
?> 