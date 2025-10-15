// Set default marker icon paths to the provided local file
L.Icon.Default.prototype.options.iconUrl = 'assets/images/marker-icon-red.png';
L.Icon.Default.prototype.options.iconRetinaUrl = '';
L.Icon.Default.prototype.options.shadowUrl = '';

var map;
var marker;
document.getElementById('getLocationBtn').addEventListener('click', function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            // Reverse geocode using BigDataCloud
            fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lng}&localityLanguage=en`)
                .then(response => response.json())
                .then(data => {
                    // Fill the address fields
                    const admin = data.localityInfo?.administrative || [];
                    document.querySelector('input[name="province"]').value = admin[2]?.name || data.countryName || '';
                    document.querySelector('input[name="city"]').value = admin[3]?.name || data.city || '';
                    document.querySelector('input[name="barangay"]').value = admin[4]?.name || '';
                    document.querySelector('input[name="purok_subdivision"]').value = data.locality || '';
                    document.querySelector('input[name="postal_code"]').value = data.postcode || '';
                    document.querySelector('input[name="shelter_unit"]').value = '';
                    // Show map with satellite tiles
                    document.getElementById('map').style.display = 'block';
                    if (!map) {
                        map = L.map('map').setView([lat, lng], 15);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors'
                        }).addTo(map);
                        marker = L.marker([lat, lng], { icon: L.icon({ iconUrl: 'assets/images/marker-icon-red.png', iconSize: [25, 41], iconAnchor: [12, 41] }), draggable: true }).addTo(map);
                        map.invalidateSize();
                        setTimeout(() => map.invalidateSize(), 500);
                        marker.on('dragend', function(e) {
                            const pos = e.target.getLatLng();
                            fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${pos.lat}&longitude=${pos.lng}&localityLanguage=en`)
                                .then(response => response.json())
                                .then(data => {
                                    // Fill the fields with new location
                                    const admin = data.localityInfo?.administrative || [];
                                    document.querySelector('input[name="province"]').value = admin[2]?.name || data.countryName || '';
                                    document.querySelector('input[name="city"]').value = admin[3]?.name || data.city || '';
                                    document.querySelector('input[name="barangay"]').value = admin[4]?.name || '';
                                    document.querySelector('input[name="purok_subdivision"]').value = data.locality || '';
                                    document.querySelector('input[name="postal_code"]').value = data.postcode || '';
                                    document.querySelector('input[name="shelter_unit"]').value = '';
                                });
                        });
                    } else {
                        map.setView([lat, lng], 15);
                        marker.setLatLng([lat, lng]);
                    }
                })
                .catch(error => {
                    console.error('Reverse geocoding error:', error);
                    // Still show map
                    document.getElementById('map').style.display = 'block';
                    if (!map) {
                        map = L.map('map').setView([lat, lng], 15);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors'
                        }).addTo(map);
                        marker = L.marker([lat, lng], { icon: L.icon({ iconUrl: 'assets/images/marker-icon-red.png', iconSize: [25, 41], iconAnchor: [12, 41] }), draggable: true }).addTo(map);
                        map.invalidateSize();
                        setTimeout(() => map.invalidateSize(), 500);
                        marker.on('dragend', function(e) {
                            const pos = e.target.getLatLng();
                            // No fill
                        });
                    } else {
                        map.setView([lat, lng], 15);
                        marker.setLatLng([lat, lng]);
                    }
                });
        }, function(error) {
            var message = '';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    message = "Location access was denied. Please allow location access in your browser settings or enter your address manually.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    message = "Location information is unavailable. Please enter your address manually.";
                    break;
                case error.TIMEOUT:
                    message = "Location request timed out. Please try again or enter your address manually.";
                    break;
                default:
                    message = "An unknown error occurred while retrieving location. Please enter your address manually.";
                    break;
            }
            var errorDiv = document.getElementById('locationError');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            setTimeout(function() {
                errorDiv.style.display = 'none';
            }, 5000); // Hide after 5 seconds
        }, {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        });
    } else {
        alert('Geolocation is not supported by this browser.');
    }
});