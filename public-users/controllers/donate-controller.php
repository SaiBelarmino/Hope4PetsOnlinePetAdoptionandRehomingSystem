<?php
/**
 * Donate Controller
 * 
 * Handles donation processing for shelters.
 * Donations are linked to the logged-in user (donor).
 */

require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

// Require login
SessionManager::requireLogin();

// Handle donation form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = SessionManager::getUserId();
    $user = SessionManager::getUser();
    $shelterId = !empty($_POST['shelter_id']) ? (int)$_POST['shelter_id'] : null;
    $amount = floatval($_POST['amount'] ?? 0);
    $paymentMethod = $_POST['payment_method'] ?? 'gcash';
    $donorName = trim($_POST['donor_name'] ?? $user['full_name']);
    
    $errors = [];
    
    // Validation
    if (empty($shelterId)) {
        $errors[] = 'Please select a shelter to donate to.';
    }
    
    if ($amount <= 0) {
        $errors[] = 'Donation amount must be greater than zero.';
    }
    
    if (!in_array($paymentMethod, ['credit_card', 'paypal', 'gcash', 'paymaya', 'bank_transfer', 'other'])) {
        $errors[] = 'Invalid payment method.';
    }
    
    if (empty($errors)) {
        // Process donation
        $result = DonateController::processDonation($userId, [
            'shelter_id' => $shelterId,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'donor_name' => $donorName
        ]);
        
        if ($result['success']) {
            SessionManager::setFlash('success', 'Thank you for your donation!');
            header('Location: ../views/donation_receipt.php?id=' . $result['donation_id']);
            exit;
        } else {
            SessionManager::setFlash('error', $result['message']);
            header('Location: ../views/donate.php');
            exit;
        }
    } else {
        SessionManager::setFlash('error', implode('<br>', $errors));
        header('Location: ../views/donate.php');
        exit;
    }
}

class DonateController extends BaseController {
    public static function make(int $userId, int $shelterId, float $amount): bool {
        $mysqli = self::db();
        $stmt = $mysqli->prepare("INSERT INTO donations (user_id, shelter_id, amount, created_at) VALUES (?,?,?,NOW())");
        if (!$stmt) return false;
        $stmt->bind_param('iid', $userId, $shelterId, $amount);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
    
    /**
     * Process a donation
     * 
     * @param int $donorId User ID making the donation
     * @param array $data Donation data
     * @return array Result with success status and donation ID
     */
    public static function processDonation(int $donorId, array $data): array {
        $mysqli = self::db();
        
        // Generate unique transaction ID
        $transactionId = 'TXN_' . strtoupper(uniqid()) . '_' . time();
        
        // Insert donation record
        $stmt = $mysqli->prepare(
            "INSERT INTO donations (donor_id, shelter_id, transaction_id, donor_name, amount, payment_method, status, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, 'completed', NOW())"
        );
        
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error. Please try again.'];
        }
        
        $stmt->bind_param(
            'iissds',
            $donorId,
            $data['shelter_id'],
            $transactionId,
            $data['donor_name'],
            $data['amount'],
            $data['payment_method']
        );
        
        $success = $stmt->execute();
        
        if (!$success) {
            $stmt->close();
            return ['success' => false, 'message' => 'Failed to process donation.'];
        }
        
        $donationId = $mysqli->insert_id;
        $stmt->close();
        
        return [
            'success' => true,
            'message' => 'Donation processed successfully!',
            'donation_id' => $donationId,
            'transaction_id' => $transactionId
        ];
    }
    
    /**
     * Get donations by donor ID (user)
     */
    public static function getDonationsByDonorId(int $donorId): array {
        return self::fetchAll(
            "SELECT d.*, s.shelter_name 
             FROM donations d
             LEFT JOIN shelters s ON d.shelter_id = s.id
             WHERE d.donor_id = ?
             ORDER BY d.created_at DESC",
            'i',
            [$donorId]
        );
    }
    
    /**
     * Get donation by ID (with security check)
     */
    public static function getDonationById(int $donationId, int $donorId): ?array {
        return self::fetchOne(
            "SELECT d.*, s.shelter_name, s.address 
             FROM donations d
             LEFT JOIN shelters s ON d.shelter_id = s.id
             WHERE d.id = ? AND d.donor_id = ?
             LIMIT 1",
            'ii',
            [$donationId, $donorId]
        );
    }
    
    /**
     * Get donations received by shelter ID
     */
    public static function getDonationsByShelterId(int $shelterId): array {
        return self::fetchAll(
            "SELECT d.*, u.full_name as donor_full_name 
             FROM donations d
             LEFT JOIN users u ON d.donor_id = u.id
             WHERE d.shelter_id = ?
             ORDER BY d.created_at DESC",
            'i',
            [$shelterId]
        );
    }
}
?>