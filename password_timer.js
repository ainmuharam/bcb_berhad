document.addEventListener("DOMContentLoaded", () => {
    const countdownElement = document.getElementById("countdown-message");

    if (countdownElement) {
        let seconds = parseInt(countdownElement.dataset.seconds, 10);

        if (!isNaN(seconds)) {
            const interval = setInterval(() => {
                seconds--;
                countdownElement.textContent = `Account locked. Please try again in ${seconds} seconds.`;

                if (seconds <= 0) {
                    clearInterval(interval);
                    location.reload();
                }
            }, 1000);
        } else {
            console.error("Invalid or missing data-seconds value.");
        }
    }
});
