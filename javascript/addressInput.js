// export function initializeAddressInput(inputId, suggestionsId, latId, longId, displayLatId, displayLongId) {
//     console.log('Initializing address input...');
//     const locationInput = document.getElementById(inputId);
//     const suggestionsDiv = document.getElementById(suggestionsId);
//     const latInput = document.getElementById(latId);
//     const longInput = document.getElementById(longId);
//     const displayLat = document.getElementById(displayLatId);
//     const displayLong = document.getElementById(displayLongId);

//     console.log('Location Input:', locationInput);
//     console.log('Suggestions Div:', suggestionsDiv);
//     console.log('Latitude Input:', latInput);
//     console.log('Longitude Input:', longInput);

//     locationInput.addEventListener('input', function () {
//         const address = this.value;
//         console.log('Input value:', address);
//         if (address.length > 2) {
//             const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&addressdetails=1`;

//             fetch(url, {
//                 headers: {
//                     'User-Agent': 'blooddonation/1.0'
//                 }
//             })
//             .then(response => response.json())
//             .then(data => {
//                 console.log('API response data:', data); // Debugging output
//                 suggestionsDiv.innerHTML = '';
//                 if (data.length > 0) {
//                     data.forEach(place => {
//                         const suggestionDiv = document.createElement('div');
//                         suggestionDiv.className = 'suggestion';
//                         suggestionDiv.dataset.lat = place.lat;
//                         suggestionDiv.dataset.lon = place.lon;
//                         suggestionDiv.textContent = place.display_name;
//                         suggestionsDiv.appendChild(suggestionDiv);
//                     });
//                 }
//             })
//             .catch(error => {
//                 console.error('Error:', error); // Debugging output
//             });
//         } else {
//             suggestionsDiv.innerHTML = '';
//         }
//     });

//     suggestionsDiv.addEventListener('click', function (e) {
//         if (e.target && e.target.classList.contains('suggestion')) {
//             const placeName = e.target.textContent;
//             const lat = e.target.dataset.lat;
//             const lon = e.target.dataset.lon;

//             locationInput.value = placeName;
//             latInput.value = lat;
//             longInput.value = lon;
//             displayLat.textContent = `Latitude: ${lat}`;
//             displayLong.textContent = `Longitude: ${lon}`;
//             suggestionsDiv.innerHTML = '';
//         }
//     });

//     locationInput.addEventListener('keypress', function (e) {
//         if (e.key === 'Enter') {
//             e.preventDefault();
//             const firstSuggestion = suggestionsDiv.querySelector('.suggestion');
//             if (firstSuggestion) {
//                 const placeName = firstSuggestion.textContent;
//                 const lat = firstSuggestion.dataset.lat;
//                 const lon = firstSuggestion.dataset.lon;

//                 locationInput.value = placeName;
//                 latInput.value = lat;
//                 longInput.value = lon;
//                 displayLat.textContent = `Latitude: ${lat}`;
//                 displayLong.textContent = `Longitude: ${lon}`;
//                 suggestionsDiv.innerHTML = '';
//             }
//         }
//     });

//     document.addEventListener('click', function (e) {
//         if (!e.target.closest(`#${inputId}`)) {
//             suggestionsDiv.innerHTML = '';
//         }
//     });
// }


// $(document).ready(function () {
//     $('#location').on('input', function () {
//       var address = $(this).val();
//       if (address.length > 2) {
//         var url = "https://nominatim.openstreetmap.org/search?format=json&q=" + encodeURIComponent(address) + "&countrycodes=NP";
  
//         $.ajax({
//           url: url,
//           method: 'GET',
//           success: function (data) {
//             $('#suggestions').empty();
//             if (data.length > 0) {
//               data.forEach(function (place) {
//                 $('#suggestions').append('<div class="suggestion" data-lat="' + place.lat + '" data-lon="' + place.lon + '">' + place.display_name + '</div>');
//               });
//             }
//           },
//           error: function (error) {
//             console.log('Error:', error);
//           }
//         });
//       } else {
//         $('#suggestions').empty();
//       }
//     });
  
//     $(document).on('click', '.suggestion', function () {
//       var placeName = $(this).text();
//       var lat = $(this).data('lat');
//       var lon = $(this).data('lon');
  
//       $('#location').val(placeName);
//       $('#lat').val(lat);
//       $('#long').val(lon);
//       $('#display-lat').text('Latitude: ' + lat);
//       $('#display-long').text('Longitude: ' + lon);
//       $('#suggestions').empty();
//     });
  
//     $('#location').on('keypress', function (e) {
//       if (e.which == 13) { // Enter key pressed
//         e.preventDefault();
//         var firstSuggestion = $('#suggestions .suggestion').first();
//         if (firstSuggestion.length > 0) {
//           var placeName = firstSuggestion.text();
//           var lat = firstSuggestion.data('lat');
//           var lon = firstSuggestion.data('lon');
  
//           $('#location').val(placeName);
//           $('#lat').val(lat);
//           $('#long').val(lon);
//           $('#display-lat').text('Latitude: ' + lat);
//           $('#display-long').text('Longitude: ' + lon);
//           $('#suggestions').empty();
//         }
//       }
//     });
  
//     $(document).on('click', function (e) {
//       if (!$(e.target).closest('#location').length) {
//         $('#suggestions').empty();
//       }
//     });
//   });
  


// $(document).ready(function () {
//     $('#location').on('input', function () {
//       var address = $(this).val();
//       // Change the condition to show suggestions after 1 character
//       if (address.length > 0) {
//         var url = "https://nominatim.openstreetmap.org/search?format=json&q=" + encodeURIComponent(address) + "&countrycodes=NP";
  
//         $.ajax({
//           url: url,
//           method: 'GET',
//           success: function (data) {
//             $('#suggestions').empty();
//             if (data.length > 0) {
//               data.forEach(function (place) {
//                 $('#suggestions').append('<div class="suggestion" data-lat="' + place.lat + '" data-lon="' + place.lon + '">' + place.display_name + '</div>');
//               });
//             }
//           },
//           error: function (error) {
//             console.log('Error:', error);
//           }
//         });
//       } else {
//         $('#suggestions').empty();
//       }
//     });
  
//     $(document).on('click', '.suggestion', function () {
//       var placeName = $(this).text();
//       var lat = $(this).data('lat');
//       var lon = $(this).data('lon');
  
//       $('#location').val(placeName);
//       $('#lat').val(lat);
//       $('#long').val(lon);
//       $('#display-lat').text('Latitude: ' + lat);
//       $('#display-long').text('Longitude: ' + lon);
//       $('#suggestions').empty();
//     });
  
//     $('#location').on('keypress', function (e) {
//       if (e.which == 13) { // Enter key pressed
//         e.preventDefault();
//         var firstSuggestion = $('#suggestions .suggestion').first();
//         if (firstSuggestion.length > 0) {
//           var placeName = firstSuggestion.text();
//           var lat = firstSuggestion.data('lat');
//           var lon = firstSuggestion.data('lon');
  
//           $('#location').val(placeName);
//           $('#lat').val(lat);
//           $('#long').val(lon);
//           $('#display-lat').text('Latitude: ' + lat);
//           $('#display-long').text('Longitude: ' + lon);
//           $('#suggestions').empty();
//         }
//       }
//     });
  
//     $(document).on('click', function (e) {
//       if (!$(e.target).closest('#location').length) {
//         $('#suggestions').empty();
//       }
//     });
//   });
  


// $(document).ready(function () {
//     $('#location').on('input', function () {
//       var address = $(this).val();
//       // Show suggestions for each word or space input
//       if (address.trim().length > 0) {
//         var url = "https://nominatim.openstreetmap.org/search?format=json&q=" + encodeURIComponent(address) + "&countrycodes=NP";
  
//         $.ajax({
//           url: url,
//           method: 'GET',
//           success: function (data) {
//             $('#suggestions').empty();
//             if (data.length > 0) {
//               data.forEach(function (place) {
//                 $('#suggestions').append('<div class="suggestion" data-lat="' + place.lat + '" data-lon="' + place.lon + '">' + place.display_name + '</div>');
//               });
//             }
//           },
//           error: function (error) {
//             console.log('Error:', error);
//           }
//         });
//       } else {
//         $('#suggestions').empty();
//       }
//     });
  
//     $(document).on('click', '.suggestion', function () {
//       var placeName = $(this).text();
//       var lat = $(this).data('lat');
//       var lon = $(this).data('lon');
  
//       $('#location').val(placeName);
//       $('#lat').val(lat);
//       $('#long').val(lon);
//       $('#display-lat').text('Latitude: ' + lat);
//       $('#display-long').text('Longitude: ' + lon);
//       $('#suggestions').empty();
//     });
  
//     $('#location').on('keypress', function (e) {
//       if (e.which == 13) { // Enter key pressed
//         e.preventDefault();
//         var firstSuggestion = $('#suggestions .suggestion').first();
//         if (firstSuggestion.length > 0) {
//           var placeName = firstSuggestion.text();
//           var lat = firstSuggestion.data('lat');
//           var lon = firstSuggestion.data('lon');
  
//           $('#location').val(placeName);
//           $('#lat').val(lat);
//           $('#long').val(lon);
//           $('#display-lat').text('Latitude: ' + lat);
//           $('#display-long').text('Longitude: ' + lon);
//           $('#suggestions').empty();
//         }
//       }
//     });
  
//     $(document).on('click', function (e) {
//       if (!$(e.target).closest('#location').length) {
//         $('#suggestions').empty();
//       }
//     });
//   });
  

// $(document).ready(function () {
//     $('#location').on('input', function () {
//         var address = $(this).val().trim();
//         // Show suggestions for each word or space input
//         if (address.length > 0) {
//             var url = "https://nominatim.openstreetmap.org/search?format=json&q=" + encodeURIComponent(address) + "&countrycodes=NP";

//             $.ajax({
//                 url: url,
//                 method: 'GET',
//                 success: function (data) {
//                     $('#suggestions').empty();
//                     if (data.length > 0) {
//                         data.forEach(function (place) {
//                             $('#suggestions').append('<div class="suggestion" data-lat="' + place.lat + '" data-lon="' + place.lon + '">' + place.display_name + '</div>');
//                         });
//                     }
//                 },
//                 error: function (error) {
//                     console.log('Error:', error);
//                 }
//             });
//         } else {
//             $('#suggestions').empty();
//         }
//     });

//     $(document).on('click', '.suggestion', function () {
//         var placeName = $(this).text();
//         var lat = $(this).data('lat');
//         var lon = $(this).data('lon');

//         $('#location').val(placeName);
//         $('#userLat').val(lat);
//         $('#userLong').val(lon);
//         $('#display-lat').text('Latitude: ' + lat);
//         $('#display-long').text('Longitude: ' + lon);
//         $('#suggestions').empty();
//     });

//     $('#location').on('keypress', function (e) {
//         if (e.which == 13) { // Enter key pressed
//             e.preventDefault();
//             var firstSuggestion = $('#suggestions .suggestion').first();
//             if (firstSuggestion.length > 0) {
//                 var placeName = firstSuggestion.text();
//                 var lat = firstSuggestion.data('lat');
//                 var lon = firstSuggestion.data('lon');

//                 $('#location').val(placeName);
//                 $('#userLat').val(lat);
//                 $('#userLong').val(lon);
//                 $('#display-lat').text('Latitude: ' + lat);
//                 $('#display-long').text('Longitude: ' + lon);
//                 $('#suggestions').empty();
//             }
//         }
//     });

//     $(document).on('click', function (e) {
//         if (!$(e.target).closest('#location').length) {
//             $('#suggestions').empty();
//         }
//     });
// });
export function initializeAddressInput(inputId, suggestionsId, latId, longId, displayLatId, displayLongId) {
    $(document).ready(function () {
        const locationInput = $('#' + inputId);
        const suggestionsDiv = $('#' + suggestionsId);
        const latInput = $('#' + latId);
        const longInput = $('#' + longId);
        const displayLat = $('#' + displayLatId);
        const displayLong = $('#' + displayLongId);

        function setLocationData(placeName, lat, lon) {
            locationInput.val(placeName);
            latInput.val(lat);
            longInput.val(lon);
            displayLat.text('Latitude: ' + lat);
            displayLong.text('Longitude: ' + lon);
        }

        locationInput.on('input', function () {
            var address = $(this).val().trim();
            if (address.length > 0) {
                suggestionsDiv.empty().append('<div>Loading...</div>');
                var url = "https://nominatim.openstreetmap.org/search?format=json&q=" + encodeURIComponent(address) + "&countrycodes=NP";

                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function (data) {
                        suggestionsDiv.empty();
                        if (data.length > 0) {
                            data.forEach(function (place) {
                                var formattedAddress = place.display_name.replace(/(<([^>]+)>)/gi, ""); // Remove HTML tags
                                suggestionsDiv.append('<div class="suggestion" data-lat="' + place.lat + '" data-lon="' + place.lon + '">' + formattedAddress + '</div>');
                            });
                        } else {
                            suggestionsDiv.append('<div>No results found</div>');
                        }
                    },
                    error: function (error) {
                        console.log('Error:', error);
                        suggestionsDiv.empty().append('<div>Failed to fetch suggestions. Please try again.</div>');
                    }
                });
            } else {
                suggestionsDiv.empty();
                latInput.val('');
                longInput.val('');
                displayLat.text('Latitude: ');
                displayLong.text('Longitude: ');
            }
        });

        suggestionsDiv.on('click', '.suggestion', function () {
            var placeName = $(this).text();
            var lat = $(this).data('lat');
            var lon = $(this).data('lon');
            setLocationData(placeName, lat, lon);
            suggestionsDiv.empty();
        });

        locationInput.on('keypress', function (e) {
            if (e.which == 13) { // Enter key pressed
                e.preventDefault();
                var firstSuggestion = suggestionsDiv.find('.suggestion').first();
                if (firstSuggestion.length > 0) {
                    var placeName = firstSuggestion.text();
                    var lat = firstSuggestion.data('lat');
                    var lon = firstSuggestion.data('lon');
                    setLocationData(placeName, lat, lon);
                    suggestionsDiv.empty();
                }
            }
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('#' + inputId).length && !$(e.target).closest('#' + suggestionsId).length) {
                suggestionsDiv.empty();
            }
        });
    });
}
