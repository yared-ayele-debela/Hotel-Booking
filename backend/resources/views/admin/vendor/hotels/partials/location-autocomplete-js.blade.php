<script>
(function() {
    var searchInput = document.getElementById('location_search');
    var suggestionsEl = document.getElementById('location_suggestions');
    var addressInput = document.getElementById('hotel_address');
    var latInput = document.getElementById('hotel_latitude');
    var lngInput = document.getElementById('hotel_longitude');
    var coordsDisplay = document.getElementById('coordinates_display');
    var cityInput = document.querySelector('input[name="city"]');
    var countryInput = document.querySelector('input[name="country"]');

    if (!searchInput || !suggestionsEl) return;

    var debounceTimer;
    var autocompleteUrl = '{{ route("admin.geoapify.autocomplete") }}';

    function updateCoordsDisplay() {
        var lat = latInput ? latInput.value : '';
        var lng = lngInput ? lngInput.value : '';
        if (lat && lng) {
            coordsDisplay.textContent = lat + ', ' + lng;
        } else {
            coordsDisplay.textContent = '—';
        }
    }
    updateCoordsDisplay();

    function fillFromResult(feature) {
        var p = feature.properties || {};
        var cityVal = (p.city || p.county || p.state || '').trim();
        var countryVal = (p.country || '').trim();
        if (addressInput) addressInput.value = p.formatted || p.address_line1 || p.address_line2 || '';
        if (cityInput) cityInput.value = cityVal;
        if (countryInput) countryInput.value = countryVal;
        if (latInput) latInput.value = p.lat || '';
        if (lngInput) lngInput.value = p.lon || '';
        updateCoordsDisplay();
        searchInput.value = p.formatted || '';
        suggestionsEl.style.display = 'none';
        // Try to select matching city_id/country_id from dropdowns
        var countrySelect = document.getElementById('country_id');
        var citySelect = document.getElementById('city_id');
        if (countrySelect && countryVal) {
            var countryOpt = Array.from(countrySelect.options).find(function(o) {
                return o.value && o.text && o.text.toLowerCase().indexOf(countryVal.toLowerCase()) === 0;
            });
            if (countryOpt) { countrySelect.value = countryOpt.value; }
            if (typeof filterCities === 'function') filterCities();
        }
        if (citySelect && cityVal) {
            var cityOpt = Array.from(citySelect.options).find(function(o) {
                return o.value && o.text && o.text.toLowerCase().indexOf(cityVal.toLowerCase()) !== -1;
            });
            if (cityOpt) { citySelect.value = cityOpt.value; }
        }
    }

    function fetchSuggestions(text) {
        if (text.length < 2) {
            suggestionsEl.innerHTML = '';
            suggestionsEl.style.display = 'none';
            return;
        }
        fetch(autocompleteUrl + '?text=' + encodeURIComponent(text))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var features = data.features || [];
                if (data.error) {
                    suggestionsEl.innerHTML = '<div class="list-group-item text-danger">' + data.error + '</div>';
                } else if (features.length === 0) {
                    suggestionsEl.innerHTML = '<div class="list-group-item text-muted">No results found</div>';
                } else {
                    suggestionsEl.innerHTML = features.map(function(f) {
                        var label = (f.properties && f.properties.formatted) || 'Unknown';
                        var escaped = String(label).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
                        return '<a href="#" class="list-group-item list-group-item-action" data-json="' + encodeURIComponent(JSON.stringify(f)) + '">' + escaped + '</a>';
                    }).join('');
                }
                suggestionsEl.style.display = '';
            })
            .catch(function() {
                suggestionsEl.innerHTML = '<div class="list-group-item text-danger">Search failed</div>';
                suggestionsEl.style.display = '';
            });
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        var text = searchInput.value.trim();
        debounceTimer = setTimeout(function() { fetchSuggestions(text); }, 300);
    });

    searchInput.addEventListener('focus', function() {
        if (suggestionsEl.innerHTML) suggestionsEl.style.display = '';
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsEl.contains(e.target)) {
            suggestionsEl.style.display = 'none';
        }
    });

    suggestionsEl.addEventListener('click', function(e) {
        var item = e.target.closest('a[data-json]');
        if (item) {
            e.preventDefault();
            try {
                var feature = JSON.parse(decodeURIComponent(item.getAttribute('data-json')));
                fillFromResult(feature);
            } catch (err) {}
        }
    });
})();
</script>
