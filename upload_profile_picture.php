<?php
// upload_profile_picture.php — Handle profile picture uploads
session_start();
require __DIR__ . '/includes/db.php';

// Check if user is logged in
if (empty($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $user_id = $_SESSION['student_id'];
    $file = $_FILES['profile_picture'];
    
    // Validate file upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload failed. Please try again.';
    } else {
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $file['type'];
        $file_info = getimagesize($file['tmp_name']);
        
        if (!in_array($file_type, $allowed_types) || !$file_info) {
            $error = 'Invalid file type. Please upload a JPEG, PNG, GIF, or WebP image.';
        } else {
            // Validate file size (max 5MB)
            $max_size = 5 * 1024 * 1024; // 5MB in bytes
            if ($file['size'] > $max_size) {
                $error = 'File too large. Maximum size is 5MB.';
            } else {
                // Validate image dimensions (optional - max 2000x2000)
                list($width, $height) = $file_info;
                $max_dimension = 2000;
                if ($width > $max_dimension || $height > $max_dimension) {
                    $error = "Image dimensions too large. Maximum size is {$max_dimension}x{$max_dimension} pixels.";
                } else {
                    // Generate unique filename
                    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
                    $upload_path = __DIR__ . '/assets/images/profiles/' . $filename;
                    
                    // Ensure upload directory exists
                    $upload_dir = dirname($upload_path);
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    // Move uploaded file
                    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                        try {
                            // Get current profile picture to delete old one
                            $stmt = $pdo->prepare("SELECT profile_picture FROM students WHERE id = :id");
                            $stmt->execute([':id' => $user_id]);
                            $current_picture = $stmt->fetch()['profile_picture'];
                            
                            // Update database
                            $stmt = $pdo->prepare("UPDATE students SET profile_picture = :filename WHERE id = :id");
                            $stmt->execute([
                                ':filename' => $filename,
                                ':id' => $user_id
                            ]);
                            
                            // Delete old profile picture if it exists and is not default
                            if ($current_picture && $current_picture !== 'default-avatar.svg') {
                                $old_file = __DIR__ . '/assets/images/profiles/' . $current_picture;
                                if (file_exists($old_file)) {
                                    unlink($old_file);
                                }
                            }
                            
                            $message = 'Profile picture updated successfully!';
                            
                        } catch (PDOException $e) {
                            $error = 'Database error: ' . $e->getMessage();
                            // Delete uploaded file if database update failed
                            if (file_exists($upload_path)) {
                                unlink($upload_path);
                            }
                        }
                    } else {
                        $error = 'Failed to save uploaded file. Please check directory permissions.';
                    }
                }
            }
        }
    }
} else {
    $error = 'No file uploaded or invalid request.';
}

// Redirect back to profile with message
$redirect_url = 'profile.php';
if ($message) {
    $redirect_url .= '?success=' . urlencode($message);
} elseif ($error) {
    $redirect_url .= '?error=' . urlencode($error);
}

header('Location: ' . $redirect_url);
exit;
?> 