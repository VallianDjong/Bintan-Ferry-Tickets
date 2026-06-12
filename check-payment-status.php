<?php
// check-payment-status.php
session_start();
header('Content-Type: application/json');

// Get parameters
$paymentId = $_GET['payment_id'] ?? '';
$bookingId = $_GET['booking_id'] ?? '';

if (empty($paymentId) || empty($bookingId)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit;
}

// Check if payment is already marked as completed in session
if (isset($_SESSION['payment_completed']) && $_SESSION['payment_completed'] === true) {
    echo json_encode(['status' => 'completed']);
    exit;
}

// If you have a database, check there
// For now, we'll check the HitPay API
$apiKey = "c76bbbf2b4df6160ac27712172341c72921220a1ceb4944be8aedb91fd6bebfd";
$url = "https://api.hit-pay.com/v1/payment-requests/" . urlencode($paymentId);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "X-BUSINESS-API-KEY: $apiKey",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $paymentData = json_decode($response, true);
    
    // Check payment status
    // Status can be: 'pending', 'completed', 'failed'
    $status = $paymentData['status'] ?? 'pending';
    
    if ($status === 'completed') {
        $_SESSION['payment_completed'] = true;
        $_SESSION['payment_details'] = $paymentData;
        echo json_encode(['status' => 'completed']);
    } else {
        echo json_encode(['status' => $status]);
    }
} else {
    echo json_encode(['status' => 'pending', 'message' => 'Could not verify payment']);
}
?>