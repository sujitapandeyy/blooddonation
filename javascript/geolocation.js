// document.addEventListener('DOMContentLoaded', (event) => {
//     // Function to get user's current location
//     function getUserLocation() {
//         if (navigator.geolocation) {
//             navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
//         } else {
//             alert("Geolocation is not supported by this browser.");
//         }
//     }

//     function successCallback(position) {
//         var latitude = position.coords.latitude;
//         var longitude = position.coords.longitude;

//         // Send coordinates to backend (PHP)
//         fetch("process_location.php", {
//             method: "POST",
//             headers: {
//                 "Content-Type": "application/json"
//             },
//             body: JSON.stringify({ latitude: latitude, longitude: longitude })
//         })
//         .then(response => response.json())
//         .then(data => {
//             var output = "Nearest Locations:<br>";
//             data.nearest_locations.forEach(function(location) {
//                 output += location.name + ": " + location.distance.toFixed(2) + " km<br>";
//             });
//             document.getElementById("out").innerHTML = output;
//         })
//         .catch(error => {
//             console.error('Error:', error);
//         });
//     }

//     // Error callback function
//     function errorCallback(error) {
//         switch (error.code) {
//             case error.PERMISSION_DENIED:
//                 alert("User denied the request for Geolocation.");
//                 break;
//             case error.POSITION_UNAVAILABLE:
//                 alert("Location information is unavailable.");
//                 break;
//             case error.TIMEOUT:
//                 alert("The request to get user location timed out.");
//                 break;
//             case error.UNKNOWN_ERROR:
//                 alert("An unknown error occurred.");
//                 break;
//         }
//     }

//     getUserLocation();
// });




function initializeAddressInput(inputId, suggestionsId, latId, longId, displayLatId, displayLongId) {
    $('#' + inputId).on('input', function () {
        var address = $(this).val();
        if (address.length > 2) {
            var url = "https://nominatim.openstreetmap.org/search?format=json&q=" + encodeURIComponent(address);

            $.ajax({
                url: url,
                method: 'GET',
                success: function (data) {
                    $('#' + suggestionsId).empty();
                    if (data.length > 0) {
                        data.forEach(function (place) {
                            $('#' + suggestionsId).append('<div class="suggestion" data-lat="' + place.lat + '" data-lon="' + place.lon + '">' + place.display_name + '</div>');
                        });
                    }
                },
                error: function (error) {
                    console.log('Error:', error);
                }
            });
        } else {
            $('#' + suggestionsId).empty();
        }
    });

    $(document).on('click', '.suggestion', function () {
        var placeName = $(this).text();
        var lat = $(this).data('lat');
        var lon = $(this).data('lon');

        $('#' + inputId).val(placeName);
        $('#' + latId).val(lat);
        $('#' + longId).val(lon);
        $('#' + displayLatId).text('Latitude: ' + lat);
        $('#' + displayLongId).text('Longitude: ' + lon);
        $('#' + suggestionsId).empty();
    });

    $('#' + inputId).on('keypress', function (e) {
        if (e.which == 13) { // Enter key pressed
            e.preventDefault();
            var firstSuggestion = $('#' + suggestionsId + ' .suggestion').first();
            if (firstSuggestion.length > 0) {
                var placeName = firstSuggestion.text();
                var lat = firstSuggestion.data('lat');
                var lon = firstSuggestion.data('lon');

                $('#' + inputId).val(placeName);
                $('#' + latId).val(lat);
                $('#' + longId).val(lon);
                $('#' + displayLatId).text('Latitude: ' + lat);
                $('#' + displayLongId).text('Longitude: ' + lon);
                $('#' + suggestionsId).empty();
            }
        }
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#' + inputId).length) {
            $('#' + suggestionsId).empty();
        }
    });
}



// addressInput.js
function initializeAddressInput(addressInputId, suggestionsId, latInputId, longInputId, displayLatId, displayLongId) {
    // Assuming you already have address autocomplete setup
    const addressInput = document.getElementById(addressInputId);
    const latInput = document.getElementById(latInputId);
    const longInput = document.getElementById(longInputId);

    // Google Maps API setup
    const autocomplete = new google.maps.places.Autocomplete(addressInput);

    autocomplete.addListener('place_changed', function () {
        const place = autocomplete.getPlace();
        if (place.geometry) {
            latInput.value = place.geometry.location.lat();
            longInput.value = place.geometry.location.lng();
        }
    });
}

// Search button functionality
document.getElementById('searchButton').addEventListener('click', function () {
    const bloodGroup = document.getElementById('donorBloodgroup').value;
    const address = document.getElementById('userAddress').value;
    const latitude = document.getElementById('userLat').value;
    const longitude = document.getElementById('userLong').value;

    if (bloodGroup && address && latitude && longitude) {
        $.ajax({
            url: 'searchresult.php',
            type: 'POST',
            data: {
                bloodGroup: bloodGroup,
                latitude: latitude,
                longitude: longitude
            },
            success: function (response) {
                // Handle response, e.g., display donors in a new section
                $('#searchResults').html(response);
            },
            error: function (xhr, status, error) {
                console.error('Search failed:', error);
            }
        });
    } else {
        alert('Please select a blood group and enter a valid address.');
    }
});