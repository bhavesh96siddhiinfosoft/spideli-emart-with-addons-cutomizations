{{-- Zone create / edit, Google mode: the place search above the map.

     It used to attach BOTH a SearchBox and an Autocomplete to the same input.
     The suggestion list the admin clicks is the Autocomplete's, and its
     handler only filled the text - only the SearchBox moved the map. So a
     chosen suggestion never moved the map. One Autocomplete now does both:
     a chosen place is framed on the map; Enter without choosing looks the
     typed text up with the Geocoder. --}}
<script>
    function spideliZoneSearch(map, input) {
        if (!map || !input || !window.google || !google.maps || !google.maps.places) {
            return;
        }
        var autocomplete = new google.maps.places.Autocomplete(input, {
            fields: ['geometry', 'formatted_address', 'address_components', 'name']
        });
        autocomplete.bindTo('bounds', map);

        function frame(geometry) {
            if (!geometry) {
                return false;
            }
            if (geometry.viewport) {
                map.fitBounds(geometry.viewport);
            } else if (geometry.location) {
                map.setCenter(geometry.location);
                map.setZoom(14);
            } else {
                return false;
            }
            return true;
        }

        function remember(place, location) {
            var lat = location && typeof location.lat === 'function' ? location.lat() : '';
            var lng = location && typeof location.lng === 'function' ? location.lng() : '';
            $(input).attr('data-latitude', lat).attr('data-longitude', lng)
                .attr('data-city', place ? spideliPlaceCity(place) : '')
                .attr('data-state', place ? spideliPlaceComponent(place, 'administrative_area_level_1') : '')
                .attr('data-country', place ? spideliPlaceComponent(place, 'country') : '');
        }

        autocomplete.addListener('place_changed', function () {
            var place = autocomplete.getPlace();
            if (place && place.geometry) {
                frame(place.geometry);
                if (place.formatted_address) {
                    input.value = place.formatted_address;
                }
                remember(place, place.geometry.location);
                return;
            }
            // Enter pressed without choosing a suggestion: look the text up.
            var text = (input.value || '').trim();
            if (!text) {
                return;
            }
            new google.maps.Geocoder().geocode({ address: text, bounds: map.getBounds() }, function (results, status) {
                if (status === 'OK' && results && results[0]) {
                    frame(results[0].geometry);
                    input.value = results[0].formatted_address;
                    remember(results[0], results[0].geometry.location);
                } else {
                    alert("{{ trans('lang.location_not_found_please_try_again') }}");
                }
            });
        });

        // Enter in the search box must not submit the zone form.
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });
    }
</script>
