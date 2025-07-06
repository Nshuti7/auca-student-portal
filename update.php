<?php
// update.php — display the edit-profile form
session_start();
require __DIR__ . '/includes/db.php';

if (empty($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch existing data
$stmt = $pdo->prepare('SELECT full_name, email, phone, address FROM students WHERE id = :id');
$stmt->execute([':id' => $_SESSION['student_id']]);
$student = $stmt->fetch();

if (! $student) {
    die('Student record not found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile - Student Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/icons.css">
</head>
<body>
    <?php require __DIR__ . '/includes/navigation.php'; ?>

    <main>
        <div class="container">
            <section class="edit-profile-section">
                <div class="profile-header">
                    <h1>
                        <div class="icon icon-edit icon-text">
                            <span>Edit Your Profile</span>
                        </div>
                    </h1>
                    <p>Update your personal information and account settings</p>
                </div>

                <div class="profile-form-container">
                    <form id="profileForm" action="update_process.php" method="post" novalidate>
                        <!-- Personal Information Section -->
                        <div class="form-section">
                            <div class="section-header">
                                <h2>
                                    <div class="icon icon-profile icon-text">
                                        <span>Personal Information</span>
                                    </div>
                                </h2>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="full_name">
                                        <div class="icon icon-profile icon-text">
                                            <span>Full Name</span>
                                        </div>
                                    </label>
                                    <input type="text" 
                                           id="full_name" 
                                           name="full_name"
                                           value="<?= htmlspecialchars($student['full_name']) ?>" 
                                           required
                                           placeholder="Enter your full name">
                                    <div class="field-error" id="full_name_error"></div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">
                                        <div class="icon icon-email icon-text">
                                            <span>Email Address</span>
                                        </div>
                                    </label>
                                    <input type="email" 
                                           id="email" 
                                           name="email"
                                           value="<?= htmlspecialchars($student['email']) ?>" 
                                           required
                                           placeholder="Enter your email address">
                                    <div class="field-error" id="email_error"></div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="phone">
                                        <div class="icon icon-phone icon-text">
                                            <span>Phone Number</span>
                                        </div>
                                        <span class="optional">(optional)</span>
                                    </label>
                                    <input type="tel" 
                                           id="phone" 
                                           name="phone"
                                           value="<?= htmlspecialchars($student['phone'] ?? '') ?>" 
                                           placeholder="e.g., +1 (555) 123-4567">
                                    <div class="field-error" id="phone_error"></div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="address">
                                        <div class="icon icon-location icon-text">
                                            <span>Address</span>
                                        </div>
                                        <span class="optional">(optional)</span>
                                    </label>
                                    <textarea id="address" 
                                              name="address" 
                                              rows="3" 
                                              placeholder="Enter your address"><?= htmlspecialchars($student['address'] ?? '') ?></textarea>
                                    <div class="field-error" id="address_error"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Password Section -->
                        <div class="form-section">
                            <div class="section-header">
                                <h2>
                                    <div class="icon icon-lock icon-text">
                                        <span>Change Password</span>
                                    </div>
                                </h2>
                                <p class="section-description">Leave blank to keep current password</p>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="password">
                                        <div class="icon icon-lock icon-text">
                                            <span>New Password</span>
                                        </div>
                                    </label>
                                    <div class="password-field">
                                        <input type="password" 
                                               id="password" 
                                               name="password"
                                               placeholder="Enter new password">
                                        <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                                            <div class="icon icon-eye"></div>
                                        </button>
                                    </div>
                                    <div class="password-strength" id="password-strength">
                                        <div class="strength-bar">
                                            <div class="strength-fill" id="strength-fill"></div>
                                        </div>
                                        <div class="strength-text" id="strength-text">Password strength</div>
                                    </div>
                                    <div class="field-error" id="password_error"></div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">
                                        <div class="icon icon-lock icon-text">
                                            <span>Confirm New Password</span>
                                        </div>
                                    </label>
                                    <div class="password-field">
                                        <input type="password" 
                                               id="confirm_password" 
                                               name="confirm_password"
                                               placeholder="Confirm new password">
                                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)">
                                            <div class="icon icon-eye"></div>
                                        </button>
                                    </div>
                                    <div class="field-error" id="confirm_password_error"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <div class="icon icon-check btn-icon"></div>
                                <span>Save Changes</span>
                            </button>
                            <a href="profile.php" class="btn btn-secondary">
                                <div class="icon icon-x btn-icon"></div>
                                <span>Cancel</span>
                            </a>
                        </div>
                    </form>
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
        // Profile update specific JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('profileForm');
            const passwordField = document.getElementById('password');
            const confirmPasswordField = document.getElementById('confirm_password');
            const strengthBar = document.getElementById('strength-fill');
            const strengthText = document.getElementById('strength-text');
            
            // Initialize form validation
            const validator = new FormValidator(form);
            
            // Add validation rules
            validator.addRule('full_name', {
                required: true,
                minLength: 2,
                maxLength: 100
            });
            
            validator.addRule('email', {
                required: true,
                email: true
            });
            
            validator.addRule('phone', {
                pattern: /^[\+]?[1-9][\d]{0,15}$/,
                message: 'Please enter a valid phone number'
            });
            
            // Password validation (only if password is provided)
            passwordField.addEventListener('input', function() {
                const password = this.value;
                
                if (password) {
                    // Check password strength
                    const strength = checkPasswordStrength(password);
                    updatePasswordStrength(strength);
                    
                    // Add password validation rules
                    validator.addRule('password', {
                        required: true,
                        minLength: 8,
                        pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/,
                        message: 'Password must be at least 8 characters with uppercase, lowercase, and number'
                    });
                    
                    validator.addRule('confirm_password', {
                        required: true,
                        match: 'password',
                        message: 'Passwords do not match'
                    });
                } else {
                    // Remove password validation if field is empty
                    validator.removeRule('password');
                    validator.removeRule('confirm_password');
                    resetPasswordStrength();
                }
            });
            
            // Confirm password validation
            confirmPasswordField.addEventListener('input', function() {
                if (passwordField.value && this.value) {
                    if (passwordField.value !== this.value) {
                        this.setCustomValidity('Passwords do not match');
                    } else {
                        this.setCustomValidity('');
                    }
                }
            });
            
            // Real-time validation
            form.addEventListener('input', function(e) {
                validator.validateField(e.target);
            });
            
            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (validator.validateForm()) {
                    // Show loading state
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalContent = submitBtn.innerHTML;
                    
                    submitBtn.innerHTML = `
                        <div class="icon icon-spinning btn-icon"></div>
                        <span>Saving...</span>
                    `;
                    submitBtn.disabled = true;
                    
                    // Submit form
                    setTimeout(() => {
                        form.submit();
                    }, 500);
                } else {
                    toast.error('Please correct the errors in the form');
                }
            });
            
            // Phone formatting
            const phoneField = document.getElementById('phone');
            phoneField.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                if (value.length > 0) {
                    if (value.length <= 3) {
                        value = `(${value}`;
                    } else if (value.length <= 6) {
                        value = `(${value.substring(0, 3)}) ${value.substring(3)}`;
                    } else {
                        value = `(${value.substring(0, 3)}) ${value.substring(3, 6)}-${value.substring(6, 10)}`;
                    }
                    this.value = value;
                }
            });
        });
        
        // Toggle password visibility
        function togglePassword(fieldId, button) {
            const field = document.getElementById(fieldId);
            const icon = button.querySelector('.icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.className = 'icon icon-eye-off';
            } else {
                field.type = 'password';
                icon.className = 'icon icon-eye';
            }
        }
        
        // Check password strength
        function checkPasswordStrength(password) {
            let strength = 0;
            
            if (password.length >= 8) strength += 1;
            if (password.match(/[a-z]/)) strength += 1;
            if (password.match(/[A-Z]/)) strength += 1;
            if (password.match(/[0-9]/)) strength += 1;
            if (password.match(/[^a-zA-Z0-9]/)) strength += 1;
            
            return strength;
        }
        
        // Update password strength indicator
        function updatePasswordStrength(strength) {
            const strengthBar = document.getElementById('strength-fill');
            const strengthText = document.getElementById('strength-text');
            
            const levels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
            const colors = ['#dc3545', '#fd7e14', '#ffc107', '#28a745', '#198754'];
            
            const level = Math.min(strength, 5);
            const width = (level / 5) * 100;
            
            strengthBar.style.width = width + '%';
            strengthBar.style.backgroundColor = colors[level - 1] || '#dc3545';
            strengthText.textContent = levels[level - 1] || 'Very Weak';
            strengthText.style.color = colors[level - 1] || '#dc3545';
        }
        
        // Reset password strength indicator
        function resetPasswordStrength() {
            const strengthBar = document.getElementById('strength-fill');
            const strengthText = document.getElementById('strength-text');
            
            strengthBar.style.width = '0%';
            strengthText.textContent = 'Password strength';
            strengthText.style.color = 'var(--gray-600)';
        }
    </script>
</body>
</html>

<style>
/* Edit profile specific styles */
.edit-profile-section {
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

.profile-form-container {
    max-width: 800px;
    margin: 0 auto;
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    padding: var(--spacing-8);
    border: 1px solid var(--gray-200);
}

.form-section {
    margin-bottom: var(--spacing-8);
}

.form-section:last-child {
    margin-bottom: 0;
}

.section-header {
    margin-bottom: var(--spacing-6);
    padding-bottom: var(--spacing-4);
    border-bottom: 1px solid var(--gray-200);
}

.section-header h2 {
    color: var(--gray-900);
    margin: 0 0 var(--spacing-1) 0;
}

.section-description {
    color: var(--gray-600);
    font-size: var(--font-size-sm);
    margin: 0;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--spacing-6);
    margin-bottom: var(--spacing-6);
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    margin-bottom: var(--spacing-2);
    font-weight: 600;
    color: var(--gray-700);
}

.optional {
    font-size: var(--font-size-sm);
    color: var(--gray-500);
    font-weight: 400;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: var(--spacing-3);
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-md);
    font-size: var(--font-size-base);
    transition: all var(--transition-normal);
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.password-field {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: var(--spacing-3);
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--gray-500);
    cursor: pointer;
    padding: var(--spacing-1);
    border-radius: var(--radius-sm);
    transition: all var(--transition-normal);
}

.password-toggle:hover {
    color: var(--gray-700);
    background: var(--gray-100);
}

.password-strength {
    margin-top: var(--spacing-2);
}

.strength-bar {
    width: 100%;
    height: 4px;
    background: var(--gray-200);
    border-radius: var(--radius-full);
    overflow: hidden;
    margin-bottom: var(--spacing-1);
}

.strength-fill {
    height: 100%;
    width: 0%;
    background: var(--gray-300);
    transition: all var(--transition-normal);
    border-radius: var(--radius-full);
}

.strength-text {
    font-size: var(--font-size-sm);
    color: var(--gray-600);
    font-weight: 500;
}

.field-error {
    color: var(--error-color);
    font-size: var(--font-size-sm);
    margin-top: var(--spacing-1);
    min-height: 20px;
}

.form-actions {
    display: flex;
    gap: var(--spacing-4);
    justify-content: flex-end;
    padding-top: var(--spacing-6);
    border-top: 1px solid var(--gray-200);
}

.form-actions .btn {
    min-width: 120px;
    justify-content: center;
}

/* Responsive Design */
@media (max-width: 768px) {
    .profile-form-container {
        padding: var(--spacing-6);
        margin: 0 var(--spacing-4);
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: var(--spacing-4);
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .profile-form-container {
        padding: var(--spacing-4);
        margin: 0 var(--spacing-2);
    }
    
    .section-header {
        padding-bottom: var(--spacing-3);
    }
    
    .form-row {
        margin-bottom: var(--spacing-4);
    }
}
</style>
