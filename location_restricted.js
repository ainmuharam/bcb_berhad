document.addEventListener("DOMContentLoaded", () => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            position => {
                const userLat = position.coords.latitude;
                const userLon = position.coords.longitude;

                fetch("verify_location.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ lat: userLat, lon: userLon })
                })
                .then(res => res.json())
                .then(response => {
                    if (!response.allowed) {
                        alert("Access denied by server.");
                        document.body.innerHTML = "<h2 style='color:red;text-align:center;margin-top:20%'>Access Restricted by Server</h2>";
                    }
                });
            },
            error => {
                alert("Location access denied by user.");
                document.body.innerHTML = "<h2 style='color:red;text-align:center;margin-top:20%'>Location Permission Required</h2>";
            }
        );
    } else {
        alert("Geolocation is not supported by this browser.");
        document.body.innerHTML = "<h2 style='color:red;text-align:center;margin-top:20%'>Geolocation Not Supported</h2>";
    }
});