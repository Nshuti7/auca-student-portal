<?php
// signup.php — displays the registration form
session_start();
if (isset($_SESSION['user_id'])) {
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
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Apply Now - Adventist University of Central Africa</title>
  <meta name="description" content="Join AUCA - Create your student account and begin your academic journey at Adventist University of Central Africa.">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="signup-page">
  <?php require __DIR__ . '/includes/navigation.php'; ?>

  <main class="signup-main">
    <div class="container">
      <div class="signup-container">
        <!-- Information Section -->
        <div class="signup-info-section">
          <div class="info-content">
            <div class="welcome-header">
              <img src="assets/images/logo.png" alt="AUCA Logo" class="welcome-logo">
              <div class="welcome-text">
                <h1>Join AUCA</h1>
                <p>Begin Your Academic Journey</p>
              </div>
            </div>

            <div class="info-hero">
              <div class="info-illustration">
                <img src="assets/images/building.jpg" alt="AUCA Campus" class="campus-image">
                <div class="info-overlay"></div>
              </div>
              <div class="info-text">
                <h2>Excellence in Education</h2>
                <p>Join a community of scholars dedicated to academic excellence, spiritual growth, and service to humanity across Central Africa.</p>
              </div>
            </div>

            <div class="benefits-list">
              <div class="benefit-item">
                <div class="benefit-icon">
                  <div class="icon icon-graduation text-primary"></div>
                </div>
                <div class="benefit-content">
                  <h3>World-Class Education</h3>
                  <p>Internationally recognized programs with experienced faculty</p>
                </div>
              </div>
              <div class="benefit-item">
                <div class="benefit-icon">
                  <div class="icon icon-users text-success"></div>
                </div>
                <div class="benefit-content">
                  <h3>Diverse Community</h3>
                  <p>Students from across Central Africa creating lasting connections</p>
                </div>
              </div>
              <div class="benefit-item">
                <div class="benefit-icon">
                  <div class="icon icon-star text-warning"></div>
                </div>
                <div class="benefit-content">
                  <h3>Holistic Development</h3>
                  <p>Academic, spiritual, and personal growth opportunities</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Signup Form Section -->
        <div class="signup-form-section">
          <div class="form-header">
            <h2>Create Your Account</h2>
            <p>Fill out the form below to get started with your AUCA journey</p>
          </div>

          <form id="signupForm" action="signup_process.php" method="post" class="signup-form">
            <!-- Personal Information -->
            <div class="form-section">
              <div class="section-title">
                <div class="icon icon-profile section-icon"></div>
                <h3>Personal Information</h3>
              </div>
              
              <div class="form-row">
                <div class="form-group">
                  <label for="full_name" class="form-label">
                    <div class="icon icon-profile label-icon"></div>
                    <span>Full Name</span>
                    <span class="required">*</span>
                  </label>
                  <input 
                    type="text" 
                    id="full_name" 
                    name="full_name" 
                    class="form-input"
                    placeholder="Enter your full name"
                    autocomplete="name"
                    required>
                  <div class="field-error" id="full_name_error"></div>
                </div>
                
                <div class="form-group">
                  <label for="email" class="form-label">
                    <div class="icon icon-mail label-icon"></div>
                    <span>Email Address</span>
                    <span class="required">*</span>
                  </label>
                  <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-input"
                    placeholder="Enter your email address"
                    autocomplete="email"
                    required>
                  <div class="field-error" id="email_error"></div>
                </div>
              </div>
              
              <div class="form-row">
                <div class="form-group">
                  <label for="username" class="form-label">
                    <div class="icon icon-user label-icon"></div>
                    <span>Username</span>
                    <span class="required">*</span>
                  </label>
                  <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-input"
                    placeholder="Choose a username"
                    autocomplete="username"
                    required>
                  <div class="field-error" id="username_error"></div>
                  <div class="field-hint">Username must be 3-20 characters, letters and numbers only</div>
                </div>
                
                <div class="form-group">
                  <label for="phone" class="form-label">
                    <div class="icon icon-phone label-icon"></div>
                    <span>Phone Number</span>
                    <span class="optional">(optional)</span>
                  </label>
                  <input 
                    type="tel" 
                    id="phone" 
                    name="phone" 
                    class="form-input"
                    placeholder="+250 788 123 456"
                    autocomplete="tel">
                  <div class="field-error" id="phone_error"></div>
                </div>
              </div>
              
              <div class="form-group">
                <label for="address" class="form-label">
                  <div class="icon icon-location label-icon"></div>
                  <span>Address</span>
                  <span class="optional">(optional)</span>
                </label>
                <textarea 
                  id="address" 
                  name="address" 
                  class="form-input form-textarea"
                  placeholder="Enter your address"
                  rows="3"></textarea>
                <div class="field-error" id="address_error"></div>
              </div>
            </div>

            <!-- Password Section -->
            <div class="form-section">
              <div class="section-title">
                <div class="icon icon-lock section-icon"></div>
                <h3>Account Security</h3>
              </div>
              
              <div class="form-row">
                <div class="form-group">
                  <label for="password" class="form-label">
                    <div class="icon icon-lock label-icon"></div>
                    <span>Password</span>
                    <span class="required">*</span>
                  </label>
                  <div class="password-input-wrapper">
                    <input 
                      type="password" 
                      id="password" 
                      name="password" 
                      class="form-input"
                      placeholder="Create a strong password"
                      autocomplete="new-password"
                      required>
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password')">
                      <div class="icon icon-eye"></div>
                    </button>
                  </div>
                  <div class="field-error" id="password_error"></div>
                </div>
                
                <div class="form-group">
                  <label for="confirm_password" class="form-label">
                    <div class="icon icon-lock label-icon"></div>
                    <span>Confirm Password</span>
                    <span class="required">*</span>
                  </label>
                  <div class="password-input-wrapper">
                    <input 
                      type="password" 
                      id="confirm_password" 
                      name="confirm_password" 
                      class="form-input"
                      placeholder="Confirm your password"
                      autocomplete="new-password"
                      required>
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('confirm_password')">
                      <div class="icon icon-eye"></div>
                    </button>
                  </div>
                  <div class="field-error" id="confirm_password_error"></div>
                </div>
              </div>
            </div>

            <!-- Terms and Submit -->
            <div class="form-actions">
              <div class="terms-agreement">
                <label class="checkbox-label">
                  <input type="checkbox" id="terms" name="terms" required>
                  <span class="checkmark"></span>
                  <span class="terms-text">
                    I agree to the Terms of Service and Privacy Policy
                  </span>
                </label>
              </div>

              <button type="submit" class="btn btn-primary btn-lg signup-submit">
                <div class="icon icon-user-plus btn-icon"></div>
                <span>Create Account</span>
              </button>

              <div class="login-prompt">
                <p>Already have an account?</p>
                <a href="login.php" class="btn btn-outline-primary">
                  <div class="icon icon-login btn-icon"></div>
                  <span>Sign In</span>
                </a>
              </div>
            </div>
        </form>
        </div>
      </div>
    </div>
  </main>

  <script src="assets/js/app.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Handle URL parameters for errors and messages
      const urlParams = new URLSearchParams(window.location.search);
      const error = urlParams.get('error');
      const success = urlParams.get('success');
      
      if (error) {
        toast.error(decodeURIComponent(error));
        window.history.replaceState({}, '', window.location.pathname);
      }
      
      if (success) {
        toast.success(decodeURIComponent(success));
        window.history.replaceState({}, '', window.location.pathname);
      }

      // Focus on first input
      const firstInput = document.querySelector('#full_name');
      if (firstInput) {
        firstInput.focus();
      }

      // Enhanced form handling
      const form = document.getElementById('signupForm');
      const submitButton = form.querySelector('.signup-submit');
      
      form.addEventListener('submit', function(e) {
        // Validate all fields before submission
        let isValid = true;
        const inputs = form.querySelectorAll('input[required]');
        
        inputs.forEach(input => {
          if (!validateField(input)) {
            isValid = false;
          }
        });

        // Check password confirmation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        if (password.value !== confirmPassword.value) {
          showFieldError(confirmPassword, 'Passwords do not match');
          isValid = false;
        }

        if (!isValid) {
          e.preventDefault();
          toast.error('Please fix the errors in the form');
          return;
        }

        // Add loading state
        submitButton.classList.add('loading');
        submitButton.innerHTML = `
          <div class="icon icon-spinner btn-icon spinning"></div>
          <span>Creating Account...</span>
        `;
        submitButton.disabled = true;
      });

      // Real-time validation
      const inputs = form.querySelectorAll('input');
      inputs.forEach(input => {
        input.addEventListener('blur', function() {
          validateField(this);
        });
        
        input.addEventListener('input', function() {
          if (this.classList.contains('error')) {
            clearFieldError(this);
          }
          
          // Real-time password confirmation check
          if (this.name === 'confirm_password') {
            const password = document.getElementById('password');
            if (this.value && password.value && this.value !== password.value) {
              showFieldError(this, 'Passwords do not match');
            } else if (this.value === password.value) {
              clearFieldError(this);
            }
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

      if (field.required && !value) {
        isValid = false;
        message = 'This field is required';
      } else if (value) {
        switch (field.name) {
          case 'full_name':
            if (value.length < 2) {
              isValid = false;
              message = 'Name must be at least 2 characters';
            } else if (!/^[a-zA-Z\s]+$/.test(value)) {
              isValid = false;
              message = 'Name can only contain letters and spaces';
            }
            break;
          
          case 'email':
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
              isValid = false;
              message = 'Please enter a valid email address';
            }
            break;
          
          case 'username':
            if (value.length < 3 || value.length > 20) {
              isValid = false;
              message = 'Username must be 3-20 characters';
            } else if (!/^[a-zA-Z0-9]+$/.test(value)) {
              isValid = false;
              message = 'Username can only contain letters and numbers';
            }
            break;
          
          case 'phone':
            const phoneRegex = /^\+?[\d\s\-\(\)]+$/;
            if (value && !phoneRegex.test(value)) {
              isValid = false;
              message = 'Please enter a valid phone number';
            }
            break;
          
          case 'password':
            if (value.length < 6) {
              isValid = false;
              message = 'Password must be at least 6 characters';
            }
            break;
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
      const errorDiv = document.getElementById(field.name + '_error');
      if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
      }
    }

    function clearFieldError(field) {
      field.classList.remove('error');
      const errorDiv = document.getElementById(field.name + '_error');
      if (errorDiv) {
        errorDiv.textContent = '';
        errorDiv.style.display = 'none';
      }
    }
  </script>
</body>
</html>

<style>
/* Signup Page Styles */
.signup-page {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    min-height: 100vh;
}

.signup-main {
    padding: var(--spacing-8) 0;
    min-height: calc(100vh - 80px);
}

.signup-container {
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    gap: var(--spacing-12);
    align-items: start;
    max-width: 1400px;
    margin: 0 auto;
}

/* Information Section */
.signup-info-section {
    position: sticky;
    top: var(--spacing-8);
}

.welcome-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    margin-bottom: var(--spacing-8);
}

.welcome-logo {
    width: 80px;
    height: 80px;
    object-fit: contain;
    margin-bottom: var(--spacing-4);
}

.welcome-text h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.welcome-text p {
    font-size: 1.2rem;
    color: var(--gray-600);
    margin: var(--spacing-2) 0 0;
}

.info-hero {
    position: relative;
    margin-bottom: var(--spacing-8);
    border-radius: var(--border-radius-xl);
    overflow: hidden;
}

.campus-image {
    width: 100%;
    height: 250px;
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
    padding: var(--spacing-6);
    color: white;
}

.info-text h2 {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0 0 var(--spacing-3);
    color: white;
}

.info-text p {
    font-size: 1rem;
    margin: 0;
    opacity: 0.95;
    line-height: 1.6;
}

.benefits-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-4);
}

.benefit-item {
    display: flex;
    gap: var(--spacing-4);
    align-items: flex-start;
    padding: var(--spacing-4);
    background: white;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-sm);
    transition: transform var(--transition-fast), box-shadow var(--transition-fast);
}

.benefit-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.benefit-icon {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: var(--gray-100);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.benefit-icon .icon {
    font-size: 18px;
}

.benefit-content h3 {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 var(--spacing-1);
    color: var(--gray-900);
}

.benefit-content p {
    font-size: 0.875rem;
    margin: 0;
    color: var(--gray-600);
    line-height: 1.4;
}

/* Form Section */
.signup-form-section {
    background: white;
    padding: var(--spacing-10);
    border-radius: var(--border-radius-xl);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.form-header {
    text-align: center;
    margin-bottom: var(--spacing-8);
}

.form-header h2 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--gray-900);
    margin: 0 0 var(--spacing-2);
}

.form-header p {
    font-size: 1rem;
    color: var(--gray-600);
    margin: 0;
}

.signup-form {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-8);
}

.form-section {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-6);
}

.section-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    padding-bottom: var(--spacing-3);
    border-bottom: 2px solid var(--gray-100);
}

.section-icon {
    font-size: 20px;
    color: var(--primary-color);
}

.section-title h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-900);
    margin: 0;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-4);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
}

.form-label {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-weight: 500;
    color: var(--gray-700);
}

.label-icon {
    font-size: 14px;
    color: var(--primary-color);
}

.required {
    color: var(--error-color);
    font-weight: 600;
}

.optional {
    color: var(--gray-500);
    font-size: 0.875rem;
    font-weight: 400;
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

.form-textarea {
    resize: vertical;
    min-height: 80px;
}

.field-error {
    display: none;
    color: var(--error-color);
    font-size: 0.875rem;
}

.field-hint {
    color: var(--gray-500);
    font-size: 0.875rem;
}

.form-actions {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-6);
    margin-top: var(--spacing-4);
}

.terms-agreement {
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-3);
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-3);
    cursor: pointer;
    font-size: 0.9rem;
    line-height: 1.5;
}

.checkbox-label input[type="checkbox"] {
    display: none;
}

.checkmark {
    width: 20px;
    height: 20px;
    border: 2px solid var(--gray-300);
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all var(--transition-fast);
}

.checkbox-label input[type="checkbox"]:checked + .checkmark {
    background: var(--primary-color);
    border-color: var(--primary-color);
}

.checkbox-label input[type="checkbox"]:checked + .checkmark:after {
    content: "✓";
    color: white;
    font-size: 12px;
    font-weight: bold;
}

.terms-text a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
}

.terms-text a:hover {
    text-decoration: underline;
}

.signup-submit {
    width: 100%;
    padding: var(--spacing-4);
    font-size: 1.1rem;
    font-weight: 600;
    transition: all var(--transition-fast);
}

.signup-submit.loading {
    opacity: 0.8;
    transform: scale(0.98);
}

.login-prompt {
    text-align: center;
    padding-top: var(--spacing-6);
    border-top: 1px solid var(--gray-200);
}

.login-prompt p {
    margin: 0 0 var(--spacing-4);
    color: var(--gray-600);
    font-weight: 500;
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
@media (max-width: 1200px) {
    .signup-container {
        grid-template-columns: 1fr;
        gap: var(--spacing-8);
        padding: 0 var(--spacing-4);
    }
    
    .signup-info-section {
        position: static;
        order: -1;
    }
    
    .benefits-list {
        flex-direction: row;
        gap: var(--spacing-3);
    }
    
    .benefit-item {
        flex: 1;
        flex-direction: column;
        text-align: center;
        padding: var(--spacing-3);
    }
}

@media (max-width: 768px) {
    .signup-main {
        padding: var(--spacing-4) 0;
    }
    
    .signup-form-section {
        padding: var(--spacing-6);
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-header h2 {
        font-size: 1.75rem;
    }
    
    .welcome-text h1 {
        font-size: 2rem;
    }
    
    .info-text h2 {
        font-size: 1.5rem;
    }
    
    .benefits-list {
        flex-direction: column;
    }
    
    .benefit-item {
        flex-direction: row;
        text-align: left;
    }
}

@media (max-width: 480px) {
    .signup-container {
        padding: 0 var(--spacing-2);
    }
    
    .signup-form-section {
        padding: var(--spacing-4);
    }
    
    .welcome-logo {
        width: 60px;
        height: 60px;
    }
    
    .campus-image {
        height: 200px;
    }
    
    .info-text {
        padding: var(--spacing-4);
    }
}
</style>
