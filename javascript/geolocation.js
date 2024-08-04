document.addEventListener('DOMContentLoaded', (event) => {
    // Function to get user's current location
    function getUserLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
        } else {
            alert("Geolocation is not supported by this browser.");
        }
    }

    function successCallback(position) {
        var latitude = position.coords.latitude;
        var longitude = position.coords.longitude;

        // Send coordinates to backend (PHP)
        fetch("process_location.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ latitude: latitude, longitude: longitude })
        })
        .then(response => response.json())
        .then(data => {
            var output = "Nearest Locations:<br>";
            data.nearest_locations.forEach(function(location) {
                output += location.name + ": " + location.distance.toFixed(2) + " km<br>";
            });
            document.getElementById("out").innerHTML = output;
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // Error callback function
    function errorCallback(error) {
        switch (error.code) {
            case error.PERMISSION_DENIED:
                alert("User denied the request for Geolocation.");
                break;
            case error.POSITION_UNAVAILABLE:
                alert("Location information is unavailable.");
                break;
            case error.TIMEOUT:
                alert("The request to get user location timed out.");
                break;
            case error.UNKNOWN_ERROR:
                alert("An unknown error occurred.");
                break;
        }
    }

    getUserLocation();
});
