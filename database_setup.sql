-- Student Portal Database Setup
-- Run this script to create the database and tables

CREATE DATABASE IF NOT EXISTS student_portal;
USE student_portal;

-- Students table with role support and profile pictures
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') DEFAULT 'student',
    profile_picture VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Courses table
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(255) NOT NULL,
    course_code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT,
    instructor VARCHAR(255) NOT NULL,
    credits INT DEFAULT 3,
    semester VARCHAR(50) NOT NULL,
    max_students INT DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Enrollments table
CREATE TABLE IF NOT EXISTS enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    grade VARCHAR(2) DEFAULT NULL,
    status ENUM('enrolled', 'completed', 'dropped') DEFAULT 'enrolled',
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_id)
);

-- Password resets table
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    INDEX idx_token (token),
    INDEX idx_email (email),
    INDEX idx_expires (expires_at)
);

-- Student activity tracking table
CREATE TABLE IF NOT EXISTS student_activity (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    activity_description TEXT,
    related_course_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (related_course_id) REFERENCES courses(id) ON DELETE SET NULL,
    INDEX idx_student_activity (student_id, created_at),
    INDEX idx_activity_type (activity_type)
);

-- Student progress tracking
CREATE TABLE IF NOT EXISTS student_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    progress_percentage DECIMAL(5,2) DEFAULT 0.00,
    last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completion_date DATE NULL,
    study_hours DECIMAL(5,2) DEFAULT 0.00,
    UNIQUE KEY unique_student_course (student_id, course_id),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_student_progress (student_id),
    INDEX idx_course_progress (course_id)
);

-- Sample courses the are default but you can add more in admin dashboard
INSERT INTO courses (course_name, course_code, description, instructor, credits, semester, max_students) VALUES 
('Introduction to Computer Science', 'CS101', 'Basic programming concepts and computer science fundamentals', 'Dr. Smith', 3, 'Fall 2024', 25),
('Web Development Fundamentals', 'WEB101', 'HTML, CSS, JavaScript basics for web development', 'Prof. Johnson', 4, 'Fall 2024', 20),
('Database Systems', 'DB201', 'Database design, SQL, and database management systems', 'Dr. Brown', 3, 'Spring 2024', 30),
('Mathematics for Computing', 'MATH101', 'Mathematical foundations for computer science', 'Prof. Davis', 3, 'Fall 2024', 35),
('Project Management', 'PM101', 'Project planning, execution, and management principles', 'Dr. Wilson', 2, 'Spring 2024', 25);

-- Create default admin user (password: admin123)
INSERT IGNORE INTO students (full_name, email, username, password_hash, role) 
VALUES ('Admin User', 'admin@studentportal.com', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'); 