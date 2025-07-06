<?php
// includes/db.php — Database connection using PDO

// Database credentials
$host    = 'localhost';
$db_name = 'student_portal';
$db_user = 'root';
$db_pass = ''; // XAMPP’s default root user has no password

try {
    // Create a new PDO instance and assign it to $pdo
    $pdo = new PDO(
        "mysql:host={$host};dbname={$db_name};charset=utf8",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

} catch (PDOException $e) {
    // If connection fails, stop execution and show error
    die("Database connection failed: " . $e->getMessage());
}
