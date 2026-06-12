<?php
session_start();

function getApiToken() {
    // Check if token exists in session and is still valid (not expired)
    if (isset($_SESSION['api_token']) && isset($_SESSION['token_expiry']) && time() < $_SESSION['token_expiry']) {
        return $_SESSION['api_token'];
    }
    
    // If no valid token exists, fetch a new one
    $url = "http://apitest-koobysae.brf.com.sg/";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    
    // Use form-data for token request (NOT JSON)
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'password',
        'username'   => 'Koobysae',
        'password'   => '123456'
    ]));
    
    // Important: Set content type for form-data
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    curl_setopt($ch, CURLOPT_VERBOSE, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        error_log("API Token Error: " . $error_msg);
        return null;
    }
    
    curl_close($ch);
    
    if ($httpCode == 200 && $response) {
        $data = json_decode($response, true);
        
        if (isset($data['access_token'])) {
            $_SESSION['api_token'] = $data['access_token'];
            $expiresIn = isset($data['expires_in']) ? $data['expires_in'] : 1799;
            $_SESSION['token_expiry'] = time() + $expiresIn;
            
            error_log("Token obtained successfully. Expires in: " . $expiresIn . " seconds");
            return $data['access_token'];
        }
    }
    
    error_log("Failed to get API token. HTTP Code: " . $httpCode . " Response: " . $response);
    return null;
}

function fetchCountryList() {
    $token = getApiToken();
    if (!$token) return [];

    $url = "http://apitest-koobysae.brf.com.sg/api/GetCountryList/";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'Username' => 'vtapi',
            'Password' => 'Vtl123456'
        ]),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($response, true);

    if ($httpCode === 200 && isset($decoded['Status']) && $decoded['Status'] === 'Success') {
        return $decoded['Countries'] ?? [];
    }

    error_log("GetCountryList failed: " . $response);
    return [];
}

function renderCountryOptions($countries, $type = 'nationality') {
    $priorityCodes = ['SG', 'ID', 'MY'];
    
    // Add any country short codes you want to hide
    $excludedCodes = ['AD', 'AE', 'AG', 'AI', 'AN', 'AO', 'AS','AW','AX','AZ','BA','BB','BF', 'BH', 'BI','BJ','BL','BM', 'BS','BT', 'BV','BW','BY','BZ', 'CC','CD', 'CF','CG','CI','CK','CM','CV','CX','CY','CZ','DJ','DM','DO', 'DZ','EE','EH','ER','ET','FJ','FK','FM','FO','GA','GD','GE','GF','GG','GH','GM','GN','GP','GQ','GS','GT','GU','GW','GY','HM','HN','HT','IM','IO','JE','JO','KE','KG','KI','KM','KN','KW','KY','LC', 'LI','LS','LT','LU','MD','ME','MF','MH','ML','MP','MQ','MR','MS','MT','MU','MV','MW','MZ','NA','NC','NE','NF', 'NI','NR','NU','OT','PA','PF','PG','PM','PN','PR','PS', 'PW','RE','RW','SB','SC','SD','SH','SI','SJ','SK','SL','SM','SN','SO','ST','SV','SY','SZ','TC','TD','TF','TG','TJ','TK','TM','TN','TO','TT','TV','UM','VC','VG','VI','VU','WF','WS','XX','YE','YT','ZM',]; // Replace with actual codes e.g. ['KP', 'IR']
    
    $priorityLabels = [
        'SG' => ['nationality' => 'Singapore', 'country' => 'Singapore'],
        'ID' => ['nationality' => 'Indonesia',  'country' => 'Indonesia'],
        'MY' => ['nationality' => 'Malaysia',   'country' => 'Malaysia'],
    ];

    $label = $type === 'nationality' ? 'Nationality' : 'Country';
    $html  = '<option value="" selected disabled>Select ' . $label . '</option>';

    $html .= '<optgroup label="── Common ──">';
    foreach ($priorityCodes as $code) {
        if (in_array($code, $excludedCodes)) continue; // skip if excluded
        $display = $priorityLabels[$code][$type] ?? $priorityLabels[$code]['country'];
        $html .= '<option value="' . $code . '">' . htmlspecialchars($display) . '</option>';
    }
    $html .= '</optgroup>';

    $html .= '<optgroup label="── All Countries ──">';
    foreach ($countries as $country) {
        if (in_array($country['ShortCode'], $priorityCodes)) continue;
        if (in_array($country['ShortCode'], $excludedCodes)) continue; // skip if excluded
        $value   = htmlspecialchars($country['ShortCode']);
        $display = $type === 'nationality'
            ? htmlspecialchars(ucwords(strtolower($country['Nationality'])))
            : htmlspecialchars(ucwords(strtolower($country['CountryName'])));
        $html .= '<option value="' . $value . '">' . $display . '</option>';
    }
    $html .= '</optgroup>';

    return $html;
}

// Fetch and cache countries in session
if (!isset($_SESSION['country_list']) || empty($_SESSION['country_list'])) {
    $_SESSION['country_list'] = fetchCountryList();
}
$countries = $_SESSION['country_list'];

// Add this after getting search results and params
$tripType = $searchParams['trip_type'] ?? 'round_trip';
$isOpenTrip = ($tripType === 'open_trip');
$isRoundTrip = isset($searchResults['isRoundTrip']) && $searchResults['isRoundTrip'];

// Add this at the top of booking-review.php to debug
if (isset($_GET['debug'])) {
    echo "<pre>";
    echo "=== SESSION DATA ===\n";
    print_r($_SESSION);
    echo "=== POST DATA ===\n";
    print_r($_POST);
    
    // Test the booking data generation
    $testData = processBookingData();
    echo "=== GENERATED BOOKING DATA ===\n";
    print_r($testData);
    echo "=== JSON ENCODED ===\n";
    echo json_encode($testData, JSON_PRETTY_PRINT);
    echo "</pre>";
    exit;
}

// Check if trips are selected
if (!isset($_SESSION['selected_departure'])) {
    header('Location: search-result.php');
    exit;
}

// For round trips AND open trips, check if return is selected
// Open trips require a return route selected (but no specific return date)
if (($isRoundTrip || $isOpenTrip) && !isset($_SESSION['selected_return'])) {
    header('Location: search-result.php');
    exit;
}

// For one-way trips, only departure is needed
if ($tripType === 'one_way' && !isset($_SESSION['selected_departure'])) {
    header('Location: search-result.php');
    exit;
}

// Get selected trips from session
$selectedDeparture = $_SESSION['selected_departure'];
$selectedReturn = $_SESSION['selected_return'] ?? null;

// Get search results and params
$searchResults = $_SESSION['search_results'] ?? [];
$searchParams = $_SESSION['search_params'] ?? [];

// Get departure and return trips data
$departTrips = $searchResults['DepartTrips'] ?? [];

// Find selected departure trip by composite key (RouteCode_DepartureTime)
$selectedDepartureDetails = null;
foreach ($departTrips as $trip) {
    $compositeKey = ($trip['RouteCode'] ?? '') . '_' . ($trip['DepartureTime'] ?? '');
    if ($compositeKey == $selectedDeparture['trip_id']) {
        $selectedDepartureDetails = $trip;
        break;
    }
}

// Find selected return trip by composite key (RouteCode_ReturnTime)
$selectedReturnDetails = null;
if ($selectedReturn) {
    foreach ($departTrips as $trip) {
        $compositeKey = ($trip['RouteCode'] ?? '') . '_' . ($trip['ReturnTime'] ?? '');
        if ($compositeKey == $selectedReturn['trip_id']) {
            $selectedReturnDetails = $trip;
            break;
        }
    }
}

// Parse route names from search params
$depParts = explode('|', $searchParams['departure_route'] ?? '');
$retParts = explode('|', $searchParams['return_route'] ?? '');

// Get departure details
$departOperator  = $selectedDepartureDetails['ShipCode'] ?? 'Batam Fast';
$departOrigin    = $depParts[1] ?? 'Unknown';
$departDestination = $depParts[2] ?? 'Unknown';
$departDate      = $selectedDepartureDetails['DepartureDate'] ?? '';
$departTime      = $selectedDepartureDetails['DepartureTime'] ?? '';
$departTimeRegion = 'SGT';

// Get return details
if ($selectedReturnDetails) {
    $returnOperator    = $selectedReturnDetails['ShipCode'] ?? 'Batam Fast';
    $returnOrigin      = $retParts[1] ?? 'Unknown';
    $returnDestination = $retParts[2] ?? 'Unknown';
    $returnDate        = $selectedReturnDetails['ReturnDate'] ?? '';
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

// Calculate prices
$departurePrice = $selectedDeparture['price'] ?? 0;
$returnPrice = $selectedReturn['price'] ?? 0;
$passengers = $selectedDeparture['passengers'] ?? 1;
$currency = $selectedDeparture['currency'] ?? 'SGD';
$currencySymbol = $currency == 'SGD' ? '$' : 'Rp ';

// For open trips, double the departure price (it includes return)
if ($isOpenTrip) {
    $totalPrice = ($departurePrice * 2) * $passengers;
    $returnPrice = $departurePrice; // Return is included in the open ticket price
} else {
    $totalPrice = ($departurePrice + $returnPrice) * $passengers;
}

// Get operator logo function
function getOperatorLogo($vesselName) {
    $vesselName = strtolower($vesselName);
    if (strpos($vesselName, 'batam') !== false) {
        return 'batam fast.png';
    } elseif (strpos($vesselName, 'majestic') !== false) {
        return 'majestic ferry.png';
    } elseif (strpos($vesselName, 'horizon') !== false) {
        return 'horizon ferry.png';
    } elseif (strpos($vesselName, 'sindo') !== false) {
        return 'sindo ferry.png';
    }
    return 'logo-brf.webp'; // Default
}

// Get passenger counts from search params (default to adults if not specified)
$adultQty = $searchParams['adultQty'] ?? $passengers;
$childQty = $searchParams['childQty'] ?? 0;
$infantQty = $searchParams['infantQty'] ?? 0;
$totalPassengers = $adultQty + $childQty + $infantQty;
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
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

        /* Active HOME pill */
        .nav-center .nav-link.active {
            background-color: #fff;
            color: #000;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
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

        .card { border-radius: 16px; box-shadow: 0px 0px 16px 0px rgba(0, 0, 0, 0.08); }
        .login-banner { background-color: #ffe8d6; border-radius: 8px; padding: 15px; display: flex; align-items: center; justify-content: space-between; }
        .form-label { font-weight: 600; color: #555; margin-top: 15px; }
        .info-text { font-size: 0.85rem; color: #0d6efd; margin-top: 5px; }
        .operator-logo { width: 80px; height: auto; margin-bottom: 5px; }
        .details-section { font-size: 0.9rem; }
        .details-label { color: #888; margin-bottom: 0; }
        .details-value { font-weight: 500; margin-bottom: 15px; }
        /* Passenger Form Styling */
.passenger-form-card {
    border-radius: 12px;
    background: white;
}

.passenger-header {
    font-size: 1.25rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0;
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

.form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 6px;
    font-size: 0.9rem;
}

.form-control, .form-select {
    border: 1px solid rgba(5, 88, 142, 0.12)
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 0.95rem;
    color: rgba(38, 38, 38, 1);
    background-color: rgba(247, 248, 250, 1);
}

.form-control:focus, .form-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.form-control::placeholder {
    color: #999;
    font-size: 0.9rem;
}

.required-star {
    color: #dc3545;
    font-weight: bold;
}

/* Gender Toggle Styling */
.gender-toggle-group {
    display: flex;
    gap: 10px;
    margin-top: 6px;
}

.gender-toggle {
    flex: 1;
}

.gender-toggle .btn {
    width: 100%;
    border: 1px solid #dee2e6;
    color: #666;
    font-size: 0.9rem;
    padding: 8px 16px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    background: #F9FAFB;
}

.gender-toggle .btn-check:checked + .btn {
    background-color: #359DD7;
    color: white;
    border: 1px solid rgba(5, 88, 142, 0.12);
}

.gender-toggle .btn:hover {
    background-color: #f8f9fa;
}

/* Date Select Styling */
.date-select-group {
    display: flex;
    gap: 10px;
}

.date-select {
    flex: 1;
}

.date-select .form-select {
    width: 100%;
}

/* Form Section Spacing */
.form-section {
    margin-bottom: 20px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .date-select-group {
        flex-direction: column;
        gap: 8px;
    }
    
    .gender-toggle-group {
        flex-direction: column;
    }
    
    .passenger-header {
        font-size: 1.1rem;
    }
}

/* Field spacing */
.form-field {
    margin-bottom: 4px;
}

/* Additional styling for better match */
.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 20px;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}
        
        /* Container spacing if needed */
.d-flex { gap: 20px; }

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
      background-color: #359DD7;
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
        
        /* Custom Checkbox Styling */
        .custom-checkbox {
            width: 22px;
            height: 22px;
            background-color: #359DD7;
            border-color: #359DD7;
            cursor: pointer;
        }
        
        .custom-checkbox:checked {
            background-color: #359DD7;
            border-color: #359DD7;
        }
        
        /* Input Group Icon Alignment */
        .input-group-text {
            color: #6c757d;
            font-size: 1.2rem;
        }

        /* Changes the overall box color */
    .input-group-text, 
    .form-control {
        background-color: rgba(247, 248, 250, 1); /* Light grey/blue background */
        border: 1px solid rgba(5, 88, 142, 0.12);     /* Soft border color */
        color: rgba(117, 133, 153, 1);                      /* Text color */
    }
        
        /* Price Summary */
        .price-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }
        
        .price-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .price-total {
            font-weight: bold;
            font-size: 1.2rem;
            color: #e35a4f;
        }
        
        /* Ticket details */
        .ticket-details {
            background: #f8fff9;
            border: 1px solid #e8f5e9;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .change-selection {
            font-size: 0.85rem;
            color: #007bff;
            cursor: pointer;
            text-decoration: underline;
        }
        
        .change-selection:hover {
            text-decoration: none;
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

        /* Custom Nationality Field Styling */
.custom-nationality-container {
    transition: all 0.3s ease;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Custom Field Container Styling */
.custom-field-container {
    transition: all 0.3s ease;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Make sure the custom field has proper spacing */
.custom-field-container .form-label {
    margin-top: 0;
}

.custom-modal-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; z-index: 9999;
}
.custom-modal-card {
    background: #fff; padding: 40px; border-radius: 15px; width: 90%; max-width: 480px;
    text-align: center; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
.modal-title { font-weight: 700; font-size: 26px; margin-bottom: 15px; color: #000; }
.modal-desc { color: #333; font-size: 17px; margin-bottom: 30px; }
.modal-footer { display: flex; justify-content: flex-end; }
.btn-continue {
    background-color: #359DD7; color: white; border: none; padding: 12px 40px;
    border-radius: 30px; font-weight: 600; cursor: pointer; font-size: 18px;
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
            background: url(https://ferry.desaruteambuilding.com/illustration.png);
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

        .input-icon-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.field-icon {
    position: absolute;
    left: 12px; /* Adjust based on preference */
    color: #888; /* Icon color */
    pointer-events: none; /* Allows clicking "through" the icon to focus the input */
}

/* Apply padding to both inputs and selects */
.input-icon-wrapper .form-control,
.input-icon-wrapper .form-select {
    padding-left: 35px; 
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
$current_step = 3; 
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="badge-outline">Complete Your Ferry Booking</div>

        </div>
    </div>
</div>

    <div class="row g-4">
        <div class="col-lg-12">
            <form id="bookingForm" action="add-on.php" method="POST">
                <!-- Hidden fields to pass selected trip data -->
                <input type="hidden" name="departure_trip_id" value="<?= htmlspecialchars($selectedDeparture['trip_id']) ?>">
                <input type="hidden" name="departure_price" value="<?= $departurePrice ?>">
                <?php if ($selectedReturn): ?>
                <input type="hidden" name="return_trip_id" value="<?= htmlspecialchars($selectedReturn['trip_id']) ?>">
                <input type="hidden" name="return_price" value="<?= $returnPrice ?>">
                <?php endif; ?>
                <input type="hidden" name="passengers" value="<?= $passengers ?>">
                <input type="hidden" name="currency" value="<?= $currency ?>">
                <input type="hidden" name="adultQty" value="<?= $adultQty ?>">
                <input type="hidden" name="childQty" value="<?= $childQty ?>">
                <input type="hidden" name="infantQty" value="<?= $infantQty ?>">
                <input type="hidden" name="total_passengers" value="<?= $totalPassengers ?>">
                <!-- Add this with the other hidden fields -->
<input type="hidden" name="trip_type" value="<?= htmlspecialchars($tripType) ?>">
<input type="hidden" name="is_open_trip" value="<?= $isOpenTrip ? '1' : '0' ?>">
                
                  <div class="card p-0 mb-4">
                    <div class="p-4" style="background-color: #F7F8FA;">
        <h2 class="mb-0" style="color: #021320; font-size: 28px; font-weight: 700;">Booking Details</h2>
    </div>

                    <div class="p-4">

                    <label class="form-label">Name</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                        <input type="text" name="full_name" class="form-control" placeholder="Enter full name" required>
                    </div>

                    <label class="form-label">Phone Number</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white">🇸🇬 +65</span>
                        <input type="tel" name="phone" class="form-control" placeholder="Enter phone number" required>
                    </div>

                    <label class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter email address" required>
                    </div>
                </div> 
                </div>

               <!-- Passenger Forms -->
                <?php 
                $passengerCounter = 0;

                // Adult passengers
                for ($i = 1; $i <= $adultQty; $i++): 
                    $passengerCounter++;
                ?>
                <div class="card passenger-form-card p-0 mt-5">
                <div class="p-4" style="background-color: #F7F8FA;">
                 <h3 class="fw-bold mb-1">Booking Passenger Details</h3>
                <p class="text-muted mb-4" style="font-size: 0.9rem;">
                    Enter all passenger information as it appears on official travel documents. Incorrect or incomplete details may cause check-in or boarding issues.
                </p>
                </div>

                 <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="passenger-header">Passenger <?= $passengerCounter ?>
                            <span class="passenger-type-badge">Adult</span>
                        </h5>
                    </div>
                    
                        <div class="row g-3">
                        <input type="hidden" name="passenger_type_<?= $passengerCounter ?>" value="adult">
                        
                        <!-- Passport No. -->
                        <div class="col-md-6 form-field">
                            <label class="form-label">Passport No. <span class="required-star">*</span></label>
                            <div class="input-icon-wrapper">
                           <img src="passport.png" alt="document" class="field-icon"> <input type="text" name="passportNo_<?= $passengerCounter ?>" class="form-control" placeholder="Enter number" required>
                            </div>
                        </div>
                        
                        <!-- Nationality -->
                        <div class="col-md-6 form-field">
    <label class="form-label">Nationality <span class="required-star">*</span></label>
    
    <div class="input-icon-wrapper">
       <img src="globe.png" alt="globe" class="field-icon"> 
       <select class="form-select" name="nationalityId_<?= $passengerCounter ?>" required>
    <?= renderCountryOptions($countries, 'nationality') ?>
</select>
    </div>
</div>
                        
                        <!-- First Name -->
<div class="col-md-6 form-field">
    <label class="form-label">First Name (As In Passport) <span class="required-star">*</span></label>
    <div class="input-icon-wrapper">
        <img src="person.png" alt="person" class="field-icon">
        <input type="text" name="first_name_<?= $passengerCounter ?>" class="form-control" placeholder="Enter first name" maxlength="20" required>
    </div>
    <small class="text-muted">Max 20 characters</small>
</div>

<!-- Last Name -->
<div class="col-md-6 form-field">
    <label class="form-label">Last Name (As In Passport)</label>
    <div class="input-icon-wrapper">
        <img src="person.png" alt="person" class="field-icon">
        <input type="text" name="last_name_<?= $passengerCounter ?>" class="form-control" placeholder="Enter last name (optional)" maxlength="20">
    </div>
    <small class="text-muted">Max 20 characters</small>
</div>
                        
                        <!-- Date of Birth -->
                        <div class="col-md-6 form-field">
                            <label class="form-label">Date of Birth <span class="required-star">*</span></label>
                            <div class="date-select-group">
                                <div class="date-select">
                                    <select class="form-select" name="birthDate_day_<?= $passengerCounter ?>" required>
                                        <option value="" selected disabled>Day</option>
                                        <?php for ($day = 1; $day <= 31; $day++): ?>
                                        <option value="<?= sprintf('%02d', $day) ?>"><?= $day ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="date-select">
                                    <select class="form-select" name="birthDate_month_<?= $passengerCounter ?>" required>
                                        <option value="" selected disabled>Month</option>
                                        <?php 
                                        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                                        foreach ($months as $index => $month): 
                                        ?>
                                        <option value="<?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?>"><?= $month ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="date-select">
    <select class="form-select" name="birthDate_year_<?= $passengerCounter ?>" required>
        <option value="" selected disabled>Year</option>
        <?php 
        $currentYear = date('Y');
        $minYear = $currentYear - 100; // Oldest possible (100 years old)
        $maxYear = $currentYear - 12;   // Youngest allowed (12 years old)
        
        for ($year = $maxYear; $year >= $minYear; $year--): 
        ?>
        <option value="<?= $year ?>"><?= $year ?></option>
        <?php endfor; ?>
    </select>
</div>
                            </div>
                        </div>
                        
                        <!-- Gender -->
                        <div class="col-md-6 form-field">
                            <label class="form-label">Gender</label>
                            <div class="gender-toggle-group">
                                <div class="gender-toggle">
                                    <input type="radio" class="btn-check" name="gender_<?= $passengerCounter ?>" id="male_<?= $passengerCounter ?>" value="male" checked required>
                                    <label class="btn" for="male_<?= $passengerCounter ?>">
                                        <i class="bi bi-gender-male"></i> Male
                                    </label>
                                </div>
                                <div class="gender-toggle">
                                    <input type="radio" class="btn-check" name="gender_<?= $passengerCounter ?>" id="female_<?= $passengerCounter ?>" value="female" required>
                                    <label class="btn" for="female_<?= $passengerCounter ?>">
                                        <i class="bi bi-gender-female"></i> Female
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Country of Residence (Hidden - will use same as Passport Country of Issue) -->
                        <input type="hidden" name="countryResidenceId_<?= $passengerCounter ?>" value="">
                        
                        <!-- Place of Issuance (Hidden - will use same as Passport Country of Issue) -->
                        <input type="hidden" name="passportPlaceIssue_<?= $passengerCounter ?>" value="">
                        
                        <!-- Issuance Country (Passport Country of Issue) -->
                        <div class="col-md-6 form-field">
                            <label class="form-label">Place of Birth <span class="required-star">*</span></label>
                            <div class="input-icon-wrapper">
                            <img src="globe.png" alt="globe" class="field-icon"> 
                            <select class="form-select" name="placeOfBirth_<?= $passengerCounter ?>" required>
    <?= renderCountryOptions($countries, 'nationality') ?>
</select>
                            </div>
                        </div>
                        
                        <!-- Passport Expiry Date -->
                        <div class="col-md-6 form-field">
                            <label class="form-label">Passport Expiry Date <span class="required-star">*</span></label>
                            <div class="date-select-group">
                                <div class="date-select">
                                    <select class="form-select" name="passportExpiryDate_day_<?= $passengerCounter ?>" required>
                                        <option value="" selected disabled>Day</option>
                                        <?php for ($day = 1; $day <= 31; $day++): ?>
                                        <option value="<?= sprintf('%02d', $day) ?>"><?= $day ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="date-select">
                                    <select class="form-select" name="passportExpiryDate_month_<?= $passengerCounter ?>" required>
                                        <option value="" selected disabled>Month</option>
                                        <?php foreach ($months as $index => $month): ?>
                                        <option value="<?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?>"><?= $month ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="date-select">
                                    <select class="form-select" name="passportExpiryDate_year_<?= $passengerCounter ?>" required>
                                        <option value="" selected disabled>Year</option>
                                        <?php for ($year = date('Y') + 20; $year >= date('Y'); $year--): ?>
                                        <option value="<?= $year ?>"><?= $year ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>

                <?php
// Child passengers
for ($i = 1; $i <= $childQty; $i++): 
    $passengerCounter++;
    $passengerType = 'child';
?>
<div class="card passenger-form-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="passenger-header">Passenger <?= $passengerCounter ?>
            <span class="passenger-type-badge">Child</span>
        </h5>
    </div>
    
    <div class="row g-3">
        <input type="hidden" name="passenger_type_<?= $passengerCounter ?>" value="child">
        
        <!-- Passport No. -->
        <div class="col-md-6 form-field">
            <label class="form-label">Passport No. <span class="required-star">*</span></label>
            <input type="text" name="passportNo_<?= $passengerCounter ?>" class="form-control" placeholder="Enter number" required>
        </div>
        
        <!-- Nationality -->
        <div class="col-md-6 form-field">
            <label class="form-label">Nationality <span class="required-star">*</span></label>
            <select class="form-select" name="nationalityId_<?= $passengerCounter ?>" required>
    <?= renderCountryOptions($countries, 'nationality') ?>
</select>
        </div>
        
<!-- First Name -->
<div class="col-md-6 form-field">
    <label class="form-label">First Name (As In Passport) <span class="required-star">*</span></label>
    <div class="input-icon-wrapper">
        <img src="person.png" alt="person" class="field-icon">
        <input type="text" name="first_name_<?= $passengerCounter ?>" class="form-control" placeholder="Enter first name" maxlength="20" required>
    </div>
    <small class="text-muted">Max 20 characters</small>
</div>

<!-- Last Name -->
<div class="col-md-6 form-field">
    <label class="form-label">Last Name (As In Passport)</label>
    <div class="input-icon-wrapper">
        <img src="person.png" alt="person" class="field-icon">
        <input type="text" name="last_name_<?= $passengerCounter ?>" class="form-control" placeholder="Enter last name (optional)" maxlength="20">
    </div>
    <small class="text-muted">Max 20 characters</small>
</div>
        
        <!-- Date of Birth -->
        <div class="col-md-6 form-field">
            <label class="form-label">Date of Birth <span class="required-star">*</span></label>
            <div class="date-select-group">
                <div class="date-select">
                    <select class="form-select" name="birthDate_day_<?= $passengerCounter ?>" required>
                        <option value="" selected disabled>Day</option>
                        <?php for ($day = 1; $day <= 31; $day++): ?>
                        <option value="<?= sprintf('%02d', $day) ?>"><?= $day ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="date-select">
                    <select class="form-select" name="birthDate_month_<?= $passengerCounter ?>" required>
                        <option value="" selected disabled>Month</option>
                        <?php foreach ($months as $index => $month): ?>
                        <option value="<?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?>"><?= $month ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="date-select">
    <select class="form-select" name="birthDate_year_<?= $passengerCounter ?>" required>
        <option value="" selected disabled>Year</option>
        <?php 
        $currentYear = date('Y');
        $minYear = $currentYear - 12; // Youngest: 12 years old
        $maxYear = $currentYear - 0;   // Oldest: 0 years old
        
        for ($year = $maxYear; $year >= $minYear; $year--): 
        ?>
        <option value="<?= $year ?>"><?= $year ?></option>
        <?php endfor; ?>
    </select>
</div>
            </div>
        </div>
        
        <!-- Gender -->
        <div class="col-md-6 form-field">
            <label class="form-label">Gender</label>
            <div class="gender-toggle-group">
                <div class="gender-toggle">
                    <input type="radio" class="btn-check" name="gender_<?= $passengerCounter ?>" id="male_<?= $passengerCounter ?>" value="male" checked required>
                    <label class="btn" for="male_<?= $passengerCounter ?>">
                        <i class="bi bi-person"></i> Male
                    </label>
                </div>
                <div class="gender-toggle">
                    <input type="radio" class="btn-check" name="gender_<?= $passengerCounter ?>" id="female_<?= $passengerCounter ?>" value="female" required>
                    <label class="btn" for="female_<?= $passengerCounter ?>">
                        <i class="bi bi-person-arms-up"></i> Female
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Country of Residence (Hidden - will use same as Passport Country of Issue) -->
        <input type="hidden" name="countryResidenceId_<?= $passengerCounter ?>" value="">
        
        <!-- Place of Issuance (Hidden - will use same as Passport Country of Issue) -->
        <input type="hidden" name="passportPlaceIssue_<?= $passengerCounter ?>" value="">
        
        <!-- Issuance Country (Passport Country of Issue) -->
        <div class="col-md-6 form-field">
            <label class="form-label">Place of Birth <span class="required-star">*</span></label>
            <select class="form-select" name="placeOfBirth_<?= $passengerCounter ?>" required>
    <?= renderCountryOptions($countries, 'nationality') ?>
</select>
        </div>
        
        <!-- Passport Expiry Date -->
        <div class="col-md-6 form-field">
            <label class="form-label">Passport Expiry Date <span class="required-star">*</span></label>
            <div class="date-select-group">
                <div class="date-select">
                    <select class="form-select" name="passportExpiryDate_day_<?= $passengerCounter ?>" required>
                        <option value="" selected disabled>Day</option>
                        <?php for ($day = 1; $day <= 31; $day++): ?>
                        <option value="<?= sprintf('%02d', $day) ?>"><?= $day ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="date-select">
                    <select class="form-select" name="passportExpiryDate_month_<?= $passengerCounter ?>" required>
                        <option value="" selected disabled>Month</option>
                        <?php foreach ($months as $index => $month): ?>
                        <option value="<?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?>"><?= $month ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="date-select">
                    <select class="form-select" name="passportExpiryDate_year_<?= $passengerCounter ?>" required>
                        <option value="" selected disabled>Year</option>
                        <?php for ($year = date('Y') + 20; $year >= date('Y'); $year--): ?>
                        <option value="<?= $year ?>"><?= $year ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endfor; ?>

<?php
// Infant passengers
for ($i = 1; $i <= $infantQty; $i++): 
    $passengerCounter++;
    $passengerType = 'infant';
?>
<div class="card passenger-form-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="passenger-header">Passenger <?= $passengerCounter ?>
            <span class="passenger-type-badge">Infant</span>
        </h5>
    </div>
    
    <div class="row g-3">
        <input type="hidden" name="passenger_type_<?= $passengerCounter ?>" value="infant">
        
        <!-- Passport No. (Optional for infants, but good to have) -->
        <div class="col-md-6 form-field">
            <label class="form-label">Passport No. <span class="required-star">*</span></label>
            <input type="text" name="passportNo_<?= $passengerCounter ?>" class="form-control" placeholder="Enter number">
        </div>
        
        <!-- Nationality -->
        <div class="col-md-6 form-field">
            <label class="form-label">Nationality <span class="required-star">*</span></label>
            <select class="form-select" name="nationalityId_<?= $passengerCounter ?>" required>
    <?= renderCountryOptions($countries, 'nationality') ?>
</select>
        </div>
        
<!-- First Name -->
<div class="col-md-6 form-field">
    <label class="form-label">First Name (As In Passport) <span class="required-star">*</span></label>
    <div class="input-icon-wrapper">
        <img src="person.png" alt="person" class="field-icon">
        <input type="text" name="first_name_<?= $passengerCounter ?>" class="form-control" placeholder="Enter first name" maxlength="20" required>
    </div>
    <small class="text-muted">Max 20 characters</small>
</div>

<!-- Last Name -->
<div class="col-md-6 form-field">
    <label class="form-label">Last Name (As In Passport)</label>
    <div class="input-icon-wrapper">
        <img src="person.png" alt="person" class="field-icon">
        <input type="text" name="last_name_<?= $passengerCounter ?>" class="form-control" placeholder="Enter last name (optional)" maxlength="20">
    </div>
    <small class="text-muted">Max 20 characters</small>
</div>
        
        <!-- Date of Birth -->
        <div class="col-md-6 form-field">
            <label class="form-label">Date of Birth <span class="required-star">*</span></label>
            <div class="date-select-group">
                <div class="date-select">
                    <select class="form-select" name="birthDate_day_<?= $passengerCounter ?>" required>
                        <option value="" selected disabled>Day</option>
                        <?php for ($day = 1; $day <= 31; $day++): ?>
                        <option value="<?= sprintf('%02d', $day) ?>"><?= $day ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="date-select">
                    <select class="form-select" name="birthDate_month_<?= $passengerCounter ?>" required>
                        <option value="" selected disabled>Month</option>
                        <?php foreach ($months as $index => $month): ?>
                        <option value="<?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?>"><?= $month ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="date-select">
                    <select class="form-select" name="birthDate_year_<?= $passengerCounter ?>" required>
                        <option value="" selected disabled>Year</option>
                        <?php for ($year = date('Y'); $year >= date('Y') - 1; $year--): ?>
                        <option value="<?= $year ?>"><?= $year ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Gender -->
        <div class="col-md-6 form-field">
            <label class="form-label">Gender</label>
            <div class="gender-toggle-group">
                <div class="gender-toggle">
                    <input type="radio" class="btn-check" name="gender_<?= $passengerCounter ?>" id="male_<?= $passengerCounter ?>" value="male" checked required>
                    <label class="btn" for="male_<?= $passengerCounter ?>">
                        <i class="bi bi-person"></i> Male
                    </label>
                </div>
                <div class="gender-toggle">
                    <input type="radio" class="btn-check" name="gender_<?= $passengerCounter ?>" id="female_<?= $passengerCounter ?>" value="female" required>
                    <label class="btn" for="female_<?= $passengerCounter ?>">
                        <i class="bi bi-person-arms-up"></i> Female
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Country of Residence (Hidden - will use same as Passport Country of Issue) -->
        <input type="hidden" name="countryResidenceId_<?= $passengerCounter ?>" value="">
        
        <!-- Place of Issuance (Hidden - will use same as Passport Country of Issue) -->
        <input type="hidden" name="passportPlaceIssue_<?= $passengerCounter ?>" value="">
        
        <!-- Issuance Country (if passport available) -->
        <div class="col-md-6 form-field">
            <label class="form-label">Place of Birth <span class="required-star">*</span></label>
            <select class="form-select" name="placeOfBirth_<?= $passengerCounter ?>" required>
    <?= renderCountryOptions($countries, 'nationality') ?>
</select>
        </div>
        
        <!-- Passport Expiry Date -->
        <div class="col-md-6 form-field">
            <label class="form-label">Passport Expiry Date <span class="required-star">*</span></label>
            <div class="date-select-group">
                <div class="date-select">
                    <select class="form-select" name="passportExpiryDate_day_<?= $passengerCounter ?>" required>
                        <option value="" selected disabled>Day</option>
                        <?php for ($day = 1; $day <= 31; $day++): ?>
                        <option value="<?= sprintf('%02d', $day) ?>"><?= $day ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="date-select">
                    <select class="form-select" name="passportExpiryDate_month_<?= $passengerCounter ?>" required>
                        <option value="" selected disabled>Month</option>
                        <?php foreach ($months as $index => $month): ?>
                        <option value="<?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?>"><?= $month ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="date-select">
                    <select class="form-select" name="passportExpiryDate_year_<?= $passengerCounter ?>" required>
                        <option value="" selected disabled>Year</option>
                        <?php for ($year = date('Y') + 20; $year >= date('Y'); $year--): ?>
                        <option value="<?= $year ?>"><?= $year ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endfor; ?>

                <div class="mt-5 mb-5 pb-5">
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <input class="form-check-input flex-shrink-0 custom-checkbox" type="checkbox" id="confirmData" name="confirmData" required checked>
                        <label class="form-check-label" style="color: #758599; font-size: 12px;" for="confirmData">
                            I confirm that all passenger data entered here is accurate and every passenger holds a valid travelling document (min 6 months from travel date), entry, exit visa(s) and other required documents. I have checked and verified the data of every passenger and understand and accept the consequences for failing to comply with the requirements.
                            We will not seek compensation or refund from BRF in these cases.
                        </label>
                    </div>
                    
        <!-- RESPONSIVE BUTTON SECTION (key part) -->
        <div class="step-actions-wrapper">
          <a href="search-result.php" class="btn-back">
            <i class="bi bi-arrow-left-short fs-5"></i> Back to Previous Step
          </a>
          <button type="submit" class="btn-continue">
            Continue to Next Step <i class="bi bi-arrow-right-short fs-5"></i>
          </button>
        </div>
                </div>
            </form>
        </div>

        </div>
    </div>
</div>

<div class="container">

<div class="vtl-separator"></div>


<?php require './assets/includes/footer.php'; ?>

        <!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5SVD439S"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<div id="customAlertModal" class="custom-modal-overlay" style="display: none;">
    <div class="custom-modal-card">
        <h2 id="modalTitle" class="modal-title">Title Goes Here</h2>
        <p id="modalDescription" class="modal-desc">Description of the title goes here.</p>
        <div class="modal-footer">
            <button id="modalContinue" class="btn-continue">Continue</button>
        </div>
    </div>
</div>

<script>

// Add these modal variables at the top of your script if not already present
const modal = document.getElementById('customAlertModal');
const modalTitle = document.getElementById('modalTitle');
const modalDesc = document.getElementById('modalDescription');
const modalBtn = document.getElementById('modalContinue');

function showModal(title, message) {
    modalTitle.innerText = title;
    modalDesc.innerText = message;
    modal.style.display = 'flex';
}

document.getElementById('bookingForm').addEventListener('submit', function(e) {
    // Validate email match (commented out as per your code)
    // const email = document.getElementById('email').value;
    // const confirm = document.getElementById('confirm_email').value;
    
    // if (email !== confirm) {
    //     alert("Emails do not match!");
    //     e.preventDefault();
    //     return false;
    // }
    
    // Validate required checkbox
    const requiredCheckbox = document.getElementById('confirmData');
    if (!requiredCheckbox.checked) {
        alert("Please confirm that all passenger data is accurate.");
        e.preventDefault();
        return false;
    }
    
    // Validate all passenger fields
    const totalPassengers = <?= $totalPassengers ?>;
    let isValid = true;
    let firstInvalidField = null;
    
    for (let i = 1; i <= totalPassengers; i++) {

        const passengerType = document.querySelector(`input[name="passenger_type_${i}"]`);
        const isAdult = passengerType && passengerType.value === 'adult';
        
        // Check passport number
        const passportNo = document.querySelector(`input[name="passportNo_${i}"]`);
        if (passportNo && !passportNo.value.trim()) {
            alert(`Please enter passport number for Passenger ${i}`);
            firstInvalidField = passportNo;
            isValid = false;
            break;
        }
        
        // Check full name - must contain at least first and last name
        // Check first name
const firstName = document.querySelector(`input[name="first_name_${i}"]`);
if (firstName) {
    const nameValue = firstName.value.trim();
    if (!nameValue) {
        alert(`Please enter first name for Passenger ${i}`);
        firstInvalidField = firstName;
        isValid = false;
        break;
    }
    if (nameValue.length > 20) {
        alert(`First name for Passenger ${i} must not exceed 20 characters`);
        firstInvalidField = firstName;
        isValid = false;
        break;
    }
}

// Check last name (optional, but max 20 chars if provided)
const lastName = document.querySelector(`input[name="last_name_${i}"]`);
if (lastName && lastName.value.trim().length > 20) {
    alert(`Last name for Passenger ${i} must not exceed 20 characters`);
    firstInvalidField = lastName;
    isValid = false;
    break;
}
        
        // Check nationality
        const nationality = document.querySelector(`select[name="nationalityId_${i}"]`);
        if (nationality && !nationality.value) {
            alert(`Please select nationality for Passenger ${i}`);
            firstInvalidField = nationality;
            isValid = false;
            break;
        }
        
        // Check custom nationality if "Others" is selected
        if (nationality && nationality.value === 'OTHER') {
            const customNationality = document.querySelector(`input[name="custom_nationality_${i}"]`);
            if (!customNationality || !customNationality.value.trim()) {
                alert(`Please specify nationality for Passenger ${i}`);
                firstInvalidField = customNationality;
                isValid = false;
                break;
            }
        }
        
        // Check place of birth
const placeOfBirth = document.querySelector(`select[name="placeOfBirth_${i}"]`);
if (placeOfBirth && !placeOfBirth.value) {
    alert(`Please select place of birth for Passenger ${i}`);
    firstInvalidField = placeOfBirth;
    isValid = false;
    break;
}
        
        // Check date of birth

        const birthDay = document.querySelector(`select[name="birthDate_day_${i}"]`);

        const birthMonth = document.querySelector(`select[name="birthDate_month_${i}"]`);

        const birthYear = document.querySelector(`select[name="birthDate_year_${i}"]`);

       

        if (birthDay && birthMonth && birthYear) {

            if (!birthDay.value || !birthMonth.value || !birthYear.value) {

                alert(`Please select complete date of birth for Passenger ${i}`);

                firstInvalidField = birthDay;

                isValid = false;

                break;

            }

           

            // For adult passengers, validate age is 18 or above

            if (isAdult) {

                const birthDate = new Date(

                    parseInt(birthYear.value),

                    parseInt(birthMonth.value) - 1,

                    parseInt(birthDay.value)

                );

               

                const today = new Date();

                let age = today.getFullYear() - birthDate.getFullYear();

                const monthDiff = today.getMonth() - birthDate.getMonth();

               

                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {

                    age--;

                }

               

                if (age < 12) {

                    alert(`Passenger ${i} is marked as Adult but age (${age}) is under 18. Please correct the date of birth.`);

                    firstInvalidField = birthDay;

                    isValid = false;

                    break;

                }

               

                if (age > 120) {

                    alert(`Please verify the date of birth for Passenger ${i}. Age appears to be over 120 years.`);

                    firstInvalidField = birthDay;

                    isValid = false;

                    break;

                }

            }

            // For child passengers, validate age is between 2 and 17
if (passengerType === 'child') {
    const birthDate = new Date(
        parseInt(birthYear.value),
        parseInt(birthMonth.value) - 1,
        parseInt(birthDay.value)
    );
    
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    if (age < 0) {
        alert(`Passenger ${i} is marked as Child but age (${age}) is under 2. Please correct the date of birth or change passenger type.`);
        firstInvalidField = birthDay;
        isValid = false;
        break;
    }
    
    if (age > 12) {
        alert(`Passenger ${i} is marked as Child but age (${age}) is over 17. Please correct the date of birth or change passenger type.`);
        firstInvalidField = birthDay;
        isValid = false;
        break;
    }
}

// For infant passengers, validate age is under 2
if (passengerType === 'infant') {
    const birthDate = new Date(
        parseInt(birthYear.value),
        parseInt(birthMonth.value) - 1,
        parseInt(birthDay.value)
    );
    
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    if (age >= 2) {
        alert(`Passenger ${i} is marked as Infant but age (${age}) is 2 or above. Please correct the date of birth or change passenger type.`);
        firstInvalidField = birthDay;
        isValid = false;
        break;
    }
}

        }
    }
    
    if (!isValid && firstInvalidField) {
        e.preventDefault();
        firstInvalidField.focus();
        return false;
    }
    
    // If everything is valid, show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
    submitBtn.disabled = true;
    
    // Form will submit normally to booking-checking.php
});

// Email confirmation validation
// document.getElementById('confirm_email').addEventListener('blur', function() {
//     const email = document.getElementById('email').value;
//     const confirm = this.value;
    
//     if (email !== confirm && confirm !== '') {
//         this.classList.add('is-invalid');
//         const feedback = document.createElement('div');
//         feedback.className = 'invalid-feedback';
//         feedback.textContent = 'Email addresses do not match';
//         if (!this.nextElementSibling || !this.nextElementSibling.classList.contains('invalid-feedback')) {
//             this.parentNode.insertBefore(feedback, this.nextSibling);
//         }
//     } else {
//         this.classList.remove('is-invalid');
//         const feedback = this.nextElementSibling;
//         if (feedback && feedback.classList.contains('invalid-feedback')) {
//             feedback.remove();
//         }
//     }
// });

// Add CSS for invalid feedback
const style = document.createElement('style');
style.textContent = `
    .is-invalid {
        border-color: #dc3545 !important;
    }
    .is-invalid:focus {
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }
    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }
`;
document.head.appendChild(style);

// Add this to your existing JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Handle nationality "Others" selection for all passenger forms
    const nationalitySelects = document.querySelectorAll('select[name^="nationalityId_"]');
    
    nationalitySelects.forEach((select, index) => {
        // Create container for the custom nationality input
        const passengerIndex = index + 1;
        const formRow = select.closest('.row');
        
        // Create the custom input field (initially hidden)
        const customNationalityDiv = document.createElement('div');
        customNationalityDiv.className = 'col-md-12 form-field mt-2 custom-nationality-container';
        customNationalityDiv.id = `custom_nationality_container_${passengerIndex}`;
        customNationalityDiv.style.display = 'none';
        
        customNationalityDiv.innerHTML = `
            <label class="form-label">Specify Nationality <span class="required-star">*</span></label>
            <input type="text" 
                   class="form-control" 
                   name="custom_nationality_${passengerIndex}" 
                   id="custom_nationality_${passengerIndex}"
                   placeholder="Enter nationality">
        `;
        
        // Insert the custom field after the nationality select's parent div
        const nationalityCol = select.closest('.col-md-6');
        nationalityCol.parentNode.insertBefore(customNationalityDiv, nationalityCol.nextSibling);
        
        // Add change event listener
        select.addEventListener('change', function() {
            const container = document.getElementById(`custom_nationality_container_${passengerIndex}`);
            const customInput = document.getElementById(`custom_nationality_${passengerIndex}`);
            
            if (this.value === 'OTHER') {
                container.style.display = 'block';
                customInput.setAttribute('required', 'required');
            } else {
                container.style.display = 'none';
                customInput.removeAttribute('required');
                customInput.value = ''; // Clear the value when hidden
            }
        });
    });
});

// Add this to your existing JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const totalPassengers = <?= $totalPassengers ?>;
    
    for (let i = 1; i <= totalPassengers; i++) {
        // Handle Nationality "Others" selection
        setupCustomField(i, 'nationality');
    }
    
    function setupCustomField(passengerIndex, fieldType) {
        const selectElement = document.querySelector(`select[name="${fieldType}_${passengerIndex}"]`);
        if (!selectElement) return;
        
        // Create container for the custom input
        const formRow = selectElement.closest('.row');
        const fieldCol = selectElement.closest('.col-md-6, .col-md-12');
        
        // Create the custom input field (initially hidden)
        const customFieldDiv = document.createElement('div');
        customFieldDiv.className = fieldCol.classList.contains('col-md-6') ? 'col-md-12 form-field mt-2 custom-field-container' : 'col-md-12 form-field mt-2 custom-field-container';
        customFieldDiv.id = `custom_${fieldType}_container_${passengerIndex}`;
        customFieldDiv.style.display = 'none';
        
        const fieldLabel = fieldType === 'nationality' ? 'Specify Nationality' : 'Specify Issuance Country';
        const fieldName = fieldType === 'nationality' ? 'custom_nationality' : 'custom_issuance_country';
        
        customFieldDiv.innerHTML = `
            <label class="form-label">${fieldLabel} <span class="required-star">*</span></label>
            <input type="text" 
                   class="form-control" 
                   name="${fieldName}_${passengerIndex}" 
                   id="${fieldName}_${passengerIndex}"
                   placeholder="Enter ${fieldLabel.toLowerCase()}">
        `;
        
        // Insert the custom field after the select's parent div
        fieldCol.parentNode.insertBefore(customFieldDiv, fieldCol.nextSibling);
        
        // Add change event listener
        selectElement.addEventListener('change', function() {
            const container = document.getElementById(`custom_${fieldType}_container_${passengerIndex}`);
            const customInput = document.getElementById(`${fieldName}_${passengerIndex}`);
            
            if (this.value === 'OTHER') {
                container.style.display = 'block';
                customInput.setAttribute('required', 'required');
                
                // Add animation
                container.style.animation = 'slideDown 0.3s ease';
            } else {
                container.style.display = 'none';
                customInput.removeAttribute('required');
                customInput.value = ''; // Clear the value when hidden
            }
        });
        
        // Check if already selected OTHER (for form validation after page reload)
        if (selectElement.value === 'OTHER') {
            const container = document.getElementById(`custom_${fieldType}_container_${passengerIndex}`);
            const customInput = document.getElementById(`${fieldName}_${passengerIndex}`);
            if (container) {
                container.style.display = 'block';
                customInput.setAttribute('required', 'required');
            }
        }
    }
});
</script>
<script>			var url = 'https://prod-crmb2b.s3.ap-southeast-1.amazonaws.com/widget/64c917df6f3bf83bd6d2b5b3/69954aba97bf000e7bb2baf4.js?v=' + Date.now();			var s = document.createElement('script');			s.type = 'text/javascript';			s.async = true;			s.src = url;			s.onload = function() {				CreateWhatsappChatWidget();			};			var x = document.getElementsByTagName('script')[0];			x.parentNode.insertBefore(s, x);		</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</body>
</html>