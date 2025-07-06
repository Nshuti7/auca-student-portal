<?php
// login.php — displays the login form
session_start();
if (isset($_SESSION['user_id'])) {
    // Redirect based on user role
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: student_dashboard.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Adventist University of Central Africa</title>
    <meta name="description" content="Access your AUCA student portal - manage courses, track progress, and connect with your academic community.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="login-page">
    <?php require __DIR__ . '/includes/navigation.php'; ?>

    <main class="login-main">
        <div class="container">
            <div class="login-container">
                <!-- Login Form Section -->
                <div class="login-form-section">
                    <div class="login-header">
                        <div class="login-logo">
                            <img src="assets/images/logo.png" alt="AUCA Logo" class="header-logo">
                            <div class="header-text">
                                <h1>Welcome Back</h1>
                                <p>Sign in to your AUCA Student Portal</p>
                            </div>
                        </div>
                    </div>

                    <div class="login-form-container">
                        <form id="loginForm" action="login_process.php" method="post" class="login-form">
                            <div class="form-group">
                                <label for="username_or_email" class="form-label">
                                    <div class="icon icon-mail label-icon"></div>
                                    <span>Email or Username</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="username_or_email" 
                                    name="username_or_email" 
                                    class="form-input"
                                    placeholder="Enter your email or username"
                                    autocomplete="username"
                                    required>
                                <div class="field-error" id="username_error"></div>
                            </div>

                            <div class="form-group">
                                <label for="password" class="form-label">
                                    <div class="icon icon-lock label-icon"></div>
                                    <span>Password</span>
                                </label>
                                <div class="password-input-wrapper">
                                    <input 
                                        type="password" 
                                        id="password" 
                                        name="password" 
                                        class="form-input"
                                        placeholder="Enter your password"
                                        autocomplete="current-password"
                                        required>
                                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password')">
                                        <div class="icon icon-eye"></div>
                                    </button>
                                </div>
                                <div class="field-error" id="password_error"></div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-lg login-submit">
                                    <div class="icon icon-login btn-icon"></div>
                                    <span>Sign In</span>
                                </button>

                                <div class="form-links">
                                    <a href="forgot_password.php" class="forgot-link">
                                        <div class="icon icon-help"></div>
                                        <span>Forgot your password?</span>
                                    </a>
                                </div>
                            </div>
                        </form>

                        <div class="signup-prompt">
                            <p>New to AUCA?</p>
                            <a href="signup.php" class="btn btn-outline-primary">
                                <div class="icon icon-user-plus btn-icon"></div>
                                <span>Create Account</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Information Section -->
                <div class="login-info-section">
                    <div class="info-content">
                        <div class="info-hero">
                            <div class="info-illustration">
                                <img src="assets/images/building.jpg" alt="AUCA Campus" class="campus-image">
                                <div class="info-overlay"></div>
                            </div>
                            <div class="info-text">
                                <h2>Your Academic Journey Awaits</h2>
                                <p>Access your personalized dashboard, enroll in courses, track your progress, and connect with the AUCA community.</p>
                            </div>
                        </div>

                        <div class="feature-highlights">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <div class="icon icon-dashboard text-primary"></div>
                                </div>
                                <div class="feature-content">
                                    <h3>Personal Dashboard</h3>
                                    <p>Track assignments, grades, and academic progress in real-time</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <div class="icon icon-courses text-success"></div>
                                </div>
                                <div class="feature-content">
                                    <h3>Course Management</h3>
                                    <p>Enroll in courses and access learning materials online</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <div class="icon icon-users text-warning"></div>
                                </div>
                                <div class="feature-content">
                                    <h3>Community Connection</h3>
                                    <p>Connect with classmates and faculty members</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> Adventist University of Central Africa. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/app.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle URL parameters for errors and messages
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');
            const success = urlParams.get('success');
            
            if (error) {
                toast.error(decodeURIComponent(error));
                // Clear the URL parameter
                window.history.replaceState({}, '', window.location.pathname);
            }
            
            if (success) {
                toast.success(decodeURIComponent(success));
                // Clear the URL parameter
                window.history.replaceState({}, '', window.location.pathname);
            }

            // Focus on first input
            const firstInput = document.querySelector('#username_or_email');
            if (firstInput) {
                firstInput.focus();
            }

            // Enhanced form handling
            const form = document.getElementById('loginForm');
            const submitButton = form.querySelector('.login-submit');
            
            form.addEventListener('submit', function(e) {
                // Add loading state
                submitButton.classList.add('loading');
                submitButton.innerHTML = `
                    <div class="icon icon-spinner btn-icon spinning"></div>
                    <span>Signing In...</span>
                `;
                submitButton.disabled = true;
            });

            // Form validation
            const inputs = form.querySelectorAll('input[required]');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });
                
                input.addEventListener('input', function() {
                    if (this.classList.contains('error')) {
                        clearFieldError(this);
                    }
                });
            });
        });

        function togglePasswordVisibility(fieldId) {
            const field = document.getElementById(fieldId);
            const toggle = field.parentNode.querySelector('.password-toggle .icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                toggle.className = 'icon icon-eye-off';
            } else {
                field.type = 'password';
                toggle.className = 'icon icon-eye';
            }
        }

        function validateField(field) {
            const value = field.value.trim();
            let isValid = true;
            let message = '';

            if (!value) {
                isValid = false;
                message = 'This field is required';
            } else if (field.name === 'username_or_email') {
                if (value.length < 3) {
                    isValid = false;
                    message = 'Username or email must be at least 3 characters';
                }
            } else if (field.name === 'password') {
                if (value.length < 6) {
                    isValid = false;
                    message = 'Password must be at least 6 characters';
                }
            }

            if (!isValid) {
                showFieldError(field, message);
            } else {
                clearFieldError(field);
            }

            return isValid;
        }

        function showFieldError(field, message) {
            field.classList.add('error');
            const errorDiv = document.getElementById(field.name.replace('_or_email', '') + '_error');
            if (errorDiv) {
                errorDiv.textContent = message;
                errorDiv.style.display = 'block';
            }
        }

        function clearFieldError(field) {
            field.classList.remove('error');
            const errorDiv = document.getElementById(field.name.replace('_or_email', '') + '_error');
            if (errorDiv) {
                errorDiv.textContent = '';
                errorDiv.style.display = 'none';
            }
        }
    </script>
</body>
</html>

<style>
/* Login Page Styles */
.login-page {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    min-height: 100vh;
}

.login-main {
    padding: var(--spacing-8) 0;
    min-height: calc(100vh - 80px);
    display: flex;
    align-items: center;
}

.login-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-12);
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
}

/* Login Form Section */
.login-form-section {
    background: white;
    padding: var(--spacing-12);
    border-radius: var(--border-radius-xl);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.login-header {
    text-align: center;
    margin-bottom: var(--spacing-10);
}

.login-logo {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--spacing-4);
}

.header-logo {
    width: 80px;
    height: 80px;
    object-fit: contain;
}

.header-text h1 {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--gray-900);
    margin: 0;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.header-text p {
    font-size: 1.1rem;
    color: var(--gray-600);
    margin: var(--spacing-2) 0 0;
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-6);
}

.form-label {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-weight: 500;
    color: var(--gray-700);
    margin-bottom: var(--spacing-2);
}

.label-icon {
    font-size: 16px;
    color: var(--primary-color);
}

.password-input-wrapper {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: var(--spacing-3);
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: var(--spacing-2);
    color: var(--gray-500);
    transition: color var(--transition-fast);
    border-radius: var(--border-radius);
}

.password-toggle:hover {
    color: var(--primary-color);
    background: var(--gray-100);
}

.form-input {
    width: 100%;
    padding: var(--spacing-4);
    border: 2px solid var(--gray-200);
    border-radius: var(--border-radius-lg);
    font-size: 1rem;
    transition: all var(--transition-fast);
    background: white;
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
}

.form-input.error {
    border-color: var(--error-color);
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.field-error {
    display: none;
    color: var(--error-color);
    font-size: 0.875rem;
    margin-top: var(--spacing-2);
}

.form-actions {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-4);
    margin-top: var(--spacing-4);
}

.login-submit {
    width: 100%;
    padding: var(--spacing-4);
    font-size: 1.1rem;
    font-weight: 600;
    transition: all var(--transition-fast);
}

.login-submit.loading {
    opacity: 0.8;
    transform: scale(0.98);
}

.form-links {
    text-align: center;
}

.forgot-link {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-2);
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    padding: var(--spacing-2) var(--spacing-4);
    border-radius: var(--border-radius);
    transition: all var(--transition-fast);
}

.forgot-link:hover {
    background: var(--primary-light);
    transform: translateY(-1px);
}

.signup-prompt {
    margin-top: var(--spacing-8);
    padding-top: var(--spacing-8);
    border-top: 1px solid var(--gray-200);
    text-align: center;
}

.signup-prompt p {
    margin: 0 0 var(--spacing-4);
    color: var(--gray-600);
    font-weight: 500;
}

/* Information Section */
.login-info-section {
    display: flex;
    flex-direction: column;
    justify-content: center;
    height: 100%;
}

.info-hero {
    position: relative;
    margin-bottom: var(--spacing-10);
    border-radius: var(--border-radius-xl);
    overflow: hidden;
}

.campus-image {
    width: 100%;
    height: 300px;
    object-fit: cover;
}

.info-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.8), rgba(var(--primary-rgb), 0.6));
}

.info-text {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: var(--spacing-8);
    color: white;
}

.info-text h2 {
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 var(--spacing-4);
    color: white;
}

.info-text p {
    font-size: 1.1rem;
    margin: 0;
    opacity: 0.95;
    line-height: 1.6;
}

.feature-highlights {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-6);
}

.feature-item {
    display: flex;
    gap: var(--spacing-4);
    align-items: flex-start;
    padding: var(--spacing-4);
    background: white;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-sm);
    transition: transform var(--transition-fast), box-shadow var(--transition-fast);
}

.feature-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.feature-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--gray-100);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.feature-icon .icon {
    font-size: 20px;
}

.feature-content h3 {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0 0 var(--spacing-2);
    color: var(--gray-900);
}

.feature-content p {
    font-size: 0.9rem;
    margin: 0;
    color: var(--gray-600);
    line-height: 1.5;
}

/* Animations */
@keyframes spinning {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.spinning {
    animation: spinning 1s linear infinite;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .login-container {
        grid-template-columns: 1fr;
        gap: var(--spacing-8);
        padding: 0 var(--spacing-4);
    }
    
    .login-info-section {
        order: -1;
    }
    
    .feature-highlights {
        flex-direction: row;
        gap: var(--spacing-4);
    }
    
    .feature-item {
        flex: 1;
        flex-direction: column;
        text-align: center;
    }
}

@media (max-width: 768px) {
    .login-main {
        padding: var(--spacing-4) 0;
    }
    
    .login-form-section {
        padding: var(--spacing-8);
    }
    
    .header-text h1 {
        font-size: 2rem;
    }
    
    .info-text h2 {
        font-size: 1.5rem;
    }
    
    .feature-highlights {
        flex-direction: column;
    }
    
    .feature-item {
        flex-direction: row;
        text-align: left;
    }
}

@media (max-width: 480px) {
    .login-container {
        padding: 0 var(--spacing-2);
    }
    
    .login-form-section {
        padding: var(--spacing-6);
    }
    
    .header-logo {
        width: 60px;
        height: 60px;
    }
    
    .campus-image {
        height: 200px;
    }
    
    .info-text {
        padding: var(--spacing-6);
    }
}
</style>
