<?php
// admin_add_course.php — Add new course
session_start();
require __DIR__ . '/includes/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['student_id']) || $_SESSION['student_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = trim($_POST['course_name']);
    $course_code = trim($_POST['course_code']);
    $description = trim($_POST['description']);
    $instructor = trim($_POST['instructor']);
    $credits = (int)$_POST['credits'];
    $semester = trim($_POST['semester']);
    $max_students = (int)$_POST['max_students'];
    
    // Validate input
    if (empty($course_name) || empty($course_code) || empty($instructor) || empty($semester)) {
        $error = 'Please fill in all required fields.';
    } elseif ($credits < 1 || $credits > 10) {
        $error = 'Credits must be between 1 and 10.';
    } elseif ($max_students < 1 || $max_students > 200) {
        $error = 'Maximum students must be between 1 and 200.';
    } else {
        try {
            // Check if course code already exists
            $stmt = $pdo->prepare("SELECT id FROM courses WHERE course_code = :course_code");
            $stmt->execute([':course_code' => $course_code]);
            
            if ($stmt->fetch()) {
                $error = 'Course code already exists. Please use a different code.';
            } else {
                // Insert new course
                $stmt = $pdo->prepare("
                    INSERT INTO courses (course_name, course_code, description, instructor, credits, semester, max_students) 
                    VALUES (:course_name, :course_code, :description, :instructor, :credits, :semester, :max_students)
                ");
                $stmt->execute([
                    ':course_name' => $course_name,
                    ':course_code' => $course_code,
                    ':description' => $description,
                    ':instructor' => $instructor,
                    ':credits' => $credits,
                    ':semester' => $semester,
                    ':max_students' => $max_students
                ]);
                
                header('Location: admin_courses.php?success=Course added successfully');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course - Admin Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <img src="assets/images/logo.png" alt="Student Portal Logo" class="logo">
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="courses.php">Course Catalog</a></li>
                <li><a href="admin_courses.php">Manage Courses</a></li>
                <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Log Out</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="add-course-section">
            <h1>➕ Add New Course</h1>
            <p>Create a new course for student enrollment</p>
            
            <?php if ($message): ?>
                <div class="success-message">
                    <p><?= htmlspecialchars($message) ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>
            
            <form method="post" class="course-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="course_name">Course Name *</label>
                        <input type="text" id="course_name" name="course_name" 
                               value="<?= htmlspecialchars($_POST['course_name'] ?? '') ?>" 
                               required maxlength="255">
                    </div>
                    
                    <div class="form-group">
                        <label for="course_code">Course Code *</label>
                        <input type="text" id="course_code" name="course_code" 
                               value="<?= htmlspecialchars($_POST['course_code'] ?? '') ?>" 
                               required maxlength="20" placeholder="e.g., CS101">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Course Description</label>
                    <textarea id="description" name="description" rows="4" 
                              placeholder="Brief description of the course content and objectives"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="instructor">Instructor *</label>
                        <input type="text" id="instructor" name="instructor" 
                               value="<?= htmlspecialchars($_POST['instructor'] ?? '') ?>" 
                               required maxlength="255" placeholder="e.g., Dr. Smith">
                    </div>
                    
                    <div class="form-group">
                        <label for="semester">Semester *</label>
                        <select id="semester" name="semester" required>
                            <option value="">Select Semester</option>
                            <option value="Fall 2024" <?= ($_POST['semester'] ?? '') === 'Fall 2024' ? 'selected' : '' ?>>Fall 2024</option>
                            <option value="Spring 2025" <?= ($_POST['semester'] ?? '') === 'Spring 2025' ? 'selected' : '' ?>>Spring 2025</option>
                            <option value="Summer 2025" <?= ($_POST['semester'] ?? '') === 'Summer 2025' ? 'selected' : '' ?>>Summer 2025</option>
                            <option value="Fall 2025" <?= ($_POST['semester'] ?? '') === 'Fall 2025' ? 'selected' : '' ?>>Fall 2025</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="credits">Credits *</label>
                        <input type="number" id="credits" name="credits" 
                               value="<?= htmlspecialchars($_POST['credits'] ?? '3') ?>" 
                               min="1" max="10" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="max_students">Maximum Students *</label>
                        <input type="number" id="max_students" name="max_students" 
                               value="<?= htmlspecialchars($_POST['max_students'] ?? '30') ?>" 
                               min="1" max="200" required>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Course</button>
                    <a href="admin_courses.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> Student Portal. All rights reserved.</p>
    </footer>
</body>
</html>

<style>
.add-course-section {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.add-course-section h1 {
    text-align: center;
    color: #333;
    margin-bottom: 10px;
}

.add-course-section p {
    text-align: center;
    color: #666;
    margin-bottom: 30px;
}

.success-message, .error-message {
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
}

.success-message {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.course-form {
    background: #f8f9fa;
    padding: 30px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
    box-sizing: border-box;
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #007cba;
    box-shadow: 0 0 0 2px rgba(0, 124, 186, 0.2);
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.2s;
    display: inline-block;
    text-align: center;
}

.btn-primary {
    background: #007cba;
    color: white;
}

.btn-primary:hover {
    background: #005a87;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .btn {
        width: 100%;
        max-width: 200px;
    }
}
</style> 