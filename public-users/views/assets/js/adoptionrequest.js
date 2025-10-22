document.addEventListener('DOMContentLoaded', function() {
    var applicantModal = document.getElementById('applicantModal');
    if (!applicantModal) return;
    applicantModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget; // Button that triggered the modal
        var name = button.getAttribute('data-name') || '';
        var phone = button.getAttribute('data-phone') || '';
        var address = button.getAttribute('data-address') || '';
        var message = button.getAttribute('data-message') || '';
        var created = button.getAttribute('data-created') || '';
        document.getElementById('app-name').textContent = name;
        document.getElementById('app-phone').textContent = phone;
        document.getElementById('app-address').textContent = address;
        document.getElementById('app-message').textContent = message;
        document.getElementById('app-created').textContent = created ? new Date(created).toLocaleString() : '';
        // set message link to open chat with applicant id if available
        var hiddenAnchor = button.closest('.card').querySelector('a[href^="./ChatMessages.php"]');
        var applicantHref = hiddenAnchor ? hiddenAnchor.getAttribute('href') : '#';
        var link = document.getElementById('app-message-link');
        if (link) link.setAttribute('href', applicantHref || '#');
    });
});