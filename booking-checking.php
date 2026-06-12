<?php
// booking-checking.php - FIXED VERSION

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Start session FIRST - this is critical
session_start();

// Log for debugging
error_log("=== booking-checking.php STARTED ===");
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Session ID: " . session_id());

// Check if this is a form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST request received");
    
    // Store POST data in session immediately
    $_SESSION['booking_form_data'] = $_POST;

    // ============ PERSIST ADD-ON ROUTE DATA FROM add-on.php ============
// These were set by add-on.php — just make sure they survive into payment-success.php
// Only overwrite if not already set (prevent wiping on page refresh)

if (!isset($_SESSION['addon_dep_route']) && isset($selectedDepartureDetails)) {
    $_SESSION['addon_dep_route'] = $selectedDepartureDetails['RouteCode'] ?? '';
    $_SESSION['addon_dep_date']  = $selectedDepartureDetails['DepartureDate'] ?? '';
    $_SESSION['addon_dep_time']  = $selectedDepartureDetails['DepartureTime'] ?? '';
}

if (!isset($_SESSION['addon_ret_route']) && $selectedReturnDetails) {
    $retPartsForAddon        = explode('|', $searchParams['return_route'] ?? '');
    $_SESSION['addon_ret_route'] = $retPartsForAddon[0] ?? '';
    $_SESSION['addon_ret_date']  = $searchParams['return_date'] 
                                ?? ($selectedReturnDetails['ReturnDate'] ?? '');
    $_SESSION['addon_ret_time']  = $selectedReturnDetails['ReturnTime'] ?? '';
}

error_log("Addon dep session: route=" . ($_SESSION['addon_dep_route'] ?? 'none') 
    . " date=" . ($_SESSION['addon_dep_date'] ?? 'none') 
    . " time=" . ($_SESSION['addon_dep_time'] ?? 'none'));
error_log("Addon ret session: route=" . ($_SESSION['addon_ret_route'] ?? 'none') 
    . " date=" . ($_SESSION['addon_ret_date'] ?? 'none') 
    . " time=" . ($_SESSION['addon_ret_time'] ?? 'none'));
    
    // Debug log
    error_log("POST Data stored in session: " . print_r($_POST, true));
    error_log("Current Session: " . print_r($_SESSION, true));
    
    // Validate we have the required session data
    // Update the validation section
// Validate we have the required session data
if (!isset($_SESSION['selected_departure'])) {
    error_log("ERROR: No departure selected in session");
    header('Location: add-on.php?error=no_departure');
    exit;
}

// For round trips (not open trips), check if return is also selected
$tripType = $_SESSION['search_params']['trip_type'] ?? 'round_trip';
$isRoundTrip = isset($_SESSION['search_results']['isRoundTrip']) && $_SESSION['search_results']['isRoundTrip'];

if ($tripType === 'round_trip' && !isset($_SESSION['selected_return'])) {
    error_log("ERROR: Round trip selected but no return trip");
    header('Location: add-on.php?error=no_return');
    exit;
}

// For open trips, we don't need a selected return
if ($tripType === 'open_trip') {
    error_log("Processing open trip - return not required");
    // Open trips don't need selected_return in session
}
    
    // Process the booking data
    $bookingData = processBookingData();
    
    if (!$bookingData) {
        error_log("ERROR: Failed to process booking data");
        header('Location: add-on.php?error=processing_failed');
        exit;
    }
    
    
} elseif (!isset($_SESSION['booking_result'])) {
    //No booking result and not a POST request
    error_log("No booking result - redirecting to add-on.php");
    header('Location: add-on.php');
    exit;
} 

// If we reach here, we have a booking result to display
$bookingFormData = $_SESSION['booking_form_data'] ?? [];
$searchResults = $_SESSION['search_results'] ?? [];
$searchParams = $_SESSION['search_params'] ?? [];

// Continue with your existing display logic...
// ============ GET SELECTED TRIPS ============
$selectedDeparture = $_SESSION['selected_departure'] ?? [];
$selectedReturn    = $_SESSION['selected_return'] ?? null;
$searchResults     = $_SESSION['search_results'] ?? [];
$searchParams      = $_SESSION['search_params'] ?? [];
$tripType          = $searchParams['trip_type'] ?? 'round_trip';

$departTripsList = $searchResults['DepartTrips'] ?? [];

// Match by composite key — same as add-on.php
$selectedDepartureDetails = null;
foreach ($departTripsList as $trip) {
    $compositeKey = ($trip['RouteCode'] ?? '') . '_' . ($trip['DepartureTime'] ?? '');
    if ($compositeKey == ($selectedDeparture['trip_id'] ?? '')) {
        $selectedDepartureDetails = $trip;
        break;
    }
}

// Return is embedded in DepartTrips, matched by ReturnTime
$selectedReturnDetails = null;
if ($selectedReturn) {
    foreach ($departTripsList as $trip) {
        $compositeKey = ($trip['RouteCode'] ?? '') . '_' . ($trip['ReturnTime'] ?? '');
        if ($compositeKey == ($selectedReturn['trip_id'] ?? '')) {
            $selectedReturnDetails = $trip;
            break;
        }
    }
}

// Parse route names from search params
$depParts = explode('|', $searchParams['departure_route'] ?? '');
$retParts = explode('|', $searchParams['return_route'] ?? '');

// Display variables — Koobysae field names
$departOperator    = $selectedDepartureDetails['ShipCode'] ?? 'Batam Fast';
$departOrigin      = $depParts[1] ?? 'Unknown';
$departDestination = $depParts[2] ?? 'Unknown';
$departDate        = $selectedDepartureDetails['DepartureDate'] ?? '';
$departTime        = $selectedDepartureDetails['DepartureTime'] ?? '';
$departTimeRegion  = 'SGT';

$returnOrigin      = '';
$returnDestination = '';
$returnDate        = '';
$returnTime        = '';
$returnTimeRegion  = 'WIB';
$returnOperator    = '';

// booking-checking.php & add-on.php — FIXED
if ($selectedReturnDetails) {
    $returnOperator    = $selectedReturnDetails['ShipCode'] ?? 'Batam Fast';
    $returnOrigin      = $retParts[1] ?? 'Unknown';
    $returnDestination = $retParts[2] ?? 'Unknown';
    $returnDate        = $searchParams['return_date']                   // user's actual selection
                         ?? $selectedReturn['departure_date']           // fallback: stored in session
                         ?? ($selectedReturnDetails['ReturnDate'] ?? ''); // last resort: API field
    $returnTime        = $selectedReturnDetails['ReturnTime'] ?? '';
    $returnTimeRegion  = 'WIB';
}

// Helper functions
function formatDateDisplay($dateStr) {
    if (empty($dateStr)) return 'Not set';
    return date('j M Y', strtotime($dateStr));
}
function formatTimeDisplay($timeStr) {
    if (empty($timeStr)) return '';
    return date('H:i', strtotime($timeStr));
}
function formatDateForDisplay($dateStr) {
    if (empty($dateStr)) return '';
    return date('d M Y', strtotime($dateStr));
}

// Price variables — Koobysae field names
$passengers      = $selectedDeparture['passengers'] ?? 1;
$currency        = $selectedDeparture['currency'] ?? 'SGD';
$currencySymbol  = $currency == 'SGD' ? 'S$' : 'Rp ';

$adultFare       = floatval($selectedDepartureDetails['AdultPrice'] ?? 0);
$fee1            = floatval($selectedDepartureDetails['Surcharge'] ?? 0);
$fee2            = 0;
$fee3            = 0;

$surchargeName   = 'Surcharge';
$pdfSgName       = 'Departure Fee';
$pdfBtmName      = 'Return Fee';

$adultQty  = (int)($searchParams['adultQty']  ?? $passengers);
$childQty  = (int)($searchParams['childQty']  ?? 0);
$infantQty = (int)($searchParams['infantQty'] ?? 0);

$departureBasePrice      = $adultFare;
$departureSurcharge      = $fee1;
$departurePdfSg          = $fee2;
$departurePdfBtm         = $fee3;
$depChildFare            = floatval($selectedDepartureDetails['ChildPrice'] ?? $adultFare);
$departureTicketTotal    = (($departureBasePrice + 6) * $adultQty) + (($depChildFare + 6) * $childQty);
$departureSurchargeTotal = $departureSurcharge * $passengers;
$departurePdfSgTotal     = $departurePdfSg * $passengers;
$departurePdfBtmTotal    = $departurePdfBtm * $passengers;
$departureSubtotal       = $departureTicketTotal + $departureSurchargeTotal + $departurePdfSgTotal + $departurePdfBtmTotal;

$returnBasePrice      = 0;
$returnSurcharge      = 0;
$returnPdfSg          = 0;
$returnPdfBtm         = 0;
$returnTicketTotal    = 0;
$returnSurchargeTotal = 0;
$returnPdfSgTotal     = 0;
$returnPdfBtmTotal    = 0;
$returnSubtotal       = 0;

if ($tripType === 'round_trip' && $selectedReturn && $selectedReturnDetails) {
    $returnBasePrice      = floatval($selectedReturnDetails['AdultPrice'] ?? 0);
    $returnSurcharge      = floatval($selectedReturnDetails['Surcharge'] ?? 0);
    $retChildFare         = floatval($selectedReturnDetails['ChildPrice'] ?? $returnBasePrice);
    $returnTicketTotal    = (($returnBasePrice + 6) * $adultQty) + (($retChildFare + 6) * $childQty);
    $returnSurchargeTotal = $returnSurcharge * $passengers;
    $returnSubtotal       = $returnTicketTotal + $returnSurchargeTotal;
} elseif ($tripType === 'open_trip') {
    $returnBasePrice      = $adultFare;
    $returnSurcharge      = $fee1;
    $retChildFare         = floatval($selectedDepartureDetails['ChildPrice'] ?? $adultFare);
    $returnTicketTotal    = (($returnBasePrice + 6) * $adultQty) + (($retChildFare + 6) * $childQty);
    $returnSurchargeTotal = $returnSurcharge * $passengers;
    $returnSubtotal       = $returnTicketTotal + $returnSurchargeTotal;
}

$subtotal    = ($departureTicketTotal + $returnTicketTotal) / 2;
$totalFees   = $departureSurchargeTotal + $departurePdfSgTotal + $departurePdfBtmTotal
             + $returnSurchargeTotal + $returnPdfSgTotal + $returnPdfBtmTotal;
$grandTotal  = $departureSubtotal;

// ============ PROMO CODE DISCOUNT CALCULATION ============
$promoDiscount = 0;
$promoMessage = '';

if (isset($_SESSION['promo_discount']) && isset($_SESSION['promo_code'])) {
    $discount = $_SESSION['promo_discount'];
    $promoCode = $_SESSION['promo_code'];
    
    // Check discount type
    if (isset($_SESSION['promo_type'])) {
        // From new validatePromoCode function
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
}

// Apply promo discount
$grandTotalAfterDiscount = $grandTotal - $promoDiscount;
if ($grandTotalAfterDiscount < 0) $grandTotalAfterDiscount = 0;

// Store the discounted total in session for payment-success.php to use
$_SESSION['grand_total_after_discount'] = $grandTotalAfterDiscount;
error_log("Stored grand_total_after_discount in session: $grandTotalAfterDiscount");

// Clear promo messages after display
$showPromoSuccess = $_SESSION['promo_success'] ?? '';
$showPromoError = $_SESSION['promo_error'] ?? '';

$bookingId = $bookingResult['id'] ?? 'BIN';

// Contact and passenger details
$bookingFormData = $_SESSION['booking_form_data'] ?? [];
$contactName     = $bookingFormData['full_name'] ?? 'Guest User';
$contactEmail    = $bookingFormData['email'] ?? '';
$contactPhone    = $bookingFormData['phone'] ?? '';


$passengerDetails = [];
for ($i = 1; $i <= $passengers; $i++) {
    $firstName = trim($bookingFormData["first_name_{$i}"] ?? '');
    $lastName  = trim($bookingFormData["last_name_{$i}"] ?? '');
    $passengerDetails[] = [
        'name'                 => trim("$firstName $lastName"),
        'passportNo'           => $bookingFormData["passportNo_{$i}"] ?? '',
        'nationality'          => $bookingFormData["nationalityId_{$i}"] ?? '',
        'dob'                  => sprintf('%s-%s-%s',
                                    $bookingFormData["birthDate_year_{$i}"] ?? '',
                                    $bookingFormData["birthDate_month_{$i}"] ?? '',
                                    $bookingFormData["birthDate_day_{$i}"] ?? ''),
        'gender'               => $bookingFormData["gender_{$i}"] ?? 'male',
        'passportExpiryDate'   => sprintf('%s-%s-%s',
                                    $bookingFormData["passportExpiryDate_year_{$i}"] ?? '',
                                    $bookingFormData["passportExpiryDate_month_{$i}"] ?? '',
                                    $bookingFormData["passportExpiryDate_day_{$i}"] ?? ''),
        'passportCountryIssued' => $bookingFormData["placeOfBirth_{$i}"] ?? '',
        'passenger_type'       => $bookingFormData["passenger_type_{$i}"] ?? 'adult',
    ];
}
// ============ END OF ADDED SECTION ============

// ============ READ ADD-ON DATA FROM SESSION ============
$bookingFormData = $_SESSION['booking_form_data'] ?? [];

$flexiTripValue  = (int)($bookingFormData['flexi_trip'] ?? 0);
$addonCodesRaw   = $bookingFormData['addon_codes'] ?? '';
$addonTotalValue = floatval($bookingFormData['addon_total'] ?? 0);
$addonOwnersRaw  = $bookingFormData['addon_owners'] ?? '{}';

$addonCodes  = array_filter(explode(',', $addonCodesRaw));
$addonOwners = json_decode($addonOwnersRaw, true) ?? [];
$bintanTourSelected = (int)($bookingFormData['bintan_tour_selected'] ?? 0);
$bintanTourPax      = (int)($bookingFormData['bintan_tour_pax']      ?? 0);
$bintanTourTotal    = $bintanTourSelected ? 35 * ($bintanTourPax ?: $passengers) : 0;

// Store in session for persistence across page refreshes
$_SESSION['flexi_trip_selected'] = $flexiTripValue;
$_SESSION['addon_codes']         = $addonCodes;
$_SESSION['addon_total']         = $addonTotalValue;
$_SESSION['addon_owners']        = $addonOwners;

// Friendly names for addon codes
$addonNameMap = [
    'BIC' => 'Bicycle Add-on',
    'SFB' => 'Surfboard Add-on',
];

// ============ FUNCTIONS ============

// Function to process booking data - UPDATED TO MATCH API REQUIREMENTS
function processBookingData() {
    $postData = $_SESSION['booking_form_data'] ?? [];
    
    if (empty($postData)) {
        error_log("ERROR: No form data in session");
        return null;
    }
    
    $searchResults    = $_SESSION['search_results'] ?? [];
    $selectedDeparture = $_SESSION['selected_departure'] ?? [];
    $selectedReturn   = $_SESSION['selected_return'] ?? null;
    $searchParams     = $_SESSION['search_params'] ?? [];
    $tripType         = $searchParams['trip_type'] ?? 'round_trip';
    
    // FIX 1: Use correct session key (capital D)
    $departTripsList = $searchResults['DepartTrips'] ?? [];
    
    // FIX 2: Match by composite key (RouteCode_DepartureTime)
    $selectedDepartureDetails = null;
    $selectedReturnDetails    = null;
    $selectedDepartureForReturn = null;

    foreach ($departTripsList as $trip) {
        $compositeKey = ($trip['RouteCode'] ?? '') . '_' . ($trip['DepartureTime'] ?? '');
        if ($compositeKey === ($selectedDeparture['trip_id'] ?? '')) {
            $selectedDepartureDetails = $trip;
        }
        if ($selectedReturn) {
            $returnKey = ($trip['RouteCode'] ?? '') . '_' . ($trip['ReturnTime'] ?? '');
            if ($returnKey === ($selectedReturn['trip_id'] ?? '')) {
                $selectedDepartureForReturn = $trip;
            }
        }
    }
    
    if (!$selectedDepartureDetails) {
        error_log("ERROR: Could not find departure trip. trip_id=" . ($selectedDeparture['trip_id'] ?? 'none'));
        error_log("Available trips: " . json_encode(array_map(fn($t) => ($t['RouteCode']??'').'_'.($t['DepartureTime']??''), $departTripsList)));
        return null;
    }
    
    $adultQty  = (int)($searchParams['adultQty'] ?? 1);
    $childQty  = (int)($searchParams['childQty'] ?? 0);
    $infantQty = (int)($searchParams['infantQty'] ?? 0);
    $passengers = $adultQty + $childQty + $infantQty;

    // Trip type flags
    $isRoundTrip = 0;
    $isReturnOpen = 0;
    if ($tripType === 'round_trip')  { $isRoundTrip = 1; $isReturnOpen = 0; }
    if ($tripType === 'open_trip')   { $isRoundTrip = 1; $isReturnOpen = 1; }
    
    $bookingData = [
        'Username'               => 'vtapi',
        'Password'               => 'Vtl123456',
        'RouteCode'              => $selectedDepartureDetails['RouteCode'] ?? '',
        'DepartureDate'          => $selectedDepartureDetails['DepartureDate'] ?? '',
        'DepartureTime'          => $selectedDepartureDetails['DepartureTime'] ?? '',
        'JourneyType'            => $isRoundTrip,
        'IsReturnOpen'           => $isReturnOpen,
        'AdultQty'               => $adultQty,
        'ChildQty'               => $childQty,
        'InfantQty'              => $infantQty,
        'SeatCategoryName'       => $searchParams['seat_class'] ?? 'Economy',
        'ContactEmail'           => $postData['email'] ?? '',
        'ContactMobile'          => $postData['phone'] ?? '',
        'ContactName'            => $postData['full_name'] ?? '',
    ];
    
    // Add return data for round trips
    // processBookingData() — FIXED
if ($isRoundTrip && !$isReturnOpen) {
    $bookingData['ReturnDate'] = $searchParams['return_date']                          // correct
                                 ?? ($selectedDepartureForReturn['ReturnDate'] ?? ''); // fallback
    $bookingData['ReturnTime'] = $selectedDepartureForReturn['ReturnTime'] ?? '';
}
    
    // FIX 3: Build passenger array using correct field names from booking-review.php
    $bookingData['Paxs'] = [];
    for ($i = 1; $i <= $passengers; $i++) {
        $firstName = trim($postData["first_name_{$i}"] ?? '');
        $lastName  = trim($postData["last_name_{$i}"] ?? '');
        $fullName  = strtoupper(trim("$firstName $lastName"));
        
        $birthDate = sprintf('%04d-%02d-%02d',
            $postData["birthDate_year_{$i}"]  ?? '2000',
            $postData["birthDate_month_{$i}"] ?? '01',
            $postData["birthDate_day_{$i}"]   ?? '01'
        );
        $expiryDate = sprintf('%04d-%02d-%02d',
            $postData["passportExpiryDate_year_{$i}"]  ?? (date('Y') + 5),
            $postData["passportExpiryDate_month_{$i}"] ?? '01',
            $postData["passportExpiryDate_day_{$i}"]   ?? '01'
        );
        
        $genderInput = strtolower($postData["gender_{$i}"] ?? 'male');
        $gender = ($genderInput === 'female') ? 'F' : 'M';
        
        $nationality = $postData["nationalityId_{$i}"] ?? 'SG';
        $placeOfBirth = $postData["placeOfBirth_{$i}"] ?? $nationality;
        
        $bookingData['Paxs'][] = [
            'Name'                 => $fullName,
            'BirthDate'            => $birthDate,
            'NationalityId'        => $nationality,
            'CountryResidenceId'   => $nationality,
            'Gender'               => $gender,
            'PassportNo'           => strtoupper($postData["passportNo_{$i}"] ?? ''),
            'PassportPlaceIssue'   => $placeOfBirth,
            'PassportCountryIssueId' => $placeOfBirth,
            'PassportExpiryDate'   => $expiryDate,
        ];
    }
    
    error_log("Booking payload: " . json_encode($bookingData, JSON_PRETTY_PRINT));
    return $bookingData;
}

function createHitpayPayment($amount, $currency, $name, $email, $phone, $reference)
{
    //$apiKey = "c76bbbf2b4df6160ac27712172341c72921220a1ceb4944be8aedb91fd6bebfd"; // keep on server only

    //$url = "https://api.hit-pay.com/v1/payment-requests";

    $apiKey = "test_a78190e72a07e0a600037ac1f18804b0e60fa8ef7d4eeb986b33ff110ba9c221"; // keep on server only

    $url = "https://api.sandbox.hit-pay.com/v1/payment-requests";

    $data = [
        "amount" => number_format($amount, 2, '.', ''),
        "currency" => strtolower($currency),
        "payment_methods" => ["paynow_online"],
        "generate_qr" => true,
        "name" => $name,
        "email" => $email,
        "phone" => $phone,
        "reference_number" => $reference,
        "purpose" => "bintanferrytickets.com",
        "redirect_url" => 'https://bintan.desaruteambuilding.com/payment-success.php', // Update with your domain
        "webhook" => 'https://bintan.desaruteambuilding.com/payment-webhook.php', // Update with your domain
        "send_email" => 'true',
        "send_sms" => 'true',
        "allow_repeated_payments" => false,
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

    $response = curl_exec($ch);

    if(curl_errno($ch)){
        error_log("HitPay CURL ERROR: " . curl_error($ch));
        return false;
    }

    curl_close($ch);

    return json_decode($response, true);
}

// ============ FLEXI-TRIP CALCULATION ============
// ✅ FIX — $5/pax one-way, $10/pax round trip
$flexiCostPerPassengerPerTrip = 5;
$flexiTotalCost = 0;

if ($flexiTripValue === 1) {
    $numberOfTrips = 1;
    if ($tripType === 'round_trip' && $selectedReturn) {
        $numberOfTrips = 2;
    } elseif ($tripType === 'open_trip') {
        $numberOfTrips = 2;
    }
    $flexiTotalCost = $flexiCostPerPassengerPerTrip * $passengers * $numberOfTrips;
    $_SESSION['flexi_trip_cost'] = $flexiTotalCost;
} else {
    unset($_SESSION['flexi_trip_cost']);
}

// ============ GRAND TOTAL WITH ALL ADD-ONS ============
$grandTotalAfterDiscount = $grandTotal - $promoDiscount + $addonTotalValue;
if ($grandTotalAfterDiscount < 0) $grandTotalAfterDiscount = 0;

$_SESSION['grand_total_after_discount'] = $grandTotalAfterDiscount;
$_SESSION['flexi_trip_included']        = $flexiTripValue;
$_SESSION['flexi_trip_amount']          = $flexiTotalCost;

error_log("Flexi cost: $flexiTotalCost | Addon total: $addonTotalValue | Grand Total: $grandTotalAfterDiscount");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-5SVD439S');</script>
<!-- End Google Tag Manager -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bintan Ferry Tickets</title>
    <meta name="description" content="Contact Bintan Ferry Tickets for online ferry booking assistance, bintan fast ferry schedule information, and customer support. We are ready to help you to book your ferry tickets for your journey to Bintan.">
    <meta name="keywords" content="contact bintan ferry tickets, bintan ferry customer service, bintan ferry ticket assistance, bintan ferry help desk, online ferry booking">
    <meta name="author" content="VTL Travel">
    <meta name="robots" content="index, follow">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="capital-favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
       body {font-family: 'Inter', sans-serif; }

        /* Navbar Background */
        .custom-navbar {
            background: linear-gradient(white);
            padding: 12px 0;
            box-shadow: 0px 4px 20px 0px rgba(0, 0, 0, 0.08);
        }

        /* Logo */
        .navbar-brand {
            font-size: 22px;
            font-weight: 700;
            color: #111;
        }

        .navbar-brand .dot {
            color: #ff4d4f;
        }

        /* Center Menu */
        .nav-center .nav-link {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            padding: 6px 14px;
            border-radius: 20px;
            transition: all 0.2s ease;
        }

        /* Active HOME link - NOT a pill, just underlined */
.nav-center .nav-link.active {
    color: #359DD7 !important;
    font-weight: 700 !important;
    background-color: transparent !important;
    border-bottom: 1.5px solid #359DD7 !important;
    border-radius: 0 !important;
    padding-bottom: 4px !important;
}

        /* Hover effect */
        .nav-center .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.6);
        }

        /* Language button */
        .btn-lang {
            font-size: 13px;
            border: 1px solid #ddd;
            background: #fff;
            padding: 6px 12px;
            border-radius: 20px;
        }

        /* Login button */
        .btn-login {
            font-size: 13px;
            font-weight: 600;
            background-color: #ff6a3d;
            color: #fff;
            padding: 7px 16px;
            border-radius: 20px;
            border: none;
        }

        .btn-login:hover {
            background-color: #e85a2f;
            color: #fff;
        }

        /* Account link */
.account-link {
    color: #333;
    font-weight: 500;
    font-size: 14px;
    text-decoration: none;
}

.account-link:hover {
    color: #f85a40;
}

/* Book tickets button */
.btn-book-tickets {
    background-color: #f85a40;
    color: white;
    border-radius: 30px;
    padding: 10px 25px;
    font-weight: 600;
    font-size: 14px;
    border: none;
    transition: background-color 0.2s;
}

.btn-book-tickets:hover {
    background-color: #e64a2e;
    color: white;
}

/* Mobile adjustments */
@media (max-width: 991px) {
    .nav-center .nav-link.active::after {
        display: none;
    }
    
    .nav-center .nav-link.active {
        color: #f85a40;
        background: transparent;
    }
    
    .d-flex.align-items-center.gap-3 {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #eee;
        justify-content: center;
    }
}

.nav-logo {
    height: 55px; /* Adjust this to your preference */
    width: auto;
    display: block;
    transition: transform 0.3s ease; /* Adds a nice smooth feel */
}

/* Optional: Make it slightly smaller on mobile */
@media (max-width: 768px) {
    .nav-logo {
        height: 40px;
    }
}

        /* Ensure navbar doesn't overflow */
.custom-navbar {
    width: 100% !important;
    left: 0 !important;
    right: 0 !important;
}

/* Fix for mobile devices */
@media (max-width: 991px) {
    .custom-navbar .container-fluid {
        padding-left: 15px !important;
        padding-right: 15px !important;
    }
    
    /* Ensure no elements are causing overflow */
    .navbar-collapse {
        max-width: 100%;
    }
    
    .navbar-nav {
        width: 100%;
        margin: 0;
        padding: 10px 0;
    }
}

/* Additional fix for very small screens */
@media (max-width: 576px) {
    .custom-navbar .container-fluid {
        padding-left: 10px !important;
        padding-right: 10px !important;
    }
}

.smaller-heading {
    font-size: 1.25rem; /* Adjust this value */
}

.promo-badge-container {
    margin-bottom: 20px; /* Adjust spacing between badge and title */
}

.promo-badge {
    display: inline-block;
    border: 2px solid #ffffff; 
    padding: 10px 30px;
    border-radius: 50px;
    
    /* FIX HERE: Remove the space */
    font-size: 1rem; 
    
    font-weight: normal;
    font-style: normal; /* Ensures it isn't italicized by a theme */
    color: #ffffff;
    text-transform: none;
    margin: 0;
    line-height: 1.2; /* Helps center the text vertically in the pill */
}

/* Remove the default arrow if you want a cleaner look, or style it */
.nav-link.dropdown-toggle::after {
    vertical-align: middle;
    border-top: 0.3em solid;
    border-right: 0.3em solid transparent;
    border-left: 0.3em solid transparent;
    margin-left: 5px;
    color: #758599; /* Matches your sub-heading color */
}

/* Dropdown Item Hover Effect */
.dropdown-item:hover {
    background-color: #fff1f0; /* Very light red/coral tint */
    color: #359DD7 !important;
}

/* Ensure the dropdown menu matches your site's rounded aesthetic */
.dropdown-menu {
    border-radius: 12px;
    padding: 10px 0;
    margin-top: 0;
}

/* For Mobile: Ensure dropdown doesn't break layout */
@media (max-width: 991px) {
    .dropdown-menu {
        background-color: #f8fafc;
        border: none;
        box-shadow: none !important;
        padding-left: 20px;
    }
}

/* Show dropdown on hover (Desktop only) */
@media (min-width: 992px) {
    .nav-item.dropdown:hover .dropdown-menu {
        display: block;
        margin-top: 0; /* Prevents a gap that could close the menu when moving the mouse */
        opacity: 1;
        visibility: visible;
        animation: fadeIn 0.2s ease-in; /* Optional: smooth fade in */
    }
}

/* Optional: Smooth fade-in animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.badge-outline {
            border: 1px solid #94a3b8;
            color: #64748b;
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 0.85rem;
            display: inline-block;
            margin-bottom: 1rem;
        }

                /* Replace your existing promo banner style with this */
.promo-banner-fixed {
    background-color: #E9F2FF; /* Lighter blue to match the image */
    color: #333;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px; /* Space between text and button */
    padding: 12px 20px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    font-weight: 400;
    width: 100%;
    top: 0;
    left: 0;
    z-index: 1041;
    transition: transform 0.3s ease;
}

.promo-banner-fixed.hidden {
    transform: translateY(-100%);
}

/* The "Buy a pass" Button Style */
.promo-btn {
    background-color: #00A68F; /* Emerald green from image */
    color: white;
    text-decoration: none;
    padding: 8px 24px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 14px;
    transition: background-color 0.2s;
}

.promo-btn:hover {
    background-color: #008f7a;
}

/* Hide banner on scroll down, show on scroll up */
.promo-banner-fixed.hidden {
    transform: translateY(-100%);
}

        .stepper-container {
        max-width: 900px; /* Limits overall width to prevent over-stretching */
    }

        .main-heading {
            font-weight: 700;
            font-size: 48px;
            margin-bottom: 0.5rem;
        }

        .sub-heading {
            color: #758599;
            font-size: 16px;
            font-weight: 400;
            margin-bottom: 3rem;
        }

        /* Stepper Logic */
        .stepper-wrapper {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 20px;
        }
        
        /* Card Styling */
        .booking-card {
        border-radius: 16px;
        overflow: hidden;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        margin-bottom: 25px;
        background-color: #f8f9fa; /* Light grey body */
    }
    .header-red {
        background-color: #359DD7;
        color: white;
        padding: 12px 25px;
        font-weight: bold;
        font-size: 1.2rem;
    }
    .header-dark {
        background-color: #06121f;
        color: white;
        padding: 12px 25px;
        font-weight: bold;
        font-size: 1.1rem;
    }
    .route-label {
        color: #359DD7;
        font-size: 0.75rem;
        margin-bottom: 2px;
        font-weight: 600;
    }
    .station-code {
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 0;
        color: #000;
    }
    .station-name {
        font-size: 0.7rem;
        color: #adb5bd;
    }
    .dashed-divider {
        position: absolute;
        left: 0;
        top: 0%;
        bottom: 0%;
        border-left: 1px dashed #dee2e6;
        z-index: 1;
    }
    /* The white circle "punch-out" effect */
    .punch-hole {
        position: absolute;
        left: -20px;
        top: 50%;
        transform: translateY(-50%);
        width: 40px;
        height: 40px;
        background-color: #fff; /* Match this to your site background color */
        border-radius: 50%;
        z-index: 2;
    }
    .line-segment {
        border-bottom: 2px dotted #06121f;
        flex-grow: 1;
        margin: 0 15px;
        height: 1px;
    }

        /* Sidebar Elements */
        .promo-box { background-color: #ff8a65; border-radius: 12px; padding: 15px; color: white; }
        .points-box { background-color: #ffe0b2; border-radius: 12px; padding: 15px; color: #5d4037; margin-top: 15px; }
        .summary-card { border-radius: 12px; border: 1px solid #ddd; background: white; margin-top: 15px; }
        .summary-header { background: black; color: white; padding: 10px 15px; border-radius: 12px 12px 0 0; }
        
        .price-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.95rem; }
        .total-row { font-weight: bold; font-size: 1.1rem; border-top: 1px solid #eee; padding-top: 10px; margin-top: 10px; }
        .details-card { background: white; border-radius: 12px; border: 1px solid #e0e0e0; margin-bottom: 25px; overflow: hidden; }
        .card-header-custom { padding: 15px 20px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; }
        .card-body-custom { padding: 20px; }
        .label-text { color: #888; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 2px; }
        .value-text { font-weight: 600; color: #333; font-size: 0.95rem; }
        
        /* Summary Sidebar */
        .summary-sticky { position: sticky; top: 20px; background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 20px; }
        .price-line { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem; }
        .grand-total { font-size: 1.2rem; font-weight: 800; border-top: 1px solid #eee; padding-top: 15px; margin-top: 15px; }
        
        /* Checkbox & Button */
        .form-check-input:checked { background-color: #df4d45; border-color: #df4d45; }
        .btn-pay { background-color: #df4d45; color: white; border-radius: 50px; padding: 12px; font-weight: bold; width: 100%; border: none; transition: 0.3s; }
        .btn-pay:hover { background-color: #c43b35; color: white; }
        .journey-wrapper {
    min-height: 80px;
}

.route-label {
    color: #a0a0a0;
    font-size: 0.75rem;
    margin-bottom: 2px;
    text-transform: none;
}

.station-code {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0;
    color: #333;
}

.station-name {
    font-size: 0.7rem;
    color: #888;
}

/* Journey Line Styling */
.journey-line-container {
    position: relative;
    min-width: 120px;
}

.line-segment {
    flex-grow: 1;
    height: 1px;
    background-color: #e0e0e0;
}

.line-segment-short {
    width: 80px;
    height: 1px;
    background-color: #e0e0e0;
}
 
.route-icon-img {
    width: 22px; /* Adjust size to match image */
    height: auto;
    object-fit: contain;
}

/* Ensures the icons are tinted reddish if you aren't using colored images */
.text-danger-icon {
    filter: invert(37%) sepia(61%) saturate(3458%) hue-rotate(338deg) brightness(98%) contrast(88%);
}

/* Mobile Optimizations */
@media (max-width: 767.98px) {
    /* Fix for departure/return cards on mobile */
    .booking-card .row {
        flex-direction: column;
    }
    
    .booking-card .col-7,
    .booking-card .col-5 {
        width: 100% !important;
        flex: 0 0 auto !important;
    }
    
    /* Remove dashed divider on mobile */
    .booking-card .dashed-divider {
        display: none;
    }
    
    /* Add top border instead of left divider */
    .booking-card .col-5 {
        border-top: 2px dashed #dee2e6;
        position: relative !important;
        padding-top: 15px !important;
        margin-top: 5px;
    }
    
    /* Adjust padding for mobile */
    .booking-card .p-4 {
        padding: 1rem !important;
    }
    
    /* Make station codes smaller on mobile */
    .station-code {
        font-size: 1.2rem;
    }
    
    /* Adjust icon group for mobile */
    .icon-group {
        flex-wrap: nowrap;
    }
    
    .line-segment-short {
        width: 40px;
    }
    
    /* Ensure route details stack properly */
    .d-flex.justify-content-between.align-items-center {
        flex-wrap: wrap;
    }
    
    /* Make header text smaller on mobile */
    .header-red, .header-blue {
        font-size: 0.9rem;
        padding: 8px 15px;
    }
    
    /* Fix for the divider column */
    .col-5.position-relative {
        position: static !important;
    }
    
    /* ============ FIXED: Trip Details Section Alignment ============ */
    /* Reset the trip details container */
    .booking-card .col-5 .p-4 {
        display: flex !important;
        flex-direction: column !important;
        align-items: flex-start !important;
        justify-content: flex-start !important;
        padding: 1rem !important;
    }
    
    /* Style for each trip detail row */
    .booking-card .col-5 .p-4 .route-label {
        display: block !important;
        margin-bottom: 2px !important;
        margin-right: 0 !important;
        font-size: 0.7rem;
        color: #888;
        text-transform: uppercase;
    }
    
    /* Style for the date and time values */
    .booking-card .col-5 .p-4 h5 {
        display: block !important;
        font-size: 1.1rem !important;
        font-weight: 700 !important;
        margin-bottom: 12px !important;
        color: #333;
    }
    
    /* Remove any inline styles that might interfere */
    .booking-card .col-5 .p-4 .route-label.mt-3 {
        margin-top: 8px !important;
        margin-left: 0 !important;
    }
    
    /* Ensure proper spacing between date and time sections */
    .booking-card .col-5 .p-4 {
        gap: 4px;
    }
    
    /* Make the Departure Date and Departure Time stack vertically */
    .booking-card .col-5 .p-4 > div {
        width: 100%;
    }
    
    /* Alternative fix if the above doesn't work - force block display */
    .booking-card .col-5 .p-4 br {
        display: none;
    }
    
    /* Force each label and value pair to be on separate lines */
    .booking-card .col-5 .p-4 p.route-label,
    .booking-card .col-5 .p-4 h5 {
        float: none;
        clear: both;
        width: 100%;
    }
}

.stepper-header {
            background: white;
            padding: 15px 30px;
            border-bottom: 1px solid #eee;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .back-button {
            cursor: pointer;
            color: #333;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .step-item {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            color: #adb5bd; /* Gray for inactive */
            white-space: nowrap;
        }

        .step-item.active {
            color: #28a745; /* Green for completed */
            font-weight: 500;
        }

        .step-item.current {
            color: #212529; /* Dark for current */
            font-weight: 600;
        }

        .step-divider {
            height: 1px;
            width: 30px;
            background-color: #dee2e6;
            margin: 0 10px;
        }

        .check-icon {
            margin-right: 6px;
        }

        /* Mobile Steps - Only visible on mobile */
        .mobile-steps {
            display: none;
        }

        @media (max-width: 768px) {
            .desktop-steps {
                display: none !important;
            }

            .mobile-steps {
                display: block;
                width: 100%;
                margin-top: 8px;
            }

            .stepper-header {
                padding: 12px 15px;
                flex-wrap: wrap;
            }

            .stepper-header .d-flex.align-items-center {
                width: 100%;
                justify-content: space-between;
            }

            .mobile-progress-container {
                display: flex;
                align-items: center;
                justify-content: space-between;
                width: 100%;
                margin-top: 5px;
            }

            .mobile-step-item {
                display: flex;
                flex-direction: column;
                align-items: center;
                flex: 1;
                text-align: center;
                position: relative;
            }

            .mobile-step-icon {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                background-color: #e0e0e0;
                color: #999;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.8rem;
                font-weight: 600;
                margin-bottom: 4px;
                transition: all 0.3s ease;
            }

            .mobile-step-label {
                font-size: 0.65rem;
                color: #999;
                font-weight: 500;
                line-height: 1.2;
                max-width: 70px;
                white-space: nowrap;
            }

            .mobile-step-item.completed .mobile-step-icon {
                background-color: #28a745;
                color: white;
            }

            .mobile-step-item.completed .mobile-step-label {
                color: #28a745;
            }

            .mobile-step-item.active .mobile-step-icon {
                background-color: #df4d45;
                color: white;
            }

            .mobile-step-item.active .mobile-step-label {
                color: #df4d45;
                font-weight: 600;
            }

            .mobile-step-connector {
                flex: 1;
                height: 2px;
                background-color: #e0e0e0;
                margin: 0 5px;
                margin-bottom: 18px;
            }

            .mobile-step-connector.completed {
                background-color: #28a745;
            }

            /* Adjust container padding for mobile */
            .container.py-5 {
                padding-top: 1rem !important;
                padding-bottom: 1rem !important;
            }

            /* Make back button text smaller on mobile */
            .back-button {
                font-size: 0.9rem;
            }

            .back-button svg {
                width: 16px;
                height: 16px;
            }
        }

        @media (max-width: 375px) {
            .mobile-step-label {
                font-size: 0.6rem;
                max-width: 60px;
            }

            .mobile-step-icon {
                width: 28px;
                height: 28px;
                font-size: 0.75rem;
            }
        }

        /* Error message container */
    .error-container {
        max-width: 500px;
        margin: 20px auto;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    }

    /* Title style (matching image) */
    .error-title {
        font-size: 24px;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        letter-spacing: -0.3px;
    }

    /* Description style (matching image) */
    .error-description {
        font-size: 14px;
        color: #666;
        margin-bottom: 30px;
    }

    /* Error cards for each requirement */
    .error-card {
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 12px;
        background-color: white;
        transition: all 0.2s ease;
    }

    .error-card:hover {
        border-color: #ff6b6b;
        box-shadow: 0 2px 8px rgba(255, 107, 107, 0.1);
    }

    /* Error card content layout */
    .error-card-content {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    /* Error icon styling */
    .error-icon {
        width: 24px;
        height: 24px;
        min-width: 24px;
        border-radius: 50%;
        background-color: #ff6b6b;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: bold;
        margin-top: 2px;
    }

    /* Error text container */
    .error-text {
        flex: 1;
    }

    /* Error title within card */
    .error-card-title {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-bottom: 4px;
    }

    /* Error message details */
    .error-message {
        font-size: 14px;
        color: #666;
        line-height: 1.5;
    }

    /* Requirement highlight */
    .requirement-highlight {
        background-color: #fff8f8;
        border-left: 3px solid #ff6b6b;
        padding: 8px 12px;
        margin-top: 8px;
        font-size: 13px;
        color: #555;
        border-radius: 0 8px 8px 0;
    }

    /* Continue button (matching image) */
    .continue-btn {
        background-color: #ff6b6b;
        color: white;
        border: none;
        border-radius: 30px;
        padding: 14px 32px;
        font-size: 16px;
        font-weight: 500;
        cursor: pointer;
        margin-top: 24px;
        width: 100%;
        transition: background-color 0.2s;
    }

    .continue-btn:hover {
        background-color: #ff5252;
    }

    .continue-btn:disabled {
        background-color: #ccc;
        cursor: not-allowed;
    }

    /* Checkbox styling for the actual form */
    .requirement-checkbox {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 15px;
        cursor: pointer;
    }

    .requirement-checkbox input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin-top: 2px;
        cursor: pointer;
    }

    .checkbox-label {
        font-size: 14px;
        color: #444;
        line-height: 1.5;
        flex: 1;
    }

    .checkbox-label strong {
        color: #ff6b6b;
        font-weight: 600;
    }

    .custom-modal-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;
}
.custom-modal-card {
    background: #fff; padding: 40px; border-radius: 12px; width: 90%; max-width: 500px;
    text-align: center; font-family: sans-serif; box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
.modal-title { font-weight: bold; font-size: 24px; margin-bottom: 20px; color: #000; }
.modal-desc { color: #333; font-size: 16px; margin-bottom: 30px; line-height: 1.5; }
.modal-footer { display: flex; justify-content: flex-end; }
.btn-continue {
    background-color: #d9443e; color: white; border: none; padding: 12px 35px;
    border-radius: 25px; font-weight: bold; cursor: pointer; font-size: 16px;
}
.btn-continue:hover { background-color: #c0392b; }

 #hitpay-iframe {
        background: #f8f9fa;
    }
    
    /* Loading spinner for iframe */
    .iframe-loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }
    
    .iframe-loading .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #ef534e;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 10px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Mobile responsive adjustments */
    @media (max-width: 576px) {
        #payment-modal > div > div {
            width: 95%;
            max-height: 95vh;
        }
        
        #payment-modal > div > div > div:nth-child(2) {
            height: 500px;
        }
    }

/* Payment method hover effects */
.payment-option:hover {
    border-color: #1a3a8a !important;
    background-color: #f8f9ff;
    transition: all 0.2s;
}

.payment-option.selected {
    border-color: #1a3a8a !important;
    background-color: #f0f3ff;
}

.payment-option.selected .radio-circle {
    border-color: #1a3a8a !important;
}

.payment-option.selected .radio-inner {
    background-color: #1a3a8a !important;
}

/* Disabled state styling */
.payment-option.disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background-color: #f8f9fa;
}

.payment-option.disabled:hover {
    border-color: #dee2e6 !important;
}

/* PayNow QR preview animation */
.paynow-preview {
    animation: slideDown 0.4s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* QR code container hover effect */
.qr-placeholder {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.qr-placeholder:hover {
    transform: scale(1.02);
    box-shadow: 0 12px 30px rgba(26, 58, 138, 0.15) !important;
}

/* Mobile adjustments */
@media (max-width: 576px) {
    .payment-option .d-flex {
        flex-wrap: wrap;
    }
    
    .accepted-cards {
        margin-left: 38px;
        margin-top: 5px;
    }
    
    /* Responsive QR code */
    .qr-placeholder {
        width: 160px !important;
        height: 160px !important;
    }
    
    .qr-placeholder i {
        font-size: 4.5rem !important;
    }
    
    .amount-box {
        width: 100%;
    }
    
    .d-flex.justify-content-center.gap-3 {
        flex-direction: column;
        gap: 0.5rem !important;
    }
    
    .badge {
        width: 100%;
    }
}

/* Tablet adjustments */
@media (min-width: 577px) and (max-width: 768px) {
    .qr-placeholder {
        width: 180px !important;
        height: 180px !important;
    }
}

/* --- 1. Customer Support Banner --- */
        .vtl-support-banner {
            background-color: #021320; /* Dark blue background */
            border-radius: 12px;
            color: #ffffff;
            padding: 50px 40px;
            margin: 20px auto 50px auto; /* Change to auto for horizontal centering */
            position: relative;
            overflow: hidden;
        }
        
        .vtl-banner-content {
            z-index: 2;
            position: relative;
        }

        .vtl-support-text {
            max-width: 60%;
        }

        .vtl-banner-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .vtl-banner-desc {
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 25px;
            color: white;
        }

        .vtl-contact-btn {
            background-color: #359DD7; /* The primary coral/red button color */
            border: none;
            border-radius: 20px;
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 30px;
            transition: background-color 0.2s;
        }
        
        .vtl-contact-btn:hover {
            background-color: #d1493b;
        }

        /* The Illustration (positioned to match the image) */
        .vtl-banner-illustration {
            position: absolute;
            left: 50px;
            bottom: 0px;
            z-index: 1;
            
            /* Placeholder: Replace with your actual image asset */
            /* width: 200px; height: 160px; background: url('assets/images/customer_support_banner.png') no-repeat bottom left / contain; */
        }
        
        /* Placeholder styling so you can see where it goes */
        /* Fixed styling for the illustration */
        .vtl-ill-placeholder {
            position: absolute;
            left: 50px;
            bottom: 0px; /* Changed from 50px to 0 to align with the bottom edge */
            width: 330px;
            height: 248px;
            background: url(https://bintanferrytickets.com/illustration.png);
            /* Removed the border-radius placeholders so the image is not distorted */
        }

        /* --- 2. Main Footer Links Area --- */
        footer.vtl-footer {
            padding: 0;
        }

        .vtl-footer-logo-area {
            margin-bottom: 30px;
        }

        .vtl-footer-logo {
            max-width: 150px;
            height: auto;
        }

        .vtl-selector-label {
            font-size: 12px;
            color: #7a7a7a;
            font-weight: 600;
            margin-bottom: 5px;
            display: block;
        }

        /* Select styling for Currency/Payment */
        .vtl-select-custom {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 8px 15px;
            font-size: 14px;
            color: #4a4a4a;
            appearance: none; /* Hide default arrow */
            background: #ffffff url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%234a4a4a' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") no-repeat right 12px center/10px 10px;
            margin-bottom: 15px;
            cursor: pointer;
        }

        /* Payment Icons */
        .vtl-payment-icons img {
            height: 18px; /* Standard icon height */
            margin-right: 15px;
            opacity: 0.8;
        }


        /* Link Column Styling */
        .vtl-footer-column h6 {
            font-size: 16px;
            font-weight: 700;
            color: #333333;
            margin-bottom: 20px;
        }

        .vtl-footer-column ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .vtl-footer-column ul li {
            margin-bottom: 12px;
        }

        .vtl-footer-column ul li a {
            text-decoration: none;
            color: #666666;
            font-size: 14px;
            transition: color 0.1s;
        }

        .vtl-footer-column ul li a:hover {
            color: #359DD7;
        }

        /* Contact Details with Icons */
        .vtl-contact-list li {
            font-size: 13px;
            line-height: 1.5;
            display: flex;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .vtl-contact-icon {
    margin-right: 18px;       /* Increased space between icon and text */
    flex-shrink: 0;           /* Prevents the icon from squishing */
    display: flex;
    align-items: center;
    position: relative;
    top: -2px;                /* Nudges the icon up slightly */
}
        
        .vtl-contact-icon svg, 
        .vtl-contact-icon i {
            width: 100%;
            height: auto;
            opacity: 0.5; /* Match the subtle color from image */
        }
        
        .vtl-address-text {
            color: #666666;
            display: block;
        }

        /* Separator Line */
        .vtl-separator {
            border-top: 1px solid #f0f0f0;
            margin: 40px 0;
        }

        /* --- 3. Newsletter & Bottom Line --- */
        .vtl-newsletter-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
        }

        .vtl-newsletter-text-block h3 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .vtl-newsletter-form-block {
            text-align: right;
            max-width: 450px;
        }

        .vtl-newsletter-subtitle {
            font-size: 13px;
            color: #7a7a7a;
            margin-bottom: 10px;
        }

        .vtl-newsletter-input-group {
            position: relative;
            display: flex;
        }

        .vtl-newsletter-input {
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            padding: 10px 140px 10px 20px; /* Extra padding right for button */
            font-size: 14px;
            width: 100%;
        }

        .vtl-subscribe-btn {
            position: absolute;
            right: 4px;
            top: 4px;
            bottom: 4px;
            background-color: #359DD7;
            color: #ffffff;
            border: none;
            border-radius: 16px;
            font-size: 13px;
            font-weight: 600;
            padding: 0 20px;
            transition: background-color 0.2s;
        }
        
        .vtl-subscribe-btn:hover {
            background-color: #d1493b;
        }

        /* Bottom Copyright Line */
        .vtl-bottom-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: #7a7a7a;
            padding-bottom: 30px;
        }

        .vtl-social-links {
            display: flex;
            gap: 15px;
        }

        .vtl-social-link {
            text-decoration: none;
            color: #7a7a7a;
            font-size: 18px;
            transition: color 0.1s;
        }

        .vtl-social-link:hover {
            color: #359DD7;
        }

        /* Media Queries for Responsiveness */
        @media (max-width: 1200px) {
            .vtl-support-banner, footer.vtl-footer { padding-left: 30px; padding-right: 30px; margin-left: 30px; margin-right: 30px; }
            .vtl-newsletter-section { flex-direction: column; }
            .vtl-newsletter-form-block { text-align: left; width: 100%; margin-top: 20px; }
            .vtl-bottom-line { flex-direction: column; gap: 15px; text-align: center; }
        }
        
        @media (max-width: 768px) {
            .vtl-support-banner { text-align: center; padding-top: 150px; }
            .vtl-support-text { max-width: 100%; }
            .vtl-banner-illustration { left: 50%; transform: translateX(-50%); top: 10px; }
            .vtl-support-banner, footer.vtl-footer { margin-left: 10px; margin-right: 10px; }
        }

        /* Stepper Logic */
        .stepper-wrapper {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 20px;
        }

        /* The dotted line behind the icons */
        .stepper-wrapper::before {
            content: "";
            position: absolute;
            top: 25px;
            left: 5%;
            right: 5%;
            height: 2px;
            border-top: 1px dashed #758599;
            z-index: 0;
        }

        .step-item {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            flex: 1;
        }

        .step-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: white;
            border: 2px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            color: #64748b;
        }

        /* Completed State Styles */
        .step-item.completed .step-icon {
            background-color: #359DD7; /* Red color from image */
            border-color: #359DD7;
            width: 36px;
            height: 36px;
            color: white;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.2);
        }

        .step-label-top {
            text-transform: uppercase;
            font-size: 12px;
            font-weight: 500;
            color: #758599;
            margin-bottom: 4px;
        }

        .step-label-bottom {
            font-weight: 600;
            font-size: 16px;
            color: #021320;
        }

.custom-breadcrumb {
    display: flex;
    flex-wrap: wrap;
    padding: 0;
    list-style: none;
    font-size: 16px;
}

.custom-breadcrumb .breadcrumb-item {
    display: flex;
    align-items: center;
}

/* The "Home" Link */
.custom-breadcrumb .breadcrumb-item a {
    color: #7d8ea1; /* Muted blue-grey */
    text-decoration: none;
    transition: color 0.2s;
}

/* The "/" Separator */
.custom-breadcrumb .breadcrumb-item + .breadcrumb-item::before {
    display: inline-block;
    padding-right: 12px;
    padding-left: 12px;
    color: #758599;
    content: "/";
    font-weight: 600;
}

/* The "Search Results" Active Text */
.custom-breadcrumb .breadcrumb-item.active {
    color: #359DD7; /* The specific red/coral from your image */
    font-weight: 600;
}

.custom-breadcrumb .breadcrumb-item a:hover {
    color: #e6554d;
}

/* Target the placeholder across different browsers */
.form-control::placeholder {
    color: white;
    opacity: 1; /* Firefox fix */
}

.passenger-type-badge {
    background: #359DD7;
    color: white;
    font-size: 0.75rem;
    padding: 2px 10px;
    border-radius: 20px;
    margin-left: 10px;
    font-weight: 500;
}

.payment-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.2s ease;
        position: relative;
    }
    
    /* State when a payment method is selected */
    .payment-card.active {
        border-color: #3b82f6; /* Blue border */
        background-color: #f8fafc;
    }

    .custom-radio {
        width: 22px;
        height: 22px;
        border: 2px solid #cbd5e0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .payment-card.active .custom-radio {
        border-color: #359DD7;
        background-color: #359DD7;
    }

    /* Checkmark icon inside the active radio */
    .payment-card.active .custom-radio::after {
        content: "\F26E"; /* Bootstrap icon check */
        font-family: "bootstrap-icons";
        color: white;
        font-size: 12px;
        font-weight: bold;
    }

    .logo-container {
        background: #fff;
        border: 1px solid #edf2f7;
        border-radius: 8px;
        width: 80px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 5px;
    }

    /* Navigation Buttons */
    .btn-back {
        border: 1px solid #06121f;
        border-radius: 50px;
        padding: 12px 25px;
        font-weight: 700;
        color: #06121f;
        background: white;
    }

    .btn-checkout {
        background-color: #359DD7;
        border: none;
        border-radius: 50px;
        padding: 12px 30px;
        font-weight: 700;
        color: white;
    }

    /* ========== FULL MOBILE RESPONSIVENESS FOR SUPPORT BANNER ========== */
        @media (max-width: 992px) {
            .vtl-support-banner {
                padding: 200px 24px 40px 24px; /* Extra top padding to accommodate illustration on top */
                text-align: center;
            }
            
            .vtl-support-text {
                max-width: 100%;
                text-align: center;
            }
            
            .vtl-banner-title {
                font-size: 24px;
            }
            
            .vtl-banner-desc {
                font-size: 13px;
                line-height: 1.5;
            }
            
            /* Move illustration to top center on tablet/mobile */
            .vtl-banner-illustration {
                left: 50%;
                transform: translateX(-50%);
                top: 20px;
                bottom: auto;
                width: 280px;
                height: auto;
            }
            
            .vtl-ill-placeholder {
                position: relative;
                left: 0;
                bottom: auto;
                width: 260px;
                height: 180px;
                margin: 0 auto;
                background-size: contain;
            }
            
            .vtl-banner-content {
                text-align: center;
            }
            
            .vtl-banner-content .text-end {
                text-align: center !important;
            }
            
            .vtl-contact-btn {
                display: inline-block;
                margin-top: 8px;
            }
        }
        
        @media (max-width: 576px) {
            .vtl-support-banner {
                padding: 180px 20px 35px 20px;
                margin-left: 15px;
                margin-right: 15px;
            }
            
            .vtl-banner-title {
                font-size: 22px;
                line-height: 1.3;
            }
            
            .vtl-banner-desc {
                font-size: 12px;
                line-height: 1.45;
                margin-bottom: 20px;
            }
            
            .vtl-banner-illustration {
                top: 15px;
                width: 220px;
            }
            
            .vtl-ill-placeholder {
                width: 200px;
                height: 150px;
            }
            
            .vtl-contact-btn {
                padding: 8px 24px;
                font-size: 13px;
            }
        }

        /* CORE BUTTON STYLES — FULLY RESPONSIVE */
    .btn-back, .btn-continue {
      border-radius: 60px;
      font-weight: 700;
      font-size: 1rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      transition: all 0.25s ease;
      padding: 12px 28px;
      border: none;
      cursor: pointer;
      background: transparent;
      text-decoration: none;
    }
    .btn-back {
      background-color: transparent;
      color: #1e2a3a;
      border: 1.5px solid #1e2a3a;
    }
    .btn-back:hover {
      background-color: #f1f5f9;
      color: #0f172a;
      transform: translateY(-2px);
    }
    .btn-continue {
      background-color: #359DD7;
      color: #ffffff;
      box-shadow: 0 4px 8px rgba(231, 74, 67, 0.2);
    }
    .btn-continue:hover {
      background-color: #d63e37;
      transform: translateY(-2px);
      box-shadow: 0 8px 18px rgba(231, 74, 67, 0.25);
    }

    /* MOBILE FRIENDLY BUTTON WRAPPER */
    .step-actions-wrapper {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
      margin-top: 2rem;
      margin-bottom: 2rem;
    }
    /* On very small screens, buttons stack full width, still look great */
    @media (max-width: 576px) {
      .step-actions-wrapper {
        flex-direction: column;
        gap: 14px;
      }
      .btn-back, .btn-continue {
        width: 100%;
        justify-content: center;
        padding: 14px 20px;
        font-size: 1rem;
      }
    }

    /* Container styling */
.custom-travel-dropdown {
    padding: 20px;
    border-radius: 20px;
    min-width: 300px; /* Adjust based on your preference */
}

.dropdown-flex-container {
    display: flex;
    gap: 15px;
    justify-content: center;
}

    /* Base Card Styling */
.custom-travel-dropdown .dropdown-item {
    width: 200px;
    height: 75px;
    border-radius: 50px; /* Makes it a pill shape */
    display: flex;
    align-items: center;
    justify-content: center;
    color: white !important;
    font-size: 16px;
    font-weight: 700;
    text-shadow: 1px 1px 4px rgba(0,0,0,0.5);
    background-size: cover;
    background-position: center;
    position: relative;
    overflow: hidden;
    transition: transform 0.2s ease;
    padding: 0; /* Reset default padding */
}

.custom-travel-dropdown .dropdown-item:hover {
    transform: scale(1.03);
    background-color: transparent; /* Prevents Bootstrap default gray hover */
}

/* Background Images with Dark Overlay */
.baggage-card {
    background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), 
                url('baggage.jpg');
}

.visa-card {
    background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), 
                url('visa.jpg');
}

/* Remove default Bootstrap bullet/list styles */
.custom-travel-dropdown li {
    list-style: none;
}

/* Specific Backgrounds for Big Groups */
.teambuilding-card {
    background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), 
                url('teambuilding.jpg');
}

/* Update this specific part of your CSS */
.group-card {
    /* Replace with your actual image path */
    background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), 
                url('group.jpg'); 
    
    background-size: cover !important;
    background-position: center !important;
    background-color: #555 !important; /* Backup color so it's not invisible */
    display: flex !important; /* Ensures the pill shape holds up */
}

/* Ensure the text is always white and visible */
.custom-travel-dropdown .dropdown-item {
    color: #ffffff !important;
    opacity: 1 !important;
}
    </style>
    <!-- Custom Css -->
    <link rel="stylesheet" href="./assets/css/helper.css" />
    <link rel="stylesheet" href="./assets/css/style.css" />
    <link rel="stylesheet" href="./assets/css/responsive.css" />
</head>
<body>

<?php require './assets/includes/navbar.php'; ?>

<div class="container py-5">
<nav aria-label="breadcrumb">
  <ol class="custom-breadcrumb mb-5">
    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
    <li class="breadcrumb-item"><a href="/search-result">Search Results</a></li>
    <li class="breadcrumb-item active" aria-current="page">Book Ferry</li>
  </ol>
</nav>

<?php
// PHP logic to control the UI state
// current_step 3 means steps 1 & 2 are completed, 3 is active
$current_step = 4; 
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="badge-outline">Complete Your Ferry Booking</div>

        </div>
    </div>
</div>

<div class="container py-5">
    <h2 class="fw-bold mb-1" style="color: #06121f;">Your Booking</h2>
    <p class="text-muted mb-4">Please review your booking and make your payment below</p>

    <div class="row">
        <div class="col-lg-8"> <?php 
            $trips = [
                ['label' => 'Departure', 'origin' => $departOrigin, 'dest' => $departDestination, 'date' => $departDate, 'time' => $departTime, 'reg' => $departTimeRegion],
            ];
            if ($selectedReturn) {
                $trips[] = ['label' => 'Return', 'origin' => $returnOrigin, 'dest' => $returnDestination, 'date' => $returnDate, 'time' => $returnTime, 'reg' => $returnTimeRegion];
            }

        foreach ($trips as $trip): ?>
            <div class="booking-card">
                <div class="row g-0">
                    <div class="col-md-7 bg-white">
                        <div class="header-red"><?= $trip['label'] ?></div>
                        <div class="p-4">
                            <h6 class="fw-bold mb-4" style="color: #000;">Route Details</h6>
                            
                            <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center">
                            <img src="ferry-icon.png" alt="ferry" style="width: 32px; height: auto;" class="me-3">
                            <div>
                                <p class="route-label">From</p>
                                <h3 class="station-code"><?= htmlspecialchars(strtoupper(substr($trip['origin'], 0, 3))) ?></h3>
                                <p class="station-name"><?= htmlspecialchars($trip['origin']) ?></p>
                            </div>
                        </div>

                                <div class="line-segment"></div>

                                <div class="d-flex align-items-center">
                            <i class="bi bi-geo-alt me-3" style="font-size: 2rem; color: #000;"></i>
                            <div>
                                <p class="route-label">To</p>
                                <h3 class="station-code"><?= htmlspecialchars(strtoupper(substr($trip['dest'], 0, 3))) ?></h3>
                                <p class="station-name"><?= htmlspecialchars($trip['dest']) ?></p>
                            </div>
                        </div>
                    </div>

                            <div class="mt-4 pt-2 text-muted small d-flex align-items-center">
                                <i class="bi bi-clock-fill me-2" style="color: #6c757d;"></i> 
                                <span>Gate: Opens <span class="fw-bold">1 hour before departure</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5 position-relative" style="background-color: #f1f4f8;">
                        <div class="dashed-divider"></div>
                        <div class="punch-hole"></div>
                        <div class="header-dark">Ferry Trip Details</div>
                        
                        <div class="p-4 px-5">
                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bi bi-calendar3 me-2" style="color: #359DD7; font-size: 0.9rem;"></i>
                                    <p class="route-label m-0">Departure Date</p>
                                </div>
                                <h4 class="fw-bold m-0"><?= formatDateDisplay($trip['date']) ?></h4>
                            </div>

                            <div>
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bi bi-clock me-2" style="color: #359DD7; font-size: 0.9rem;"></i>
                                    <p class="route-label m-0">Departure Time</p>
                                </div>
                                <h4 class="fw-bold m-0"><?= formatTimeDisplay($trip['time']) ?> <?= htmlspecialchars($trip['reg']) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>


            <div class="alert alert-light border p-3" style="border-radius: 12px; font-size: 0.85rem;">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Traveling Time:</strong> WIB (Western Indonesian Time) is one hour behind Singapore Time. Departure and arrival times are in local time zones. Journey times are approximate and subject to sea & weather conditions.
            </div>
            
             <h4 class="fw-bold">Contact Details</h4>
            <p class="text-muted small mb-4">We'll use this information as the main contact for your booking.</p>
            
            <div class="details-card shadow-sm">
                <div class="card-header-custom">
                    <span class="fw-bold"><?= htmlspecialchars($contactName) ?></span>
                   <!-- <a href="booking-review.php" class="text-primary text-decoration-none small"><i class="bi bi-pencil-square"></i> Edit Details</a> -->
                </div>
                <div class="card-body-custom">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="label-text">Email</p>
                            <p class="value-text"><?= htmlspecialchars($contactEmail) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="label-text">Phone</p>
                            <p class="value-text"><?= htmlspecialchars($contactPhone) ?></p>
                        </div>
                    </div>
                </div>
            </div> 

            <h4 class="fw-bold mt-5">Traveler Details <span class="text-muted fw-normal">(<?= $passengers ?> PAX)</span></h4>
            <p class="text-muted small mb-4">Please check and ensure the passenger details are correct. Inaccuracies may result in rejection or denial of entry.</p>

            <?php foreach ($passengerDetails as $index => $passenger): ?>
            <div class="details-card shadow-sm mb-3">
                <div class="card-header-custom">
                    <div>
                        <span class="fw-bold"><?= htmlspecialchars(strtoupper($passenger['name'])) ?></span>
                    </div>
                    <span class="passenger-type-badge"><?= htmlspecialchars(strtoupper($passenger['passenger_type'])) ?></span>
                </div>
                <div class="card-body-custom">
                    <div class="row mb-4">
                        <div class="col-6 col-md-4">
                            <p class="label-text">Passport Number</p>
                            <p class="value-text"><?= htmlspecialchars($passenger['passportNo']) ?></p>
                        </div>
                        <div class="col-6 col-md-4">
                            <p class="label-text">Date of Birth</p>
                            <p class="value-text"><?= formatDateForDisplay($passenger['dob']) ?></p>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-6 col-md-4">
                            <p class="label-text">Expires on</p>
                            <p class="value-text"><?= formatDateForDisplay($passenger['passportExpiryDate']) ?></p>
                        </div>
                        <div class="col-6 col-md-4">
    <p class="label-text">Place of Birth</p>
    <p class="value-text">
        <?php 
        $issuedAt = $passenger['passportCountryIssued'];
        // If it's OTHER and we have custom value, show the custom value
        if ($issuedAt === 'OTHER' && isset($bookingFormData["custom_issuance_country_" . ($index + 1)])) {
            echo htmlspecialchars($bookingFormData["custom_issuance_country_" . ($index + 1)]);
        } else {
            // Convert country codes to display names
            $countryNames = [
                'SG' => 'Singapore',
                'ID' => 'Indonesia',
                'MY' => 'Malaysia',
                'TH' => 'Thailand',
                'VN' => 'Vietnam',
                'PH' => 'Philippines',
                'JP' => 'Japan',
                'KR' => 'South Korea',
                'CN' => 'China',
                'IN' => 'India',
                'AU' => 'Australia',
                'GB' => 'United Kingdom',
                'US' => 'United States',
                'OT' => 'Other'
            ];
            
            if (strlen($issuedAt) === 2 && isset($countryNames[$issuedAt])) {
                echo $countryNames[$issuedAt];
            } else {
                echo htmlspecialchars($issuedAt);
            }
        }
        ?>
    </p>
</div>
                    </div>
                    <div class="row">
                        <div class="col-6 col-md-4">
    <p class="label-text">Nationality</p>
    <p class="value-text">
        <?php 
        $nationality = $passenger['nationality'];
        // If it's OTHER and we have custom value, show the custom value instead
        if ($nationality === 'OTHER' && isset($bookingFormData["custom_nationality_" . ($index + 1)])) {
            echo htmlspecialchars($bookingFormData["custom_nationality_" . ($index + 1)]);
        } else {
            // Convert country codes to display names if needed
            $countryNames = [
                'SG' => 'Singapore',
                'ID' => 'Indonesia',
                'MY' => 'Malaysia',
                'TH' => 'Thailand',
                'VN' => 'Vietnam',
                'PH' => 'Philippines',
                'JP' => 'Japan',
                'KR' => 'South Korea',
                'CN' => 'China',
                'IN' => 'India',
                'AU' => 'Australia',
                'GB' => 'United Kingdom',
                'US' => 'United States',
                'OT' => 'Other'
            ];
            
            if (strlen($nationality) === 2 && isset($countryNames[$nationality])) {
                echo $countryNames[$nationality];
            } else {
                echo htmlspecialchars($nationality);
            }
        }
        ?>
    </p>
</div>
                        <div class="col-6 col-md-4">
                            <p class="label-text">Gender</p>
                            <p class="value-text"><?= htmlspecialchars(ucfirst($passenger['gender'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="col-lg-4">
<!-- ========== PROCEED TO PAYMENT BUTTON (TOP OF PROMO BOX) ========== -->
            <div class="mb-3">
                <button class="btn-continue w-100 justify-content-center" id="proceedPaymentBtn" onclick="redirectToHitPay()">
                    Proceed to Payment <i class="bi bi-arrow-right-short fs-5"></i>
                </button>
            </div>

   <!-- <div class="promo-box" style="
        background-color: #030d17; /* Matches the dark background from the image */
        color: white;             /* Standard text color */
        border-radius: 10px;      /* Rounded corners */
        padding: 10px;            /* Narrower padding, similar to the image */
        margin-bottom: 20px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2); /* Soft shadow for depth */
    ">
        
        <p class="mb-1 fw-bold" style="font-size: 1.1rem; margin-top: 5px;">Promo Code</p>
        
        <p class="small mb-3" style="color: white;">Enter coupon code and make your payment below.</p>
        
        <form id="promoForm" method="post" action="apply-promo.php">
            
            <div class="input-group" style="
                display: flex;
                align-items: center;
                border: 1px solid #4a5568; /* Light grey border */
                border-radius: 20px;       /* Heavily rounded input field */
                background-color: transparent;
                padding-left: 5px;          /* Minimal left padding for icon */
                padding-right: 5px;         /* Minimal right padding for button */
                height: 40px;               /* Consistent height */
            ">
                
                <i class="bi bi-tag" style="
                    color: #ec5d55;
                    font-size: 1.2rem;
                    margin-left: 10px;
                    margin-right: 10px;
                "></i>
                
                <input type="text" name="promo_code" class="form-control border-0" 
                       placeholder="PROMO CODE" 
                       value="<?= htmlspecialchars($_SESSION['promo_code'] ?? '') ?>"
                       style="
                           background-color: transparent; /* Seamless input */
                           color: white;                 /* Text visible on dark background */
                           text-transform: uppercase;   /* Matches image placeholder style */
                           padding-left: 5px;            /* Align with icon */
                           margin: 0;
                           box-shadow: none;             /* Prevent browser outlines */
                       ">
                
                <button type="submit" class="btn" style="
                    background-color: #ec5d55; /* Orange/red color from image */
                    border-radius: 20px;       /* Rounded ends for the button */
                    color: white;             /* Button text color */
                    border: none;             /* Remove default borders */
                    padding: 0px 20px;         /* Consistent side padding */
                    height: 30px;              /* Shorter than the overall container */
                    font-weight: bold;         /* Bold button text */
                    font-size: 0.8rem;         /* Smaller text size */
                ">
                    APPLY
                </button>
            </div>
        </form>
        
        <?php if (isset($_SESSION['promo_success'])): ?>
            <div class="alert alert-success mt-2 p-1 small mb-0" style="background-color: #d4edda; color: #155724; border: none; border-radius: 5px;">
                <i class="bi bi-check-circle"></i> <?= htmlspecialchars($_SESSION['promo_success']) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['promo_error'])): ?>
            <div class="alert alert-danger mt-2 p-1 small mb-0" style="background-color: #f8d7da; color: #721c24; border: none; border-radius: 5px;">
                <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['promo_error']) ?>
            </div>
        <?php endif; ?>
        
    </div> -->

            <div class="summary-card">
                <div class="summary-header d-flex justify-content-between align-items-center">
                    <span>Booking Summary</span>
                </div>
                <div class="p-3">
                    <div class="price-row fw-bold"><span>Passengers:</span></div>
                    <div class="price-row"><span>Adult passenger(s)</span> <span><?= $adultQty ?></span></div>
                    <?php if ($childQty > 0): ?>
                    <div class="price-row"><span>Child passenger(s)</span> <span><?= $childQty ?></span></div>
                    <?php endif; ?>
                    <?php if ($infantQty > 0): ?>
                    <div class="price-row"><span>Infant passenger(s)</span> <span><?= $infantQty ?></span></div>
                    <?php endif; ?>
                    <hr>
                    
                    <!-- Departure Trip Summary -->
      <!--  <h6 class="fw-bold">Departure</h6>
        <p class="text-muted small mb-3"><?= htmlspecialchars($departOrigin) ?> - <?= htmlspecialchars($departDestination) ?></p>
        
        <div class="price-line"><span>Ticket Fare ×<?= $passengers ?></span> <span><?= $currencySymbol ?><?= number_format($departureTicketTotal, 2) ?></span></div>
        
        <?php if ($departureSurchargeTotal > 0): ?>
        <div class="price-line"><span><?= htmlspecialchars($surchargeName) ?> ×<?= $passengers ?></span> <span><?= $currencySymbol ?><?= number_format($departureSurchargeTotal, 2) ?></span></div>
        <?php endif; ?>
        
        <?php if ($departurePdfSgTotal > 0): ?>
        <div class="price-line"><span>Departure Fee</span> <span><?= $currencySymbol ?><?= number_format($departurePdfSgTotal, 2) ?></span></div>
        <?php endif; ?>
        
        <?php if ($departurePdfBtmTotal > 0): ?>
        <div class="price-line"><span>Return Fee</span> <span><?= $currencySymbol ?><?= number_format($departurePdfBtmTotal, 2) ?></span></div>
        <?php endif; ?>
        
        <div class="price-line fw-bold mt-2 border-top pt-2">
            <span>Departure Total</span> 
            <span><?= $currencySymbol ?><?= number_format($departureSubtotal, 2) ?></span>
        </div>
        
        <?php if ($tripType === 'round_trip' && $selectedReturn && isset($selectedReturnDetails)): ?>
        <!-- Regular Round Trip Return Summary -->
     <!--   <h6 class="fw-bold mt-4">Return</h6>
        <p class="text-muted small mb-3"><?= htmlspecialchars($returnOrigin) ?> - <?= htmlspecialchars($returnDestination) ?></p>
        
        <div class="price-line"><span>Ticket Fare ×<?= $passengers ?></span> <span><?= $currencySymbol ?><?= number_format($returnTicketTotal, 2) ?></span></div>
        
        <?php if ($returnSurchargeTotal > 0): ?>
        <div class="price-line"><span><?= htmlspecialchars($surchargeName) ?> ×<?= $passengers ?></span> <span><?= $currencySymbol ?><?= number_format($returnSurchargeTotal, 2) ?></span></div>
        <?php endif; ?>
        
        <?php if ($returnPdfSgTotal > 0): ?>
        <div class="price-line"><span>Departure Fee</span> <span><?= $currencySymbol ?><?= number_format($returnPdfSgTotal, 2) ?></span></div>
        <?php endif; ?>
        
        <?php if ($returnPdfBtmTotal > 0): ?>
        <div class="price-line"><span>Return Fee</span> <span><?= $currencySymbol ?><?= number_format($returnPdfBtmTotal, 2) ?></span></div>
        <?php endif; ?>
        
        <div class="price-line fw-bold mt-2 border-top pt-2">
            <span>Return Total</span> 
            <span><?= $currencySymbol ?><?= number_format($returnSubtotal, 2) ?></span>
        </div>
        
        <?php elseif ($tripType === 'open_trip'): ?>
        <!-- Open Trip Return Summary -->
     <!--   <h6 class="fw-bold mt-4">Open Return</h6>
        <p class="text-muted small mb-3">Valid for return within 30 days</p>
        
        <div class="price-line"><span>Open Return Ticket ×<?= $passengers ?></span> <span><?= $currencySymbol ?><?= number_format($returnTicketTotal, 2) ?></span></div>
        
        <?php if ($returnSurchargeTotal > 0): ?>
        <div class="price-line"><span><?= htmlspecialchars($surchargeName) ?> ×<?= $passengers ?></span> <span><?= $currencySymbol ?><?= number_format($returnSurchargeTotal, 2) ?></span></div>
        <?php endif; ?>
        
        <?php if ($returnPdfSgTotal > 0): ?>
        <div="price-line">Return Fee ×<?= $passengers ?></span> <span><?= $currencySymbol ?><?= number_format($returnPdfSgTotal, 2) ?></span></div>
        <?php endif; ?>
        
        <?php if ($returnPdfBtmTotal > 0): ?>
        <div class="price-line"><span>Return Fee ×<?= $passengers ?></span> <span><?= $currencySymbol ?><?= number_format($returnPdfBtmTotal, 2) ?></span></div>
        <?php endif; ?>
        
        <div class="price-line fw-bold mt-2 border-top pt-2">
            <span>Open Return Total</span> 
            <span><?= $currencySymbol ?><?= number_format($returnSubtotal, 2) ?></span>
        </div>
        <?php endif; ?>
        
        <!-- Summary Totals -->
        <div class="price-line mt-3 pt-3">
            <span>Ticket Subtotal</span>
            <span><?= $currencySymbol ?><?= number_format($subtotal, 2) ?></span>
        </div>
        
        <?php if (($departureSurchargeTotal + $returnSurchargeTotal) > 0): ?>
        <div class="price-line">
            <span><?= htmlspecialchars($surchargeName) ?></span> 
            <span><?= $currencySymbol ?><?= number_format($departureSurchargeTotal + $returnSurchargeTotal, 2) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if (($departurePdfSgTotal + $returnPdfSgTotal) > 0): ?>
        <div class="price-line">
            <span>Departure Fee</span> 
            <span><?= $currencySymbol ?><?= number_format($departurePdfSgTotal + $returnPdfSgTotal, 2) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if (($departurePdfBtmTotal + $returnPdfBtmTotal) > 0): ?>
        <div class="price-line">
            <span>Return Fee</span> 
            <span><?= $currencySymbol ?><?= number_format($departurePdfBtmTotal + $returnPdfBtmTotal, 2) ?></span>
        </div>
        <?php endif; ?>

<!-- Flexi-Trip row -->
<?php if ($flexiTripValue === 1 && $flexiTotalCost > 0): ?>
<div class="price-line">
    <span>Flexi-Trip Protection <i class="bi bi-shield-check text-success"></i></span>
    <span><?= $currencySymbol ?><?= number_format($flexiTotalCost, 2) ?></span>
</div>
<?php endif; ?>

<!-- API Addon rows -->
<?php foreach ($addonCodes as $code):
    $code = trim($code);
    if (empty($code)) continue;
    $addonLabel = $addonNameMap[$code] ?? ($code . ' Add-on');
    $owners     = $addonOwners[$code] ?? [];
    $paxCount   = count($owners);
?>
<div class="price-line">
    <span><?= htmlspecialchars($addonLabel) ?><?= $paxCount > 0 ? ' (' . $paxCount . ' pax)' : '' ?></span>
    <span>included</span>
</div>
<?php endforeach; ?>

<?php if ($bintanTourSelected): ?>
<div class="price-line">
    <span>Bintan Half-Day Tour</span>
    <span><?= $currencySymbol ?><?= number_format($bintanTourTotal, 2) ?></span>
</div>
<?php endif; ?>

<?php if ($addonTotalValue > 0): ?>
<div class="price-line">
    <span>Add-ons Total</span>
    <span><?= $currencySymbol ?><?= number_format($addonTotalValue, 2) ?></span>
</div>
<?php endif; ?>

<!-- Promo Discount -->
<?php if ($promoDiscount > 0): ?>
<div class="price-line text-success">
    <span>Promo Discount (<?= htmlspecialchars($_SESSION['promo_code'] ?? 'PROMO') ?>)</span>
    <span>- <?= $currencySymbol ?><?= number_format($promoDiscount, 2) ?></span>
</div>
<?php endif; ?>

<div class="price-line grand-total">
    <span>Grand Total:</span>
    <span><?= $currencySymbol ?><?= number_format($grandTotalAfterDiscount, 2) ?></span>
</div>
                    
                    <a href="hitpay-integration.php" class="btn btn-pay">Pay <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>

<!-- Payment Method Selection Section -->
<!--<div class="payment-method-section mt-5 p-5 bg-white shadow-sm" style="border-radius: 24px;">
    <h2 class="fw-bold mb-1" style="color: #06121f;">Select Payment Method</h2>
    <p class="text-muted mb-4">Choose your preferred payment method to securely complete your booking.</p>

    <div class="payment-methods">
        <div class="payment-card d-flex align-items-center mb-3 active" 
             style="cursor: pointer;" 
             onclick="selectPaymentMethod('paynow')" id="paynow-option">
            
            <div class="custom-radio me-3"></div>
            
            <div class="flex-grow-1">
                <p class="fw-bold mb-0" style="font-size: 1.1rem; color: #06121f;">PayNow</p>
                <small class="text-muted">Pay instantly with <span class="fw-bold" style="color: black;">PayNow QR</span></small>
            </div>

            <div class="logo-container">
                <img src="paynow-2.png" alt="PayNow" style="max-width: 100%; height: auto;">
            </div>
        </div>

        <div class="payment-card d-flex align-items-center mb-4" 
             style="cursor: pointer;" 
             onclick="selectPaymentMethod('card')" id="card-option">
            
            <div class="custom-radio me-3"></div>
            
            <div class="flex-grow-1">
                <p class="fw-bold mb-0" style="font-size: 1.1rem; color: #06121f;">Credit / Debit Card</p>
                <small class="text-muted">Visa, Mastercard, American Express, UnionPay, JCB & more</small>
            </div>

            <div class="logo-container">
                <img src="card-2.png" style="max-width: 100%; height: auto;" alt="Card">
            </div>
        </div>
    </div>
</div> -->

<!-- RESPONSIVE BUTTON SECTION (key part) -->
<div class="step-actions-wrapper">
    <a href="index.php" class="btn-back">
        <i class="bi bi-arrow-left-short fs-5"></i> Back to homepage
    </a>
    <button class="btn-continue" id="proceedPaymentBtn" onclick="redirectToHitPay()">
        Proceed to Payment <i class="bi bi-arrow-right-short fs-5"></i>
    </button>
</div>


<div id="customAlertModal" class="custom-modal-overlay" style="display: none;">
    <div class="custom-modal-card">
        <h2 id="modalTitle" class="modal-title">Attention</h2>
        <p id="modalDescription" class="modal-desc">Description goes here.</p>
        <div class="modal-footer">
            <button id="modalContinue" class="btn-continue">Continue</button>
        </div>
    </div>
</div>

<div class="vtl-separator"></div>


<?php require './assets/includes/footer.php'; ?>

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5SVD439S"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<script src="https://hit-pay.com/hitpay.js"></script>

<script>
// Direct redirect to HitPay checkout page
function redirectToHitPay() {
    const btn = document.getElementById('proceedPaymentBtn');
    btn.innerHTML = 'Redirecting to Payment Gateway...';
    btn.disabled = true;
    
    // Create payment request and get redirect URL
    fetch('create-hitpay-payment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            booking_id: 'BIN',
            amount: '<?= $grandTotalAfterDiscount ?>',
            currency: '<?= $currency ?>',
            email: '<?= $contactEmail ?>',
            name: '<?= $contactName ?>',
            phone: '<?= $contactPhone ?>',
            purpose: 'Bintan Ferry Tickets'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.checkout_url) {
            // Redirect to HitPay checkout page
            window.location.href = data.checkout_url;
        } else {
            throw new Error(data.message || 'Failed to create payment');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error creating payment: ' + error.message);
        btn.innerHTML = 'Proceed to Payment <i class="bi bi-arrow-right-short fs-5"></i>';
        btn.disabled = false;
    });
}

// Hide the old Pay button when page loads
document.addEventListener('DOMContentLoaded', function() {
    const existingPayBtn = document.querySelector('.btn-pay:not(#proceedPaymentBtn)');
    if (existingPayBtn) existingPayBtn.style.display = 'none';
});

// Promo code handling
const promoForm = document.getElementById('promoForm');
if (promoForm) {
    promoForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Applying...';
        
        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            window.location.reload();
        })
        .catch(error => {
            console.error('Error applying promo:', error);
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            alert('Failed to apply promo code. Please try again.');
        });
    });
}

// Clear promo error messages after 5 seconds
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        if (alert.textContent.includes('Invalid promo') || alert.textContent.includes('expired')) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }
    });
}, 5000);

// ============ FLEXI-TRIP TOGGLE FUNCTIONALITY ============
// This maintains the flexi selection if the user navigates back and forth

// Function to get flexi status from session (via AJAX)
function checkFlexiStatus() {
    fetch('check-flexi-status.php')
        .then(response => response.json())
        .then(data => {
            if (data.flexi_selected === 1) {
                // Update UI to show flexi is selected
                const flexiRow = document.getElementById('flexiPriceRow');
                if (flexiRow) flexiRow.style.display = 'flex';
                
                // Update grand total if needed
                const grandTotalSpan = document.getElementById('grandTotalAmount');
                if (grandTotalSpan && data.grand_total) {
                    grandTotalSpan.innerText = '<?= $currencySymbol ?>' + parseFloat(data.grand_total).toFixed(2);
                }
            }
        })
        .catch(error => console.error('Error checking flexi status:', error));
}

// Store flexi selection when page unloads (to persist through navigation)
window.addEventListener('beforeunload', function() {
    const flexiInput = document.getElementById('flexiTripHiddenInput');
    if (flexiInput && flexiInput.value === '1') {
        sessionStorage.setItem('flexi_trip_selected', '1');
    } else {
        sessionStorage.removeItem('flexi_trip_selected');
    }
});

// Check for flexi selection on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check if flexi was previously selected (via session storage)
    const flexiSelected = sessionStorage.getItem('flexi_trip_selected');
    if (flexiSelected === '1') {
        // You may want to create a hidden form to maintain this selection
        const form = document.querySelector('form');
        if (form) {
            let hiddenInput = document.getElementById('flexiTripHiddenInput');
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.id = 'flexiTripHiddenInput';
                hiddenInput.name = 'flexi_trip';
                hiddenInput.value = '1';
                form.appendChild(hiddenInput);
            }
        }
    }
    
    // Check via AJAX for server-side persistence
    checkFlexiStatus();
});

// If there's a "Back" button to add-on.php, ensure flexi status is passed
const backButton = document.querySelector('a[href="add-on.php"]');
if (backButton) {
    backButton.addEventListener('click', function(e) {
        const flexiInput = document.getElementById('flexiTripHiddenInput');
        if (flexiInput && flexiInput.value === '1') {
            // Store in localStorage before navigating back
            localStorage.setItem('flexi_trip_from_checking', '1');
        }
    });
}

// Check localStorage when coming back from checking page
const flexiFromChecking = localStorage.getItem('flexi_trip_from_checking');
if (flexiFromChecking === '1') {
    // This page was loaded after coming back from checking
    // The flexi selection should be maintained
    localStorage.removeItem('flexi_trip_from_checking');
    
    // Ensure the hidden input exists
    const existingForm = document.querySelector('form');
    if (existingForm) {
        let hiddenInput = document.getElementById('flexiTripHiddenInput');
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.id = 'flexiTripHiddenInput';
            hiddenInput.name = 'flexi_trip';
            hiddenInput.value = '1';
            existingForm.appendChild(hiddenInput);
        }
    }
}
</script>
<script>			var url = 'https://prod-crmb2b.s3.ap-southeast-1.amazonaws.com/widget/64c917df6f3bf83bd6d2b5b3/69954aba97bf000e7bb2baf4.js?v=' + Date.now();			var s = document.createElement('script');			s.type = 'text/javascript';			s.async = true;			s.src = url;			s.onload = function() {				CreateWhatsappChatWidget();			};			var x = document.getElementsByTagName('script')[0];			x.parentNode.insertBefore(s, x);		</script>

</body>
</html>