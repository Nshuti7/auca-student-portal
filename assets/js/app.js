/* =====================================================
   STUDENT PORTAL - MODERN JAVASCRIPT INTERACTIONS
   ===================================================== */

// Initialize app when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

// Main app initialization
function initializeApp() {
    initializeToasts();
    initializeModals();
    initializeFormValidations();
    initializeProgressBars();
    initializeImageUpload();
    initializeNavigation();
    initializePasswordStrength();
    initializePasswordToggle();
    initializeConfirmDialogs();
}

/* =====================================================
   TOAST NOTIFICATIONS
   ===================================================== */

class ToastManager {
    constructor() {
        this.container = this.createContainer();
        this.toasts = [];
    }

    createContainer() {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        return container;
    }

    show(message, type = 'info', duration = 5000) {
        const toast = this.createToast(message, type);
        this.container.appendChild(toast);
        this.toasts.push(toast);

        // Animate in
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        // Auto remove
        setTimeout(() => {
            this.remove(toast);
        }, duration);

        return toast;
    }

    createToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        const iconClass = this.getIconClass(type);
        
        toast.innerHTML = `
            <div class="toast-content">
                <div class="icon ${iconClass}"></div>
                <div class="toast-message">${message}</div>
                <button class="toast-close" onclick="toastManager.remove(this.closest('.toast'))">
                    <div class="icon icon-x"></div>
                </button>
            </div>
        `;

        return toast;
    }

    getIconClass(type) {
        const icons = {
            'success': 'icon-check',
            'error': 'icon-error',
            'warning': 'icon-warning',
            'info': 'icon-info'
        };
        return icons[type] || 'icon-info';
    }

    remove(toast) {
        toast.classList.add('removing');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
            this.toasts = this.toasts.filter(t => t !== toast);
        }, 300);
    }

    success(message, duration = 5000) {
        return this.show(message, 'success', duration);
    }

    error(message, duration = 7000) {
        return this.show(message, 'error', duration);
    }

    warning(message, duration = 6000) {
        return this.show(message, 'warning', duration);
    }

    info(message, duration = 5000) {
        return this.show(message, 'info', duration);
    }
}

// Global toast manager instance
const toastManager = new ToastManager();

function initializeToasts() {
    // Check for URL parameters and show appropriate toasts
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('success')) {
        toastManager.success(urlParams.get('success'));
    }
    
    if (urlParams.get('error')) {
        toastManager.error(urlParams.get('error'));
    }
    
    if (urlParams.get('warning')) {
        toastManager.warning(urlParams.get('warning'));
    }
    
    if (urlParams.get('info')) {
        toastManager.info(urlParams.get('info'));
    }
}

/* =====================================================
   MODAL SYSTEM
   ===================================================== */

class ModalManager {
    constructor() {
        this.modals = [];
        this.currentModal = null;
    }

    confirm(message, title = 'Confirm') {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal">
                    <div class="modal-header">
                        <h3 class="modal-title">${title}</h3>
                        <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">
                            <div class="icon icon-x"></div>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" onclick="this.closest('.modal-overlay').remove(); modalManager.resolveConfirm(false)">Cancel</button>
                        <button class="btn btn-primary" onclick="this.closest('.modal-overlay').remove(); modalManager.resolveConfirm(true)">Confirm</button>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
            modal.classList.add('active');
            this.confirmResolve = resolve;
        });
    }

    resolveConfirm(result) {
        if (this.confirmResolve) {
            this.confirmResolve(result);
            this.confirmResolve = null;
        }
    }
}

// Global modal manager instance
const modalManager = new ModalManager();

function initializeModals() {
    // Modal styles are handled in main CSS
}

/* =====================================================
   FORM VALIDATIONS
   ===================================================== */

function initializeFormValidations() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        // Real-time validation on input
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', () => validateField(input));
            input.addEventListener('input', () => clearFieldError(input));
        });
        
        // Validation on form submit
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                toastManager.error('Please fix the errors below and try again.');
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    let isValid = true;
    const value = field.value.trim();
    
    // Clear previous error
    clearFieldError(field);
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    // Skip other validations if field is empty and not required
    if (!value && !field.hasAttribute('required')) {
        return true;
    }
    
    // Email validation
    if (field.type === 'email') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showFieldError(field, 'Please enter a valid email address');
            isValid = false;
        }
    }
    
    // Password strength validation
    if (field.type === 'password' && field.name === 'password') {
        if (value.length < 6) {
            showFieldError(field, 'Password must be at least 6 characters long');
            isValid = false;
        }
    }
    
    // Password matching
    if (field.name === 'confirm_password') {
        const passwordField = field.form.querySelector('input[name="password"]');
        if (passwordField && value !== passwordField.value) {
            showFieldError(field, 'Passwords do not match');
            isValid = false;
        }
    }
    
    // Phone validation
    if (field.type === 'tel' && value) {
        const phoneRegex = /^[\+]?[0-9\(\)\-\s\.]+$/;
        if (!phoneRegex.test(value)) {
            showFieldError(field, 'Please enter a valid phone number');
            isValid = false;
        }
    }
    
    return isValid;
}

function showFieldError(field, message) {
    clearFieldError(field);
    field.classList.add('error');
    
    const errorElement = document.createElement('div');
    errorElement.className = 'form-error';
    errorElement.textContent = message;
    
    // Insert after the field
    field.parentNode.insertBefore(errorElement, field.nextSibling);
}

function clearFieldError(field) {
    field.classList.remove('error');
    const errorElement = field.parentNode.querySelector('.form-error');
    if (errorElement) {
        errorElement.remove();
    }
}

/* =====================================================
   PROGRESS BARS
   ===================================================== */

function initializeProgressBars() {
    const progressBars = document.querySelectorAll('.progress-bar');
    
    progressBars.forEach(bar => {
        const fill = bar.querySelector('.progress-fill');
        if (fill) {
            const percentage = fill.style.width || fill.getAttribute('data-width');
            
            // Animate progress bar
            fill.style.width = '0%';
            setTimeout(() => {
                fill.style.width = percentage;
            }, 500);
        }
    });
}

/* =====================================================
   IMAGE UPLOAD HANDLING
   ===================================================== */

function initializeImageUpload() {
    const uploadInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    
    uploadInputs.forEach(input => {
        input.addEventListener('change', handleImageUpload);
    });
}

function handleImageUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    // Validate file type
    if (!file.type.startsWith('image/')) {
        toastManager.error('Please select a valid image file');
        event.target.value = '';
        return;
    }
    
    // Validate file size (5MB limit)
    if (file.size > 5 * 1024 * 1024) {
        toastManager.error('Image must be smaller than 5MB');
        event.target.value = '';
        return;
    }
    
    // Show preview if preview element exists
    const previewId = event.target.getAttribute('data-preview');
    if (previewId) {
        const preview = document.getElementById(previewId);
        if (preview) {
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
    
    toastManager.success('Image selected successfully');
}

/* =====================================================
   NAVIGATION ENHANCEMENTS
   ===================================================== */

function initializeNavigation() {
    // Add active class to current page
    const currentPath = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('nav a');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && href === currentPath) {
            link.classList.add('active');
        }
    });
}

/* =====================================================
   PASSWORD STRENGTH INDICATOR
   ===================================================== */

function initializePasswordStrength() {
    // Only initialize password strength on specific pages where password creation/change occurs
    const currentPage = window.location.pathname.split('/').pop();
    const passwordCreationPages = ['signup.php', 'reset_password.php', 'update.php'];
    
    // Skip password strength validation on login pages
    if (currentPage === 'login.php' || currentPage === 'admin_login.php') {
        return;
    }
    
    // Only apply to password fields that are for password creation/setting (not login)
    const passwordFields = document.querySelectorAll('input[type="password"][name="password"]:not([autocomplete="current-password"])');
    
    passwordFields.forEach(field => {
        const strengthIndicator = createPasswordStrengthIndicator();
        
        // Insert after the password field container or parent
        const container = field.closest('.password-field') || field.parentNode;
        container.appendChild(strengthIndicator);
        
        field.addEventListener('input', () => {
            updatePasswordStrength(field.value, strengthIndicator);
        });
    });
}

function createPasswordStrengthIndicator() {
    const container = document.createElement('div');
    container.className = 'password-strength';
    container.innerHTML = `
        <div class="strength-bar">
            <div class="strength-fill"></div>
        </div>
        <div class="strength-text">Password strength: <span class="strength-level">Weak</span></div>
    `;
    return container;
}

function updatePasswordStrength(password, indicator) {
    const fill = indicator.querySelector('.strength-fill');
    const text = indicator.querySelector('.strength-level');
    
    let strength = 0;
    let level = 'Very Weak';
    let color = '#ef4444';
    
    if (password.length >= 6) strength += 1;
    if (password.length >= 8) strength += 1;
    if (/[a-z]/.test(password)) strength += 1;
    if (/[A-Z]/.test(password)) strength += 1;
    if (/\d/.test(password)) strength += 1;
    if (/[@$!%*?&]/.test(password)) strength += 1;
    
    switch (strength) {
        case 0:
        case 1:
            level = 'Very Weak';
            color = '#ef4444';
            break;
        case 2:
            level = 'Weak';
            color = '#f59e0b';
            break;
        case 3:
            level = 'Fair';
            color = '#eab308';
            break;
        case 4:
            level = 'Good';
            color = '#22c55e';
            break;
        case 5:
        case 6:
            level = 'Strong';
            color = '#10b981';
            break;
    }
    
    fill.style.width = `${(strength / 6) * 100}%`;
    fill.style.backgroundColor = color;
    text.textContent = level;
    text.style.color = color;
}

/* =====================================================
   CONFIRM DIALOGS
   ===================================================== */

function initializeConfirmDialogs() {
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    
    confirmButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            
            const message = button.getAttribute('data-confirm');
            const confirmed = await modalManager.confirm(message);
            
            if (confirmed) {
                // If it's a link, navigate to it
                if (button.tagName === 'A') {
                    window.location.href = button.href;
                }
                // If it's a form button, submit the form
                else if (button.type === 'submit') {
                    button.closest('form').submit();
                }
                // If it has onclick, execute it
                else if (button.onclick) {
                    button.onclick();
                }
            }
        });
    });
}

/* =====================================================
   UTILITY FUNCTIONS AND GLOBAL EXPORTS
   ===================================================== */

// Format date utility
function formatDate(date) {
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }).format(new Date(date));
}

// Copy to clipboard utility
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            toastManager.success('Copied to clipboard');
        }).catch(() => {
            toastManager.error('Failed to copy to clipboard');
        });
    } else {
        toastManager.warning('Clipboard not supported in this browser');
    }
}

// Global helper functions
window.toast = toastManager;
window.modal = modalManager;
window.copyToClipboard = copyToClipboard;
window.formatDate = formatDate;

/* =====================================================
   CUSTOM STYLES FOR JAVASCRIPT COMPONENTS
   ===================================================== */

// Add custom styles for JavaScript components
const style = document.createElement('style');
style.textContent = `
    /* Toast Styles */
    .toast {
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
    }
    
    .toast.show {
        opacity: 1;
        transform: translateX(0);
    }
    
    .toast.removing {
        opacity: 0;
        transform: translateX(100%);
    }
    
    .toast-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .toast-message {
        flex: 1;
    }
    
    /* Password Strength Styles */
    .password-strength {
        margin-top: 0.5rem;
    }
    
    .strength-bar {
        width: 100%;
        height: 4px;
        background: #e5e7eb;
        border-radius: 2px;
        overflow: hidden;
        margin-bottom: 0.25rem;
    }
    
    .strength-fill {
        height: 100%;
        width: 0%;
        transition: all 0.3s ease;
        border-radius: 2px;
    }
    
    .strength-text {
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    .strength-level {
        font-weight: 500;
    }
    
    /* Tooltip Styles */
    .tooltip {
        position: absolute;
        background: #1f2937;
        color: white;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        opacity: 0;
        pointer-events: none;
        transform: translateX(-50%) translateY(-100%);
        transition: opacity 0.2s ease;
        z-index: 1060;
    }
    
    .tooltip.show {
        opacity: 1;
    }
    
    .tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-top-color: #1f2937;
    }
    
    /* Mobile Navigation */
    @media (max-width: 768px) {
        .mobile-nav-toggle {
            display: block;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
        }
        
        nav {
            display: none;
        }
        
        nav.mobile-open {
            display: block;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        nav.mobile-open ul {
            flex-direction: column;
            padding: 1rem;
        }
    }
`;

document.head.appendChild(style);

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        ToastManager,
        ModalManager,
        toastManager,
        modalManager
    };
}

// Password toggle functionality - integrated into main initialization
function initializePasswordToggle() {
    const passwordToggles = document.querySelectorAll('.password-toggle');
    
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const passwordField = this.closest('.password-field').querySelector('input');
            const isPassword = passwordField.type === 'password';
            
            // Toggle password visibility
            passwordField.type = isPassword ? 'text' : 'password';
            
            // Update icon
            const icon = this.querySelector('.icon');
            if (isPassword) {
                icon.classList.remove('icon-eye');
                icon.classList.add('icon-eye-off');
                this.setAttribute('aria-label', 'Hide password');
            } else {
                icon.classList.remove('icon-eye-off');
                icon.classList.add('icon-eye');
                this.setAttribute('aria-label', 'Show password');
            }
            
            // Keep focus on the password field
            passwordField.focus();
        });
    });
}