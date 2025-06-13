document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("defaultPasswordModal");
    const closeBtn = document.querySelector("#defaultPasswordModal .close");

    if (modal && closeBtn) {
        // Close when clicking outside modal content
        window.addEventListener("click", (event) => {
            if (event.target === modal) {
                window.location.href = "login.php";
            }
        });

        // Close when clicking the X button
        closeBtn.addEventListener("click", () => {
            window.location.href = "login.php";
        });
    }
});
