<?php
// forgot_password.php — Password reset request form
session_start();

// Redirect if already logged in
if (isset($_SESSION['student_id'])) {
    header('Location: profile.php');
    exit;
}

// Handle success/error messages
$message = '';
$error = '';
if (isset($_GET['success'])) {
    $message = htmlspecialchars($_GET['success']);
}
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Student Portal</title>
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
            <div class="forgot-password-container">
                <div class="forgot-password-header">
                    <h1>
                        <div class="icon icon-lock icon-text">
                            <span>Forgot Password</span>
                        </div>
                    </h1>
                    <p>Enter your email address and we'll send you a link to reset your password</p>
                </div>
                
                <?php if ($message): ?>
                    <div class="success-message">
                        <p><?= $message ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="error-message">
                        <p><?= $error ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="forgot-password-form-container">
                    <form id="forgotPasswordForm" method="post" action="send_reset_email.php">
                        <div class="form-group">
                            <label for="email">
                                <div class="icon icon-email icon-text">
                                    <span>Email Address</span>
                                </div>
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="<?= htmlspecialchars($_GET['email'] ?? '') ?>" 
                                   required 
                                   placeholder="Enter your email address">
                            <small class="form-help">We'll send password reset instructions to this email</small>
                            <div class="field-error" id="email_error"></div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <div class="icon icon-email btn-icon"></div>
                                <span>Send Reset Link</span>
                            </button>
                            <a href="login.php" class="btn btn-secondary btn-lg">
                                <div class="icon icon-arrow-left btn-icon"></div>
                                <span>Back to Login</span>
                            </a>
                        </div>
                    </form>
                </div>
                
                <div class="help-section">
                    <div class="section-header">
                        <h3>
                            <div class="icon icon-info icon-text">
                                <span>Password Reset Help</span>
                            </div>
                        </h3>
                    </div>
                    
                    <div class="help-cards">
                        <div class="help-card">
                            <div class="help-icon">
                                <div class="icon icon-email"></div>
                            </div>
                            <div class="help-content">
                                <h4>Enter Your Email</h4>
                                <p>Use the email address associated with your account</p>
                            </div>
                        </div>
                        
                        <div class="help-card">
                            <div class="help-icon">
                                <div class="icon icon-clock"></div>
                            </div>
                            <div class="help-content">
                                <h4>Check Your Inbox</h4>
                                <p>Reset instructions will arrive within a few minutes</p>
                            </div>
                        </div>
                        
                        <div class="help-card">
                            <div class="help-icon">
                                <div class="icon icon-lock"></div>
                            </div>
                            <div class="help-content">
                                <h4>Reset Your Password</h4>
                                <p>Click the link in the email to create a new password</p>
                            </div>
                        </div>
                        
                        <div class="help-card">
                            <div class="help-icon">
                                <div class="icon icon-clock"></div>
                            </div>
                            <div class="help-content">
                                <h4>Link Expires</h4>
                                <p>Reset links expire after 1 hour for security</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="contact-support">
                        <div class="support-card">
                            <div class="support-icon">
                                <div class="icon icon-help"></div>
                            </div>
                            <div class="support-content">
                                <h4>Still Having Trouble?</h4>
                                <p>Contact our support team for assistance</p>
                                <a href="mailto:support@studentportal.com" class="support-link">
                                    <div class="icon icon-email"></div>
                                    <span>support@studentportal.com</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
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
        // Forgot password specific JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('forgotPasswordForm');
            
            // Handle success/error messages from URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            
            if (urlParams.has('success')) {
                toast.success(urlParams.get('success'));
                // Clean URL
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            if (urlParams.has('error')) {
                toast.error(urlParams.get('error'));
                // Clean URL
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            // Initialize form validation
            const validator = new FormValidator(form);
            validator.addRule('email', {
                required: true,
                email: true
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
                        <span>Sending...</span>
                    `;
                    submitBtn.disabled = true;
                    
                    // Submit form
                    setTimeout(() => {
                        form.submit();
                    }, 500);
                } else {
                    toast.error('Please enter a valid email address');
                }
            });
            
            // Add animation to help cards
            const helpCards = document.querySelectorAll('.help-card');
            helpCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease-out';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 150);
            });
            
            // Add focus effect to email input
            const emailInput = document.getElementById('email');
            emailInput.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            emailInput.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>

<style>
/* Forgot password page specific styles */
.forgot-password-container {
    max-width: 600px;
    margin: 0 auto;
    padding: var(--spacing-6) 0;
}

.forgot-password-header {
    text-align: center;
    margin-bottom: var(--spacing-8);
}

.forgot-password-header h1 {
    color: var(--primary-color);
    margin-bottom: var(--spacing-2);
}

.forgot-password-header p {
    color: var(--gray-600);
    font-size: var(--font-size-lg);
}

.forgot-password-form-container {
    background: var(--white);
    padding: var(--spacing-8);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--gray-200);
    margin-bottom: var(--spacing-8);
}

.form-group {
    margin-bottom: var(--spacing-6);
    transition: all var(--transition-normal);
}

.form-group label {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    margin-bottom: var(--spacing-2);
    font-weight: 600;
    color: var(--gray-700);
}

.form-group input {
    width: 100%;
    padding: var(--spacing-3);
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-md);
    font-size: var(--font-size-base);
    transition: all var(--transition-normal);
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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

.form-actions {
    display: flex;
    gap: var(--spacing-4);
    justify-content: center;
    margin-top: var(--spacing-6);
}

.help-section {
    background: var(--white);
    padding: var(--spacing-8);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--gray-200);
}

.section-header {
    margin-bottom: var(--spacing-6);
}

.section-header h3 {
    color: var(--gray-900);
    margin: 0;
}

.help-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-4);
    margin-bottom: var(--spacing-8);
}

.help-card {
    background: var(--gray-50);
    padding: var(--spacing-4);
    border-radius: var(--radius-md);
    border: 1px solid var(--gray-200);
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    transition: all var(--transition-normal);
}

.help-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary-color);
}

.help-icon {
    width: 40px;
    height: 40px;
    background: var(--primary-color);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    flex-shrink: 0;
}

.help-content h4 {
    margin: 0 0 var(--spacing-1) 0;
    color: var(--gray-900);
    font-size: var(--font-size-sm);
    font-weight: 600;
}

.help-content p {
    margin: 0;
    color: var(--gray-600);
    font-size: var(--font-size-sm);
}

.contact-support {
    border-top: 1px solid var(--gray-200);
    padding-top: var(--spacing-6);
}

.support-card {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    padding: var(--spacing-6);
    border-radius: var(--radius-md);
    color: var(--white);
    text-align: center;
}

.support-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto var(--spacing-4);
    color: var(--white);
}

.support-content h4 {
    margin: 0 0 var(--spacing-2) 0;
    color: var(--white);
    font-size: var(--font-size-lg);
}

.support-content p {
    margin: 0 0 var(--spacing-4) 0;
    color: rgba(255, 255, 255, 0.9);
}

.support-link {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-2);
    color: var(--white);
    text-decoration: none;
    background: rgba(255, 255, 255, 0.2);
    padding: var(--spacing-2) var(--spacing-4);
    border-radius: var(--radius-md);
    transition: all var(--transition-normal);
}

.support-link:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-1px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .forgot-password-container {
        padding: var(--spacing-4) var(--spacing-2);
    }
    
    .forgot-password-form-container {
        padding: var(--spacing-6);
    }
    
    .help-section {
        padding: var(--spacing-6);
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
    
    .help-cards {
        grid-template-columns: 1fr;
    }
    
    .help-card {
        flex-direction: column;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .forgot-password-form-container {
        padding: var(--spacing-4);
    }
    
    .help-section {
        padding: var(--spacing-4);
    }
    
    .help-cards {
        gap: var(--spacing-3);
    }
    
    .help-card {
        padding: var(--spacing-3);
    }
    
    .support-card {
        padding: var(--spacing-4);
    }
}
</style> 