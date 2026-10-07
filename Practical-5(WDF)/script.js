

document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('registrationForm');
    const fullNameInput = document.getElementById('fullName');
    const emailInput = document.getElementById('email');
    const mobileInput = document.getElementById('mobile');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const courseSelect = document.getElementById('course');
    const yearRadios = document.getElementsByName('year');
    const genderRadios = document.getElementsByName('gender');
    const termsCheckbox = document.getElementById('terms');
    
    
    const togglePasswordBtn = document.getElementById('togglePasswordBtn');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const ruleLength = document.getElementById('rule-length');
    const ruleUpper = document.getElementById('rule-upper');
    const ruleLower = document.getElementById('rule-lower');
    const ruleNumber = document.getElementById('rule-number');
    const ruleSpecial = document.getElementById('rule-special');

    
    const captchaCanvas = document.getElementById('captchaCanvas');
    const refreshCaptchaBtn = document.getElementById('refreshCaptchaBtn');
    const captchaInput = document.getElementById('captchaInput');
    let currentCaptchaText = '';

    const successModal = document.getElementById('successModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const submittedDataSummary = document.getElementById('submittedDataSummary');

  
    const REGEX = {
        fullName: /^[A-Za-z\s]{3,50}$/, // Only letters & spaces, 3-50 chars
        email: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/, // Standard email pattern
        mobile: /^[6-9]\d{9}$/, // 10 digits starting with 6,7,8,9
        password: {
            length: /.{8,}/,
            upper: /[A-Z]/,
            lower: /[a-z]/,
            number: /[0-9]/,
            special: /[!@#$%^&*(),.?":{}|<>]/
        }
    };

   
    function showError(fieldId, message) {
        const group = document.getElementById(`group-${fieldId}`);
        const errorSpan = document.getElementById(`${fieldId}-error`);
        const input = document.getElementById(fieldId);

        if (group) {
            group.classList.add('has-error');
            group.classList.remove('has-success');
        }

        if (errorSpan) {
            errorSpan.textContent = message;
        }

        if (input) {
            input.setAttribute('aria-invalid', 'true');
        }
    }

    function clearError(fieldId) {
        const group = document.getElementById(`group-${fieldId}`);
        const errorSpan = document.getElementById(`${fieldId}-error`);
        const input = document.getElementById(fieldId);

        if (group) {
            group.classList.remove('has-error');
            group.classList.add('has-success');
        }

        if (errorSpan) {
            errorSpan.textContent = '';
        }

        if (input) {
            input.setAttribute('aria-invalid', 'false');
        }
    }

    function validateFullName() {
        const value = fullNameInput.value.trim();
        if (!value) {
            showError('fullName', 'Full Name is required.');
            return false;
        }
        if (!REGEX.fullName.test(value)) {
            showError('fullName', 'Name must contain only alphabets & spaces (min 3 characters).');
            return false;
        }
        clearError('fullName');
        return true;
    }

    function validateEmail() {
        const value = emailInput.value.trim();
        if (!value) {
            showError('email', 'Email Address is required.');
            return false;
        }
        if (!REGEX.email.test(value)) {
            showError('email', 'Please enter a valid email address (e.g. user@domain.com).');
            return false;
        }
        clearError('email');
        return true;
    }

    function validateMobile() {
        const value = mobileInput.value.trim();
        if (!value) {
            showError('mobile', 'Mobile Number is required.');
            return false;
        }
        if (!REGEX.mobile.test(value)) {
            showError('mobile', 'Enter a valid 10-digit Indian mobile number starting with 6-9.');
            return false;
        }
        clearError('mobile');
        return true;
    }

    function updatePasswordStrength(val) {
        const hasLength = REGEX.password.length.test(val);
        const hasUpper = REGEX.password.upper.test(val);
        const hasLower = REGEX.password.lower.test(val);
        const hasNumber = REGEX.password.number.test(val);
        const hasSpecial = REGEX.password.special.test(val);

        // Update visual checklist rules
        ruleLength.className = hasLength ? 'valid' : 'invalid';
        ruleUpper.className = hasUpper ? 'valid' : 'invalid';
        ruleLower.className = hasLower ? 'valid' : 'invalid';
        ruleNumber.className = hasNumber ? 'valid' : 'invalid';
        ruleSpecial.className = hasSpecial ? 'valid' : 'invalid';

        const score = [hasLength, hasUpper, hasLower, hasNumber, hasSpecial].filter(Boolean).length;

        strengthBar.className = 'strength-bar';

        if (val.length === 0) {
            strengthText.textContent = 'Password Strength: Empty';
            strengthBar.style.width = '0%';
        } else if (score <= 2) {
            strengthText.textContent = 'Password Strength: Weak';
            strengthBar.classList.add('weak');
        } else if (score <= 4) {
            strengthText.textContent = 'Password Strength: Medium';
            strengthBar.classList.add('medium');
        } else {
            strengthText.textContent = 'Password Strength: Strong';
            strengthBar.classList.add('strong');
        }

        return score === 5;
    }

    function validatePassword() {
        const val = passwordInput.value;
        const isStrong = updatePasswordStrength(val);

        if (!val) {
            showError('password', 'Password is required.');
            return false;
        }
        if (!isStrong) {
            showError('password', 'Password does not meet all strength requirements listed below.');
            return false;
        }
        clearError('password');
        
        // Re-validate confirm password if it has a value
        if (confirmPasswordInput.value) {
            validateConfirmPassword();
        }
        return true;
    }

    function validateConfirmPassword() {
        const passVal = passwordInput.value;
        const confirmVal = confirmPasswordInput.value;

        if (!confirmVal) {
            showError('confirmPassword', 'Please confirm your password.');
            return false;
        }
        if (passVal !== confirmVal) {
            showError('confirmPassword', 'Passwords do not match.');
            return false;
        }
        clearError('confirmPassword');
        return true;
    }

    function validateCourse() {
        const val = courseSelect.value;
        if (!val) {
            showError('course', 'Please select a course / program.');
            return false;
        }
        clearError('course');
        return true;
    }

    function validateYear() {
        const isSelected = Array.from(yearRadios).some(r => r.checked);
        if (!isSelected) {
            showError('year', 'Please select your academic year.');
            return false;
        }
        clearError('year');
        return true;
    }

    function validateGender() {
        const isSelected = Array.from(genderRadios).some(r => r.checked);
        if (!isSelected) {
            showError('gender', 'Please select your gender.');
            return false;
        }
        clearError('gender');
        return true;
    }

    function validateTerms() {
        if (!termsCheckbox.checked) {
            showError('terms', 'You must accept the terms and conditions to proceed.');
            return false;
        }
        clearError('terms');
        return true;
    }

    function validateCaptcha() {
        const userVal = captchaInput.value.trim().toUpperCase();
        if (!userVal) {
            showError('captcha', 'CAPTCHA verification code is required.');
            return false;
        }
        if (userVal !== currentCaptchaText) {
            showError('captcha', 'Incorrect CAPTCHA code. Please try again.');
            generateCanvasCaptcha();
            captchaInput.value = '';
            return false;
        }
        clearError('captcha');
        return true;
    }


    function generateCanvasCaptcha() {
        const ctx = captchaCanvas.getContext('2d');
        const chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'; // Avoid ambiguous chars 0,1,O,I
        let captcha = '';
        for (let i = 0; i < 6; i++) {
            captcha += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        currentCaptchaText = captcha;

        // Clear canvas
        ctx.clearRect(0, 0, captchaCanvas.width, captchaCanvas.height);
        
        // Background Noise Lines
        for (let i = 0; i < 6; i++) {
            ctx.strokeStyle = `rgba(${Math.random()*150}, ${Math.random()*150}, ${Math.random()*150}, 0.5)`;
            ctx.beginPath();
            ctx.moveTo(Math.random() * captchaCanvas.width, Math.random() * captchaCanvas.height);
            ctx.lineTo(Math.random() * captchaCanvas.width, Math.random() * captchaCanvas.height);
            ctx.lineWidth = Math.random() * 2 + 1;
            ctx.stroke();
        }

        // Background Dots Noise
        for (let i = 0; i < 40; i++) {
            ctx.fillStyle = `rgba(${Math.random()*200}, ${Math.random()*200}, ${Math.random()*200}, 0.6)`;
            ctx.beginPath();
            ctx.arc(Math.random() * captchaCanvas.width, Math.random() * captchaCanvas.height, Math.random() * 2, 0, Math.PI * 2);
            ctx.fill();
        }

    
        ctx.font = 'bold 24px Inter, sans-serif';
        ctx.textBaseline = 'middle';

        for (let i = 0; i < captcha.length; i++) {
            ctx.save();
            const x = 20 + i * 25;
            const y = captchaCanvas.height / 2 + (Math.random() * 6 - 3);
            const angle = (Math.random() - 0.5) * 0.4; // Small tilt

            ctx.translate(x, y);
            ctx.rotate(angle);
            ctx.fillStyle = `rgb(${Math.floor(Math.random()*100)}, ${Math.floor(Math.random()*100)}, ${Math.floor(Math.random()*150)})`;
            ctx.fillText(captcha.charAt(i), 0, 0);
            ctx.restore();
        }
    }

    // Initialize CAPTCHA on load
    generateCanvasCaptcha();
    refreshCaptchaBtn.addEventListener('click', () => {
        generateCanvasCaptcha();
        captchaInput.value = '';
        clearError('captcha');
    });

    togglePasswordBtn.addEventListener('click', () => {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        togglePasswordBtn.textContent = isPassword ? 'Hide' : 'Show';
    });

    fullNameInput.addEventListener('input', validateFullName);
    emailInput.addEventListener('input', validateEmail);
    mobileInput.addEventListener('input', validateMobile);
    passwordInput.addEventListener('input', validatePassword);
    confirmPasswordInput.addEventListener('input', validateConfirmPassword);
    courseSelect.addEventListener('change', validateCourse);
    
    Array.from(yearRadios).forEach(radio => radio.addEventListener('change', validateYear));
    Array.from(genderRadios).forEach(radio => radio.addEventListener('change', validateGender));
    termsCheckbox.addEventListener('change', validateTerms);
    captchaInput.addEventListener('input', () => {
        if (captchaInput.value.length >= 6) {
            validateCaptcha();
        }
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();

        // Run all validations
        const isNameValid = validateFullName();
        const isEmailValid = validateEmail();
        const isMobileValid = validateMobile();
        const isPassValid = validatePassword();
        const isConfirmPassValid = validateConfirmPassword();
        const isCourseValid = validateCourse();
        const isYearValid = validateYear();
        const isGenderValid = validateGender();
        const isTermsValid = validateTerms();
        const isCaptchaValid = validateCaptcha();

        const isFormValid = isNameValid && isEmailValid && isMobileValid &&
                            isPassValid && isConfirmPassValid && isCourseValid &&
                            isYearValid && isGenderValid && isTermsValid && isCaptchaValid;

        if (isFormValid) {
            // Collect Form Data for Summary
            const selectedYear = Array.from(yearRadios).find(r => r.checked)?.value;
            const selectedGender = Array.from(genderRadios).find(r => r.checked)?.value;

            submittedDataSummary.innerHTML = `
                <p><strong>Name:</strong> ${fullNameInput.value.trim()}</p>
                <p><strong>Email:</strong> ${emailInput.value.trim()}</p>
                <p><strong>Mobile:</strong> ${mobileInput.value.trim()}</p>
                <p><strong>Course:</strong> ${courseSelect.value}</p>
                <p><strong>Year:</strong> ${selectedYear}</p>
                <p><strong>Gender:</strong> ${selectedGender}</p>
                <p><strong>Status:</strong> Frontend Validation Passed</p>
            `;

            // Display Success Modal
            successModal.removeAttribute('hidden');
        } else {
            // Focus on the first invalid field for UX & Accessibility
            const firstErrorField = form.querySelector('.has-error .form-control, .has-error input');
            if (firstErrorField) {
                firstErrorField.focus();
            }
        }
    });

    // Close Modal Button
    closeModalBtn.addEventListener('click', () => {
        successModal.setAttribute('hidden', '');
        form.reset();
        generateCanvasCaptcha();
        updatePasswordStrength('');
        passwordInput.type = 'password';
        togglePasswordBtn.textContent = 'Show';
        
        // Remove all error/success classes
        document.querySelectorAll('.form-group').forEach(group => {
            group.classList.remove('has-error', 'has-success');
        });
        document.querySelectorAll('.error-message').forEach(span => span.textContent = '');
    });

    // Reset Form Handler
    form.addEventListener('reset', () => {
        setTimeout(() => {
            generateCanvasCaptcha();
            updatePasswordStrength('');
            passwordInput.type = 'password';
            togglePasswordBtn.textContent = 'Show';
            document.querySelectorAll('.form-group').forEach(group => {
                group.classList.remove('has-error', 'has-success');
            });
            document.querySelectorAll('.error-message').forEach(span => span.textContent = '');
        }, 10);
    });
});
