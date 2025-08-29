// Enhanced Login Form JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle functionality
    const passwordToggles = document.querySelectorAll('.password-toggle');
    
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const passwordInput = this.parentElement.querySelector('input[type="password"], input[type="text"]');
            const eyeOpen = this.querySelector('.eye-open');
            const eyeClosed = this.querySelector('.eye-closed');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'block';
            } else {
                passwordInput.type = 'password';
                eyeOpen.style.display = 'block';
                eyeClosed.style.display = 'none';
            }
        });
    });

    // Enhanced form validation
    const loginForm = document.querySelector('.woocommerce-form-login');
    
    if (loginForm) {
        const inputs = loginForm.querySelectorAll('input[type="text"], input[type="email"], input[type="password"]');
        
        inputs.forEach(input => {
            // Real-time validation feedback
            input.addEventListener('input', function() {
                validateInput(this);
            });
            
            input.addEventListener('blur', function() {
                validateInput(this);
            });
            
            // Enhanced focus effects
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
        
        // Form submission with enhanced loading state
        loginForm.addEventListener('submit', function(e) {
            const submitButton = this.querySelector('.woocommerce-button');
            const buttonText = submitButton.querySelector('.button-text');
            const buttonIcon = submitButton.querySelector('.button-icon');
            
            if (submitButton && buttonText) {
                submitButton.disabled = true;
                submitButton.classList.add('loading');
                buttonText.textContent = 'Logowanie...';
                
                if (buttonIcon) {
                    buttonIcon.style.animation = 'spin 1s linear infinite';
                }
            }
        });
    }
    
    // Add floating label effect
    function addFloatingLabels() {
        const formRows = document.querySelectorAll('.woocommerce-form-row');
        
        formRows.forEach(row => {
            const input = row.querySelector('input');
            const label = row.querySelector('label');
            
            if (input && label) {
                // Check if input has value on load
                if (input.value.trim() !== '') {
                    label.classList.add('floating');
                }
                
                input.addEventListener('focus', () => {
                    label.classList.add('floating');
                });
                
                input.addEventListener('blur', () => {
                    if (input.value.trim() === '') {
                        label.classList.remove('floating');
                    }
                });
            }
        });
    }
    
    // Initialize floating labels
    addFloatingLabels();
    
    // Add smooth animations to benefits list
    function animateBenefits() {
        const benefitItems = document.querySelectorAll('.login-benefits li');
        
        benefitItems.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-20px)';
            
            setTimeout(() => {
                item.style.transition = 'all 0.5s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
            }, index * 150);
        });
    }
    
    // Initialize benefit animations
    setTimeout(animateBenefits, 500);
});

// Input validation function
function validateInput(input) {
    const inputWrapper = input.closest('.input-wrapper') || input.parentElement;
    const existingError = inputWrapper.querySelector('.field-error');
    
    // Remove existing error
    if (existingError) {
        existingError.remove();
    }
    
    let isValid = true;
    let errorMessage = '';
    
    // Email validation
    if (input.type === 'email' || input.name === 'username') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const value = input.value.trim();
        
        if (value && !emailRegex.test(value) && value.indexOf('@') > -1) {
            isValid = false;
            errorMessage = 'Wprowadź prawidłowy adres email';
        }
    }
    
    // Password validation
    if (input.type === 'password' && input.value) {
        if (input.value.length < 6) {
            isValid = false;
            errorMessage = 'Hasło musi mieć co najmniej 6 znaków';
        }
    }
    
    // Add error message if validation fails
    if (!isValid) {
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error';
        errorElement.textContent = errorMessage;
        errorElement.style.color = '#ef4444';
        errorElement.style.fontSize = '12px';
        errorElement.style.marginTop = '5px';
        errorElement.style.fontWeight = '500';
        
        inputWrapper.appendChild(errorElement);
        input.classList.add('error');
    } else {
        input.classList.remove('error');
    }
    
    return isValid;
}

// Add CSS for loading and error states
const style = document.createElement('style');
style.textContent = `
    .woocommerce-button.loading {
        opacity: 0.7;
        cursor: not-allowed;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .input-wrapper.focused .input-icon {
        color: var(--primary-color) !important;
        transform: scale(1.1);
    }
    
    input.error {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
    }
    
    .field-error {
        animation: slideInError 0.3s ease;
    }
    
    @keyframes slideInError {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    label.floating {
        transform: translateY(-25px) scale(0.85);
        color: var(--primary-color);
        font-weight: 600;
    }
    
    .woocommerce-form-row {
        position: relative;
    }
    
    .woocommerce-form-row label {
        transition: all 0.3s ease;
        transform-origin: left top;
        pointer-events: none;
        position: absolute;
        top: 45px;
        left: 50px;
        z-index: 1;
        background: white;
        padding: 0 5px;
        border-radius: 3px;
    }
`;

document.head.appendChild(style);
