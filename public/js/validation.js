(function ($) {
    'use strict';
    $(document).ready(function () {
        // Email validation
        $('#register-email').on('input', function () {
            var email = $(this).val();
            console.log(email)
            if (!validateEmail(email)) {
                $(this).addClass('is-invalid');
                $('#emailError').text('Please enter a valid email address.');
            } else {
                $(this).removeClass('is-invalid');
                $('#emailError').text('');
            }
        });

        function validateEmail(email) {
            var emailRegex = /\S+@\S+\.\S+/;
            return emailRegex.test(email);
        }

        // Password validation
        $('#register-password').on('input', function () {
            var password = $(this).val();
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
            const isLongEnough = password.length >= 12;
            const hasNumber = /\d/.test(password);

            if (!(hasUppercase && hasLowercase && hasSpecialChar && isLongEnough && hasNumber)) {
                $(this).addClass('is-invalid');
                $('#passwordError').html("- Password must be at least 12 characters long.<br>- Password should include a combination of uppercase and lowercase letters, numbers, and special characters");
            } else {
                $(this).removeClass('is-invalid');
                $('#passwordError').text('');
            }
        });

        // Date of Birth validation
        $('#dob').on('input', function () {
            var dob = $(this).val();
            if (!validateDate(dob)) {
                $(this).addClass('is-invalid');
                $('#dobError').text('Please enter a valid date of birth (YYYY-MM-DD).');
            } else {
                $(this).removeClass('is-invalid');
                $('#dobError').text('');
            }
        });

        function validateDate(dob) {
            var dateRegex = /^\d{4}-\d{2}-\d{2}$/;
            if (!dateRegex.test(dob)) {
                return false;
            }
            var date = new Date(dob);
            if (date == "Invalid Date") {
                return false;
            }
            return true;
        }

        // Image input validation
        $('#image').on('change', function () {
            var file = $(this).prop('files')[0];
            var fileType = file.type;
            var fileSize = file.size;
            var allowedTypes = ['image/jpeg', 'image/png'];
            var maxSize = 2 * 1024 * 1024; // 2MB

            if (allowedTypes.indexOf(fileType) === -1) {
                $(this).addClass('is-invalid');
                $('#imageError').text('Please upload a JPEG or PNG image.');
            } else if (fileSize > maxSize) {
                $(this).addClass('is-invalid');
                $('#imageError').text('Please upload an image smaller than 2MB.');
            } else {
                $(this).removeClass('is-invalid');
                $('#imageError').text('');
            }
        });
    });
})(jQuery);
