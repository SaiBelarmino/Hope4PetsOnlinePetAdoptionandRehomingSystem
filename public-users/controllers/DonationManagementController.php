<?php
declare(strict_types=1);

/**
 * PaymentGateway class
 * Handles payment processing and webhook verification with the payment provider.
 */
class PaymentGateway
{
    /**
     * Create a payment request at the real provider.
     * Replace endpoints, payload and auth with real provider docs.
     *
     * Returns array: ['status' => 'completed'|'pending'|'failed', 'message' => '', 'provider_data' => []]
     */
    public static function createPayment(string $method, float $amount, string $txid, string $donor = ''): array
    {
        $apiKey = getenv('PAYMENT_API_KEY') ?: 'replace_with_key';
        $apiUrl = getenv('PAYMENT_API_URL') ?: 'https://api.example-payments.com/v1/payments';

        $payload = [
            'reference'   => $txid,
            'amount'      => round($amount, 2),
            'currency'    => 'PHP',
            'method'      => $method,
            'payer_name'  => $donor,
            'metadata'    => ['source' => 'hope4pets'],
        ];

        $ch = curl_init((string)$apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $resp = curl_exec($ch);
        $err = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err || $code >= 500) {
            return [
                'status' => 'failed',
                'message' => 'Payment provider error. Try again later.',
                'provider_data' => [],
            ];
        }

        $data = json_decode((string)$resp, true);
        if (!is_array($data)) {
            return [
                'status' => 'failed',
                'message' => 'Invalid response from payment provider.',
                'provider_data' => [],
            ];
        }

        // Adapt mapping to your provider's response structure
        if (($data['status'] ?? '') === 'success' || ($data['payment_status'] ?? '') === 'completed') {
            return [
                'status' => 'completed',
                'message' => $data['message'] ?? 'Payment accepted.',
                'provider_data' => $data['data'] ?? $data,
            ];
        }

        if (($data['status'] ?? '') === 'pending' || ($data['payment_status'] ?? '') === 'pending') {
            return [
                'status' => 'pending',
                'message' => $data['message'] ?? 'Payment pending. Complete provider steps.',
                'provider_data' => $data['data'] ?? $data,
            ];
        }

        return [
            'status' => 'failed',
            'message' => $data['message'] ?? 'Payment failed or declined.',
            'provider_data' => $data['data'] ?? $data,
        ];
    }

    /**
     * Verify webhook signature/header. Implement per provider docs.
     * Return true if valid.
     */
    public static function verifyWebhook(array $headers, string $body): bool
    {
        $secret = getenv('PAYMENT_WEBHOOK_SECRET') ?: 'replace_with_secret';

        // Example: provider sends X-Signature header with HMAC SHA256
        $sigHeader = $headers['X-Signature'] ?? $headers['X_SIGNATURE'] ?? $headers['HTTP_X_SIGNATURE'] ?? null;
        if (!$sigHeader) {
            return false;
        }

        $calc = hash_hmac('sha256', $body, $secret);
        return hash_equals($calc, (string)$sigHeader);
    }
}
require_once __DIR__ . '/../../controllers/BaseController.php';


class AdoptController extends BaseController {
    public static function details(int $petId): ?array {
        return self::fetchOne("SELECT id, name, species, breed, age, status, description FROM pets WHERE id=?", 'i', [$petId]);
    }

    public static function request(int $userId, int $petId): bool {
        $mysqli = self::db();
        $stmt = $mysqli->prepare("INSERT INTO adoption_requests (user_id, pet_id, status, created_at) VALUES (?,?, 'pending', NOW())");
        if (!$stmt) return false;
        $stmt->bind_param('ii', $userId, $petId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}

class DonationManagementController extends BaseController {
    /**
     * Save donation payload to `donations` table. Non-fatal; returns bool.
     * Expected payload keys: transaction_id, donor_name, amount, method, status, timestamp
     */
    public static function saveDonation(array $p): bool {
        $mysqli = self::db();
        if (!$mysqli) return false;

        // accept donor_id and shelter_id if provided (nullable)
        $donorId = isset($p['donor_id']) && $p['donor_id'] !== '' ? (int)$p['donor_id'] : null;
        $shelterId = isset($p['shelter_id']) && $p['shelter_id'] !== '' ? (int)$p['shelter_id'] : null;

        $tx = $p['transaction_id'] ?? '';
        $donor = $p['donor_name'] ?? '';
        $amount = isset($p['amount']) ? (float)$p['amount'] : 0.0;
        $method = $p['method'] ?? '';
        $status = $p['status'] ?? 'pending';

        // Map frontend method names to DB enum values
        $methodMap = [
            'bank'  => 'bank_transfer',
            'gcash' => 'gcash',
            'credit_card' => 'credit_card',
            'paypal' => 'paypal',
            'other' => 'other',
        ];
        $dbMethod = $methodMap[strtolower($method)] ?? $method;

        // Insert with donor_id and shelter_id (nullable)
        $stmt = $mysqli->prepare(
            "INSERT INTO donations (donor_id, shelter_id, transaction_id, donor_name, amount, payment_method, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        if (!$stmt) {
            error_log('DonationManagementController::saveDonation prepare error: ' . $mysqli->error);
            return false;
        }

        // bind_param expects variables; null values will be sent as SQL NULL
        $stmt->bind_param('iissdss',
            $donorId,
            $shelterId,
            $tx,
            $donor,
            $amount,
            $dbMethod,
            $status
        );

        $ok = $stmt->execute();
        if (!$ok) {
            error_log('DonationManagementController::saveDonation execute error: ' . $stmt->error);
        }
        $stmt->close();
        return (bool)$ok;
    }

    // optional: alias methods for process_donation.php compatibility
    public static function recordDonation(array $p): bool {
        return self::saveDonation($p);
    }
    public static function create(array $p): bool {
        return self::saveDonation($p);
    }

    /**
     * Handle a POST donation request. This encapsulates previous process_donation.php logic.
     * Call directly: DonationManagementController::processDonationEndpoint();
     * If this file is requested directly via HTTP with a POST, it will auto-run.
     */
    public static function processDonationEndpoint(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        // Clean any accidental output
        if (ob_get_level()) {
            @ob_clean();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed. Use POST.']);
            exit;
        }

        $donor_name = isset($_POST['donor_name']) ? trim((string)$_POST['donor_name']) : '';
        $amount_raw = $_POST['amount'] ?? null;
        $method     = isset($_POST['method']) ? strtolower(trim((string)$_POST['method'])) : '';

        $errors = [];

        // Validate amount
        if ($amount_raw === null || $amount_raw === '') {
            $errors[] = 'Amount is required.';
        } elseif (!is_numeric($amount_raw) || floatval($amount_raw) <= 0) {
            $errors[] = 'Amount must be a positive number.';
        } else {
            $amount = round(floatval($amount_raw), 2);
        }

        // Validate method
        $allowed_methods = ['gcash', 'bank',];
        if (!in_array($method, $allowed_methods, true)) {
            $errors[] = 'Invalid payment method.';
        }

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
            exit;
        }

        // Generate local reference (used to track before donor provides their provider reference)
        try {
            $localRef = strtoupper(bin2hex(random_bytes(6))) . '-' . time();
        } catch (Exception $e) {
            $localRef = strtoupper(uniqid('TX-', true)) . '-' . time();
        }

        $timestamp = date('c');

        // Determine donor_id and shelter_id from session if available
        // Common session shapes: $_SESSION['user_id'] or $_SESSION['user']['id'], shelter id may be in session too
        $donorId = null;
        if (!empty($_SESSION['user_id'])) {
            $donorId = (int)$_SESSION['user_id'];
        } elseif (!empty($_SESSION['user']['id'])) {
            $donorId = (int)$_SESSION['user']['id'];
        }

        $shelterId = null;
        if (!empty($_SESSION['shelter_id'])) {
            $shelterId = (int)$_SESSION['shelter_id'];
        } elseif (!empty($_SESSION['user']['shelter_id'])) {
            $shelterId = (int)$_SESSION['user']['shelter_id'];
        }

        // For offline methods, we create pending donation using localRef.
        $providerResponseData = [];
        $status = 'pending';
        $message = 'Awaiting donor confirmation. Follow the instructions to complete the transfer.';

        // Store last donation in session (use db-friendly method)
        $_SESSION['last_donation'] = [
            'local_ref'       => $localRef,
            // transaction_id will be set to provider reference when donor confirms
            'transaction_id'  => $localRef,
            'status'          => $status,
            'message'         => $message,
            'donor_name'      => $donor_name,
            'amount'          => $amount,
            'method'          => $method,
            'db_method'       => (['bank' => 'bank_transfer','gcash' => 'gcash'][$method] ?? $method),
            'donor_id'        => $donorId,
            'shelter_id'      => $shelterId,
            'timestamp'       => $timestamp,
            'provider_response'=> $providerResponseData,
        ];

        // Previously we persisted a pending donation here. To avoid storing records
        // when the user may abandon the process, do NOT save to DB yet.
        // Persist only when the user confirms (confirm endpoint).

        // Build provider-specific response for frontend using providerResponseData where helpful
        $providerData = [];

        if ($method === 'gcash') {
            $account = '0917-xxx-xxxx';
            $account_name = 'Hope4Pets Foundation';

            // filename (change env var if your filename differs)
            $qrFileName = getenv('GCASH_QR_FILENAME') ?: 'gcash.png';

            // filesystem path to the PNG
            $qrDir = realpath(__DIR__ . '/../../assets/images/qrcodes');
            $fileSystemPath = $qrDir ? $qrDir . DIRECTORY_SEPARATOR . $qrFileName : realpath(__DIR__ . '/../../assets/images/qrcodes/' . $qrFileName);

            // build base URL and project path (works when project is under Apache's DOCUMENT_ROOT)
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseUrl = $scheme . '://' . $host;

            $projectRoot = realpath(__DIR__ . '/../../');
            $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: null;
            $publicPrefix = '';
            if ($docRoot && $projectRoot && strpos($projectRoot, $docRoot) === 0) {
                $publicPrefix = str_replace('\\', '/', substr($projectRoot, strlen($docRoot)));
                $publicPrefix = rtrim($publicPrefix, '/');
            }
            // public URL to image
            $publicQrUrl = $baseUrl . $publicPrefix . '/assets/images/qrcodes/' . rawurlencode($qrFileName);

            $gcashQr = null;
            $qrExists = false;
            if ($fileSystemPath && is_file($fileSystemPath)) {
                // use the PNG from assets
                $gcashQr = $publicQrUrl;
                $qrExists = true;
            } else {
                // fallback: dynamic QR image (Google Charts)
                $payload = "GCASH|{$account}|PHP{$amount}|REF:{$localRef}";
                $gcashQr = 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . rawurlencode($payload);
                $qrExists = false;
            }

            $providerData = [
                'action' => 'show_qr',
                'provider' => 'gcash',
                'account' => $account,
                'account_name' => $account_name,
                'qr_url' => $gcashQr,
                'qr_file_exists' => $qrExists,
                'instructions' => 'Scan the GCash QR. After sending, provide the GCash reference number (transaction reference) so we can mark your donation completed. Use reference: ' . $localRef,
                'transaction_reference' => $localRef,
            ];
        } else {
            $providerData = [
                'action' => 'show_bank',
                'provider' => 'bank',
                'bank_name' => 'Example Bank',
                'account_number' => '0123456789',
                'account_name' => 'Hope4Pets Foundation',
                'instructions' => 'Transfer the amount to the account above. Use the reference provided. Upload proof if requested.',
                'transaction_reference' => $localRef,
            ];
        }

        // Return JSON response
        http_response_code(200);
        echo json_encode([
            'transaction_id' => $localRef,
            'status'         => $status,
            'message'        => $message,
            'provider'       => $method,
            'provider_data'  => $providerData,
        ]);
        exit;
    }

    /**
     * Webhook endpoint to receive provider callbacks and update donation status.
     * Call via POST to DonationManagementController.php?webhook=1
     */
    public static function donationWebhookEndpoint(): void {
        // Read raw body and headers
        $body = file_get_contents('php://input');
        $headers = [];
        foreach ($_SERVER as $k => $v) {
            if (strpos($k, 'HTTP_') === 0) {
                $headers[str_replace('HTTP_', '', $k)] = $v;
            }
        }

        // Verify signature (implement provider-specific logic in PaymentGateway::verifyWebhook)
        if (!PaymentGateway::verifyWebhook($headers, $body)) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
            exit;
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid payload']);
            exit;
        }

        // Example: provider sends { reference: txid, status: 'completed'|'failed'|'pending' }
        $txid = $data['reference'] ?? $data['transaction_id'] ?? null;
        $status = $data['status'] ?? null;
        $providerData = $data;

        if (!$txid || !$status) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing fields']);
            exit;
        }

        // Update DB record
        self::updateDonationStatus($txid, $status, $providerData);

        http_response_code(200);
        echo json_encode(['status' => 'ok']);
        exit;
    }

    /**
     * Endpoint to confirm a donation after user clicks "Done" (or enters reference).
     * Expects POST with transaction_id and optional payer_reference.
     * Only allows confirming the transaction that is stored in session->last_donation.
     */
    public static function confirmDonationEndpoint(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed. Use POST.']);
            exit;
        }

        $tx = isset($_POST['transaction_id']) ? trim((string)$_POST['transaction_id']) : '';
        $payerRef = isset($_POST['payer_reference']) ? trim((string)$_POST['payer_reference']) : '';
        // read payer_name from the bank confirm form so we can save it as donor_name
        $payerName = isset($_POST['payer_name']) ? trim((string)$_POST['payer_name']) : '';

        if (!$tx) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing transaction id.']);
            exit;
        }

        // Verify transaction matches last donation in session to avoid arbitrary updates
        $sessionLast = $_SESSION['last_donation'] ?? null;
        if (!$sessionLast || ($sessionLast['transaction_id'] ?? '') !== $tx && ($sessionLast['local_ref'] ?? '') !== $tx) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Transaction not found in session or mismatch.']);
            exit;
        }

        // If donor provided payer reference (e.g. receipt number), we'll update the stored transaction_id to that value
        $newTxId = $payerRef !== '' ? $payerRef : null;

        // Prepare provider data update (store payer reference for receipts)
        $providerData = $sessionLast['provider_response'] ?? [];
        if ($payerRef !== '') {
            $providerData['payer_reference'] = $payerRef;
        }
        if ($payerName !== '') {
            $providerData['payer_name'] = $payerName;
        }
        $providerData['confirmed_by_user'] = true;
        $providerData['confirmed_at'] = date('c');

        // Update DB record: set status completed and replace transaction_id with payerRef if provided.
        // Also update donor_name if payer_name provided (so name becomes donor_name in DB).
        // If a record was previously stored (legacy behavior) update it; otherwise create it now.
        $mysqli = self::db();
        $existing = false;
        if ($mysqli) {
            $chk = $mysqli->prepare("SELECT id FROM donations WHERE transaction_id = ?");
            if ($chk) {
                $chk->bind_param('s', $sessionLast['transaction_id']);
                $chk->execute();
                $chk->store_result();
                $existing = $chk->num_rows > 0;
                $chk->close();
            }
        }

        if ($existing) {
            $ok = self::updateDonationStatus($sessionLast['transaction_id'], 'completed', $providerData, $newTxId, $payerName);
        } else {
            // create a new donation record now that the user confirmed
            $payload = [
                'transaction_id' => $newTxId !== null ? $newTxId : $sessionLast['transaction_id'],
                'donor_name'     => $payerName !== '' ? $payerName : ($sessionLast['donor_name'] ?? ''),
                'amount'         => $sessionLast['amount'] ?? 0,
                'method'         => $sessionLast['db_method'] ?? ($sessionLast['method'] ?? 'other'),
                'status'         => 'completed',
                'donor_id'       => $sessionLast['donor_id'] ?? null,
                'shelter_id'     => $sessionLast['shelter_id'] ?? null,
            ];
            $ok = self::saveDonation($payload);
        }
         if ($ok) {
             // update session copy as well
             $_SESSION['last_donation']['status'] = 'completed';
             if ($newTxId) {
                 $_SESSION['last_donation']['transaction_id'] = $newTxId;
             }
             if ($payerName) {
                 $_SESSION['last_donation']['donor_name'] = $payerName;
             }
             $_SESSION['last_donation']['provider_response'] = $providerData;

             http_response_code(200);
             echo json_encode(['status' => 'ok', 'message' => 'Donation confirmed and marked completed.']);
             exit;
         } else {
             http_response_code(500);
             echo json_encode(['status' => 'error', 'message' => 'Failed to update donation.']);
             exit;
         }
    }

    private static function updateDonationStatus(string $txid, string $status, array $providerData = [], ?string $newTransactionId = null, ?string $donorName = null): bool {
        $mysqli = self::db();
        if (!$mysqli) return false;

        // Build SQL depending on which fields need updating
        if ($newTransactionId !== null && $newTransactionId !== '' && $donorName !== null && $donorName !== '') {
            $stmt = $mysqli->prepare("UPDATE donations SET transaction_id = ?, status = ?, donor_name = ? WHERE transaction_id = ?");
            if (!$stmt) return false;
            $stmt->bind_param('ssss', $newTransactionId, $status, $donorName, $txid);
        } elseif ($newTransactionId !== null && $newTransactionId !== '') {
            $stmt = $mysqli->prepare("UPDATE donations SET transaction_id = ?, status = ? WHERE transaction_id = ?");
            if (!$stmt) return false;
            $stmt->bind_param('sss', $newTransactionId, $status, $txid);
        } elseif ($donorName !== null && $donorName !== '') {
            $stmt = $mysqli->prepare("UPDATE donations SET donor_name = ?, status = ? WHERE transaction_id = ?");
            if (!$stmt) return false;
            $stmt->bind_param('sss', $donorName, $status, $txid);
        } else {
            // Only update status
            $stmt = $mysqli->prepare("UPDATE donations SET status = ? WHERE transaction_id = ?");
            if (!$stmt) return false;
            $stmt->bind_param('ss', $status, $txid);
        }

        $ok = $stmt->execute();
        if (!$ok) {
            error_log('DonationManagementController::updateDonationStatus execute error: ' . $stmt->error);
        }
        $stmt->close();
        return (bool)$ok;
    }

    // ...existing code...
}

// Route direct requests: when file is called directly, support webhook/confirm routes
if (php_sapi_name() !== 'cli' && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_GET['webhook'])) {
            DonationManagementController::donationWebhookEndpoint();
        } elseif (isset($_GET['confirm'])) {
            DonationManagementController::confirmDonationEndpoint();
        } else {
            DonationManagementController::processDonationEndpoint();
        }
    }
}