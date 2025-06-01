    const bellIcon = document.querySelector('.notification');
    const dropdown = document.getElementById('notificationDropdown');

    bellIcon.addEventListener('click', function (e) {
        e.preventDefault();
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    });

    // Close dropdown if clicked outside
    document.addEventListener('click', function (e) {
        if (!bellIcon.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
