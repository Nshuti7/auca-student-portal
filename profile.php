<?php
session_start();
require __DIR__ . '/includes/db.php';

// 1) Ensure the user is logged in
if (empty($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

// 2) Fetch student data
$stmt = $pdo->prepare('SELECT full_name, email, username, created_at, role, profile_picture, phone, address FROM students WHERE id = :id');
$stmt->execute([':id' => $_SESSION['student_id']]);
$student = $stmt->fetch();

if (! $student) {
    // Weird case: session ID doesn't match any record
    die('Student record not found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile - Student Portal</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/icons.css">
  <style>
    .icon-text span {
      margin-left: 20px !important;
    }
  </style>
</head>
<body>
  <?php require __DIR__ . '/includes/navigation.php'; ?>

  <main>
    <div class="container">
      <section class="profile-section">
        <div class="profile-header">
          <h1>
           
              My Profile
           
          </h1>
          <p>Manage your personal information and account settings</p>
        </div>

        <div class="profile-layout">
          <!-- Profile Picture Card -->
          <div class="card profile-picture-card">
            <div class="card-header">
              <h2>
               
                  Profile Picture
               
              </h2>
            </div>
            <div class="card-body">
              <div class="profile-picture-section">
                <?php
                $profile_picture = $student['profile_picture'];
                if ($profile_picture && file_exists(__DIR__ . '/assets/images/profiles/' . $profile_picture)) {
                    $picture_url = 'assets/images/profiles/' . htmlspecialchars($profile_picture);
                } else {
                    $picture_url = 'assets/images/default-avatar.svg';
                }
                ?>
                <div class="profile-picture-container">
                  <img src="<?= $picture_url ?>" alt="Profile Picture" class="profile-picture">
                  <div class="picture-overlay">
                    <div class="icon icon-camera"></div>
                  </div>
                </div>
                
                <div class="picture-upload">
                  <form action="upload_profile_picture.php" method="post" enctype="multipart/form-data" class="upload-form">
                    <label for="profile_picture" class="btn btn-secondary">
                      <div class="icon icon-upload btn-icon"></div>
                      <span>Change Picture</span>
                      <input type="file" id="profile_picture" name="profile_picture" accept="image/*" style="display: none;">
                    </label>
                    <button type="submit" class="btn btn-primary" style="display: none;" id="upload-btn">
                      <div class="icon icon-check btn-icon"></div>
                      <span>Upload</span>
                    </button>
                  </form>
                  <div class="upload-info">
                    <small>Max 5MB • JPEG, PNG, GIF, WebP</small>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Profile Information Card -->
          <div class="card profile-info-card">
            <div class="card-header">
              <h2>
                <div class=" icon-text">
                  <span>Personal Information</span>
                </div>
              </h2>
              <a href="update.php" class="btn btn-primary btn-sm">
                <div class="icon icon-edit btn-icon"></div>
                <span>Edit Profile</span>
              </a>
            </div>
            <div class="card-body">
              <div class="profile-details">
                <div class="detail-row">
                  <div class="detail-label">
                    <div class="icon icon-profile icon-text">
                      <span>Name</span>
                    </div>
                  </div>
                  <div class="detail-value"><?= htmlspecialchars($student['full_name']) ?></div>
                </div>
                
                <div class="detail-row">
                  <div class="detail-label">
                    <div class="icon icon-profile icon-text">
                      <span>Username</span>
                    </div>
                  </div>
                  <div class="detail-value"><?= htmlspecialchars($student['username']) ?></div>
                </div>
                
                <div class="detail-row">
                  <div class="detail-label">
                    <div class="icon icon-email icon-text">
                      <span>Email</span>
                    </div>
                  </div>
                  <div class="detail-value"><?= htmlspecialchars($student['email']) ?></div>
                </div>
                
                <div class="detail-row">
                  <div class="detail-label">
                    <div class="icon icon-phone icon-text">
                      <span>Phone</span>
                    </div>
                  </div>
                  <div class="detail-value">
                    <?= htmlspecialchars($student['phone'] ?? 'Not provided') ?>
                    <?php if (empty($student['phone'])): ?>
                      <span class="missing-info">
                        <a href="update.php" class="text-primary">Add phone number</a>
                      </span>
                    <?php endif; ?>
                  </div>
                </div>
                
                <div class="detail-row">
                  <div class="detail-label">
                    <div class="icon icon-location icon-text">
                      <span>Address</span>
                    </div>
                  </div>
                  <div class="detail-value">
                    <?= htmlspecialchars($student['address'] ?? 'Not provided') ?>
                    <?php if (empty($student['address'])): ?>
                      <span class="missing-info">
                        <a href="update.php" class="text-primary">Add address</a>
                      </span>
                    <?php endif; ?>
                  </div>
                </div>
                
                <div class="detail-row">
                  <div class="detail-label">
                    <div class="icon icon-admin icon-text">
                      <span>Role</span>
                    </div>
                  </div>
                  <div class="detail-value">
                    <span class="role-badge <?= $student['role'] === 'admin' ? 'admin' : 'student' ?>">
                      <?= ucfirst(htmlspecialchars($student['role'])) ?>
                    </span>
                  </div>
                </div>
                
                <div class="detail-row">
                  <div class="detail-label">
                    <div class="icon icon-calendar icon-text">
                      <span>Member</span>
                    </div>
                  </div>
                  <div class="detail-value"><?= date('F j, Y', strtotime($student['created_at'])) ?></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="card mt-6">
          <div class="card-header">
            <h2>
              <div class="icon icon-settings icon-text">
                <span>Quick Actions</span>
              </div>
            </h2>
          </div>
          <div class="card-body">
            <div class="quick-actions">
              <a href="courses.php" class="action-btn">
                <div class="action-icon">
                  <div class="icon icon-search"></div>
                </div>
                <div class="action-content">
                  <span class="action-title">Browse Courses</span>
                  <span class="action-desc">Discover new learning opportunities</span>
                </div>
              </a>
              
              <a href="student_courses.php" class="action-btn">
                <div class="action-icon">
                  <div class="icon icon-graduation"></div>
                </div>
                <div class="action-content">
                  <span class="action-title">My Courses</span>
                  <span class="action-desc">View and manage enrolled courses</span>
                </div>
              </a>
              
              <a href="student_dashboard.php" class="action-btn">
                <div class="action-icon">
                  <div class="icon icon-dashboard"></div>
                </div>
                <div class="action-content">
                  <span class="action-title">Dashboard</span>
                  <span class="action-desc">View your academic progress</span>
                </div>
              </a>
              
              <a href="update.php" class="action-btn">
                <div class="action-icon">
                  <div class="icon icon-edit"></div>
                </div>
                <div class="action-content">
                  <span class="action-title">Edit Profile</span>
                  <span class="action-desc">Update your information</span>
                </div>
              </a>
            </div>
          </div>
        </div>

        <!-- Danger Zone Card -->
        <div class="card danger-card mt-6">
          <div class="card-header">
            <h2>
              <div class="icon-text">
                <span>Danger Zone</span>
              </div>
            </h2>
          </div>
          <div class="card-body">
            <div class="danger-actions">
              <div class="danger-info">
                <h3>Delete Account</h3>
                <p>Permanently remove your account and all associated data. This action cannot be undone.</p>
              </div>
              <button class="btn btn-danger" onclick="confirmDelete()">
                <div class="icon icon-trash btn-icon"></div>
                <span>Delete Profile</span>
              </button>
            </div>
          </div>
        </div>
      </section>
    </div>
  </main>

  <footer>
    <div class="container">
      <p>&copy; <?= date('Y') ?> Student Portal. All rights reserved.</p>
    </div>
  </footer>

  <script src="assets/js/app.js"></script>
  <script>
    // Profile-specific JavaScript
    document.addEventListener('DOMContentLoaded', function() {
      // Handle success/error messages
      <?php
      if (isset($_GET['success'])) {
          echo 'toast.success("' . htmlspecialchars($_GET['success']) . '");';
      }
      if (isset($_GET['error'])) {
          echo 'toast.error("' . htmlspecialchars($_GET['error']) . '");';
      }
      ?>
      
      // Auto-submit form when file is selected
      const profilePicInput = document.getElementById('profile_picture');
      const uploadBtn = document.getElementById('upload-btn');
      
      profilePicInput.addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
          const file = this.files[0];
          
          // Validate file size (5MB limit)
          if (file.size > 5 * 1024 * 1024) {
            toast.error('File size must be less than 5MB');
            this.value = '';
            return;
          }
          
          // Validate file type
          const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
          if (!allowedTypes.includes(file.type)) {
            toast.error('Only JPEG, PNG, GIF, and WebP files are allowed');
            this.value = '';
            return;
          }
          
          // Show upload button and preview
          uploadBtn.style.display = 'inline-flex';
          uploadBtn.innerHTML = `
            <div class="icon icon-upload btn-icon"></div>
            <span>Upload ${file.name}</span>
          `;
          
          // Preview the image
          const reader = new FileReader();
          reader.onload = function(e) {
            const profilePic = document.querySelector('.profile-picture');
            profilePic.src = e.target.result;
          };
          reader.readAsDataURL(file);
          
          // Auto-submit after a short delay
          setTimeout(() => {
            uploadBtn.innerHTML = `
              <div class="icon icon-spinning btn-icon"></div>
              <span>Uploading...</span>
            `;
            this.closest('form').submit();
          }, 1000);
        }
      });
      
      // Profile picture hover effect
      const profilePicContainer = document.querySelector('.profile-picture-container');
      if (profilePicContainer) {
        profilePicContainer.addEventListener('click', function() {
          profilePicInput.click();
        });
      }
    });
    
    // Confirm delete function
    function confirmDelete() {
      modal.confirm(
        'Are you sure you want to delete your profile? This action cannot be undone and will permanently remove all your data.',
        'Delete Profile',
        'Delete',
        'Cancel'
      ).then(confirmed => {
        if (confirmed) {
          window.location.href = 'delete.php';
        }
      });
    }
  </script>
</body>
</html>

<style>
/* Profile-specific modern styles */
.profile-section {
  padding: var(--spacing-6) 0;
}

.profile-header {
  text-align: center;
  margin-bottom: var(--spacing-8);
}

.profile-header h1 {
  color: var(--primary-color);
  margin-bottom: var(--spacing-2);
}

.profile-header p {
  color: var(--gray-600);
  font-size: var(--font-size-lg);
}

.profile-layout {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: var(--spacing-8);
  margin-bottom: var(--spacing-6);
}

.profile-picture-card {
  height: fit-content;
}

.profile-picture-section {
  text-align: center;
}

.profile-picture-container {
  position: relative;
  display: inline-block;
  margin-bottom: var(--spacing-6);
  cursor: pointer;
  transition: transform var(--transition-fast);
}

.profile-picture-container:hover {
  transform: scale(1.02);
}

.profile-picture {
  width: 200px;
  height: 200px;
  border-radius: var(--radius-full);
  object-fit: cover;
  border: 4px solid var(--primary-color);
  box-shadow: var(--shadow-lg);
  transition: all var(--transition-fast);
}

.picture-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  border-radius: var(--radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity var(--transition-fast);
  color: var(--white);
}

.profile-picture-container:hover .picture-overlay {
  opacity: 1;
}

.picture-upload {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--spacing-3);
}

.upload-info {
  color: var(--gray-500);
  font-size: var(--font-size-sm);
}

.profile-details {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-4);
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--spacing-3);
  background: var(--gray-50);
  border-radius: var(--radius-md);
  border: 1px solid var(--gray-200);
}

.detail-label {
  font-weight: 600;
  color: var(--gray-700);
  min-width: 120px;
}

.detail-value {
  flex: 1;
  text-align: right;
  color: var(--gray-900);
}

.missing-info {
  color: var(--gray-500);
  font-style: italic;
}

.missing-info a {
  color: var(--primary-color);
  text-decoration: none;
  font-weight: 500;
}

.missing-info a:hover {
  text-decoration: underline;
}

.role-badge {
  padding: var(--spacing-1) var(--spacing-2);
  border-radius: var(--radius-sm);
  font-size: var(--font-size-sm);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.role-badge.admin {
  background: var(--error-light);
  color: var(--error-color);
}

.role-badge.student {
  background: var(--primary-light);
  color: var(--primary-color);
}

.quick-actions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: var(--spacing-4);
}

.action-btn {
  display: flex;
  align-items: center;
  gap: var(--spacing-3);
  padding: var(--spacing-4);
  background: var(--gray-50);
  border: 1px solid var(--gray-200);
  border-radius: var(--radius-md);
  text-decoration: none;
  color: var(--gray-800);
  transition: all var(--transition-fast);
}

.action-btn:hover {
  background: var(--white);
  border-color: var(--primary-color);
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.action-icon {
  width: 40px;
  height: 40px;
  background: var(--primary-color);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--white);
}

.action-content {
  display: flex;
  flex-direction: column;
}

.action-title {
  font-weight: 600;
  color: var(--gray-900);
  margin-bottom: var(--spacing-1);
}

.action-desc {
  font-size: var(--font-size-sm);
  color: var(--gray-600);
}

.danger-card {
  border-color: var(--error-color);
}

.danger-card .card-header {
  background: var(--error-light);
  color: var(--error-color);
}

.danger-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: var(--spacing-4);
}

.danger-info h3 {
  color: var(--error-color);
  margin-bottom: var(--spacing-2);
}

.danger-info p {
  color: var(--gray-600);
  margin: 0;
}

.mt-6 {
  margin-top: var(--spacing-6);
}

/* Responsive Design */
@media (max-width: 768px) {
  .profile-layout {
    grid-template-columns: 1fr;
    gap: var(--spacing-4);
  }
  
  .profile-picture {
    width: 150px;
    height: 150px;
  }
  
  .detail-row {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--spacing-2);
  }
  
  .detail-value {
    text-align: left;
  }
  
  .quick-actions {
    grid-template-columns: 1fr;
  }
  
  .danger-actions {
    flex-direction: column;
    align-items: flex-start;
  }
}

@media (max-width: 480px) {
  .profile-picture {
    width: 120px;
    height: 120px;
  }
  
  .action-btn {
    flex-direction: column;
    text-align: center;
    padding: var(--spacing-3);
  }
}
</style>
