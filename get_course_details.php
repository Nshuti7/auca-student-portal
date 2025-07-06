<?php
// get_course_details.php — API endpoint for fetching course details
session_start();
require __DIR__ . '/includes/db.php';

// Set JSON header
header('Content-Type: application/json');

// Check if course ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid course ID']);
    exit;
}

$course_id = (int)$_GET['id'];

try {
    // Get course details with enrollment count
    $stmt = $pdo->prepare("
        SELECT c.*, 
               COUNT(e.id) as enrolled_count,
               CASE 
                   WHEN :student_id IS NOT NULL AND se.id IS NOT NULL THEN 1 
                   ELSE 0 
               END as is_enrolled
        FROM courses c
        LEFT JOIN enrollments e ON c.id = e.course_id AND e.status = 'enrolled'
        LEFT JOIN enrollments se ON c.id = se.course_id 
                                 AND se.student_id = :student_id 
                                 AND se.status = 'enrolled'
        WHERE c.id = :course_id
        GROUP BY c.id
    ");
    
    $student_id = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : null;
    $stmt->execute([
        ':course_id' => $course_id,
        ':student_id' => $student_id
    ]);
    
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$course) {
        echo json_encode(['success' => false, 'error' => 'Course not found']);
        exit;
    }
    
    // Return course data
    echo json_encode([
        'success' => true,
        'course' => [
            'id' => (int)$course['id'],
            'course_name' => $course['course_name'],
            'course_code' => $course['course_code'],
            'description' => $course['description'],
            'instructor' => $course['instructor'],
            'credits' => (int)$course['credits'],
            'semester' => $course['semester'],
            'max_students' => (int)$course['max_students'],
            'enrolled_count' => (int)$course['enrolled_count'],
            'is_enrolled' => (bool)$course['is_enrolled'],
            'is_full' => $course['enrolled_count'] >= $course['max_students'],
            'available_spots' => max(0, $course['max_students'] - $course['enrolled_count'])
        ]
    ]);
    
} catch (PDOException $e) {
    error_log('Course details fetch error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?> 