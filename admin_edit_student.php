<?php
// admin_edit_student.php — Edit student details and roles
session_start();
require __DIR__ . '/includes/db.php';

// Check if user is logged in and is admin
if (empty($_SESSION['student_id']) || $_SESSION['student_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$student_id = $_GET['id'] ?? 0;
$message = '';
$error = '';

if (!$student_id) {
    header('Location: admin_dashboard.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $role = $_POST['role'] ?? 'student';
    $new_password = $_POST['new_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    $errors = [];
    
    // Validate inputs
    if ($full_name === '') {
        $errors[] = 'Full name is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if ($username === '' || strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters.';
    }
    if (!in_array($role, ['student', 'admin'])) {
        $errors[] = 'Invalid role selected.';
    }
    if ($new_password !== '' && strlen($new_password) < 6) {
        $errors[] = 'New password must be at least 6 characters.';
    }
    if ($phone !== '' && !preg_match('/^[\+]?[0-9\(\)\-\s\.]+$/', $phone)) {
        $errors[] = 'Enter a valid phone number.';
    }
    
    if (empty($errors)) {
        try {
            // Check if email/username already exists for other users
            $stmt = $pdo->prepare("SELECT id FROM students WHERE (email = :email OR username = :username) AND id != :id");
            $stmt->execute([
                ':email' => $email,
                ':username' => $username,
                ':id' => $student_id
            ]);
            
            if ($stmt->fetch()) {
                $errors[] = 'Email or username already exists.';
            } else {
                // Update student
                if ($new_password !== '') {
                    // Update with new password
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("
                        UPDATE students 
                        SET full_name = :full_name, email = :email, username = :username, 
                            role = :role, password_hash = :password_hash, phone = :phone, address = :address
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':full_name' => $full_name,
                        ':email' => $email,
                        ':username' => $username,
                        ':role' => $role,
                        ':password_hash' => $password_hash,
                        ':phone' => $phone !== '' ? $phone : null,
                        ':address' => $address !== '' ? $address : null,
                        ':id' => $student_id
                    ]);
                } else {
                    // Update without changing password
                    $stmt = $pdo->prepare("
                        UPDATE students 
                        SET full_name = :full_name, email = :email, username = :username, role = :role, phone = :phone, address = :address
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':full_name' => $full_name,
                        ':email' => $email,
                        ':username' => $username,
                        ':role' => $role,
                        ':phone' => $phone !== '' ? $phone : null,
                        ':address' => $address !== '' ? $address : null,
                        ':id' => $student_id
                    ]);
                }
                
                $message = 'Student updated successfully!';
                
                // If editing own role, update session
                if ($student_id == $_SESSION['student_id']) {
                    $_SESSION['student_role'] = $role;
                    $_SESSION['student_name'] = $full_name;
                }
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
    
    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    }
}

// Fetch student data
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id");
    $stmt->execute([':id' => $student_id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        header('Location: admin_dashboard.php');
        exit;
    }
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Edit Student — Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <img src="assets/images/logo.png" alt="Student Portal Logo" class="logo">
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
                <li><a href="logout.php">Log Out</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="form-section">
            <article>
                <h1>Edit Student</h1>
                <div class="student-header">
                    <div class="student-picture">
                        <?php
                        $profile_picture = $student['profile_picture'];
                        if ($profile_picture && file_exists(__DIR__ . '/assets/images/profiles/' . $profile_picture)) {
                            $picture_url = 'assets/images/profiles/' . htmlspecialchars($profile_picture);
                        } else {
                            $picture_url = 'assets/images/default-avatar.svg';
                        }
                        ?>
                        <img src="<?= $picture_url ?>" alt="Profile Picture" class="edit-profile-picture">
                        <p><small>Student ID: <?= htmlspecialchars($student['id']) ?></small></p>
                    </div>
                    <div class="student-info">
                        <h2><?= htmlspecialchars($student['full_name']) ?></h2>
                        <p><strong>Username:</strong> <?= htmlspecialchars($student['username']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($student['phone'] ?? 'Not provided') ?></p>
                        <p><strong>Address:</strong> <?= htmlspecialchars($student['address'] ?? 'Not provided') ?></p>
                        <p><strong>Current Role:</strong> 
                            <span class="role-badge <?= $student['role'] ?>">
                                <?= ucfirst(htmlspecialchars($student['role'])) ?>
                            </span>
                        </p>
                    </div>
                </div>
                
                <?php if ($message): ?>
                    <div class="success-message">
                        <p><?= htmlspecialchars($message) ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="error-message">
                        <p><?= $error ?></p>
                    </div>
                <?php endif; ?>
                
                <form method="post" novalidate>
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?= htmlspecialchars($student['full_name']) ?>" required>
                    
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($student['email']) ?>" required>
                    
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" 
                           value="<?= htmlspecialchars($student['username']) ?>" required>
                    
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="student" <?= $student['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                        <option value="admin" <?= $student['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    
                    <label for="phone">Phone Number (optional)</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?= htmlspecialchars($student['phone'] ?? '') ?>" 
                           placeholder="e.g., +1 (555) 123-4567">
                    
                    <label for="address">Address (optional)</label>
                    <textarea id="address" name="address" rows="3" 
                              placeholder="Enter address"><?= htmlspecialchars($student['address'] ?? '') ?></textarea>
                    
                    <hr>
                    
                    <h3>Change Password (Optional)</h3>
                    <p><em>Leave blank to keep current password</em></p>
                    
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password">
                    
                    <div class="form-actions">
                        <button type="submit" class="btn">Update Student</button>
                        <a href="admin_dashboard.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </article>
        </section>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> Student Portal. All rights reserved.</p>
    </footer>
</body>
</html>

<style>
.success-message {
    background: #d4edda;
    color: #155724;
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
    border: 1px solid #c3e6cb;
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
    border: 1px solid #f5c6cb;
}

.form-actions {
    margin-top: 20px;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    background: #007cba;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin-right: 10px;
    border: none;
    cursor: pointer;
}

.btn:hover {
    background: #005a87;
}

.btn.btn-secondary {
    background: #6c757d;
}

.btn.btn-secondary:hover {
    background: #545b62;
}

select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

hr {
    margin: 30px 0;
    border: none;
    border-top: 1px solid #ddd;
}

h3 {
    margin-top: 0;
    color: #666;
}

.student-header {
    display: flex;
    gap: 30px;
    margin: 20px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    align-items: center;
}

.student-picture {
    text-align: center;
}

.edit-profile-picture {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #007cba;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.student-info h2 {
    margin: 0 0 10px 0;
    color: #333;
}

.student-info p {
    margin: 8px 0;
    color: #666;
}

.role-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8em;
    font-weight: bold;
}

.role-badge.admin {
    background-color: #dc3545;
    color: white;
}

.role-badge.student {
    background-color: #28a745;
    color: white;
}

@media (max-width: 768px) {
    .student-header {
        flex-direction: column;
        text-align: center;
    }
}
</style> 