    document.addEventListener("DOMContentLoaded", () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                position => {
                    const userLat = position.coords.latitude;
                    const userLon = position.coords.longitude;

                    const allowedLat = 2.0317;   // Example: BCB Berhad Kluang
                    const allowedLon = 103.3215;
                    const radius = 0.5; // in km

                    const distance = getDistanceFromLatLonInKm(userLat, userLon, allowedLat, allowedLon);

                    if (distance > radius) {
                        alert("Access denied. You are not in the allowed location.");
                        document.body.innerHTML = "<h2 style='color:red;text-align:center;margin-top:20%'>Access Restricted by Location</h2>";
                    }
                },
                error => {
                    alert("Location access denied. Cannot continue.");
                    document.body.innerHTML = "<h2 style='color:red;text-align:center;margin-top:20%'>Location Permission Required</h2>";
                }
            );
        } else {
            alert("Geolocation is not supported by this browser.");
        }

        // Haversine formula to calculate distance
        function getDistanceFromLatLonInKm(lat1, lon1, lat2, lon2) {
            const R = 6371; // Radius of the earth in km
            const dLat = deg2rad(lat2 - lat1);
            const dLon = deg2rad(lon2 - lon1);
            const a =
                Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c; // Distance in km
        }

        function deg2rad(deg) {
            return deg * (Math.PI / 180);
        }
    });