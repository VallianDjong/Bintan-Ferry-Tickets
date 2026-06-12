<?php
session_start();
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$amount = $input['amount'] ?? 0;
$currency = $input['currency'] ?? 'SGD';
$email = $input['email'] ?? '';
$name = $input['name'] ?? '';
$phone = $input['phone'] ?? '';
$purpose = $input['purpose'] ?? 'Ferry Ticket Booking';
$bookingId = $input['booking_id'] ?? '';

// HitPay API credentials
$apiKey = "test_a78190e72a07e0a600037ac1f18804b0e60fa8ef7d4eeb986b33ff110ba9c221";
$url = "https://api.sandbox.hit-pay.com/v1/payment-requests";

// Create payment request - include ALL payment methods
$data = [
    "amount" => number_format($amount, 2, '.', ''),
    "currency" => strtolower($currency),
    "payment_methods" => ["paynow_online", "card"], // This enables cards, PayNow, Apple Pay, Google Pay
    "name" => $name,
    "email" => $email,
    "phone" => $phone,
    "reference_number" => "BOOK-" . $bookingId . "-" . time(),
    "purpose" => $purpose,
    "redirect_url" => 'https://bintan.desaruteambuilding.com/payment-success.php',
    "webhook" => 'https://bintan.desaruteambuilding.com/payment-webhook.php',
    "send_email" => true,
    "send_sms" => true,
    "allow_repeated_payments" => false
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "X-BUSINESS-API-KEY: $apiKey",
    "Content-Type: application/json",
    "X-Requested-With: XMLHttpRequest"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Log for debugging
error_log("HitPay API Response HTTP Code: " . $httpCode);
error_log("HitPay API Response Body: " . $response);

if ($curlError) {
    error_log("HitPay CURL Error: " . $curlError);
    echo json_encode([
        'success' => false,
        'message' => 'CURL Error: ' . $curlError
    ]);
    exit;
}

if ($httpCode === 200 || $httpCode === 201) {
    $result = json_decode($response, true);
    
    // Debug: Log the full response
    error_log("HitPay Response Decoded: " . print_r($result, true));
    
    // The checkout URL is in the 'url' field, not 'checkout_url'
    if (isset($result['url']) && !empty($result['url'])) {
        // Store payment info in session
        $_SESSION['hitpay_payment_id'] = $result['id'] ?? null;
        $_SESSION['hitpay_checkout_url'] = $result['url'];
        
        echo json_encode([
            'success' => true,
            'payment_request_id' => $result['id'] ?? null,
            'checkout_url' => $result['url'],  // Using 'url' field from API response
            'message' => 'Payment created successfully'
        ]);
    } elseif (isset($result['id']) && isset($result['links']['checkout'])) {
        // Fallback for alternative response structure
        $checkoutUrl = $result['links']['checkout'] ?? null;
        
        if ($checkoutUrl) {
            $_SESSION['hitpay_payment_id'] = $result['id'];
            $_SESSION['hitpay_checkout_url'] = $checkoutUrl;
            
            echo json_encode([
                'success' => true,
                'payment_request_id' => $result['id'],
                'checkout_url' => $checkoutUrl,
                'message' => 'Payment created successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No checkout URL in response. Response: ' . json_encode($result)
            ]);
        }
    } else {
        // Log the actual response for debugging
        error_log("HitPay response missing url field. Full response: " . print_r($result, true));
        
        echo json_encode([
            'success' => false,
            'message' => 'Invalid API response - missing url field. Please check HitPay dashboard configuration.',
            'debug_response' => $result
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create payment: HTTP ' . $httpCode,
        'response' => $response
    ]);
}
?>