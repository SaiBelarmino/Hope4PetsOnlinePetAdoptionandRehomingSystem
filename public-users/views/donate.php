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
                        custom one. We accept GCash, Maya, and direct bank transfers.</p>

                    <form id="donation-form" method="post" action="../controllers/donation/process_donation.php">
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
                                <option value="maya">Maya</option>
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
                        <strong>Maya</strong>
                        <div class="small text-muted">Account: 0999-xxx-xxxx<br>Account name: Hope4Pets Foundation</div>
                    </div>
                    <div class="mb-2">
                        <strong>Bank Transfer</strong>
                        <div class="small text-muted">Bank: Example Bank<br>Account #: 0123456789<br>Account name: Hope4Pets Foundation</div>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    // preset amount buttons
    document.querySelectorAll('.btn-amount').forEach(function(btn){
        btn.addEventListener('click', function(){
            var amount = this.getAttribute('data-amount');
            document.getElementById('amount').value = amount;
            // highlight active
            document.querySelectorAll('.btn-amount').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    var methodSelect = document.getElementById('method');
    var details = document.getElementById('payment-details');

    function renderDetails() {
        var method = methodSelect.value;
        var html = '';
        if (method === 'gcash') {
            html = '<div class="alert alert-light small">Send your donation to <strong>0917-xxx-xxxx</strong> (GCash)\n<br>Account name: Hope4Pets Foundation<br>Use your name in the reference and upload proof after donation if requested.</div>';
        } else if (method === 'maya') {
            html = '<div class="alert alert-light small">Send your donation via <strong>Maya</strong> to 0999-xxx-xxxx\n<br>Account name: Hope4Pets Foundation</div>';
        } else {
            html = '<div class="alert alert-light small">Bank: Example Bank<br>Account #: 0123456789<br>Account name: Hope4Pets Foundation<br>Please include your name as reference.</div>';
        }
        details.innerHTML = html;
    }

    methodSelect.addEventListener('change', renderDetails);
    renderDetails();
});
</script>

<?php include __DIR__ . '/../include/footer.php'; ?>
