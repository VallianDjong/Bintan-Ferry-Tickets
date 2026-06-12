<?php
// check-payment.php
session_start();
header('Content-Type: application/json');

// Get payment request ID
$requestId = $_GET['request_id'] ?? $_POST['request_id'] ?? '';

// If no request ID provided, check session
if (empty($requestId) && isset($_SESSION['payment_request_id'])) {
    $requestId = $_SESSION['payment_request_id'];
}

if (empty($requestId)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No payment request ID provided'
    ]);
    exit;
}

// HitPay API Configuration test
 //$apiKey = 'test_a78190e72a07e0a600037ac1f18804b0e60fa8ef7d4eeb986b33ff110ba9c221';
 //$apiUrl = "https://api.sandbox.hit-pay.com/v1/payment-requests/" . $requestId;

// HitPay API Configuration
$apiKey = 'c76bbbf2b4df6160ac27712172341c72921220a1ceb4944be8aedb91fd6bebfd';
$apiUrl = "https://api.hit-pay.com/v1/payment-requests" . $requestId;

// Initialize cURL
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'X-BUSINESS-API-KEY: ' . $apiKey,
        'Content-Type: application/json',
        'Accept: application/json'
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true
]);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Handle response
if ($httpCode !== 200) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to verify payment'
    ]);
    exit;
}

// Parse response
$data = json_decode($response, true);

if (!$data || !isset($data['status'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid response from payment gateway'
    ]);
    exit;
}

// Get payment status
$paymentStatus = strtolower($data['status'] ?? 'pending');

// If payment is completed, update session
if ($paymentStatus === 'completed') {
    $_SESSION['payment_success'] = [
        'payment_id' => $data['id'] ?? $requestId,
        'reference' => $data['reference_number'] ?? '',
        'status' => 'completed',
        'amount' => $data['amount'] ?? 0,
        'currency' => $data['currency'] ?? 'SGD',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Clear the request ID
    unset($_SESSION['payment_request_id']);
}

// Send response
echo json_encode([
    'status' => $paymentStatus,
    'payment_id' => $data['id'] ?? $requestId,
    'reference_number' => $data['reference_number'] ?? ''
]);
?>