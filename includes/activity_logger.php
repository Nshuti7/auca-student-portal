<?php
// includes/activity_logger.php — Helper functions for logging student activities

/**
 * Log student activity to the database
 * 
 * @param PDO $pdo Database connection
 * @param int $student_id Student ID
 * @param string $activity_type Type of activity (login, course_view, enrollment, etc.)
 * @param string $activity_description Description of the activity
 * @param int|null $related_course_id Optional course ID if activity is course-related
 * @return bool Success status
 */
function logStudentActivity($pdo, $student_id, $activity_type, $activity_description, $related_course_id = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO student_activity (student_id, activity_type, activity_description, related_course_id)
            VALUES (:student_id, :activity_type, :activity_description, :related_course_id)
        ");
        
        return $stmt->execute([
            ':student_id' => $student_id,
            ':activity_type' => $activity_type,
            ':activity_description' => $activity_description,
            ':related_course_id' => $related_course_id
        ]);
    } catch (PDOException $e) {
        // Silent fail for activity logging - don't break the main functionality
        error_log('Activity logging error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Update student progress for a course
 * 
 * @param PDO $pdo Database connection
 * @param int $student_id Student ID
 * @param int $course_id Course ID
 * @param float $progress_percentage Progress percentage (0-100)
 * @param float $study_hours Optional study hours to add
 * @return bool Success status
 */
function updateStudentProgress($pdo, $student_id, $course_id, $progress_percentage, $study_hours = 0) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO student_progress (student_id, course_id, progress_percentage, last_accessed, study_hours)
            VALUES (:student_id, :course_id, :progress, NOW(), :study_hours)
            ON DUPLICATE KEY UPDATE 
            progress_percentage = :progress2, 
            last_accessed = NOW(),
            study_hours = study_hours + :study_hours2,
            completion_date = CASE WHEN :progress3 >= 100 THEN CURDATE() ELSE NULL END
        ");
        
        return $stmt->execute([
            ':student_id' => $student_id,
            ':course_id' => $course_id,
            ':progress' => $progress_percentage,
            ':progress2' => $progress_percentage,
            ':progress3' => $progress_percentage,
            ':study_hours' => $study_hours,
            ':study_hours2' => $study_hours
        ]);
    } catch (PDOException $e) {
        error_log('Progress update error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get student's recent activities
 * 
 * @param PDO $pdo Database connection
 * @param int $student_id Student ID
 * @param int $limit Number of activities to retrieve
 * @return array Recent activities
 */
function getRecentActivities($pdo, $student_id, $limit = 10) {
    try {
        $stmt = $pdo->prepare("
            SELECT sa.*, c.course_name, c.course_code
            FROM student_activity sa
            LEFT JOIN courses c ON sa.related_course_id = c.id
            WHERE sa.student_id = :student_id
            ORDER BY sa.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':student_id', $student_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Recent activities error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get student's academic statistics
 * 
 * @param PDO $pdo Database connection
 * @param int $student_id Student ID
 * @return array Academic statistics
 */
function getStudentStats($pdo, $student_id) {
    try {
        // Enrolled courses count
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as enrolled_count
            FROM enrollments 
            WHERE student_id = :student_id AND status = 'enrolled'
        ");
        $stmt->execute([':student_id' => $student_id]);
        $enrolled_count = $stmt->fetchColumn();
        
        // Completed courses count
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as completed_count
            FROM enrollments 
            WHERE student_id = :student_id AND status = 'completed'
        ");
        $stmt->execute([':student_id' => $student_id]);
        $completed_count = $stmt->fetchColumn();
        
        // Total credits enrolled
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(c.credits), 0) as total_credits
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            WHERE e.student_id = :student_id AND e.status = 'enrolled'
        ");
        $stmt->execute([':student_id' => $student_id]);
        $total_credits = $stmt->fetchColumn();
        
        // Average progress
        $stmt = $pdo->prepare("
            SELECT COALESCE(AVG(progress_percentage), 0) as avg_progress
            FROM student_progress
            WHERE student_id = :student_id
        ");
        $stmt->execute([':student_id' => $student_id]);
        $avg_progress = $stmt->fetchColumn();
        
        return [
            'enrolled_count' => $enrolled_count,
            'completed_count' => $completed_count,
            'total_credits' => $total_credits,
            'avg_progress' => $avg_progress
        ];
    } catch (PDOException $e) {
        error_log('Student stats error: ' . $e->getMessage());
        return [
            'enrolled_count' => 0,
            'completed_count' => 0,
            'total_credits' => 0,
            'avg_progress' => 0
        ];
    }
}

/**
 * Clean up old activity logs (older than specified days)
 * 
 * @param PDO $pdo Database connection
 * @param int $days Number of days to keep activities
 * @return int Number of deleted records
 */
function cleanupOldActivities($pdo, $days = 365) {
    try {
        $stmt = $pdo->prepare("
            DELETE FROM student_activity 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)
        ");
        $stmt->execute([':days' => $days]);
        
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log('Activity cleanup error: ' . $e->getMessage());
        return 0;
    }
}
?> 