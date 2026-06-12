<?php
// booking-payment.php
session_start();

// Check if booking data exists
if (!isset($_SESSION['booking_result'])) {
    header('Location: booking-review.php');
    exit;
}

// Get all session data
$bookingResult = $_SESSION['booking_result'];
$bookingFormData = $_SESSION['booking_form_data'] ?? [];
$searchResults = $_SESSION['search_results'] ?? [];
$searchParams = $_SESSION['search_params'] ?? [];
$selectedDeparture = $_SESSION['selected_departure'] ?? [];
$selectedReturn = $_SESSION['selected_return'] ?? null;

$bookingId = $bookingResult['id'] ?? 'N/A';

// Get trip type from session
$tripType = $searchParams['trip_type'] ?? 'round_trip';
error_log("Trip Type for payment: " . $tripType);

// ============ GET TRIP DETAILS FROM SESSION ============
// Get departure trip details
$departTrips = $searchResults['departTrips'] ?? [];
$selectedDepartureDetails = null;

foreach ($departTrips as $trip) {
    if ($trip['id'] == $selectedDeparture['trip_id']) {
        $selectedDepartureDetails = $trip;
        break;
    }
}

// Get return trip details if exists and not open trip
$selectedReturnDetails = null;
if ($selectedReturn && $tripType !== 'open_trip') {
    $returnTrips = $searchResults['returnTrips'] ?? [];
    foreach ($returnTrips as $trip) {
        if ($trip['id'] == $selectedReturn['trip_id']) {
            $selectedReturnDetails = $trip;
            break;
        }
    }
}

// ============ FEE CALCULATION - HANDLE ALL TRIP TYPES ============

// Get base prices
$departureBasePrice = $selectedDeparture['price'] ?? 0;
$passengers = $selectedDeparture['passengers'] ?? 1;
$currency = $selectedDeparture['currency'] ?? 'SGD';

// Check if price already includes fees
$departureBasePrice = $selectedDepartureDetails['adultFare'] ?? 0; // Use adultFare from trip details
$returnBasePrice = $selectedReturnDetails['adultFare'] ?? 0;       // Use adultFare from trip details

// Recalculate ticket totals
$departureTicketTotal = $departureBasePrice * $passengers;
$returnTicketTotal = $returnBasePrice * $passengers;

// Get departure fees
if (isset($selectedDepartureDetails)) {
    $departureSurcharge = floatval($selectedDepartureDetails['fee1'] ?? 0);
    $departurePdfSg = floatval($selectedDepartureDetails['fee2'] ?? 0);
    $departurePdfBtm = floatval($selectedDepartureDetails['fee3'] ?? 0);
    
    $surchargeName = $selectedDepartureDetails['fee1Name'] ?? 'Surcharge';
    $pdfSgName = $selectedDepartureDetails['fee2Name'] ?? 'PDF - SG';
    $pdfBtmName = $selectedDepartureDetails['fee3Name'] ?? 'PDF - BTM';
} else {
    // Default values if no details found
    $departureSurcharge = 0;
    $departurePdfSg = 0;
    $departurePdfBtm = 0;
    $surchargeName = 'Surcharge';
    $pdfSgName = 'PDF - SG';
    $pdfBtmName = 'PDF - BTM';
}

// Initialize return fees based on trip type
$returnBasePrice = 0;
$returnSurcharge = 0;
$returnPdfSg = 0;
$returnPdfBtm = 0;

// Handle different trip types
if ($tripType === 'round_trip' && $selectedReturn && isset($selectedReturnDetails)) {
    // Regular round trip - use actual return fees
    $returnBasePrice = $selectedReturnDetails['adultFare'] ?? 0;
    $returnSurcharge = floatval($selectedReturnDetails['fee1'] ?? 0);
    $returnPdfSg = floatval($selectedReturnDetails['fee2'] ?? 0);
    $returnPdfBtm = floatval($selectedReturnDetails['fee3'] ?? 0);
    
} elseif ($tripType === 'open_trip') {
    // Open trip - use same fees as departure for return
    $returnBasePrice = $departureBasePrice; // Same base price
    $returnSurcharge = $departureSurcharge; // Same surcharge
    $returnPdfSg = $departurePdfSg;         // Same PDF-SG fee
    $returnPdfBtm = $departurePdfBtm;       // Same PDF-BTM fee
    
} else {
    // One-way trip - no return fees
    $returnBasePrice = 0;
    $returnSurcharge = 0;
    $returnPdfSg = 0;
    $returnPdfBtm = 0;
}

// Calculate totals
$departureTicketTotal = $departureBasePrice * $passengers;
$departureSurchargeTotal = $departureSurcharge * $passengers;
$departurePdfSgTotal = $departurePdfSg * $passengers;
$departurePdfBtmTotal = $departurePdfBtm * $passengers;

$returnTicketTotal = $returnBasePrice * $passengers;
$returnSurchargeTotal = $returnSurcharge * $passengers;
$returnPdfSgTotal = $returnPdfSg * $passengers;
$returnPdfBtmTotal = $returnPdfBtm * $passengers;

// Calculate subtotals
$departureSubtotal = $departureTicketTotal + $departureSurchargeTotal + $departurePdfSgTotal + $departurePdfBtmTotal;
$returnSubtotal = $returnTicketTotal + $returnSurchargeTotal + $returnPdfSgTotal + $returnPdfBtmTotal;

// Grand totals
$subtotal = $departureTicketTotal + $returnTicketTotal;
$totalFees = ($departureSurchargeTotal + $departurePdfSgTotal + $departurePdfBtmTotal + 
              $returnSurchargeTotal + $returnPdfSgTotal + $returnPdfBtmTotal);
$grandTotal = $departureSubtotal + $returnSubtotal;

// ============ PROMO CODE DISCOUNT CALCULATION ============
$promoDiscount = 0;
$promoMessage = '';
$promoCode = '';

// Check if promo is in session
if (isset($_SESSION['promo_discount']) && isset($_SESSION['promo_code'])) {
    $discount = $_SESSION['promo_discount'];
    $promoCode = $_SESSION['promo_code'];
    
    // Check discount type
    if (isset($_SESSION['promo_type'])) {
        if ($_SESSION['promo_type'] === 'percentage') {
            $promoDiscount = $grandTotal * $discount;
            $promoMessage = $_SESSION['promo_message'] ?? $promoCode . ' applied (' . ($discount * 100) . '%)';
        } else {
            // Fixed amount
            $promoDiscount = min($discount, $grandTotal);
            $promoMessage = $_SESSION['promo_message'] ?? $promoCode . ' applied';
        }
    } else {
        // Backward compatibility - assume percentage
        $promoDiscount = $grandTotal * $discount;
        $promoMessage = $promoCode . ' applied (' . ($discount * 100) . '%)';
    }
    
    // Store promo data for display
    $_SESSION['active_promo'] = [
        'code' => $promoCode,
        'discount' => $promoDiscount,
        'message' => $promoMessage
    ];
}

// Apply promo discount to get final amount
$grandTotalAfterDiscount = $grandTotal - $promoDiscount;
if ($grandTotalAfterDiscount < 0) $grandTotalAfterDiscount = 0;

// Use discounted total for payment
$paymentAmount = $grandTotalAfterDiscount;

// real HitPay Configuration
define('HITPAY_API_KEY', 'c76bbbf2b4df6160ac27712172341c72921220a1ceb4944be8aedb91fd6bebfd');
define('HITPAY_SALT', 'rLW8UxWdQ64oWHif8geTeDl2GHo1GXVG1CfyWqCrP98fqdE469mfssHwRlhYBYfj');
define('HITPAY_API_URL', 'https://api.hit-pay.com/v1/payment-requests');

// For testing, use sandbox URL first:
 //define('HITPAY_API_KEY', 'test_a78190e72a07e0a600037ac1f18804b0e60fa8ef7d4eeb986b33ff110ba9c221');
 //define('HITPAY_SALT', '6GsyUkkmpgqewus5w4SbpzIuQLLpSvDmBk3zgMYpzG54QZ6xPkSOKpILdjg7qQuK');
 //define('HITPAY_API_URL', 'https://api.sandbox.hit-pay.com/v1/payment-requests');

// Get customer details from session
$bookingFormData = $_SESSION['booking_form_data'] ?? [];
$contactName = $bookingFormData['full_name'] ?? '';
$contactEmail = $bookingFormData['email'] ?? '';
$contactPhone = $bookingFormData['phone'] ?? '';

// Initialize payment request
$paymentUrl = null;
$paymentRequestId = null;
$error = null;

// Process payment request when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Create payment request to HitPay
        $paymentRequest = createHitPayPaymentRequest([
            'email' => $contactEmail,
            'name' => $contactName,
            'phone' => $contactPhone,
            'amount' => $paymentAmount, // Use discounted amount
            'currency' => $currency,
            'reference_number' => $bookingId,
            'redirect_url' => 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/payment-success.php',
            'webhook_url' => 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/payment-webhook.php',
            'booking_id' => $bookingId
        ]);
        
        if ($paymentRequest && isset($paymentRequest['url'])) {
            $paymentUrl = $paymentRequest['url'];
            $paymentRequestId = $paymentRequest['id'];
            
            // Store payment request ID in session for verification later
            $_SESSION['payment_request_id'] = $paymentRequestId;
            $_SESSION['hitpay_payment_data'] = $paymentRequest;
            
            // Redirect to HitPay payment page
            header('Location: ' . $paymentUrl);
            exit;
        } else {
            $error = 'Failed to create payment request. Please try again.';
        }
    } catch (Exception $e) {
        $error = 'Payment error: ' . $e->getMessage();
    }
}

/**
 * Create payment request with HitPay
 */
function createHitPayPaymentRequest($data) {
    
    $postData = [
        'email' => $data['email'],
        'name' => $data['name'],
        'phone' => $data['phone'],
        'amount' => number_format($data['amount'], 2, '.', ''),
        'currency' => $data['currency'],
        'reference_number' => $data['reference_number'],
        'redirect_url' => $data['redirect_url'],
        'webhook_url' => $data['webhook_url'],
        'purpose' => 'batamferrytickets.com',
        'send_email' => 'true',
        'send_sms' => 'true',
        'allow_repeated_payments' => 'false'
    ];
    
    // Debug: Log the post data (remove in production)
    error_log('HitPay Request Data: ' . print_r($postData, true));
    
    // Initialize cURL
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => HITPAY_API_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_HTTPHEADER => [
            'X-BUSINESS-API-KEY: ' . HITPAY_API_KEY,
            'Content-Type: application/x-www-form-urlencoded',
            'X-Requested-With: XMLHttpRequest',
            'Accept: application/json'
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_VERBOSE => false // Set to true for debugging
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Debug: Log the response
    error_log('HitPay Response: ' . $response);
    error_log('HitPay HTTP Code: ' . $httpCode);
    
    if ($error) {
        throw new Exception('cURL Error: ' . $error);
    }
    
    $result = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON response: ' . json_last_error_msg());
    }
    
    if ($httpCode !== 200 && $httpCode !== 201) {
        $errorMsg = isset($result['message']) ? $result['message'] : 'HTTP Error: ' . $httpCode;
        if (isset($result['errors'])) {
            $errorMsg .= ' - ' . print_r($result['errors'], true);
        }
        throw new Exception($errorMsg);
    }
    
    return $result;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<!-- Google tag (gtag.js) - Combined -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-S8024PF2N0"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  // Google Analytics 4
  gtag('config', 'G-S8024PF2N0');

  // Google Ads
  gtag('config', 'AW-10870335725');
</script>
    <meta charset="UTF-8">
    <title>Batam Ferry Tickets</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="favicon_logo.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .payment-container { max-width: 600px; margin: 50px auto; }
        .payment-card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .payment-header { background: linear-gradient(135deg, #1a3a8a 0%, #df4d45 100%); color: white; padding: 25px; border-radius: 15px 15px 0 0; }
        .payment-body { padding: 30px; background: white; border-radius: 0 0 15px 15px; }
        .amount-display { font-size: 2.5rem; font-weight: 800; color: #df4d45; }
        .btn-pay-now { background: linear-gradient(135deg, #df4d45 0%, #c43b35 100%); color: white; border: none; padding: 15px; font-size: 1.1rem; border-radius: 10px; width: 100%; transition: all 0.3s; }
        .btn-pay-now:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(223, 77, 69, 0.3); }
        .payment-methods { display: flex; justify-content: center; gap: 20px; margin: 20px 0; }
        .payment-method-icon { width: 50px; height: 50px; background: #f8f9fa; border-radius: 10px; display: flex; align-items: center; justify-content: center; border: 1px solid #dee2e6; }
        .alert-payment { border-radius: 10px; border-left: 5px solid #df4d45; }
    </style>
</head>
<body>
    <nav class="navbar navbar-light bg-white mb-4">
    <div class="container d-flex justify-content-between">
        <a class="navbar-brand" href="index.php">
    <img src="horizontal_logo.svg" alt="VTL Travel" style="height: 30px;">
</a>
    </div>
</nav>

    <div class="container payment-container">
        <div class="payment-card">
            <div class="payment-header text-center">
                <h2><i class="bi bi-credit-card-2-front"></i> Secure Payment</h2>
                <p class="mb-0">Complete your ferry booking payment</p>
            </div>
            
            <div class="payment-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-payment">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <div class="text-center mb-4">
                    <p class="text-muted">Booking Reference</p>
                    <h4 class="fw-bold">#<?= htmlspecialchars($bookingId) ?></h4>
                </div>
                
                <div class="text-center mb-4">
    <p class="text-muted">Total Amount to Pay</p>
    <div class="amount-display">
        <?php 
        $currencySymbol = $currency == 'SGD' ? '$' : 'Rp ';
        echo $currencySymbol . number_format($paymentAmount, 2);
        ?>
    </div>
    
    <?php if ($promoDiscount > 0): ?>
    <div class="mt-2">
        <span class="badge bg-success">
            <i class="bi bi-tag"></i> 
            <?= htmlspecialchars($promoCode) ?> Applied
        </span>
        <div class="text-muted small mt-1">
            <s><?= $currencySymbol ?><?= number_format($grandTotal, 2) ?></s>
            <span class="text-success ms-2">Saved: <?= $currencySymbol ?><?= number_format($promoDiscount, 2) ?></span>
        </div>
    </div>
    <?php endif; ?>
</div>
                
                
                <div class="alert alert-light border mb-4">
                    <div class="d-flex">
                        <i class="bi bi-shield-check text-success me-3" style="font-size: 1.5rem;"></i>
                        <div>
                            <h6 class="fw-bold">Secure Payment</h6>
                            <p class="small mb-0">Your payment is secured with HitPay's PCI DSS compliant payment gateway. We do not store your credit card details.</p>
                        </div>
                    </div>
                </div>
                
                <form method="POST" action="">
                    <button type="submit" class="btn btn-pay-now">
                        <i class="bi bi-lock-fill"></i> Proceed to Secure Payment
                    </button>
                </form>
                
                <div class="text-center mt-3">
                    <a href="booking-checking.php" class="text-decoration-none">
                        <i class="bi bi-arrow-left"></i> Back to Booking Review
                    </a>
                </div>
                
                <div class="text-center mt-4">
                    <p class="text-muted small mt-2">Powered by HitPay - Secure Payment Gateway</p>
                </div>
            </div>
        </div>
    </div>
    <script>			var url = 'https://prod-crmb2b.s3.ap-southeast-1.amazonaws.com/widget/64c917df6f3bf83bd6d2b5b3/69954aba97bf000e7bb2baf4.js?v=' + Date.now();			var s = document.createElement('script');			s.type = 'text/javascript';			s.async = true;			s.src = url;			s.onload = function() {				CreateWhatsappChatWidget();			};			var x = document.getElementsByTagName('script')[0];			x.parentNode.insertBefore(s, x);		</script>
</body>
</html>