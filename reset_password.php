<?php
// reset_password.php — Password reset form with token validation
session_start();
require __DIR__ . '/includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['student_id'])) {
    header('Location: profile.php');
    exit;
}

// Check if token is provided
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header('Location: forgot_password.php?error=Invalid or missing reset token');
    exit;
}

$token = $_GET['token'];
$error = '';
$user_email = '';

// Validate token and get user information
try {
    $stmt = $pdo->prepare("
        SELECT pr.email, pr.expires_at, s.full_name 
        FROM password_resets pr
        JOIN students s ON pr.email = s.email
        WHERE pr.token = :token AND pr.used = FALSE AND pr.expires_at > :current_time
    ");
    $stmt->execute([
        ':token' => $token,
        ':current_time' => date('Y-m-d H:i:s')
    ]);
    $reset_data = $stmt->fetch();
    
    if (!$reset_data) {
        // Check if token exists but is expired or used
        $stmt = $pdo->prepare("SELECT expires_at, used FROM password_resets WHERE token = :token");
        $stmt->execute([':token' => $token]);
        $token_info = $stmt->fetch();
        
        if ($token_info) {
            if ($token_info['used']) {
                $error = 'This reset link has already been used. Please request a new password reset.';
            } else {
                $error = 'This reset link has expired. Please request a new password reset.';
            }
        } else {
            $error = 'Invalid reset link. Please request a new password reset.';
        }
    } else {
        $user_email = $reset_data['email'];
    }
} catch (PDOException $e) {
    $error = 'A system error occurred. Please try again later.';
    error_log('Password reset token validation error: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Student Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/icons.css">
</head>
<body>
    <header>
        <div class="header-content">
            <img src="assets/images/logo.png" alt="Student Portal Logo" class="logo">
            <nav>
                <ul>
                    <li>
                        <a href="index.php">
                            <div class="icon icon-home nav-icon"></div>
                            <span>Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="courses.php">
                            <div class="icon icon-courses nav-icon"></div>
                            <span>Courses</span>
                        </a>
                    </li>
                    <li>
                        <a href="login.php">
                            <div class="icon icon-login nav-icon"></div>
                            <span>Login</span>
                        </a>
                    </li>
                    <li>
                        <a href="signup.php">
                            <div class="icon icon-user-plus nav-icon"></div>
                            <span>Sign Up</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="reset-password-container">
                <div class="reset-password-header">
                    <h1>
                        <div class="icon icon-lock icon-text">
                            <span>Reset Your Password</span>
                        </div>
                    </h1>
                </div>
                
                <?php if ($error): ?>
                    <div class="error-container">
                        <div class="error-card">
                            <div class="error-icon">
                                <div class="icon icon-warning icon-xl"></div>
                            </div>
                            <div class="error-content">
                                <h3>Reset Link Issue</h3>
                                <p><?= htmlspecialchars($error) ?></p>
                                <div class="error-actions">
                                    <a href="forgot_password.php" class="btn btn-primary">
                                        <div class="icon icon-email btn-icon"></div>
                                        <span>Request New Reset</span>
                                    </a>
                                    <a href="login.php" class="btn btn-secondary">
                                        <div class="icon icon-arrow-left btn-icon"></div>
                                        <span>Back to Login</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="reset-password-form-container">
                        <div class="form-header">
                            <div class="user-info">
                                <div class="user-icon">
                                    <div class="icon icon-profile"></div>
                                </div>
                                <div class="user-details">
                                    <h3>Resetting password for</h3>
                                    <p><?= htmlspecialchars($user_email) ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <form method="post" action="process_reset.php" id="resetForm">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                            
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
                                           required 
                                           minlength="8" 
                                           placeholder="Enter your new password">
                                    <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                                        <div class="icon icon-eye"></div>
                                    </button>
                                </div>
                                <small class="form-help">Password must be at least 8 characters long</small>
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
                                           required 
                                           minlength="8" 
                                           placeholder="Confirm your new password">
                                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)">
                                        <div class="icon icon-eye"></div>
                                    </button>
                                </div>
                                <small class="form-help">Re-enter your password to confirm</small>
                                <div class="field-error" id="confirm_password_error"></div>
                            </div>
                            
                            <div class="password-strength" id="passwordStrength">
                                <div class="strength-header">
                                    <h4>Password Strength</h4>
                                </div>
                                <div class="strength-meter">
                                    <div class="strength-bar" id="strengthBar"></div>
                                </div>
                                <div class="strength-text" id="strengthText">Password strength will appear here</div>
                            </div>
                            
                            <div class="password-requirements">
                                <h4>Password Requirements</h4>
                                <div class="requirements-list">
                                    <div class="requirement" id="req-length">
                                        <div class="req-icon">
                                            <div class="icon icon-x"></div>
                                        </div>
                                        <span>At least 8 characters</span>
                                    </div>
                                    <div class="requirement" id="req-uppercase">
                                        <div class="req-icon">
                                            <div class="icon icon-x"></div>
                                        </div>
                                        <span>At least one uppercase letter</span>
                                    </div>
                                    <div class="requirement" id="req-lowercase">
                                        <div class="req-icon">
                                            <div class="icon icon-x"></div>
                                        </div>
                                        <span>At least one lowercase letter</span>
                                    </div>
                                    <div class="requirement" id="req-number">
                                        <div class="req-icon">
                                            <div class="icon icon-x"></div>
                                        </div>
                                        <span>At least one number</span>
                                    </div>
                                    <div class="requirement" id="req-match">
                                        <div class="req-icon">
                                            <div class="icon icon-x"></div>
                                        </div>
                                        <span>Passwords match</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                                    <div class="icon icon-lock btn-icon"></div>
                                    <span>Reset Password</span>
                                </button>
                                <a href="login.php" class="btn btn-secondary btn-lg">
                                    <div class="icon icon-x btn-icon"></div>
                                    <span>Cancel</span>
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    <div class="security-notice">
                        <div class="section-header">
                            <h3>
                                <div class="icon icon-shield icon-text">
                                    <span>Security Notice</span>
                                </div>
                            </h3>
                        </div>
                        <div class="security-cards">
                            <div class="security-card">
                                <div class="security-icon">
                                    <div class="icon icon-lock"></div>
                                </div>
                                <div class="security-content">
                                    <h4>Secure Encryption</h4>
                                    <p>Your new password will be securely encrypted</p>
                                </div>
                            </div>
                            <div class="security-card">
                                <div class="security-icon">
                                    <div class="icon icon-login"></div>
                                </div>
                                <div class="security-content">
                                    <h4>Auto Login</h4>
                                    <p>You'll be automatically logged in after reset</p>
                                </div>
                            </div>
                            <div class="security-card">
                                <div class="security-icon">
                                    <div class="icon icon-check"></div>
                                </div>
                                <div class="security-content">
                                    <h4>Link Invalidation</h4>
                                    <p>This reset link will be invalidated after use</p>
                                </div>
                            </div>
                            <div class="security-card">
                                <div class="security-icon">
                                    <div class="icon icon-shield"></div>
                                </div>
                                <div class="security-content">
                                    <h4>Enhanced Security</h4>
                                    <p>Consider enabling two-factor authentication</p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> Student Portal. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/app.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.getElementById('password');
            const confirmField = document.getElementById('confirm_password');
            const submitBtn = document.getElementById('submitBtn');
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            // Password requirements elements
            const reqLength = document.getElementById('req-length');
            const reqUppercase = document.getElementById('req-uppercase');
            const reqLowercase = document.getElementById('req-lowercase');
            const reqNumber = document.getElementById('req-number');
            const reqMatch = document.getElementById('req-match');
            
            if (passwordField) {
                passwordField.addEventListener('input', function() {
                    checkPasswordStrength(this.value);
                    checkPasswordMatch();
                });
                
                confirmField.addEventListener('input', function() {
                    checkPasswordMatch();
                });
            }
            
            function checkPasswordStrength(password) {
                let score = 0;
                
                // Length check
                if (password.length >= 8) {
                    score += 1;
                    updateRequirement(reqLength, true);
                } else {
                    updateRequirement(reqLength, false);
                }
                
                // Uppercase check
                if (/[A-Z]/.test(password)) {
                    score += 1;
                    updateRequirement(reqUppercase, true);
                } else {
                    updateRequirement(reqUppercase, false);
                }
                
                // Lowercase check
                if (/[a-z]/.test(password)) {
                    score += 1;
                    updateRequirement(reqLowercase, true);
                } else {
                    updateRequirement(reqLowercase, false);
                }
                
                // Number check
                if (/[0-9]/.test(password)) {
                    score += 1;
                    updateRequirement(reqNumber, true);
                } else {
                    updateRequirement(reqNumber, false);
                }
                
                updateStrengthMeter(score, password.length);
                updateSubmitButton();
            }
            
            function checkPasswordMatch() {
                const password = passwordField.value;
                const confirmPassword = confirmField.value;
                
                if (password && confirmPassword) {
                    if (password === confirmPassword) {
                        updateRequirement(reqMatch, true);
                    } else {
                        updateRequirement(reqMatch, false);
                    }
                } else {
                    updateRequirement(reqMatch, false);
                }
                
                updateSubmitButton();
            }
            
            function updateRequirement(element, isValid) {
                const icon = element.querySelector('.icon');
                if (isValid) {
                    element.classList.add('valid');
                    icon.className = 'icon icon-check';
                } else {
                    element.classList.remove('valid');
                    icon.className = 'icon icon-x';
                }
            }
            
            function updateStrengthMeter(score, length) {
                const percentage = (score / 4) * 100;
                strengthBar.style.width = percentage + '%';
                
                let strength = '';
                let color = '';
                
                if (length === 0) {
                    strength = 'Password strength will appear here';
                    color = 'var(--gray-400)';
                } else if (score < 2) {
                    strength = 'Weak';
                    color = 'var(--error-color)';
                } else if (score < 3) {
                    strength = 'Fair';
                    color = 'var(--warning-color)';
                } else if (score < 4) {
                    strength = 'Good';
                    color = 'var(--info-color)';
                } else {
                    strength = 'Strong';
                    color = 'var(--success-color)';
                }
                
                strengthText.textContent = strength;
                strengthText.style.color = color;
                strengthBar.style.backgroundColor = color;
            }
            
            function updateSubmitButton() {
                const allRequirements = document.querySelectorAll('.requirement.valid');
                const allValid = allRequirements.length === 5; // All 5 requirements met
                
                submitBtn.disabled = !allValid;
                
                if (allValid) {
                    submitBtn.classList.remove('disabled');
                } else {
                    submitBtn.classList.add('disabled');
                }
            }
            
            // Form submission
            const form = document.getElementById('resetForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const allRequirements = document.querySelectorAll('.requirement.valid');
                    if (allRequirements.length === 5) {
                        // Show loading state
                        submitBtn.innerHTML = `
                            <div class="icon icon-spinning btn-icon"></div>
                            <span>Resetting...</span>
                        `;
                        submitBtn.disabled = true;
                        
                        // Submit form
                        setTimeout(() => {
                            form.submit();
                        }, 500);
                    } else {
                        toast.error('Please meet all password requirements');
                    }
                });
            }
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
    </script>
</body>
</html>

<style>
/* Reset password page specific styles */
.reset-password-container {
    max-width: 700px;
    margin: 0 auto;
    padding: var(--spacing-6) 0;
}

.reset-password-header {
    text-align: center;
    margin-bottom: var(--spacing-8);
}

.reset-password-header h1 {
    color: var(--primary-color);
    margin-bottom: var(--spacing-2);
}

.error-container {
    margin-bottom: var(--spacing-8);
}

.error-card {
    background: var(--white);
    border: 1px solid var(--error-color);
    border-radius: var(--radius-lg);
    padding: var(--spacing-8);
    text-align: center;
    box-shadow: var(--shadow-md);
}

.error-icon {
    width: 80px;
    height: 80px;
    background: var(--error-light);
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto var(--spacing-4);
    color: var(--error-color);
}

.error-content h3 {
    color: var(--error-color);
    margin-bottom: var(--spacing-2);
}

.error-content p {
    color: var(--gray-600);
    margin-bottom: var(--spacing-6);
}

.error-actions {
    display: flex;
    gap: var(--spacing-4);
    justify-content: center;
}

.reset-password-form-container {
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--gray-200);
    padding: var(--spacing-8);
    margin-bottom: var(--spacing-8);
}

.form-header {
    margin-bottom: var(--spacing-8);
    padding-bottom: var(--spacing-6);
    border-bottom: 1px solid var(--gray-200);
}

.user-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
}

.user-icon {
    width: 60px;
    height: 60px;
    background: var(--primary-color);
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
}

.user-details h3 {
    margin: 0 0 var(--spacing-1) 0;
    color: var(--gray-900);
    font-size: var(--font-size-lg);
}

.user-details p {
    margin: 0;
    color: var(--primary-color);
    font-weight: 600;
    font-size: var(--font-size-lg);
}

.form-group {
    margin-bottom: var(--spacing-6);
}

.form-group label {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    margin-bottom: var(--spacing-2);
    font-weight: 600;
    color: var(--gray-700);
}

.password-field {
    position: relative;
    display: flex;
    align-items: center;
}

.password-field input {
    width: 100%;
    padding: var(--spacing-3);
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-md);
    font-size: var(--font-size-base);
    transition: all var(--transition-normal);
    padding-right: 45px !important;
}

.password-field input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--gray-500);
    cursor: pointer;
    padding: 4px;
    border-radius: var(--radius-sm);
    transition: all var(--transition-normal);
    z-index: 10;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.password-toggle:hover {
    color: var(--gray-700);
    background: var(--gray-100);
}

.password-toggle:focus {
    outline: 2px solid var(--primary-color);
    outline-offset: 1px;
}

.password-toggle .icon {
    width: 16px;
    height: 16px;
}

.form-help {
    display: block;
    margin-top: var(--spacing-2);
    color: var(--gray-600);
    font-size: var(--font-size-sm);
}

.field-error {
    color: var(--error-color);
    font-size: var(--font-size-sm);
    margin-top: var(--spacing-1);
    min-height: 20px;
}

.password-strength {
    margin: var(--spacing-6) 0;
    padding: var(--spacing-4);
    background: var(--gray-50);
    border-radius: var(--radius-md);
    border: 1px solid var(--gray-200);
}

.strength-header h4 {
    margin: 0 0 var(--spacing-3) 0;
    color: var(--gray-900);
    font-size: var(--font-size-base);
}

.strength-meter {
    width: 100%;
    height: 8px;
    background: var(--gray-200);
    border-radius: var(--radius-full);
    overflow: hidden;
    margin-bottom: var(--spacing-2);
}

.strength-bar {
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

.password-requirements {
    margin: var(--spacing-6) 0;
    padding: var(--spacing-4);
    background: var(--gray-50);
    border-radius: var(--radius-md);
    border: 1px solid var(--gray-200);
}

.password-requirements h4 {
    margin: 0 0 var(--spacing-4) 0;
    color: var(--gray-900);
    font-size: var(--font-size-base);
}

.requirements-list {
    display: grid;
    gap: var(--spacing-2);
}

.requirement {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    padding: var(--spacing-2);
    border-radius: var(--radius-sm);
    transition: all var(--transition-normal);
}

.requirement.valid {
    background: var(--success-light);
    color: var(--success-color);
}

.req-icon {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.requirement.valid .req-icon {
    color: var(--success-color);
}

.requirement:not(.valid) .req-icon {
    color: var(--error-color);
}

.form-actions {
    display: flex;
    gap: var(--spacing-4);
    justify-content: center;
    margin-top: var(--spacing-8);
}

.security-notice {
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--gray-200);
    padding: var(--spacing-8);
}

.section-header {
    margin-bottom: var(--spacing-6);
}

.section-header h3 {
    color: var(--gray-900);
    margin: 0;
}

.security-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-4);
}

.security-card {
    background: var(--gray-50);
    padding: var(--spacing-4);
    border-radius: var(--radius-md);
    border: 1px solid var(--gray-200);
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    transition: all var(--transition-normal);
}

.security-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary-color);
}

.security-icon {
    width: 40px;
    height: 40px;
    background: var(--success-color);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    flex-shrink: 0;
}

.security-content h4 {
    margin: 0 0 var(--spacing-1) 0;
    color: var(--gray-900);
    font-size: var(--font-size-sm);
    font-weight: 600;
}

.security-content p {
    margin: 0;
    color: var(--gray-600);
    font-size: var(--font-size-sm);
}

/* Responsive Design */
@media (max-width: 768px) {
    .reset-password-container {
        padding: var(--spacing-4) var(--spacing-2);
    }
    
    .reset-password-form-container {
        padding: var(--spacing-6);
    }
    
    .security-notice {
        padding: var(--spacing-6);
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
    
    .error-actions {
        flex-direction: column;
    }
    
    .error-actions .btn {
        width: 100%;
        justify-content: center;
    }
    
    .security-cards {
        grid-template-columns: 1fr;
    }
    
    .security-card {
        flex-direction: column;
        text-align: center;
    }
    
    .user-info {
        flex-direction: column;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .reset-password-form-container {
        padding: var(--spacing-4);
    }
    
    .security-notice {
        padding: var(--spacing-4);
    }
    
    .security-cards {
        gap: var(--spacing-3);
    }
    
    .security-card {
        padding: var(--spacing-3);
    }
}
</style> 
</style> 