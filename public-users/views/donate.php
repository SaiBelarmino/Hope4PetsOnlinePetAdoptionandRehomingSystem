<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>

<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = 'Donate';
?>

<div class="container-fluid">
    <div class="row g-3 py-4">
            <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
        <div class="col-12 col-lg-6">
            <div class="card mb-3">
                <div class="card-body">
                    <h4 class="mb-2">Donate to Hope4Pets</h4>
                    <p class="text-muted">Thank you for considering a donation. Select an amount or enter a
                        custom one. We accept GCash and direct Bank Transfers.</p>

                    <div id="donation-alert-placeholder"></div>

                    <form id="donation-form" method="post" action="../controllers/DonationManagementController.php">
                        <div class="mb-3">
                            <label for="donor_name" class="form-label">Name (optional)</label>
                            <input type="text" class="form-control" id="donor_name" name="donor_name" placeholder="Your full name">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Amount (PHP)</label>
                            <div class="d-flex gap-2 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-amount" data-amount="100">₱100</button>
                                <button type="button" class="btn btn-outline-primary btn-amount" data-amount="250">₱250</button>
                                <button type="button" class="btn btn-outline-primary btn-amount" data-amount="500">₱500</button>
                                <button type="button" class="btn btn-outline-primary btn-amount" data-amount="1000">₱1000</button>
                            </div>
                            <input type="number" min="1" step="1" name="amount" id="amount" class="form-control" placeholder="Enter custom amount (PHP)" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select name="method" id="method" class="form-select" required>
                                <option value="gcash">GCash</option>
                                <option value="bank">Bank Transfer</option>
                            </select>
                        </div>

                        <div class="mb-3" id="payment-details">
                            <div class="alert alert-info small mb-0">Select a payment method to see instructions.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">Donate Now</button>
                            <a href="./contact.php" class="btn btn-outline-secondary">Contact Us</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6>Why donate?</h6>
                    <ul>
                        <li>Provide food and shelter for rescued animals.</li>
                        <li>Cover veterinary and medical expenses.</li>
                        <li>Support spay/neuter and rehoming programs.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-2">
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="mb-2">Payment Methods</h6>
                    <div class="mb-2">
                        <strong>GCash</strong>
                        <div class="small text-muted">Account: 0917-xxx-xxxx<br>Account name: Hope4Pets Foundation</div>
                    </div>
                    <div class="mb-2">
                        <strong>Bank Transfer</strong>
                        <div class="small text-muted">Bank: CIMB BANK<br>Account #: 0123456789<br>Account name: Hope4Pets Foundation</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body small text-muted">
                    <h6 class="mb-2">Receipt & Tax Info</h6>
                    Donations above PHP 1,000 are eligible for a donation receipt. Contact us at
                    <a href="mailto:donations@hope4pets.org">donations@hope4pets.org</a> to request one.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR / Bank Modal -->
<div class="modal fade" id="donationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header position-relative">
        <!-- exit X button placed on the upper-left -->
        <button type="button" class="btn-close position-absolute start-0 ms-3" style="top:0.6rem;" data-bs-dismiss="modal" aria-label="Close"></button>

        <h5 class="modal-title w-100 text-center" id="donationModalLabel">Complete Donation</h5>

        <!-- removed the default right-side close button -->
      </div>
      <div class="modal-body" id="donationModalBody">
        <!-- populated by JS -->
      </div>
      <div class="modal-footer">

      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // preset amount buttons
    document.querySelectorAll('.btn-amount').forEach(function(btn){
        btn.addEventListener('click', function(){
            var amount = this.getAttribute('data-amount');
            document.getElementById('amount').value = amount;
            document.querySelectorAll('.btn-amount').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    var methodSelect = document.getElementById('method');
    var details = document.getElementById('payment-details');
    var form = document.getElementById('donation-form');
    var alertPlaceholder = document.getElementById('donation-alert-placeholder');
    var donationModalEl = document.getElementById('donationModal');
    var donationModal = new bootstrap.Modal(donationModalEl, {});
    var donationModalBody = document.getElementById('donationModalBody');

    // track whether the last modal interaction resulted in a confirmed donation
    var lastConfirmed = false;

    // Helper: parse possibly-non-JSON responses (returns promise -> object)
    function parseJsonResponse(resp) {
        return resp.text().then(function (txt) {
            if (!txt) return { status: 'error', message: 'Empty server response' };
            try {
                return JSON.parse(txt);
            } catch (e) {
                var stripped = txt.replace(/<\/?[^>]+(>|$)/g, '').trim();
                return { status: 'error', message: stripped || 'Invalid server response' };
            }
        });
    }

     // Helper: show alert
     function showAlert(type, message, autoDismissMs) {
         alertPlaceholder.innerHTML = '<div class="alert alert-' + type + ' alert-dismissible" role="alert">' +
             message +
             '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
         if (autoDismissMs) {
             setTimeout(function () {
                 alertPlaceholder.innerHTML = '';
             }, autoDismissMs);
         }
     }
 
    // helper to disable/remove confirmation UI after success
    function finalizeConfirmedUI() {
        // remove any confirm form inside modal body
        var formInModal = donationModalBody.querySelector('form');
        if (formInModal) formInModal.remove();

        // remove any submit buttons in modal body
        donationModalBody.querySelectorAll('button[type="submit"]').forEach(b => b.remove());

        // clear modal content to avoid lingering UI
        donationModalBody.innerHTML = '';

        // mark confirmed so modal hide handler can behave accordingly
        lastConfirmed = true;
    }

    function renderDetails() {
        var method = methodSelect.value;
        var html = '';
        if (method === 'gcash') {
            html = '<div class="alert alert-light small">Send your donation to <strong>0917-xxx-xxxx</strong> (GCash)<br>Account name: Hope4Pets Foundation<br>After clicking Donate Now you will be given a QR code or instructions to complete payment.</div>';
        } else {
            html = '<div class="alert alert-light small">Bank: Example Bank<br>Account #: 0123456789<br>Account name: Hope4Pets Foundation<br>After clicking Donate Now you will be shown account details and a form to confirm transfer.</div>';
        }
        details.innerHTML = html;
    }

    methodSelect.addEventListener('change', renderDetails);
    renderDetails();

    // When the modal is hidden (user closes it), clear modal body and
    // if donation was NOT confirmed, remove any alerts so "nothing should display".
    donationModalEl.addEventListener('hidden.bs.modal', function () {
        donationModalBody.innerHTML = '';
        if (!lastConfirmed) {
            alertPlaceholder.innerHTML = '';
        }
        // reset flag for future operations
        lastConfirmed = false;
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var url = form.getAttribute('action');
        var fd = new FormData(form);

        fetch(url, {
            method: 'POST',
            body: fd,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(parseJsonResponse).then(function(data){
            if (!data) {
                showAlert('danger', 'No response from server.', 4000);
                return;
            }
            if (data.status === 'error') {
                showAlert('danger', data.message || 'An error occurred.', 4000);
                return;
            }

            
            // provider-specific UI
            var pd = data.provider_data || {};
            if (pd.action === 'show_qr') {
                var imgHtml = '';
                if (pd.qr_url) {
                    imgHtml = '<div class="mt-3 text-center">' +
                              '<img src="' + pd.qr_url + '" alt="GCash QR" class="img-fluid" style="max-width:300px; height:auto;" />' +
                              '<div class="mt-2"><a class="btn btn-primary" href="' + pd.qr_url + '" target="_blank" rel="noopener">Open / Download QR</a></div>' +
                              '</div>';
                } else {
                    imgHtml = '<div class="alert alert-warning">QR not available. Please contact admin.</div>';
                }

                var infoHtml = '<div class="mt-3 small text-muted">Account: ' + (pd.account || '') + '<br>' +
                               'Account name: ' + (pd.account_name || '') + '<br>' +
                               'Reference: ' + (pd.transaction_reference || '') + '</div>';

                var txIdVal = (pd.transaction_reference || data.transaction_id || '');
                var formHtml = '<form id="gcash-confirm-form" class="mt-3">' +
                               '<div class="mb-3"><label for="gcash_ref" class="form-label">GCash Reference Number</label>' +
                               '<input type="text" id="gcash_ref" name="payer_reference" class="form-control" placeholder="e.g. 1234567890" required></div>' +
                               '<input type="hidden" name="transaction_id" value="' + txIdVal + '">' +
                               '<div class="text-end"><button type="submit" class="btn btn-success">I have sent the money</button></div>' +
                               '</form>';

                donationModalBody.innerHTML = '<p><strong>' + (pd.provider || 'GCASH').toUpperCase() + '</strong></p>' +
                                              imgHtml + infoHtml + formHtml;
                donationModal.show();

                var gcashForm = document.getElementById('gcash-confirm-form');
                if (gcashForm) {
                    gcashForm.addEventListener('submit', function(ev){
                        ev.preventDefault();
                        var fd = new FormData(gcashForm);

                        fetch('../controllers/DonationManagementController.php?confirm=1', {
                            method: 'POST',
                            body: fd,
                            credentials: 'same-origin',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        }).then(parseJsonResponse).then(function(res){
                            if (res && res.status === 'ok') {
                                // mark confirmed, clean modal UI and show success briefly
                                finalizeConfirmedUI();
                                donationModal.hide();
                                showAlert('success', res.message || 'Donation confirmed. Thank you!', 3000);
                                // reset main form
                                form.reset();
                                document.querySelectorAll('.btn-amount').forEach(b => b.classList.remove('active'));
                            } else {
                                showAlert('danger', (res && res.message) ? res.message : 'Confirmation failed.', 4000);
                            }
                        }).catch(function(){
                            showAlert('danger', 'Confirmation request failed. Please try again.', 4000);
                        });
                    });
                }
            } else if (pd.action === 'show_bank') {
                donationModalBody.innerHTML = '<p><strong>Bank Transfer Details</strong></p>' +
                    '<p class="small mb-1">Bank: ' + pd.bank_name + '<br>Account #: ' + pd.account_number + '<br>Account name: ' + pd.account_name + '</p>' +
                    '<p class="small mb-2">' + (pd.instructions || '') + '</p>' +
                    '<form id="bank-confirm-form">' +
                    '<input type="hidden" name="transaction_id" value="' + (data.transaction_id || '') + '">' +
                    '<div class="mb-3"><label for="payer_name" class="form-label">Your name</label><input id="payer_name" name="payer_name" class="form-control" required></div>' +
                    '<div class="mb-3"><label for="receipt_number" class="form-label">Receipt / Reference Number</label><input id="receipt_number" name="receipt_number" type="text" class="form-control" required></div>' +
                    '<div class="mb-3"><label for="proof" class="form-label">Upload proof (optional)</label><input id="proof" name="proof" type="file" accept="image/*" class="form-control"></div>' +
                    '<div class="text-end"><button type="submit" class="btn btn-success">Confirm Transfer</button></div>' +
                    '</form>';
                donationModal.show();

                var bankFormEl = document.getElementById('bank-confirm-form');
                if (bankFormEl) {
                    bankFormEl.addEventListener('submit', function(ev){
                        ev.preventDefault();
                        var fd = new FormData(bankFormEl);
                        var payerName = fd.get('payer_name') || '';
                        var receiptNumber = fd.get('receipt_number') || '';
                        var confirmFd = new FormData();
                        confirmFd.append('transaction_id', fd.get('transaction_id') || '');
                        // set payer_reference to the receipt number so server will use it as new transaction_id
                        confirmFd.append('payer_reference', receiptNumber);
                        // include donor name so server can set donor_name in DB
                        confirmFd.append('payer_name', payerName);

                        fetch('../controllers/DonationManagementController.php?confirm=1', {
                            method: 'POST',
                            body: confirmFd,
                            credentials: 'same-origin',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        }).then(parseJsonResponse).then(function(res){
                            if (res && res.status === 'ok') {
                                finalizeConfirmedUI();
                                donationModal.hide();
                                showAlert('success', res.message || 'Donation confirmed. Thank you!', 3000);
                                form.reset();
                                document.querySelectorAll('.btn-amount').forEach(b => b.classList.remove('active'));
                            } else {
                                showAlert('danger', (res && res.message) ? res.message : 'Confirmation failed.', 4000);
                            }
                        }).catch(function(){
                            showAlert('danger', 'Confirmation request failed. Please try again.', 4000);
                        });
                    });
                }
             } else {
                 // no special UI, keep showing basic message
             }

            // if donation completed, reset form
            if (data.status === 'completed') {
                form.reset();
                document.querySelectorAll('.btn-amount').forEach(b => b.classList.remove('active'));
            }
        }).catch(function(err){
            showAlert('danger', 'Request failed. Please try again.', 4000);
        });
    });
 });
</script>

<?php include __DIR__ . '/../include/footer.php'; ?>
