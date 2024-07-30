

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
