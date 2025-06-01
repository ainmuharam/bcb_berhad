    const modal = document.getElementById("forgotPasswordModal");
    const forgotPasswordLink = document.getElementById("forgotPasswordLink");
    const closeModal = document.getElementById("closeModal");
    const emailForm = document.getElementById('email-form');
    const otpForm = document.getElementById('otp-form');
    const emailStep = document.getElementById('email-step');
    const otpStep = document.getElementById('otp-step');
    const newPasswordStep = document.getElementById('new-password-step');
    const emailError = document.getElementById('email-error');
    const otpTimer = document.getElementById("otpTimer");

    let otpCountdownInterval;

    forgotPasswordLink.onclick = () => {
        modal.style.display = "block";
    };

    closeModal.onclick = closeForgotPasswordModal;

    window.onclick = event => {
        if (event.target == modal) {
            closeForgotPasswordModal();
        }
    };

    function closeForgotPasswordModal() {
        modal.style.display = "none";

        // Clear inputs
        document.getElementById('forgot-email').value = '';
        document.getElementById('otp').value = '';
        document.getElementById('new-password').value = '';
        document.getElementById('confirm-password').value = '';

        // Reset UI
        emailStep.style.display = 'block';
        otpStep.style.display = 'none';
        newPasswordStep.style.display = 'none';
        otpTimer.textContent = '';

        if (emailError) {
            emailError.style.display = 'none';
            emailError.innerText = '';
        }

        // Stop countdown if running
        if (otpCountdownInterval) {
            clearInterval(otpCountdownInterval);
            otpCountdownInterval = null;
        }
    }

    emailForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const email = document.getElementById('forgot-email').value;
        emailError.style.display = 'none';

        fetch('check_email.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'forgot-email=' + encodeURIComponent(email)
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === 'exists') {
                // Send OTP
                fetch('send_otp.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'forgot-email=' + encodeURIComponent(email)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'sent') {
                        emailStep.style.display = 'none';
                        otpStep.style.display = 'block';
                        startOtpCountdown(); // Countdown timer
                    } else {
                        emailError.textContent = 'Failed to send OTP.';
                        emailError.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('OTP Error:', error);
                    emailError.textContent = 'Could not send OTP. Try again.';
                    emailError.style.display = 'block';
                });
            } else {
                emailError.textContent = 'Email not found.';
                emailError.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Email check error:', error);
            emailError.textContent = 'Something went wrong.';
            emailError.style.display = 'block';
        });
    });

    function startOtpCountdown() {
        let timeLeft = 60;
        otpTimer.textContent = `(${timeLeft}s remaining)`;

        otpCountdownInterval = setInterval(() => {
            timeLeft--;
            otpTimer.textContent = `(${timeLeft}s remaining)`;

            if (timeLeft <= 0) {
                clearInterval(otpCountdownInterval);
                otpCountdownInterval = null;
                otpTimer.textContent = "OTP expired. Please resend.";
            }
        }, 1000);
    }
    document.getElementById('otp-form').addEventListener('submit', function (e) {
    e.preventDefault();

    const otp = document.getElementById('otp').value;
    const email = document.getElementById('forgot-email').value;
    const otpError = document.getElementById('otp-error');

    fetch('verify_otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'otp=' + encodeURIComponent(otp) + '&email=' + encodeURIComponent(email)
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'valid') {
            document.getElementById('otp-step').style.display = 'none';
            document.getElementById('new-password-step').style.display = 'block';
            otpError.style.display = 'none';
        } else {
            otpError.innerText = result.message;
            otpError.style.display = 'block';
        }
    })
    .catch(error => {
        otpError.innerText = "Something went wrong. Please try again.";
        otpError.style.display = 'block';
    });
});

document.getElementById('new-password-form').addEventListener('submit', function (e) {
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    const passwordError = document.getElementById('password-error');

    passwordError.style.display = 'none';
    passwordError.innerText = '';

    // Check if passwords match
    if (newPassword !== confirmPassword) {
        e.preventDefault(); // Prevent form submission
        passwordError.innerText = 'Passwords do not match.';
        passwordError.style.display = 'block';
        return;
    }

    // Validate password strength
    const passwordPattern = /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%&*_]).{8,}$/;
    if (!passwordPattern.test(newPassword)) {
        e.preventDefault(); // Prevent form submission
        passwordError.innerText = 'Password must include at least 8 characters, uppercase letter,number, and special character.';
        passwordError.style.display = 'block';
        return;
    }

});