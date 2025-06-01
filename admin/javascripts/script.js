let sideButton = document.querySelector("#side-button");
    let sidebar = document.querySelector(".sidebar");

    sideButton.onclick = function() {
        sidebar.classList.toggle("active");
        
    }