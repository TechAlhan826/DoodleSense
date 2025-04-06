/**
 * Authentication page scripts
 */

document.addEventListener('DOMContentLoaded', function() {
    initializePasswordStrength();
    initializePasswordToggle();
    validateFormInputs();
});

/**
 * Initialize password strength meter
 */
function initializePasswordStrength() {
    const passwordInput = document.getElementById('password');
    const strengthIndicator = document.getElementById('password-strength');
    
    if (!passwordInput || !strengthIndicator) return;
    
    // Create strength bar
    const strengthBar = document.createElement('div');
    strengthBar.className = 'password-strength-bar';
    strengthIndicator.appendChild(strengthBar);
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const strength = calculatePasswordStrength(password);
        
        // Update strength bar
        strengthBar.style.width = `${strength}%`;
        
        // Set color based on strength
        if (strength < 30) {
            strengthBar.style.backgroundColor = '#dc3545'; // Weak (red)
        } else if (strength < 60) {
            strengthBar.style.backgroundColor = '#ffc107'; // Medium (yellow)
        } else {
            strengthBar.style.backgroundColor = '#28a745'; // Strong (green)
        }
    });
}

/**
 * Calculate password strength percentage
 * @param {string} password - The password to check
 * @return {number} - Strength percentage (0-100)
 */
function calculatePasswordStrength(password) {
    if (!password) return 0;
    
    let strength = 0;
    
    // Length check
    if (password.length >= 8) {
        strength += 25;
    } else {
        strength += (password.length / 8) * 25;
    }
    
    // Character variety checks
    if (/[A-Z]/.test(password)) strength += 15; // Uppercase
    if (/[a-z]/.test(password)) strength += 15; // Lowercase
    if (/[0-9]/.test(password)) strength += 15; // Numbers
    if (/[^A-Za-z0-9]/.test(password)) strength += 15; // Special chars
    
    // Bonus for combination of character types
    let variety = 0;
    if (/[A-Z]/.test(password)) variety++;
    if (/[a-z]/.test(password)) variety++;
    if (/[0-9]/.test(password)) variety++;
    if (/[^A-Za-z0-9]/.test(password)) variety++;
    
    if (variety >= 3) strength += 15;
    
    // Cap at 100
    return Math.min(strength, 100);
}

/**
 * Initialize show/hide password toggle
 */
function initializePasswordToggle() {
    const passwordFields = document.querySelectorAll('input[type="password"]');
    
    passwordFields.forEach(field => {
        const container = field.parentNode;
        
        // Skip if not in expected container format
        if (!container.classList.contains('input-with-icon')) return;
        
        // Create toggle button
        const toggleBtn = document.createElement('i');
        toggleBtn.className = 'fas fa-eye password-toggle';
        toggleBtn.style.position = 'absolute';
        toggleBtn.style.right = '15px';
        toggleBtn.style.top = '50%';
        toggleBtn.style.transform = 'translateY(-50%)';
        toggleBtn.style.cursor = 'pointer';
        toggleBtn.style.color = '#a0aec0';
        
        container.appendChild(toggleBtn);
        
        // Add toggle functionality
        toggleBtn.addEventListener('click', function() {
            if (field.type === 'password') {
                field.type = 'text';
                this.className = 'fas fa-eye-slash password-toggle';
            } else {
                field.type = 'password';
                this.className = 'fas fa-eye password-toggle';
            }
        });
    });
}

/**
 * Form validation
 */
function validateFormInputs() {
    // Signup form validation
    const signupForm = document.querySelector('form.auth-form[action="signup.php"]');
    if (signupForm) {
        const usernameInput = document.getElementById('username');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        signupForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear previous error messages
            const existingErrors = signupForm.querySelectorAll('.input-error');
            existingErrors.forEach(err => err.remove());
            
            // Username validation
            if (usernameInput.value.trim().length < 3) {
                addErrorMessage(usernameInput, 'Username must be at least 3 characters');
                isValid = false;
            }
            
            // Email validation
            if (!isValidEmail(emailInput.value)) {
                addErrorMessage(emailInput, 'Please enter a valid email address');
                isValid = false;
            }
            
            // Password validation
            if (passwordInput.value.length < 8) {
                addErrorMessage(passwordInput, 'Password must be at least 8 characters');
                isValid = false;
            }
            
            // Confirm password validation
            if (passwordInput.value !== confirmPasswordInput.value) {
                addErrorMessage(confirmPasswordInput, 'Passwords do not match');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // Login form validation
    const loginForm = document.querySelector('form.auth-form[action="login.php"]');
    if (loginForm) {
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        
        loginForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear previous error messages
            const existingErrors = loginForm.querySelectorAll('.input-error');
            existingErrors.forEach(err => err.remove());
            
            // Email validation
            if (!isValidEmail(emailInput.value)) {
                addErrorMessage(emailInput, 'Please enter a valid email address');
                isValid = false;
            }
            
            // Password validation
            if (passwordInput.value.trim() === '') {
                addErrorMessage(passwordInput, 'Please enter your password');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // Forgot password form validation
    const forgotForm = document.querySelector('form.auth-form[action="forgot_password.php"]');
    if (forgotForm) {
        const emailInput = document.getElementById('email');
        
        forgotForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear previous error messages
            const existingErrors = forgotForm.querySelectorAll('.input-error');
            existingErrors.forEach(err => err.remove());
            
            // Email validation
            if (!isValidEmail(emailInput.value)) {
                addErrorMessage(emailInput, 'Please enter a valid email address');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // Reset password form validation
    const resetForm = document.querySelector('form.auth-form[action^="reset_password.php"]');
    if (resetForm) {
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        resetForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear previous error messages
            const existingErrors = resetForm.querySelectorAll('.input-error');
            existingErrors.forEach(err => err.remove());
            
            // Password validation
            if (passwordInput.value.length < 8) {
                addErrorMessage(passwordInput, 'Password must be at least 8 characters');
                isValid = false;
            }
            
            // Confirm password validation
            if (passwordInput.value !== confirmPasswordInput.value) {
                addErrorMessage(confirmPasswordInput, 'Passwords do not match');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
}

/**
 * Add error message below input
 * @param {HTMLElement} input - The input element
 * @param {string} message - Error message to display
 */
function addErrorMessage(input, message) {
    const formGroup = input.closest('.form-group');
    
    const errorElement = document.createElement('div');
    errorElement.className = 'input-error';
    errorElement.style.color = '#dc3545';
    errorElement.style.fontSize = '0.85rem';
    errorElement.style.marginTop = '5px';
    errorElement.textContent = message;
    
    formGroup.appendChild(errorElement);
    input.style.borderColor = '#dc3545';
    
    // Clear error when input changes
    input.addEventListener('input', function() {
        input.style.borderColor = '';
        const error = formGroup.querySelector('.input-error');
        if (error) error.remove();
    });
}

/**
 * Validate email format
 * @param {string} email - Email to validate
 * @return {boolean} - Whether email is valid
 */
function isValidEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}
