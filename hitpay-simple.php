<?php
// hitpay-simple.php - For testing
session_start();

// Simple test data
$apiKey = 'c76bbbf2b4df6160ac27712172341c72921220a1ceb4944be8aedb91fd6bebfd';
$apiUrl = 'https://api.sandbox.hit-pay.com/v1/payment-requests';

// Test data - similar to sample
$postData = http_build_query([
    'amount' => '599.00',
    'currency' => 'SGD',
    'email' => 'tom@test.com',
    'name' => 'Tom Test',
    'purpose' => 'Test Payment',
    'reference_number' => 'TEST-' . time(),
    'redirect_url' => (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/booking-payment.php',
    'send_email' => 'true',
    'allow_repeated_payments' => 'false'
]);

echo "<h2>Test HitPay Integration</h2>";
echo "<p>Sending request to: $apiUrl</p>";
echo "<pre>POST Data: " . htmlspecialchars($postData) . "</pre>";

// Make the request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-BUSINESS-API-KEY: ' . $apiKey,
    'Content-Type: application/x-www-form-urlencoded',
    'X-Requested-With: XMLHttpRequest'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<h3>Response</h3>";
echo "<p>HTTP Code: $httpCode</p>";
echo "<p>Error: " . ($error ? $error : 'None') . "</p>";
echo "<pre>Response Body: " . htmlspecialchars($response) . "</pre>";

// Try to decode response
$responseData = json_decode($response, true);
if ($responseData) {
    echo "<h3>Parsed Response</h3>";
    echo "<pre>";
    print_r($responseData);
    echo "</pre>";
    
    if (isset($responseData['url'])) {
        echo '<p><a href="' . $responseData['url'] . '" class="btn btn-success">Go to Payment Page</a></p>';
    }
}

echo '<br><a href="booking-checking.php" class="btn btn-primary">Back to Booking</a>';
?>