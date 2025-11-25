<?php
header('Content-Type: application/json');
require_once dirname(__DIR__, 3) . '/admin/controllers/Adoption/donations-controller.php';

try {
    $data = DonationsController::getDonationsByShelter();
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch donation data: ' . $e->getMessage()
    ]);
}
?>