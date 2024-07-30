<!-- <!DOCTYPE html>
<html lang="en">

<head>
  <title>Location</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBY5p5e5PtJuJL1_nRpjefL05094jdhEP8&lib"></script>
</head>

<body>
  <input type="text" id="location">

  <script type="text/javascript">
    $(document).ready(function() {
      var autocomplete;
      var id = 'location'; // Corrected variable assignment

      autocomplete = new google.maps.places.Autocomplete(document.getElementById(id), {
        types: ['geocode']
      })
      google.maps.event.addListener(autocomplete,'place_changed',finction(){
        var place=autocomplete.place();
        jQuery('#lat').val(place.geometry.location.lat());
        jQuery('#long').val(place.geometry.location.lng());
      })
    });
  </script>
</body>

</html> -->





<!DOCTYPE html>
<html lang="en">
<head>
  <title>Location</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <style>
    /* Basic styling for suggestions dropdown */
    .suggestions {
      border: 1px solid #ccc;
      max-height: 150px;
      overflow-y: auto;
      position: absolute;
      z-index: 1000;
      background: #fff;
      width: 200px;
    }
    .suggestion {
      padding: 10px;
      cursor: pointer;
    }
    .suggestion:hover {
      background: #f0f0f0;
    }
  </style>
</head>
<body>
  <input type="text" id="location" placeholder="Enter location">
  <div id="suggestions" class="suggestions"></div>
  <input type="hidden" id="lat">
  <input type="hidden" id="long">
  <div>
    <p id="display-lat"></p>
    <p id="display-long"></p>
  </div>

  <script type="text/javascript">
    $(document).ready(function () {
      $('#location').on('input', function () {
        var address = $(this).val();
        if (address.length > 2) {
          var url = "https://nominatim.openstreetmap.org/search?format=json&q=" + encodeURIComponent(address);

          $.ajax({
            url: url,
            method: 'GET',
            success: function (data) {
              $('#suggestions').empty();
              if (data.length > 0) {
                data.forEach(function (place) {
                  $('#suggestions').append('<div class="suggestion" data-lat="' + place.lat + '" data-lon="' + place.lon + '">' + place.display_name + '</div>');
                });
              }
            },
            error: function (error) {
              console.log('Error:', error);
            }
          });
        } else {
          $('#suggestions').empty();
        }
      });

      $(document).on('click', '.suggestion', function () {
        var placeName = $(this).text();
        var lat = $(this).data('lat');
        var lon = $(this).data('lon');

        $('#location').val(placeName);
        $('#lat').val(lat);
        $('#long').val(lon);
        $('#display-lat').text('Latitude: ' + lat);
        $('#display-long').text('Longitude: ' + lon);
        $('#suggestions').empty();
      });

      $('#location').on('keypress', function (e) {
        if (e.which == 13) { // Enter key pressed
          e.preventDefault();
          var firstSuggestion = $('#suggestions .suggestion').first();
          if (firstSuggestion.length > 0) {
            var placeName = firstSuggestion.text();
            var lat = firstSuggestion.data('lat');
            var lon = firstSuggestion.data('lon');

            $('#location').val(placeName);
            $('#lat').val(lat);
            $('#long').val(lon);
            $('#display-lat').text('Latitude: ' + lat);
            $('#display-long').text('Longitude: ' + lon);
            $('#suggestions').empty();
          }
        }
      });

      $(document).on('click', function (e) {
        if (!$(e.target).closest('#location').length) {
          $('#suggestions').empty();
        }
      });
    });
  </script>
</body>
</html>
