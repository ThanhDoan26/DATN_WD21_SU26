/* ============================================================
   CINETICKET AUTHENTICATION - JAVASCRIPT
   Production-Ready | ES6+ | Modular
   ============================================================ */

/**
 * VALIDATION MODULE
 * Handles form validation logic
 */
const ValidationModule = (() => {
    // Email validation pattern
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // Password requirements
    const passwordRequirements = {
        minLength: 8,
        hasUpperCase: /[A-Z]/,
        hasLowerCase: /[a-z]/,
        hasNumbers: /[0-9]/,
        hasSpecialChar: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/,
    };

    /**
     * Validate email format
     */
    const validateEmail = (email) => {
        return emailPattern.test(email);
    };

    /**
     * Validate password strength
     * Returns: { score: 0-3, level: 'weak'|'fair'|'strong' }
     */
    const validatePasswordStrength = (password) => {
        let score = 0;

        if (password.length >= passwordRequirements.minLength) score++;
        if (passwordRequirements.hasUpperCase.test(password)) score++;
        if (passwordRequirements.hasNumbers.test(password)) score++;
        if (
            passwordRequirements.hasLowerCase.test(password) &&
            (passwordRequirements.hasSpecialChar.test(password) ||
                password.length >= 12)
        ) {
            score++;
        }

        const levels = ['weak', 'weak', 'fair', 'strong'];
        return {
            score: Math.min(score, 3),
            level: levels[score] || 'weak',
        };
    };

    /**
     * Validate full name
     */
    const validateFullName = (name) => {
        return name.trim().length >= 2;
    };

    /**
     * Validate password match
     */
    const validatePasswordMatch = (password, confirmPassword) => {
        return password === confirmPassword && password.length > 0;
    };

    return {
        validateEmail,
        validatePasswordStrength,
        validateFullName,
        validatePasswordMatch,
        passwordRequirements,
    };
})();

/**
 * UI MODULE
 * Handles UI interactions and feedback
 */
const UIModule = (() => {
    /**
     * Show error message for a field
     */
    const showError = (inputElement, errorElement, message) => {
        inputElement.classList.add('error');
        errorElement.textContent = message;
        errorElement.classList.remove('hidden');
    };

    /**
     * Clear error message for a field
     */
    const clearError = (inputElement, errorElement) => {
        inputElement.classList.remove('error');
        errorElement.textContent = '';
        errorElement.classList.add('hidden');
    };

    /**
     * Show success state on input
     */
    const showSuccess = (inputElement) => {
        inputElement.classList.add('success');
    };

    /**
     * Clear success state on input
     */
    const clearSuccess = (inputElement) => {
        inputElement.classList.remove('success');
    };

    /**
     * Update password strength indicator
     */
    const updatePasswordStrength = (strength, strengthFill, strengthText) => {
        const levels = {
            weak: { level: 'Yếu', color: 'weak' },
            fair: { level: 'Trung bình', color: 'fair' },
            strong: { level: 'Mạnh', color: 'strong' },
        };

        const currentLevel = levels[strength.level] || levels.weak;

        strengthFill.classList.remove('weak', 'fair', 'strong');
        strengthFill.classList.add(currentLevel.color);
        strengthText.textContent = currentLevel.level;
    };

    /**
     * Toggle password visibility
     */
    const togglePasswordVisibility = (inputElement, buttonElement) => {
        const isPassword = inputElement.type === 'password';
        inputElement.type = isPassword ? 'text' : 'password';
        buttonElement.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
        return !isPassword;
    };

    /**
     * Set button loading state
     */
    const setButtonLoading = (button, isLoading) => {
        if (isLoading) {
            button.classList.add('loading');
            button.disabled = true;
        } else {
            button.classList.remove('loading');
            button.disabled = false;
        }
    };

    /**
     * Show notification (Toast)
     */
    const showNotification = (message, type = 'info') => {
        console.log(`[${type.toUpperCase()}] ${message}`);
        // In production, implement a toast notification component
    };

    return {
        showError,
        clearError,
        showSuccess,
        clearSuccess,
        updatePasswordStrength,
        togglePasswordVisibility,
        setButtonLoading,
        showNotification,
    };
})();

/**
 * LOGIN FORM MODULE
 * Handles login form logic
 */
const LoginFormModule = (() => {
    let form, emailInput, emailError, passwordInput, passwordError, togglePasswordBtn, submitBtn;

    /**
     * Initialize login form
     */
    const init = () => {
        form = document.getElementById('loginForm');
        emailInput = document.getElementById('email');
        emailError = document.getElementById('emailError');
        passwordInput = document.getElementById('password');
        passwordError = document.getElementById('passwordError');
        togglePasswordBtn = document.getElementById('togglePassword');
        submitBtn = document.getElementById('submitBtn');

        if (!form) return;

        attachEventListeners();
    };

    /**
     * Attach event listeners
     */
    const attachEventListeners = () => {
        // Email input - realtime validation
        emailInput.addEventListener('blur', validateEmailField);
        emailInput.addEventListener('input', () => {
            if (emailInput.value) {
                validateEmailField();
            }
        });

        // Password input - realtime validation
        passwordInput.addEventListener('blur', validatePasswordField);
        passwordInput.addEventListener('input', () => {
            if (passwordInput.value) {
                validatePasswordField();
            }
        });

        // Toggle password visibility
        togglePasswordBtn.addEventListener('click', (e) => {
            e.preventDefault();
            UIModule.togglePasswordVisibility(passwordInput, togglePasswordBtn);
        });

        // Form submit
        form.addEventListener('submit', handleSubmit);

        // Allow Enter key to submit
        passwordInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                form.dispatchEvent(new Event('submit'));
            }
        });
    };

    /**
     * Validate email field
     */
    const validateEmailField = () => {
        const email = emailInput.value.trim();

        if (!email) {
            UIModule.showError(emailInput, emailError, 'Vui lòng nhập địa chỉ email');
            return false;
        }

        if (!ValidationModule.validateEmail(email)) {
            UIModule.showError(
                emailInput,
                emailError,
                'Địa chỉ email không hợp lệ'
            );
            return false;
        }

        UIModule.clearError(emailInput, emailError);
        UIModule.showSuccess(emailInput);
        return true;
    };

    /**
     * Validate password field
     */
    const validatePasswordField = () => {
        const password = passwordInput.value;

        if (!password) {
            UIModule.showError(passwordInput, passwordError, 'Vui lòng nhập mật khẩu');
            return false;
        }

        if (password.length < 6) {
            UIModule.showError(passwordInput, passwordError, 'Mật khẩu phải ít nhất 6 ký tự');
            return false;
        }

        UIModule.clearError(passwordInput, passwordError);
        UIModule.showSuccess(passwordInput);
        return true;
    };

    /**
     * Validate entire form
     */
    const validateForm = () => {
        return validateEmailField() && validatePasswordField();
    };

    /**
     * Handle form submission
     */
    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!validateForm()) {
            return;
        }

        UIModule.setButtonLoading(submitBtn, true);

        try {
            // Simulate API call
            await new Promise((resolve) => setTimeout(resolve, 1500));

            const formData = {
                email: emailInput.value.trim(),
                password: passwordInput.value,
                rememberMe: document.getElementById('rememberMe').checked,
            };

            console.log('Login form submitted:', formData);
            UIModule.showNotification('Đăng nhập thành công!', 'success');

            // In production: redirect to dashboard
            // window.location.href = '/dashboard';
        } catch (error) {
            console.error('Login error:', error);
            UIModule.showNotification('Đăng nhập thất bại. Vui lòng thử lại.', 'error');
        } finally {
            UIModule.setButtonLoading(submitBtn, false);
        }
    };

    return {
        init,
    };
})();

/**
 * REGISTER FORM MODULE
 * Handles register form logic
 */
const RegisterFormModule = (() => {
    let form, fullNameInput, fullNameError, emailInput, registerEmailError, passwordInput,
        registerPasswordError, passwordStrengthFill, passwordStrengthText, confirmPasswordInput,
        confirmPasswordError, agreeTermsInput, agreeTermsError, togglePasswordBtn,
        toggleConfirmPasswordBtn, submitBtn;

    /**
     * Initialize register form
     */
    const init = () => {
        form = document.getElementById('registerForm');
        fullNameInput = document.getElementById('fullName');
        fullNameError = document.getElementById('fullNameError');
        emailInput = document.getElementById('registerEmail');
        registerEmailError = document.getElementById('registerEmailError');
        passwordInput = document.getElementById('registerPassword');
        registerPasswordError = document.getElementById('registerPasswordError');
        passwordStrengthFill = document.getElementById('strengthFill');
        passwordStrengthText = document.getElementById('strengthText');
        confirmPasswordInput = document.getElementById('confirmPassword');
        confirmPasswordError = document.getElementById('confirmPasswordError');
        agreeTermsInput = document.getElementById('agreeTerms');
        agreeTermsError = document.getElementById('agreeTermsError');
        togglePasswordBtn = document.getElementById('toggleRegisterPassword');
        toggleConfirmPasswordBtn = document.getElementById('toggleConfirmPassword');
        submitBtn = document.getElementById('registerSubmitBtn');

        if (!form) return;

        attachEventListeners();
    };

    /**
     * Attach event listeners
     */
    const attachEventListeners = () => {
        // Full name validation
        fullNameInput.addEventListener('blur', validateFullNameField);
        fullNameInput.addEventListener('input', () => {
            if (fullNameInput.value) {
                validateFullNameField();
            }
        });

        // Email validation
        emailInput.addEventListener('blur', validateEmailField);
        emailInput.addEventListener('input', () => {
            if (emailInput.value) {
                validateEmailField();
            }
        });

        // Password validation
        passwordInput.addEventListener('input', () => {
            validatePasswordField();
            // Auto-validate confirm password if already filled
            if (confirmPasswordInput.value) {
                validateConfirmPasswordField();
            }
        });
        passwordInput.addEventListener('blur', validatePasswordField);

        // Confirm password validation
        confirmPasswordInput.addEventListener('blur', validateConfirmPasswordField);
        confirmPasswordInput.addEventListener('input', () => {
            if (confirmPasswordInput.value) {
                validateConfirmPasswordField();
            }
        });

        // Toggle password visibility
        togglePasswordBtn.addEventListener('click', (e) => {
            e.preventDefault();
            UIModule.togglePasswordVisibility(passwordInput, togglePasswordBtn);
        });

        toggleConfirmPasswordBtn.addEventListener('click', (e) => {
            e.preventDefault();
            UIModule.togglePasswordVisibility(confirmPasswordInput, toggleConfirmPasswordBtn);
        });

        // Form submit
        form.addEventListener('submit', handleSubmit);

        // Allow Enter key to submit
        agreeTermsInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                form.dispatchEvent(new Event('submit'));
            }
        });
    };

    /**
     * Validate full name field
     */
    const validateFullNameField = () => {
        const fullName = fullNameInput.value.trim();

        if (!fullName) {
            UIModule.showError(fullNameInput, fullNameError, 'Vui lòng nhập họ và tên');
            return false;
        }

        if (!ValidationModule.validateFullName(fullName)) {
            UIModule.showError(fullNameInput, fullNameError, 'Họ và tên phải ít nhất 2 ký tự');
            return false;
        }

        UIModule.clearError(fullNameInput, fullNameError);
        UIModule.showSuccess(fullNameInput);
        return true;
    };

    /**
     * Validate email field
     */
    const validateEmailField = () => {
        const email = emailInput.value.trim();

        if (!email) {
            UIModule.showError(emailInput, registerEmailError, 'Vui lòng nhập địa chỉ email');
            return false;
        }

        if (!ValidationModule.validateEmail(email)) {
            UIModule.showError(
                emailInput,
                registerEmailError,
                'Địa chỉ email không hợp lệ'
            );
            return false;
        }

        UIModule.clearError(emailInput, registerEmailError);
        UIModule.showSuccess(emailInput);
        return true;
    };

    /**
     * Validate password field
     */
    const validatePasswordField = () => {
        const password = passwordInput.value;

        if (!password) {
            UIModule.showError(passwordInput, registerPasswordError, 'Vui lòng nhập mật khẩu');
            return false;
        }

        if (password.length < ValidationModule.passwordRequirements.minLength) {
            UIModule.showError(
                passwordInput,
                registerPasswordError,
                `Mật khẩu phải ít nhất ${ValidationModule.passwordRequirements.minLength} ký tự`
            );
            return false;
        }

        const strength = ValidationModule.validatePasswordStrength(password);
        UIModule.updatePasswordStrength(strength, passwordStrengthFill, passwordStrengthText);

        if (strength.score < 2) {
            UIModule.showError(
                passwordInput,
                registerPasswordError,
                'Mật khẩu quá yếu. Vui lòng sử dụng chữ hoa, chữ thường, số.'
            );
            return false;
        }

        UIModule.clearError(passwordInput, registerPasswordError);
        UIModule.showSuccess(passwordInput);
        return true;
    };

    /**
     * Validate confirm password field
     */
    const validateConfirmPasswordField = () => {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        if (!confirmPassword) {
            UIModule.showError(
                confirmPasswordInput,
                confirmPasswordError,
                'Vui lòng xác nhận mật khẩu'
            );
            return false;
        }

        if (!ValidationModule.validatePasswordMatch(password, confirmPassword)) {
            UIModule.showError(
                confirmPasswordInput,
                confirmPasswordError,
                'Mật khẩu xác nhận không khớp'
            );
            return false;
        }

        UIModule.clearError(confirmPasswordInput, confirmPasswordError);
        UIModule.showSuccess(confirmPasswordInput);
        return true;
    };

    /**
     * Validate entire form
     */
    const validateForm = () => {
        const isFullNameValid = validateFullNameField();
        const isEmailValid = validateEmailField();
        const isPasswordValid = validatePasswordField();
        const isConfirmPasswordValid = validateConfirmPasswordField();

        let isAgreeTermsValid = true;
        if (!agreeTermsInput.checked) {
            UIModule.showError(
                agreeTermsInput,
                agreeTermsError,
                'Vui lòng đồng ý với Điều khoản Dịch vụ'
            );
            isAgreeTermsValid = false;
        } else {
            UIModule.clearError(agreeTermsInput, agreeTermsError);
        }

        return (
            isFullNameValid &&
            isEmailValid &&
            isPasswordValid &&
            isConfirmPasswordValid &&
            isAgreeTermsValid
        );
    };

    /**
     * Handle form submission
     */
    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!validateForm()) {
            return;
        }

        UIModule.setButtonLoading(submitBtn, true);

        try {
            // Simulate API call
            await new Promise((resolve) => setTimeout(resolve, 1500));

            const formData = {
                fullName: fullNameInput.value.trim(),
                email: emailInput.value.trim(),
                password: passwordInput.value,
            };

            console.log('Register form submitted:', formData);
            UIModule.showNotification('Tạo tài khoản thành công!', 'success');

            // In production: redirect to login or verify email page
            // window.location.href = '/login?registered=true';
        } catch (error) {
            console.error('Register error:', error);
            UIModule.showNotification('Tạo tài khoản thất bại. Vui lòng thử lại.', 'error');
        } finally {
            UIModule.setButtonLoading(submitBtn, false);
        }
    };

    return {
        init,
    };
})();

/**
 * GLOBAL INITIALIZATION
 */
const initializeLoginForm = () => {
    LoginFormModule.init();
};

const initializeRegisterForm = () => {
    RegisterFormModule.init();
};