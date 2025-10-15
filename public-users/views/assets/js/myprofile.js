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
            fetch(
                    `https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lng}&localityLanguage=en`)
                .then(response => response.json())
                .then(data => {
                    // Fill the address fields
                    const admin = data.localityInfo?.administrative || [];
                    document.querySelector('input[name="province"]').value = admin[2]?.name || data
                        .countryName || '';
                    document.querySelector('input[name="city"]').value = admin[3]?.name || data
                        .city || '';
                    document.querySelector('input[name="barangay"]').value = admin[4]?.name || '';
                    document.querySelector('input[name="purok_subdivision"]').value = data
                        .locality || '';
                    document.querySelector('input[name="postal_code"]').value = data.postcode || '';
                    document.querySelector('input[name="shelter_unit"]').value = ''; // Leave empty
                    // Show map with satellite tiles
                    document.getElementById('map').style.display = 'block';
                    if (!map) {
                        map = L.map('map').setView([lat, lng], 18);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors'
                        }).addTo(map);
                        marker = L.marker([lat, lng], {
                            icon: L.icon({
                                iconUrl: 'assets/images/marker-icon-red.png',
                                iconSize: [25, 41],
                                iconAnchor: [12, 41]
                            }),
                            draggable: true
                        }).addTo(map);
                        marker.on('dragend', function(e) {
                            const pos = e.target.getLatLng();
                            fetch(
                                    `https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${pos.lat}&longitude=${pos.lng}&localityLanguage=en`)
                                .then(response => response.json())
                                .then(data => {
                                    // Fill the fields with new location
                                    const admin = data.localityInfo?.administrative ||
                                    [];
                                    document.querySelector('input[name="province"]')
                                        .value = admin[2]?.name || data.countryName ||
                                        '';
                                    document.querySelector('input[name="city"]').value =
                                        admin[3]?.name || data.city || '';
                                    document.querySelector('input[name="barangay"]')
                                        .value = admin[4]?.name || '';
                                    document.querySelector(
                                            'input[name="purok_subdivision"]').value =
                                        data.locality || '';
                                    document.querySelector('input[name="postal_code"]')
                                        .value = data.postcode || '';
                                    document.querySelector('input[name="shelter_unit"]')
                                        .value = '';
                                });
                        });
                    } else {
                        map.setView([lat, lng], 18);
                        marker.setLatLng([lat, lng]);
                    }
                })
                .catch(error => {
                    console.error('Reverse geocoding error:', error);
                    // Still show map
                    document.getElementById('map').style.display = 'block';
                    if (!map) {
                        map = L.map('map').setView([lat, lng], 18);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors'
                        }).addTo(map);
                        marker = L.marker([lat, lng], {
                            icon: L.icon({
                                iconUrl: 'assets/images/marker-icon-red.png',
                                iconSize: [25, 41],
                                iconAnchor: [12, 41]
                            }),
                            draggable: true
                        }).addTo(map);
                    } else {
                        map.setView([lat, lng], 18);
                        marker.setLatLng([lat, lng]);
                    }
                });
        }, function(error) {
            alert('Error getting location: ' + error.message);
        });
    } else {
        alert('Geolocation is not supported by this browser.');
    }
});
// End Location Script for Get Location//

// function delete photo
function deletePhoto() {
    if (confirm('Are you sure you want to delete your profile photo?')) {
        const form = document.createElement('form');
        form.method = 'post';
        form.action = '../controllers/EditMyProfileController.php';
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_photo';
        input.value = '1';
        form.appendChild(input);
        const userIdInput = document.createElement('input');
        userIdInput.type = 'hidden';
        userIdInput.name = 'user_id';
        userIdInput.value = window.userId;
        form.appendChild(userIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}
// end delete photo function script//

// Camera functionality for ID verification
const openCameraBtn = document.getElementById('openCameraBtn');
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const idPhotoInput = document.getElementById('idPhotoInput');
const idPhotoBackInput = document.getElementById('idPhotoBackInput');
let isFront = true;
let retakeFront = false;

openCameraBtn.addEventListener('click', function() {
    // Disable the button to prevent multiple clicks
    openCameraBtn.disabled = true;

    // Reset flags
    isFront = true;
    retakeFront = false;

    // Show the camera container
    document.getElementById('cameraContainer').style.display = 'block';

    // Start the video stream
    navigator.mediaDevices.getUserMedia({
            video: true
        })
        .then(function(stream) {
            video.srcObject = stream;
            video.play();
        })
        .catch(function(err) {
            console.error('Error accessing camera: ' + err);
            openCameraBtn.disabled = false; // Re-enable the button
        });
});

document.getElementById('captureBtn').addEventListener('click', function() {
    if (isFront) {
        // Capture front
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        idPhotoInput.value = canvas.toDataURL('image/png');

        // Stop stream
        const stream = video.srcObject;
        if (stream) {
            const tracks = stream.getTracks();
            tracks.forEach(function(track) {
                track.stop();
            });
        }
        video.srcObject = null;

        // Hide video, show captured images with front
        video.style.display = 'none';
        document.getElementById('capturedImages').style.display = 'block';
        document.getElementById('frontImg').src = idPhotoInput.value;

        // Change label
        document.getElementById('captureLabel').textContent = 'ID Photos Captured';
        captureBtn.style.display = 'none';
        isFront = false;
        retakeFront = true;

        // Show next back button and retake
        document.getElementById('nextBackBtn').style.display = 'inline-block';
        retakeBtn.style.display = 'inline-block';
        retakeBtn.textContent = 'Retake Front';
    } else {
        // Capture back
        navigator.mediaDevices.getUserMedia({
                video: true
            })
            .then(function(stream) {
                video.srcObject = stream;
                video.play();
                video.onloadedmetadata = function() {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0);
                    idPhotoBackInput.value = canvas.toDataURL('image/png');

                    // Stop stream
                    const stream2 = video.srcObject;
                    if (stream2) {
                        const tracks = stream2.getTracks();
                        tracks.forEach(function(track) {
                            track.stop();
                        });
                    }
                    video.srcObject = null;

                    // Hide video, show captured images with both
                    video.style.display = 'none';
                    document.getElementById('capturedImages').style.display = 'block';
                    document.getElementById('frontImg').src = idPhotoInput.value;
                    document.getElementById('backImg').src = idPhotoBackInput.value;
                    document.getElementById('backContainer').style.display = 'block';

                    // Change label
                    document.getElementById('captureLabel').textContent = 'ID Photos Captured';
                    captureBtn.style.display = 'none';
                    retakeBtn.textContent = 'Retake Back';
                    retakeFront = false;
                    document.getElementById('removeFrontBtn').style.display = 'none';
                    document.getElementById('removeBackBtn').style.display = 'none';
                    document.getElementById('removeAllBtn').style.display = 'inline-block';
                    // Enable submit button since both front and back are captured
                    document.getElementById('submitVerificationBtn').disabled = false;
                };
            })
            .catch(function(err) {
                console.error('Error accessing camera: ' + err);
            });
    }
});

document.getElementById('nextBackBtn').addEventListener('click', function() {
    // Hide captured images, show video for back
    document.getElementById('capturedImages').style.display = 'none';
    video.style.display = 'block';

    // Start stream
    navigator.mediaDevices.getUserMedia({
            video: true
        })
        .then(function(stream) {
            video.srcObject = stream;
            video.play();
        })
        .catch(function(err) {
            console.error('Error accessing camera: ' + err);
        });

    // Change label
    document.getElementById('captureLabel').textContent = 'Capture Back of ID';
    captureBtn.style.display = 'inline-block';
    captureBtn.textContent = 'Capture Back';
    document.getElementById('nextBackBtn').style.display = 'none';
    retakeBtn.style.display = 'none';
});

document.getElementById('retakeBtn').addEventListener('click', function() {
    // Hide captured images
    document.getElementById('capturedImages').style.display = 'none';

    if (retakeFront) {
        // Retake front
        video.style.display = 'block';
        navigator.mediaDevices.getUserMedia({
                video: true
            })
            .then(function(stream) {
                video.srcObject = stream;
                video.play();
                // Change label
                document.getElementById('captureLabel').textContent = 'Capture Front of ID';
                captureBtn.style.display = 'inline-block';
                captureBtn.textContent = 'Capture';
                document.getElementById('nextBackBtn').style.display = 'none';
                retakeBtn.style.display = 'none';
                isFront = true;
            });
    } else {
        // Retake back
        video.style.display = 'block';
        navigator.mediaDevices.getUserMedia({
                video: true
            })
            .then(function(stream) {
                video.srcObject = stream;
                video.play();
                // Change label
                document.getElementById('captureLabel').textContent = 'Capture Back of ID';
                captureBtn.style.display = 'inline-block';
                captureBtn.textContent = 'Capture Back';
                document.getElementById('nextBackBtn').style.display = 'none';
                retakeBtn.style.display = 'none';
            });
    }
    // Disable submit button since retaking
    document.getElementById('submitVerificationBtn').disabled = true;
});

document.getElementById('docType').addEventListener('change', function() {
    const openCameraBtn = document.getElementById('openCameraBtn');
    if (this.value) {
        openCameraBtn.disabled = false;
    } else {
        openCameraBtn.disabled = true;
    }
});

document.getElementById('removeFrontBtn').addEventListener('click', function() {
    // Clear front image
    document.getElementById('frontImg').src = '';
    idPhotoInput.value = '';
    // Hide back container
    document.getElementById('backContainer').style.display = 'none';
    // Show video for front
    document.getElementById('capturedImages').style.display = 'none';
    video.style.display = 'block';
    // Start stream
    navigator.mediaDevices.getUserMedia({
            video: true
        })
        .then(function(stream) {
            video.srcObject = stream;
            video.play();
        });
    // Change label
    document.getElementById('captureLabel').textContent = 'Capture Front of ID';
    captureBtn.style.display = 'inline-block';
    captureBtn.textContent = 'Capture';
    document.getElementById('nextBackBtn').style.display = 'none';
    retakeBtn.style.display = 'none';
    isFront = true;
    retakeFront = false;
    // Disable submit button since front is removed
    document.getElementById('submitVerificationBtn').disabled = true;
});

document.getElementById('removeBackBtn').addEventListener('click', function() {
    // Clear back image
    document.getElementById('backImg').src = '';
    idPhotoBackInput.value = '';
    // Hide back container
    document.getElementById('backContainer').style.display = 'none';
    // Show video for back
    document.getElementById('capturedImages').style.display = 'none';
    video.style.display = 'block';
    // Start stream
    navigator.mediaDevices.getUserMedia({
            video: true
        })
        .then(function(stream) {
            video.srcObject = stream;
            video.play();
        });
    // Change label
    document.getElementById('captureLabel').textContent = 'Capture Back of ID';
    captureBtn.style.display = 'inline-block';
    captureBtn.textContent = 'Capture Back';
    document.getElementById('nextBackBtn').style.display = 'none';
    retakeBtn.style.display = 'none';
    isFront = false;
    retakeFront = false;
    // Disable submit button since back is removed
    document.getElementById('submitVerificationBtn').disabled = true;
});

document.getElementById('removeAllBtn').addEventListener('click', function() {
    // Clear both images
    document.getElementById('frontImg').src = '';
    document.getElementById('backImg').src = '';
    idPhotoInput.value = '';
    idPhotoBackInput.value = '';
    // Hide back container
    document.getElementById('backContainer').style.display = 'none';
    // Show video for front
    document.getElementById('capturedImages').style.display = 'none';
    video.style.display = 'block';
    // Start stream
    navigator.mediaDevices.getUserMedia({
            video: true
        })
        .then(function(stream) {
            video.srcObject = stream;
            video.play();
        });
    // Change label
    document.getElementById('captureLabel').textContent = 'Capture Front of ID';
    captureBtn.style.display = 'inline-block';
    captureBtn.textContent = 'Capture';
    document.getElementById('nextBackBtn').style.display = 'none';
    retakeBtn.style.display = 'none';
    document.getElementById('removeAllBtn').style.display = 'none';
    isFront = true;
    retakeFront = false;
    // Disable submit button since all are removed
    document.getElementById('submitVerificationBtn').disabled = true;
});

var postMedia = {};
for (var i in window.posts) {
    postMedia[window.posts[i].id] = window.posts[i].media;
}

var currentPostId = null;
var currentIndex = 0;

function openMediaModal(postId, index, type) {
    const mediaList = postMedia[postId];
    const carouselInner = document.getElementById('carousel-inner');
    carouselInner.innerHTML = '';
    mediaList.forEach((media, i) => {
        const item = document.createElement('div');
        item.className = 'carousel-item' + (i === index ? ' active' : '');
        if (media.type === 'image') {
            item.innerHTML = `<img src="${media.url}" class="d-block w-100" alt="Media" style="border-radius: 10px;">`;
        } else {
            item.innerHTML = `<video controls muted class="d-block w-100" style="border-radius: 10px;"><source src="${media.url}" type="video/mp4"></video>`;
        }
        carouselInner.appendChild(item);
    });
    $('#imageModal').modal('show');
    $('.modal-backdrop').css('background-color', 'rgba(0, 0, 0, 0.7)');
    $('#imageCarousel').carousel(index);
}