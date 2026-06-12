<?php
// add-on.php - UPDATED FIX

session_start();

function getApiTokenForAddon() {
    if (isset($_SESSION['api_token']) && isset($_SESSION['token_expiry']) && time() < $_SESSION['token_expiry']) {
        return $_SESSION['api_token'];
    }
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "http://apitest-koobysae.brf.com.sg/token",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'grant_type' => 'password',
            'username'   => 'Koobysae',
            'password'   => '123456'
        ]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT => 20
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        if (isset($data['access_token'])) {
            $_SESSION['api_token']    = $data['access_token'];
            $_SESSION['token_expiry'] = time() + ($data['expires_in'] ?? 1799);
            return $data['access_token'];
        }
    }
    return null;
}

function fetchAddonResources($routeCode, $departureDate, $departureTime) {
    $token = getApiTokenForAddon();
    if (!$token) return [];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "http://apitest-koobysae.brf.com.sg/api/GetAddonResources/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'Username'      => 'vtapi',
            'Password'      => 'Vtl123456',
            'Route'         => $routeCode,
            'DepartureDate' => $departureDate,
            'DepartureTime' => $departureTime
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
        return $decoded['ResourceList'] ?? [];
    }
    error_log("GetAddonResources failed (HTTP $httpCode): " . $response);
    return [];
}

$tripType = $searchParams['trip_type'] ?? 'round_trip';
$isOpenTrip = ($tripType === 'open_trip');
$isRoundTrip = isset($searchResults['isRoundTrip']) && $searchResults['isRoundTrip'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    $_SESSION['booking_form_data'] = $_POST;
    $_SESSION['contact_name'] = $_POST['full_name'] ?? '';
    $_SESSION['contact_email'] = $_POST['email'] ?? '';
    $_SESSION['contact_phone'] = $_POST['phone'] ?? '';
    error_log("=== ADD-ON.PHP: Stored booking form data ===");
    error_log("Passenger count: " . (isset($_POST['total_passengers']) ? $_POST['total_passengers'] : 'not set'));
    error_log("Full name: " . ($_POST['full_name'] ?? 'not set'));
    error_log("Email: " . ($_POST['email'] ?? 'not set'));
    for ($i = 1; $i <= 10; $i++) {
        if (isset($_POST["name_$i"])) {
            error_log("Passenger $i name: " . $_POST["name_$i"]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['confirm']) && $_GET['confirm'] == 1) {
    if (!isset($_SESSION['booking_form_data']) || empty($_SESSION['booking_form_data'])) {
        error_log("ERROR: No booking form data in session - redirecting back to booking-review");
        header('Location: booking-review.php?error=missing_data');
        exit;
    }
    header('Location: booking-checking.php');
    exit;
}

if (($isRoundTrip || $isOpenTrip) && !isset($_SESSION['selected_return'])) {
    header('Location: search-result.php');
    exit;
}

if ($tripType === 'one_way' && !isset($_SESSION['selected_departure'])) {
    header('Location: search-result.php');
    exit;
}

$selectedDeparture = $_SESSION['selected_departure'];
$selectedReturn = $_SESSION['selected_return'] ?? null;

$searchResults = $_SESSION['search_results'] ?? [];
$searchParams = $_SESSION['search_params'] ?? [];

$departTrips = $searchResults['departTrips'] ?? [];
$returnTrips = $searchResults['returnTrips'] ?? [];

$departTripsList = $searchResults['DepartTrips'] ?? $searchResults['departTrips'] ?? [];

$selectedDepartureDetails = null;
foreach ($departTripsList as $trip) {
    $compositeKey = ($trip['RouteCode'] ?? '') . '_' . ($trip['DepartureTime'] ?? '');
    if ($compositeKey == ($selectedDeparture['trip_id'] ?? '')) {
        $selectedDepartureDetails = $trip;
        break;
    }
}

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

function formatDateDisplay($dateStr) {
    if (empty($dateStr)) return 'Not set';
    return date('j M Y', strtotime($dateStr));
}

function formatTimeDisplay($timeStr) {
    if (empty($timeStr)) return '';
    return date('H:i', strtotime($timeStr));
}

$departurePrice = $selectedDeparture['price'] ?? 0;
$returnPrice = $selectedReturn['price'] ?? 0;
$passengers = $selectedDeparture['passengers'] ?? 1;
$currency = $selectedDeparture['currency'] ?? 'SGD';
$currencySymbol = $currency == 'SGD' ? '$' : 'Rp ';

$totalPrice = ($departurePrice + $returnPrice) * $passengers;

function getOperatorLogo($vesselName) {
    $vesselName = strtolower($vesselName);
    if (strpos($vesselName, 'batam') !== false) return 'batam fast.png';
    elseif (strpos($vesselName, 'majestic') !== false) return 'majestic ferry.png';
    elseif (strpos($vesselName, 'horizon') !== false) return 'horizon ferry.png';
    elseif (strpos($vesselName, 'sindo') !== false) return 'sindo ferry.png';
    return 'batam fast.png';
}

$depParts = explode('|', $searchParams['departure_route'] ?? '');
$retParts = explode('|', $searchParams['return_route'] ?? '');

$departOperator    = $selectedDepartureDetails['ShipCode'] ?? 'Batam Fast';
$departOrigin      = $depParts[1] ?? 'Unknown';
$departDestination = $depParts[2] ?? 'Unknown';
$departDate        = $selectedDepartureDetails['DepartureDate'] ?? '';
$departTime        = $selectedDepartureDetails['DepartureTime'] ?? '';
$departTimeRegion  = 'SGT';

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

$adultQty = $searchParams['adultQty'] ?? $passengers;
$childQty = $searchParams['childQty'] ?? 0;
$infantQty = $searchParams['infantQty'] ?? 0;
$totalPassengers = $adultQty + $childQty + $infantQty;

$tripType = $_SESSION['search_params']['trip_type'] ?? 'round_trip';
$passengers = $selectedDeparture['passengers'] ?? 1;
$currency = $selectedDeparture['currency'] ?? 'SGD';
$currencySymbol = $currency == 'SGD' ? 'S$' : 'Rp ';

$departTripsList = $searchResults['DepartTrips'] ?? $searchResults['departTrips'] ?? [];

$selectedDepartureForAddon = null;
foreach ($departTripsList as $trip) {
    $compositeKey = ($trip['RouteCode'] ?? '') . '_' . ($trip['DepartureTime'] ?? '');
    if ($compositeKey == ($selectedDeparture['trip_id'] ?? '')) {
        $selectedDepartureForAddon = $trip;
        break;
    }
}

$selectedReturnForAddon = null;
if ($selectedReturn) {
    foreach ($departTripsList as $trip) {
        $compositeKey = ($trip['RouteCode'] ?? '') . '_' . ($trip['ReturnTime'] ?? '');
        if ($compositeKey == ($selectedReturn['trip_id'] ?? '')) {
            $selectedReturnForAddon = $trip;
            break;
        }
    }
}

$adultFare = floatval($selectedDepartureForAddon['AdultPrice'] ?? 0);
$fee1      = floatval($selectedDepartureForAddon['Surcharge'] ?? 0);
$fee2      = 0;
$fee3      = 0;

$departureBasePrice      = $adultFare;
$departureSurcharge      = $fee1;
$departurePdfSg          = $fee2;
$departurePdfBtm         = $fee3;
$returnAdultFareAddon    = floatval($selectedDepartureForAddon['ChildPrice'] ?? $adultFare);
$departureTicketTotal    = (($adultFare + 6)* $adultQty) + (($returnAdultFareAddon + 6) * $childQty);
$departureSurchargeTotal = $departureSurcharge * $passengers;
$departurePdfSgTotal     = $departurePdfSg * $passengers;
$departurePdfBtmTotal    = $departurePdfBtm * $passengers;
$departureSubtotal       = $departureTicketTotal + $departureSurchargeTotal + $departurePdfSgTotal + $departurePdfBtmTotal;

$surchargeName = 'Surcharge';
$pdfSgName     = 'Departure Fee';
$pdfBtmName    = 'Return Fee';

$returnAdultFare         = floatval($selectedReturnForAddon['AdultPrice'] ?? 0);
$returnSurcharge         = floatval($selectedReturnForAddon['Surcharge'] ?? 0);
$returnBasePrice         = 0;
$returnSurchargeTotal    = 0;
$returnPdfSgTotal        = 0;
$returnPdfBtmTotal       = 0;
$returnTicketTotal       = 0;
$returnSubtotal          = 0;

if ($tripType === 'round_trip' && $selectedReturn) {
    $returnBasePrice      = $returnAdultFare;
    $returnChildFare      = floatval($selectedReturnForAddon['ChildPrice'] ?? $returnAdultFare);
    $returnTicketTotal    = ($returnBasePrice * $adultQty) + ($returnChildFare * $childQty);
    $returnSurchargeTotal = $returnSurcharge * $passengers;
    $returnSubtotal       = $returnTicketTotal + $returnSurchargeTotal;
} elseif ($tripType === 'open_trip') {
    $returnBasePrice      = $adultFare;
    $returnChildFare      = floatval($selectedReturnForAddon['ChildPrice'] ?? $returnAdultFare);
    $returnTicketTotal    = ($returnBasePrice * $adultQty) + ($returnChildFare * $childQty);
    $returnSurchargeTotal = $fee1 * $passengers;
    $returnSubtotal       = $returnTicketTotal + $returnSurchargeTotal;
}

$subtotal    = $departureTicketTotal + $returnTicketTotal;
$totalFees   = $departureSurchargeTotal + $departurePdfSgTotal + $departurePdfBtmTotal
             + $returnSurchargeTotal + $returnPdfSgTotal + $returnPdfBtmTotal;
$grandTotal  = $departureSubtotal;

$grandTotalAfterDiscount = max(0, $grandTotal);

// After your existing departure addon fetch
$addonResources = [];
$returnAddonResources = [];

if ($selectedDepartureForAddon) {
    $addonRouteCode     = $selectedDepartureForAddon['RouteCode'] ?? '';
    $addonDepartureDate = $selectedDepartureForAddon['DepartureDate'] ?? '';
    $addonDepartureTime = $selectedDepartureForAddon['DepartureTime'] ?? '';

    if ($addonRouteCode && $addonDepartureDate && $addonDepartureTime) {
        $addonResources = fetchAddonResources($addonRouteCode, $addonDepartureDate, $addonDepartureTime);
        
        // Store departure leg details for payment-success.php to use
        $_SESSION['addon_dep_route'] = $addonRouteCode;
        $_SESSION['addon_dep_date']  = $addonDepartureDate;
        $_SESSION['addon_dep_time']  = $addonDepartureTime;
    }
}

// NEW: Fetch return leg add-on resources
$isRound = in_array($tripType, ['round_trip', 'open_trip']);

if ($isRound && $selectedReturnForAddon) {
    $retParts        = explode('|', $searchParams['return_route'] ?? '');
    $returnRouteCode = $retParts[0] ?? '';
    $returnDate      = $searchParams['return_date'] 
                    ?? ($selectedReturnForAddon['ReturnDate'] ?? '');
    $returnTime      = $selectedReturnForAddon['ReturnTime'] ?? '';

    if ($returnRouteCode && $returnDate && $returnTime) {
        $returnAddonResources = fetchAddonResources(
            $returnRouteCode,
            substr($returnDate, 0, 10),
            $returnTime
        );

        // Store return leg details for payment-success.php
        $_SESSION['addon_ret_route'] = $returnRouteCode;
        $_SESSION['addon_ret_date']  = substr($returnDate, 0, 10);
        $_SESSION['addon_ret_time']  = $returnTime;

        error_log("Return addon resources fetched: " . json_encode($returnAddonResources));
    }
}

$_SESSION['return_addon_resources'] = $returnAddonResources;

$resourceNames = [
    'BIC' => 'Bicycle Add-on',
    'SFB' => 'Surfboard Add-on',
];

$resourceDetails = [
    'BIC' => [
        'subtitle'   => 'Bring Your Bicycle Onboard',
        'promo_text' => 'Travelling with your bicycle? Add your bicycle to your booking!',
        'why_title'  => 'Why Add a Bicycle?',
        'benefits'   => [
            'Convenient: No need to leave your bicycle behind — bring it along for the ride.',
            'Explore Freely: Cycle around Bintan at your own pace without renting.',
            'Easy Process: Simply show your e-ticket at the check-in counter and we\'ll handle the rest.',
        ],
        'note' => 'Applicable to All Bicycles except Recumbent Bicycles. Please secure bicycles in box or bubble-wrapped to prevent damages. BRF will not be liable for unprotected bicycles.',
        'image' => 'assets/images/bicycle.jpeg',
        'category' => 'equipment',
        'icon' => '🚲',
    ],
    'SFB' => [
        'subtitle'   => 'Bring Your Surfboard Onboard',
        'promo_text' => 'Heading to Bintan for a surf session? Add your surfboard to your booking!',
        'why_title'  => 'Why Add a Surfboard?',
        'benefits'   => [
            'No Rental Hassle: Use your own board and enjoy the waves on your terms.',
            'Safe Transport: Your board is handled with care during the ferry journey.',
            'Simple Add-on: Just include it at checkout — no extra paperwork needed.',
        ],
        'note' => 'Surfboards cannot be more than 1.5m in length. Above 1.5m, separate charges apply. Please send queries to helpdesk@brf.com.sg. Vouchers will be issued to you upon collection of boarding passes for verification at Baggage Check-in.',
        'image' => 'assets/images/surfboard.jpeg',
        'category' => 'equipment',
        'icon' => '🏄',
    ],
];
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
    <title>Bintan Ferry Tickets - Add Ons</title>
    <meta name="description" content="Contact Bintan Ferry Tickets for online ferry booking assistance, bintan fast ferry schedule information, and customer support.">
    <meta name="keywords" content="contact bintan ferry tickets, bintan ferry customer service, bintan ferry ticket assistance">
    <meta name="author" content="VTL Travel">
    <meta name="robots" content="index, follow">
    <link rel="icon" type="image/x-icon" href="capital-favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* ===================== BASE / EXISTING STYLES ===================== */
        body { font-family: 'Inter', sans-serif; }
        .custom-navbar { background: linear-gradient(white); padding: 12px 0; box-shadow: 0px 4px 20px 0px rgba(0,0,0,0.08); }
        .navbar-brand { font-size: 22px; font-weight: 700; color: #111; }
        .nav-center .nav-link { font-size: 14px; font-weight: 600; color: #333; padding: 6px 14px; border-radius: 20px; transition: all 0.2s ease; }
        .nav-center .nav-link.active { background-color: #fff; color: #000; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        .nav-center .nav-link:hover { background-color: rgba(255,255,255,0.6); }
        .btn-book-tickets { background-color: #f85a40; color: white; border-radius: 30px; padding: 10px 25px; font-weight: 600; font-size: 14px; border: none; transition: background-color 0.2s; }
        .btn-book-tickets:hover { background-color: #e64a2e; color: white; }
        @media (max-width: 991px) {
            .nav-center .nav-link.active { color: #f85a40; background: transparent; }
            .d-flex.align-items-center.gap-3 { margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee; justify-content: center; }
        }
        .nav-logo { height: 55px; width: auto; display: block; }
        @media (max-width: 768px) { .nav-logo { height: 40px; } }
        .promo-banner-fixed { background-color: #E9F2FF; color: #333; display: flex; align-items: center; justify-content: center; gap: 20px; padding: 12px 20px; font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 400; width: 100%; top: 0; left: 0; z-index: 1041; }
        .promo-btn { background-color: #00A68F; color: white; text-decoration: none; padding: 8px 24px; border-radius: 6px; font-weight: 600; font-size: 14px; transition: background-color 0.2s; }
        .promo-btn:hover { background-color: #008f7a; }
        .nav-link.dropdown-toggle::after { vertical-align: middle; color: #758599; }
        .dropdown-item:hover { background-color: #fff1f0; color: #359DD7 !important; }
        .dropdown-menu { border-radius: 12px; padding: 10px 0; margin-top: 0; }
        @media (max-width: 991px) { .dropdown-menu { background-color: #f8fafc; border: none; box-shadow: none !important; padding-left: 20px; } }
        @media (min-width: 992px) { .nav-item.dropdown:hover .dropdown-menu { display: block; margin-top: 0; } }
        .card { border-radius: 16px; box-shadow: 0px 0px 16px 0px rgba(0,0,0,0.08); }
        .badge-outline { border: 1px solid #94a3b8; color: #64748b; border-radius: 20px; padding: 5px 15px; font-size: 0.85rem; display: inline-block; margin-bottom: 1rem; }
        .main-heading { font-weight: 700; font-size: 48px; margin-bottom: 0.5rem; }
        .sub-heading { color: #758599; font-size: 16px; font-weight: 400; margin-bottom: 3rem; }
        .stepper-wrapper { display: flex; justify-content: space-between; position: relative; margin-bottom: 20px; }
        .stepper-wrapper::before { content: ""; position: absolute; top: 25px; left: 5%; right: 5%; height: 2px; border-top: 1px dashed #758599; z-index: 0; }
        .step-item { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: flex-start; flex: 1; }
        .step-icon { width: 36px; height: 36px; border-radius: 50%; background: white; border: 2px solid #cbd5e1; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; transition: all 0.3s ease; color: #64748b; }
        .step-item.completed .step-icon { background-color: #359DD7; border-color: #359DD7; color: white; box-shadow: 0 0 0 4px rgba(53,157,215,0.2); }
        .step-label-top { text-transform: uppercase; font-size: 12px; font-weight: 500; color: #758599; margin-bottom: 4px; }
        .step-label-bottom { font-weight: 600; font-size: 16px; color: #021320; }
        .custom-breadcrumb { display: flex; flex-wrap: wrap; padding: 0; list-style: none; font-size: 16px; }
        .custom-breadcrumb .breadcrumb-item { display: flex; align-items: center; }
        .custom-breadcrumb .breadcrumb-item a { color: #7d8ea1; text-decoration: none; transition: color 0.2s; }
        .custom-breadcrumb .breadcrumb-item + .breadcrumb-item::before { display: inline-block; padding-right: 12px; padding-left: 12px; color: #758599; content: "/"; font-weight: 600; }
        .custom-breadcrumb .breadcrumb-item.active { color: #359DD7; font-weight: 600; }
        .custom-breadcrumb .breadcrumb-item a:hover { color: #e6554d; }
        .btn-back, .btn-continue { border-radius: 60px; font-weight: 700; font-size: 1rem; display: inline-flex; align-items: center; justify-content: center; gap: 12px; transition: all 0.25s ease; padding: 12px 28px; border: none; cursor: pointer; background: transparent; text-decoration: none; }
        .btn-back { background-color: transparent; color: #1e2a3a; border: 1.5px solid #1e2a3a; }
        .btn-back:hover { background-color: #f1f5f9; color: #0f172a; transform: translateY(-2px); }
        .btn-continue { background-color: #359DD7; color: #ffffff; box-shadow: 0 4px 8px rgba(53,157,215,0.3); }
        .btn-continue:hover { background-color: #2a8abf; transform: translateY(-2px); }
        .step-actions-wrapper { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-top: 2rem; margin-bottom: 2rem; }
        @media (max-width: 576px) {
            .step-actions-wrapper { flex-direction: column; gap: 14px; }
            .btn-back, .btn-continue { width: 100%; justify-content: center; padding: 14px 20px; }
        }

        /* Summary sidebar */
        .summary-card { border-radius: 12px; border: 1px solid #ddd; background: white; margin-top: 15px; }
        .summary-header { background: black; color: white; padding: 10px 15px; border-radius: 12px 12px 0 0; }
        .price-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.95rem; }
        .price-line { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem; }
        .grand-total { font-size: 1.2rem; font-weight: 800; border-top: 1px solid #eee; padding-top: 15px; margin-top: 15px; }
        .d-flex { gap: 20px; }

        /* Footer */
        .vtl-support-banner { background-color: #021320; border-radius: 12px; color: #ffffff; padding: 50px 40px; margin: 20px auto 50px auto; position: relative; overflow: hidden; }
        .vtl-banner-content { z-index: 2; position: relative; }
        .vtl-support-text { max-width: 60%; }
        .vtl-banner-title { font-size: 28px; font-weight: 700; margin-bottom: 12px; }
        .vtl-banner-desc { font-size: 14px; line-height: 1.6; margin-bottom: 25px; color: white; }
        .vtl-contact-btn { background-color: #359DD7; border: none; border-radius: 20px; color: #ffffff; font-size: 14px; font-weight: 600; padding: 10px 30px; transition: background-color 0.2s; }
        .vtl-contact-btn:hover { background-color: #d1493b; }
        .vtl-banner-illustration { position: absolute; left: 50px; bottom: 0px; z-index: 1; }
        .vtl-ill-placeholder { position: absolute; left: 50px; bottom: 0px; width: 330px; height: 248px; background: url(https://ferry.desaruteambuilding.com/illustration.png); }
        footer.vtl-footer { padding: 0; }
        .vtl-footer-logo-area { margin-bottom: 30px; }
        .vtl-footer-logo { max-width: 150px; height: auto; }
        .vtl-payment-icons img { height: 18px; margin-right: 15px; opacity: 0.8; }
        .vtl-footer-column h6 { font-size: 16px; font-weight: 700; color: #333333; margin-bottom: 20px; }
        .vtl-footer-column ul { list-style: none; padding: 0; margin: 0; }
        .vtl-footer-column ul li { margin-bottom: 12px; }
        .vtl-footer-column ul li a { text-decoration: none; color: #666666; font-size: 14px; transition: color 0.1s; }
        .vtl-footer-column ul li a:hover { color: #359DD7; }
        .vtl-contact-list li { font-size: 13px; line-height: 1.5; display: flex; align-items: flex-start; margin-bottom: 12px; }
        .vtl-contact-icon { margin-right: 18px; flex-shrink: 0; display: flex; align-items: center; position: relative; top: -2px; }
        .vtl-address-text { color: #666666; display: block; }
        .vtl-separator { border-top: 1px solid #f0f0f0; margin: 40px 0; }
        .vtl-newsletter-section { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; }
        .vtl-newsletter-text-block h3 { font-size: 26px; font-weight: 700; margin-bottom: 8px; }
        .vtl-newsletter-form-block { text-align: right; max-width: 450px; }
        .vtl-newsletter-subtitle { font-size: 13px; color: #7a7a7a; margin-bottom: 10px; }
        .vtl-newsletter-input-group { position: relative; display: flex; }
        .vtl-newsletter-input { border: 1px solid #e0e0e0; border-radius: 20px; padding: 10px 140px 10px 20px; font-size: 14px; width: 100%; }
        .vtl-subscribe-btn { position: absolute; right: 4px; top: 4px; bottom: 4px; background-color: #359DD7; color: #ffffff; border: none; border-radius: 16px; font-size: 13px; font-weight: 600; padding: 0 20px; transition: background-color 0.2s; }
        .vtl-subscribe-btn:hover { background-color: #d1493b; }
        .vtl-bottom-line { display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #7a7a7a; padding-bottom: 30px; }
        .vtl-social-links { display: flex; gap: 15px; }
        .vtl-social-link { text-decoration: none; color: #7a7a7a; font-size: 18px; transition: color 0.1s; }
        .vtl-social-link:hover { color: #359DD7; }
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

        /* Navbar dropdowns */
        .custom-travel-dropdown { padding: 20px; border-radius: 20px; min-width: 300px; }
        .dropdown-flex-container { display: flex; gap: 15px; justify-content: center; }
        .custom-travel-dropdown .dropdown-item { width: 200px; height: 75px; border-radius: 50px; display: flex; align-items: center; justify-content: center; color: white !important; font-size: 16px; font-weight: 700; text-shadow: 1px 1px 4px rgba(0,0,0,0.5); background-size: cover; background-position: center; position: relative; overflow: hidden; transition: transform 0.2s ease; padding: 0; }
        .custom-travel-dropdown .dropdown-item:hover { transform: scale(1.03); background-color: transparent; }
        .baggage-card { background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('baggage.jpg'); }
        .visa-card { background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('visa.jpg'); }
        .custom-travel-dropdown li { list-style: none; }
        .teambuilding-card { background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('teambuilding.jpg'); }
        .group-card { background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('group.jpg'); background-size: cover !important; background-position: center !important; background-color: #555 !important; display: flex !important; }

        /* ===================== MODALS ===================== */
        .custom-modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; z-index: 9999; }
        .custom-modal-card { background: #fff; padding: 40px; border-radius: 15px; width: 90%; max-width: 480px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .modal-title { font-weight: 700; font-size: 26px; margin-bottom: 15px; color: #000; }
        .modal-desc { color: #333; font-size: 17px; margin-bottom: 30px; }

        /* ===================== NEW: ADDON CARD GRID ===================== */
        .addon-page-title { font-size: 1.5rem; font-weight: 700; color: #0c1e35; margin-bottom: 4px; }
        .addon-page-sub { font-size: 0.9rem; color: #758599; margin-bottom: 22px; }

        /* Filter Tabs — matching reference image */
        .addon-filter-bar {
            display: flex;
            gap: 0;
            border: 1.5px solid #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 26px;
            width: fit-content;
            background: #fff;
        }
        .addon-filter-btn {
            padding: 10px 26px;
            font-size: 0.88rem;
            font-weight: 600;
            color: #555;
            background: #fff;
            border: none;
            cursor: pointer;
            transition: all 0.18s;
            border-right: 1.5px solid #e0e0e0;
            white-space: nowrap;
        }
        .addon-filter-btn:last-child { border-right: none; }
        .addon-filter-btn.active { background: #359DD7; color: #fff; }
        .addon-filter-btn:hover:not(.active) { background: #f5f5f5; }

        /* Grid */
        .addon-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 30px;
        }
        @media (max-width: 1100px) { .addon-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 580px) { .addon-grid { grid-template-columns: 1fr; } }

        /* Individual card */
        .ac {
            border: 1px solid #d9d9d9;
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
            display: flex;
            flex-direction: column;
            transition: box-shadow 0.18s, border-color 0.18s;
            position: relative;
            cursor: pointer;
            box-shadow: none;
        }
        .ac:hover { box-shadow: 0 4px 14px rgba(0,0,0,0.08); border-color: #c0c0c0; }
        .ac.ac-added { border-color: #28a745; box-shadow: 0 0 0 2px rgba(40,167,69,0.12); }
        .ac.ac-soldout { opacity: 0.6; cursor: not-allowed; }

        /* Card image area */
        .ac-img {
            width: 100%;
            height: 148px;
            object-fit: cover;
            display: block;
        }
        .ac-img-placeholder {
            width: 100%;
            height: 148px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
        }
        /* Flexi gradient */
        .ac-img-flexi {
            width: 100%;
            height: 148px;
            background: linear-gradient(135deg, #0c3460 0%, #16213e 55%, #c0392b 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .ac-img-flexi .flexi-shield { font-size: 3.5rem; filter: drop-shadow(0 3px 8px rgba(0,0,0,0.35)); }
        .ac-img-flexi .flexi-pill {
            position: absolute;
            bottom: 10px;
            left: 12px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(4px);
            color: #fff;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 3px 12px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.3);
        }

        /* Added badge top-right */
        .ac-badge-added {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #28a745;
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            display: none;
        }
        .ac.ac-added .ac-badge-added { display: block; }

        /* Card body */
        .ac-body { padding: 13px 13px 8px; flex: 1; display: flex; flex-direction: column; }
        .ac-title-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 5px; }
        .ac-title { font-size: 0.92rem; font-weight: 700; color: #0c1e35; flex: 1; line-height: 1.3; }
        .ac-price { background: #fff3cd; color: #856404; border: 1px solid #ffc107; font-size: 0.75rem; font-weight: 700; padding: 3px 9px; border-radius: 20px; white-space: nowrap; }
        .ac-sub { font-size: 0.78rem; color: #758599; margin-bottom: 9px; line-height: 1.4; }
        .ac-info { display: flex; flex-direction: column; gap: 5px; flex: 1; }
        .ac-info-row { display: flex; align-items: flex-start; gap: 7px; font-size: 0.76rem; color: #555; line-height: 1.35; }
        .ac-info-row .ii { font-size: 0.82rem; flex-shrink: 0; margin-top: 1px; }

        /* Card footer */
        .ac-footer {
            border-top: 1px solid #f0f0f0;
            padding: 9px 13px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .ac-cond-link { font-size: 0.73rem; color: #359DD7; text-decoration: underline; cursor: pointer; border: none; background: none; padding: 0; }
        .ac-cond-link:hover { color: #0056b3; }
        .ac-add-btn {
            background: #ffde00;
            color: #1a1a1a;
            border: none;
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.18s;
            white-space: nowrap;
        }
        .ac-add-btn:hover:not(:disabled) { background: #f2d200; transform: scale(1.03); }
        .ac-add-btn.is-added { background: #28a745; color: #fff; }
        .ac-add-btn:disabled { background: #ccc; color: #888; cursor: not-allowed; transform: none; }

        /* ===================== CONDITIONS MODAL ===================== */
        .cond-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.45); display: flex; align-items: center; justify-content: center; z-index: 10000; }
        .cond-box { background: #fff; border-radius: 16px; width: 90%; max-width: 520px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.2); }
        .cond-header { background: #0c1e35; color: #fff; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; }
        .cond-header h3 { margin: 0; font-size: 1.05rem; font-weight: 700; }
        .cond-close { background: none; border: none; color: #fff; font-size: 1.4rem; cursor: pointer; line-height: 1; }
        .cond-body { padding: 22px 24px; max-height: 60vh; overflow-y: auto; }
        .cond-item { display: flex; gap: 10px; margin-bottom: 12px; font-size: 0.88rem; }
        .cond-check { color: #28a745; font-weight: 700; flex-shrink: 0; }
        .cond-note { background: #fff8e1; border-left: 3px solid #ffc107; padding: 12px 16px; border-radius: 0 8px 8px 0; font-size: 0.8rem; color: #555; margin-top: 14px; }
        .summary-align-spacer { visibility: hidden; }
@media (min-width: 992px) {
    .summary-card { margin-top: 0; }
}
    </style>
    <!-- Custom Css -->
    <link rel="stylesheet" href="./assets/css/helper.css" />
    <link rel="stylesheet" href="./assets/css/style.css" />
    <link rel="stylesheet" href="./assets/css/responsive.css" />
</head>
<body>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TWBQ2KLF" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

<?php require './assets/includes/navbar.php'; ?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumb mb-5">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="/search-result">Search Results</a></li>
            <li class="breadcrumb-item active" aria-current="page">Book Ferry</li>
        </ol>
    </nav>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="badge-outline">Complete Your Ferry Booking</div>

        </div>
    </div>
</div>

    <div class="row g-4">

        <!-- LEFT: Add-on Card Grid -->
        <div class="col-lg-8">
            <h2 class="addon-page-title">Add On Options</h2>
            <p class="addon-page-sub">Enhance your ferry journey with optional extras</p>

            <!-- Filter tabs -->
            <div class="addon-filter-bar">
                <button class="addon-filter-btn active" data-filter="all">All Add-ons</button>
                <button class="addon-filter-btn" data-filter="protection">Protection</button>
                <button class="addon-filter-btn" data-filter="equipment">Equipment</button>
                <button class="addon-filter-btn" data-filter="tour">Tours</button>
            </div>

            <!-- Card Grid -->
            <div class="addon-grid" id="addonGrid">

                <!-- ── FLEXI-TRIP CARD ── -->
                <div class="ac" id="acFlexi" data-cat="protection">
                    <span class="ac-badge-added" id="flexiBadge"><i class="bi bi-check-circle-fill me-1"></i>Added</span>
                     <img src="assets/images/flexi.jpeg" alt="Bintan Half-Day Tour" class="ac-img-flexi">
                    <div class="ac-body">
                        <div class="ac-title-row">
                            <div class="ac-title">Upgrade to Flexi-Trip</div>
                            <span class="ac-price">+<?= $currencySymbol ?><?= number_format(($tripType === 'round_trip' || $tripType === 'open_trip') ? 5 : 5, 2) ?>/pax</span>
                        </div>
                        <div class="ac-sub">Free refund on your entire booking</div>
                        <div class="ac-info">
                            <div class="ac-info-row"><span class="ii">✕</span><span>No refund by default — upgrade for free cancellation</span></div>
                            <div class="ac-info-row"><span class="ii">🔄</span><span>5 day notice before departure date is required</span></div>
                            <div class="ac-info-row"><span class="ii">📧</span><span>Refund via email to hello@vtltravel.com</span></div>
                        </div>
                    </div>
                    <div class="ac-footer">
                        <button class="ac-cond-link" onclick="openCond('flexi')">View full ticket conditions</button>
                        <button class="ac-add-btn" id="addFlexiBtn"
                            onclick="toggleFlexi(<?= ($tripType === 'round_trip' || $tripType === 'open_trip') ? 5 : 5 ?>, <?= $passengers ?>)">
                            Add To Booking
                        </button>
                    </div>
                </div>

                <!-- ── BINTAN HALF-DAY TOUR CARD ── -->
<div class="ac" id="acBintanTour" data-cat="tour">
    <span class="ac-badge-added" id="bintanTourBadge"><i class="bi bi-check-circle-fill me-1"></i>Added</span>
<img src="assets/images/day-tour.jpeg" alt="Bintan Half-Day Tour" class="ac-img">
    <div class="ac-body">
        <div class="ac-title-row">
            <div class="ac-title">Bintan Half-Day Tour</div>
            <span class="ac-price">+S$35/pax</span>
        </div>
        <div class="ac-sub">5-hour private car tour with English-speaking driver</div>
        <div class="ac-info">
            <div class="ac-info-row"><span class="ii">🚗</span><span>5-hour car with English-speaking driver</span></div>
            <div class="ac-info-row"><span class="ii">🏖️</span><span>Entrance to Blue Lake &amp; Sand Dunes</span></div>
            <div class="ac-info-row"><span class="ii">🦐</span><span>Seafood kelong lunch for 2 pax</span></div>
            <div class="ac-info-row"><span class="ii">🛕</span><span>Visit Sleeping Buddha Temple</span></div>
        </div>
    </div>
    <div class="ac-footer">
        <button class="ac-cond-link" onclick="openCond('bintanTour')">View full conditions</button>
        <button class="ac-add-btn" id="addBintanTourBtn"
            onclick="toggleBintanTour(35, <?= $passengers ?>)">
            Add To Booking
        </button>
    </div>
</div>

                <!-- ── API ADDON CARDS ── -->
                <?php if (!empty($addonResources)):
                    foreach ($addonResources as $addon):
                        $code       = htmlspecialchars($addon['ResourceCode'] ?? '');
                        $adultPrice = floatval($addon['AdultPrice'] ?? 0) + 5;
                        $childPrice = floatval($addon['ChildPrice'] ?? 0) + 5;
                        $capacity   = intval($addon['FreeCapacity'] ?? 0);
                        $name       = $resourceNames[$addon['ResourceCode']] ?? ($code . ' Add-on');
                        $det        = $resourceDetails[$addon['ResourceCode']] ?? null;
                        $tripMul    = ($tripType === 'round_trip' || $tripType === 'open_trip') ? 2 : 1;
                        $cat        = $det['category'] ?? 'equipment';
                        $icon       = $det['icon'] ?? '📦';
                        $imgFile    = $det['image'] ?? '';
                        $bgColor    = ($cat === 'equipment') ? 'linear-gradient(135deg,#1a3a5c,#2980b9)' : 'linear-gradient(135deg,#1a3a5c,#359DD7)';
                        $priceLabel = $currencySymbol . number_format($adultPrice * $tripMul, 2) . '/adult';
                ?>
                <div class="ac <?= $capacity <= 0 ? 'ac-soldout' : '' ?>" id="ac_<?= $code ?>" data-cat="<?= htmlspecialchars($cat) ?>">
                    <span class="ac-badge-added" id="badge_<?= $code ?>"><i class="bi bi-check-circle-fill me-1"></i>Added</span>

                    <?php if ($imgFile): ?>
                    <img src="<?= htmlspecialchars($imgFile) ?>" alt="<?= htmlspecialchars($name) ?>" class="ac-img"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <div class="ac-img-placeholder" style="display:none;background:<?= $bgColor ?>;"><?= $icon ?></div>
                    <?php else: ?>
                    <div class="ac-img-placeholder" style="background:<?= $bgColor ?>;"><?= $icon ?></div>
                    <?php endif; ?>

                    <div class="ac-body">
                        <div class="ac-title-row">
                            <div class="ac-title"><?= htmlspecialchars($name) ?></div>
                            <span class="ac-price">+<?= $priceLabel ?></span>
                        </div>
                        <div class="ac-sub"><?= $det ? htmlspecialchars($det['subtitle']) : '' ?></div>
                        <div class="ac-info">
                            <?php if ($capacity <= 0): ?>
                            <div class="ac-info-row"><span class="ii">⚠️</span><span style="color:#dc3545;font-weight:600;">Sold out for this trip</span></div>
                            <?php else: ?>
                            <div class="ac-info-row"><span class="ii">✅</span><span><?= $capacity ?> slot<?= $capacity > 1 ? 's' : '' ?> available</span></div>
                            <?php if ($childPrice > 0): ?>
                            <div class="ac-info-row"><span class="ii">👶</span><span>Child: <?= $currencySymbol ?><?= number_format($childPrice * $tripMul, 2) ?></span></div>
                            <?php endif; ?>
                            <?php if ($tripType === 'round_trip' || $tripType === 'open_trip'): ?>
                            <div class="ac-info-row"><span class="ii">🔄</span><span>Price covers both ways</span></div>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="ac-footer">
                        <button class="ac-cond-link" onclick="openCond('<?= $code ?>')">View full ticket conditions</button>
                        <?php if ($capacity > 0): ?>
                        <button class="ac-add-btn" id="btn_<?= $code ?>"
                            onclick="openOwnerModal('<?= $code ?>','<?= htmlspecialchars($name) ?>',<?= $adultPrice ?>,<?= $childPrice ?>,<?= $tripMul ?>)">
                            Add To Booking
                        </button>
                        <?php else: ?>
                        <button class="ac-add-btn" disabled>Sold Out</button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; endif; ?>

            </div><!-- /.addon-grid -->

            <!-- Nav buttons -->
            <div class="step-actions-wrapper">
                <a href="index.php" class="btn-back"><i class="bi bi-arrow-left-short fs-5"></i> Back to Homepage</a>
                <form method="POST" action="booking-checking.php" style="display:inline-block;margin:0;" id="continueForm">
                    <input type="hidden" name="flexi_trip" id="flexiTripValue" value="0">
                    <input type="hidden" name="bintan_tour_selected" id="bintanTourSelected" value="0">
                    <input type="hidden" name="bintan_tour_pax" id="bintanTourPax" value="0">
                    <input type="hidden" name="addon_codes" id="addonCodesValue" value="">
                    <input type="hidden" name="addon_total" id="addonTotalValue" value="0">
                    <input type="hidden" name="addon_owners" id="addonOwnersValue" value="">
                    <input type="hidden" name="trip_type" value="<?= htmlspecialchars($tripType) ?>">
                    <input type="hidden" name="is_open_trip" value="<?= $isOpenTrip ? '1' : '0' ?>">
                    <?php
                    $storedFormData = $_SESSION['booking_form_data'] ?? [];
                    if (!empty($storedFormData)):
                        foreach ($storedFormData as $key => $value):
                            if (is_array($value)) continue;
                    ?>
                    <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
                    <?php endforeach; endif; ?>
                    <button type="submit" class="btn-continue" style="border:none;">
                        Continue to Next Step <i class="bi bi-arrow-right-short fs-5"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- RIGHT: Summary Sidebar -->
        <div class="col-lg-4">
            <!-- Invisible spacer: matches left header height so summary aligns with the cards -->
            <div class="summary-align-spacer d-none d-lg-block" aria-hidden="true">
                <h2 class="addon-page-title">.</h2>
                <p class="addon-page-sub">.</p>
                <div class="addon-filter-bar"><button class="addon-filter-btn">.</button></div>
            </div>
            <div class="summary-card">
                <div class="summary-header d-flex justify-content-between align-items-center">
                    <span>Booking Summary</span>
                </div>
                <div class="p-3">
                    <div class="price-row fw-bold"><span>Passengers:</span></div>
                    <div class="price-row"><span>Adult passenger(s)</span><span><?= $adultQty ?></span></div>
                    <?php if ($childQty > 0): ?><div class="price-row"><span>Child passenger(s)</span><span><?= $childQty ?></span></div><?php endif; ?>
                    <?php if ($infantQty > 0): ?><div class="price-row"><span>Infant passenger(s)</span><span><?= $infantQty ?></span></div><?php endif; ?>
                    <hr>

                   <!-- <h6 class="fw-bold">Departure</h6>
                    <p class="text-muted small mb-3"><?= htmlspecialchars($departOrigin) ?> - <?= htmlspecialchars($departDestination) ?></p>
                    <div class="price-line"><span>Ticket Fare ×<?= $passengers ?></span><span><?= $currencySymbol ?><?= number_format($departureTicketTotal, 2) ?></span></div>
                    <?php if ($departureSurchargeTotal > 0): ?><div class="price-line"><span><?= htmlspecialchars($surchargeName) ?> ×<?= $passengers ?></span><span><?= $currencySymbol ?><?= number_format($departureSurchargeTotal, 2) ?></span></div><?php endif; ?>
                    <?php if ($departurePdfSgTotal > 0): ?><div class="price-line"><span>Departure Fee</span><span><?= $currencySymbol ?><?= number_format($departurePdfSgTotal, 2) ?></span></div><?php endif; ?>
                    <?php if ($departurePdfBtmTotal > 0): ?><div class="price-line"><span>Return Fee</span><span><?= $currencySymbol ?><?= number_format($departurePdfBtmTotal, 2) ?></span></div><?php endif; ?>
                    <div class="price-line fw-bold mt-2 border-top pt-2"><span>Departure Total</span><span><?= $currencySymbol ?><?= number_format($departureSubtotal, 2) ?></span></div>

                    <?php if ($tripType === 'round_trip' && $selectedReturn && isset($selectedReturnDetails)): ?>
                    <h6 class="fw-bold mt-4">Return</h6>
                    <p class="text-muted small mb-3"><?= htmlspecialchars($returnOrigin) ?> - <?= htmlspecialchars($returnDestination) ?></p>
                    <div class="price-line"><span>Ticket Fare ×<?= $passengers ?></span><span><?= $currencySymbol ?><?= number_format($returnTicketTotal, 2) ?></span></div>
                    <?php if ($returnSurchargeTotal > 0): ?><div class="price-line"><span><?= htmlspecialchars($surchargeName) ?> ×<?= $passengers ?></span><span><?= $currencySymbol ?><?= number_format($returnSurchargeTotal, 2) ?></span></div><?php endif; ?>
                    <div class="price-line fw-bold mt-2 border-top pt-2"><span>Return Total</span><span><?= $currencySymbol ?><?= number_format($returnSubtotal, 2) ?></span></div>
                    <?php elseif ($tripType === 'open_trip'): ?>
                    <h6 class="fw-bold mt-4">Open Return</h6>
                    <p class="text-muted small mb-3">Valid for return within 30 days</p>
                    <div class="price-line"><span>Open Return Ticket ×<?= $passengers ?></span><span><?= $currencySymbol ?><?= number_format($returnTicketTotal, 2) ?></span></div>
                    <?php if ($returnSurchargeTotal > 0): ?><div class="price-line"><span><?= htmlspecialchars($surchargeName) ?> ×<?= $passengers ?></span><span><?= $currencySymbol ?><?= number_format($returnSurchargeTotal, 2) ?></span></div><?php endif; ?>
                    <div class="price-line fw-bold mt-2 border-top pt-2"><span>Open Return Total</span><span><?= $currencySymbol ?><?= number_format($returnSubtotal, 2) ?></span></div>
                    <?php endif; ?>

                    <div class="price-line text-muted mt-3 pt-3 border-top"><span>Ticket Subtotal</span><span><?= $currencySymbol ?><?= number_format($subtotal, 2) ?></span></div>
                    <?php if (($departureSurchargeTotal + $returnSurchargeTotal) > 0): ?><div class="price-line"><span><?= htmlspecialchars($surchargeName) ?></span><span><?= $currencySymbol ?><?= number_format($departureSurchargeTotal + $returnSurchargeTotal, 2) ?></span></div><?php endif; ?>
                    <?php if (($departurePdfSgTotal + $returnPdfSgTotal) > 0): ?><div class="price-line"><span>Departure Fee</span><span><?= $currencySymbol ?><?= number_format($departurePdfSgTotal + $returnPdfSgTotal, 2) ?></span></div><?php endif; ?>
                    <?php if (($departurePdfBtmTotal + $returnPdfBtmTotal) > 0): ?><div class="price-line"><span>Return Fee</span><span><?= $currencySymbol ?><?= number_format($departurePdfBtmTotal + $returnPdfBtmTotal, 2) ?></span></div><?php endif; ?>

                    <!-- Dynamic addon rows injected by JS -->
                    <div id="addonRowsContainer"></div>

                    <div class="price-line grand-total">
                        <span>Grand Total:</span>
                        <span id="grandTotalAmount" class="text-dark"><?= $currencySymbol ?><?= number_format($grandTotalAfterDiscount, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="vtl-separator"></div>

<?php require './assets/includes/footer.php'; ?>

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5SVD439S"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<!-- ===================== PASSENGER OWNER MODAL (unchanged) ===================== -->
<div id="ownerModal" class="custom-modal-overlay" style="display:none;">
    <div class="custom-modal-card" style="max-width:520px;text-align:left;">
        <h2 class="modal-title" id="ownerModalTitle" style="font-size:1.3rem;margin-bottom:8px;">Select Passenger(s)</h2>
        <p class="modal-desc" style="font-size:0.95rem;color:#666;margin-bottom:20px;" id="ownerModalSub">Select which passenger(s) this add-on applies to.</p>
        <div id="ownerPassList" style="display:flex;flex-direction:column;gap:10px;margin-bottom:25px;"></div>
        <div style="display:flex;justify-content:space-between;gap:12px;">
            <button type="button" onclick="closeOwnerModal()" style="flex:1;padding:12px;border-radius:30px;border:1.5px solid #1e2a3a;background:transparent;font-weight:600;cursor:pointer;">Cancel</button>
            <button type="button" onclick="confirmOwners()" style="flex:1;padding:12px;border-radius:30px;border:none;background:#359DD7;color:white;font-weight:600;cursor:pointer;">Confirm</button>
        </div>
    </div>
</div>

<!-- ===================== CONDITIONS MODAL ===================== -->
<div id="condModal" class="cond-overlay" style="display:none;" onclick="if(event.target===this)closeCond()">
    <div class="cond-box">
        <div class="cond-header">
            <h3 id="condTitle">Ticket Conditions</h3>
            <button class="cond-close" onclick="closeCond()">&times;</button>
        </div>
        <div class="cond-body" id="condBody"></div>
    </div>
</div>

<script>
// ===================== DATA =====================
const CS = '<?= $currencySymbol ?>';
const baseTotal = <?= $grandTotalAfterDiscount ?>;
let addonTotals = {};
let addonOwners = {};
let curCode = null, curName = null, curAdult = 0, curChild = 0, curMul = 1;

const paxList = [
<?php
$sd = $_SESSION['booking_form_data'] ?? [];
$tp = intval($sd['total_passengers'] ?? ($adultQty + $childQty + $infantQty));
for ($i = 1; $i <= $tp; $i++) {
    $fn = htmlspecialchars($sd["first_name_{$i}"] ?? '');
    $ln = htmlspecialchars($sd["last_name_{$i}"] ?? '');
    $ty = htmlspecialchars($sd["passenger_type_{$i}"] ?? 'adult');
    $nm = trim($fn . ' ' . $ln) ?: "Passenger {$i}";
    echo "{ idx:{$i}, name:'{$nm}', type:'{$ty}' },\n";
}
?>
];

const condData = {
    flexi: {
        title: 'Flexi-Trip Conditions',
        items: [
            'Free refund by emailing hello@vtltravel.com before departure',
            '5 day notice before departure date is required. No refund for any changes within 5 days of departure date',
            '<?= $currencySymbol ?><?= ($tripType === 'round_trip' || $tripType === 'open_trip') ? '5.00' : '5.00' ?> per person'
        ],
        //note: 'The Flexi Trip applies rescheduling to all passengers on the same booking. Selective rescheduling is not permitted.'
    },
<?php foreach ($addonResources as $addon):
    $c2 = $addon['ResourceCode'] ?? '';
    $d2 = $resourceDetails[$c2] ?? null;
    if (!$d2) continue;
    $n2 = $resourceNames[$c2] ?? ($c2 . ' Add-on');
    $bj = json_encode($d2['benefits']);
    $nt = addslashes($d2['note']);
?>
    '<?= htmlspecialchars($c2) ?>': {
        title: '<?= htmlspecialchars($n2) ?> Conditions',
        items: <?= $bj ?>,
        note: '<?= $nt ?>'
    },
    bintanTour: {
    title: 'Bintan Half-Day Tour Conditions',
    items: [
        '5-hour private car with English-speaking driver included',
        'Entrance fees to Blue Lake and Sand Dunes included',
        'Seafood kelong lunch inclusive for 2 pax (additional pax charged separately)',
        'Visit to Sleeping Buddha Temple included',
        'Tour operates on arrival date to Bintan as hotel checkin is usually after 3pm',
    ],
    note: 'Tour is subject to weather and availability.'
},
<?php endforeach; ?>
};

// ===================== FILTER TABS =====================
document.querySelectorAll('.addon-filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.addon-filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const f = this.dataset.filter;
        document.querySelectorAll('.ac').forEach(card => {
            card.style.display = (f === 'all' || card.dataset.cat === f) ? 'flex' : 'none';
        });
        // keep flex-direction correct
        document.querySelectorAll('.ac[style*="flex"]').forEach(c => c.style.flexDirection = 'column');
    });
});

// ===================== CONDITIONS MODAL =====================
function openCond(code) {
    const d = condData[code]; if (!d) return;
    document.getElementById('condTitle').textContent = d.title;
    let h = '';
    d.items.forEach(i => { h += `<div class="cond-item"><span class="cond-check">✓</span><span>${i}</span></div>`; });
    if (d.note) h += `<div class="cond-note"><strong>Note:</strong> ${d.note}</div>`;
    document.getElementById('condBody').innerHTML = h;
    document.getElementById('condModal').style.display = 'flex';
}
function closeCond() { document.getElementById('condModal').style.display = 'none'; }

// ===================== FLEXI TOGGLE =====================
function toggleFlexi(costPerTrip, paxCount) {
    const btn = document.getElementById('addFlexiBtn');
    const card = document.getElementById('acFlexi');
    const badge = document.getElementById('flexiBadge');
    const added = btn.classList.contains('is-added');

    if (!added) {
        const tot = costPerTrip * paxCount;
        addonTotals['flexi'] = tot;
        btn.textContent = 'Added'; btn.classList.add('is-added');
        card.classList.add('ac-added'); badge.style.display = 'block';
        upsertRow('flexi', 'Flexi-Trip Protection <i class="bi bi-shield-check text-success"></i>', tot);
    } else {
        delete addonTotals['flexi'];
        btn.textContent = 'Add To Booking'; btn.classList.remove('is-added');
        card.classList.remove('ac-added'); badge.style.display = 'none';
        removeRow('flexi');
    }
    syncHidden(); refreshTotal();
}

function toggleBintanTour(costPerPax, paxCount) {
    const btn = document.getElementById('addBintanTourBtn');
    const card = document.getElementById('acBintanTour');
    const badge = document.getElementById('bintanTourBadge');
    const added = btn.classList.contains('is-added');

    if (!added) {
        const tot = costPerPax * paxCount;
        addonTotals['bintanTour'] = tot;
        btn.textContent = 'Added'; btn.classList.add('is-added');
        card.classList.add('ac-added'); badge.style.display = 'block';
        upsertRow('bintanTour', 'Bintan Half-Day Tour', tot);
    } else {
        delete addonTotals['bintanTour'];
        btn.textContent = 'Add To Booking'; btn.classList.remove('is-added');
        card.classList.remove('ac-added'); badge.style.display = 'none';
        removeRow('bintanTour');
    }
    syncHidden(); refreshTotal();
}

// ===================== OWNER MODAL =====================
function openOwnerModal(code, name, adult, child, mul) {
    const btn = document.getElementById('btn_' + code);
    if (btn && btn.classList.contains('is-added')) { removeAddon(code); return; }
    curCode = code; curName = name; curAdult = adult; curChild = child; curMul = mul;
    document.getElementById('ownerModalTitle').textContent = 'Who is bringing the ' + name.replace(' Add-on','') + '?';
    document.getElementById('ownerModalSub').textContent = 'Select which passenger(s) this add-on applies to.';

    const list = document.getElementById('ownerPassList');
    list.innerHTML = '';
    if (!paxList.length) {
        list.innerHTML = '<p style="color:#999;font-size:.9rem;">No passenger data found. Please go back to fill in details.</p>';
    } else {
        paxList.forEach(p => {
            const price = p.type === 'adult' ? adult : child;
            const lbl = document.createElement('label');
            lbl.style.cssText = 'display:flex;align-items:center;gap:12px;padding:12px 15px;border:1px solid #e0e0e0;border-radius:10px;cursor:pointer;transition:border-color .2s;';
            lbl.innerHTML = `<input type="checkbox" value="${p.idx}" data-type="${p.type}" data-price="${price}" style="width:18px;height:18px;accent-color:#359DD7;cursor:pointer;flex-shrink:0;">
                <div><div style="font-weight:600;color:#0c1e35;">${p.name}</div>
                <div style="font-size:.8rem;color:#888;">${p.type.charAt(0).toUpperCase()+p.type.slice(1)} &mdash; ${CS}${(price*mul).toFixed(2)}${mul>1?' (both ways)':' (one way)'}</div></div>`;
            lbl.querySelector('input').addEventListener('change', function() {
                lbl.style.borderColor = this.checked ? '#359DD7' : '#e0e0e0';
                lbl.style.backgroundColor = this.checked ? '#f0f8ff' : 'white';
            });
            list.appendChild(lbl);
        });
    }
    document.getElementById('ownerModal').style.display = 'flex';
}
function closeOwnerModal() { document.getElementById('ownerModal').style.display = 'none'; curCode = null; }
document.getElementById('ownerModal').addEventListener('click', e => { if (e.target === document.getElementById('ownerModal')) closeOwnerModal(); });

function confirmOwners() {
    const cbs = document.querySelectorAll('#ownerPassList input:checked');
    if (!cbs.length) { alert('Please select at least one passenger.'); return; }
    let tot = 0; const idxs = [];
    cbs.forEach(cb => { tot += parseFloat(cb.dataset.price) * curMul; idxs.push(parseInt(cb.value)); });
    addonOwners[curCode] = idxs; addonTotals[curCode] = tot;

    const card = document.getElementById('ac_' + curCode);
    const badge = document.getElementById('badge_' + curCode);
    const btn = document.getElementById('btn_' + curCode);
    if (card) card.classList.add('ac-added');
    if (badge) badge.style.display = 'block';
    if (btn) { btn.textContent = 'Added'; btn.classList.add('is-added'); }

    upsertRow(curCode, curName + ' (' + idxs.length + ' pax)', tot);
    syncHidden(); refreshTotal(); closeOwnerModal();
}

function removeAddon(code) {
    delete addonTotals[code]; delete addonOwners[code];
    const card = document.getElementById('ac_' + code);
    const badge = document.getElementById('badge_' + code);
    const btn = document.getElementById('btn_' + code);
    if (card) card.classList.remove('ac-added');
    if (badge) badge.style.display = 'none';
    if (btn) { btn.textContent = 'Add To Booking'; btn.classList.remove('is-added'); }
    removeRow(code);
    syncHidden(); refreshTotal();
}

// ===================== SUMMARY HELPERS =====================
function upsertRow(code, label, amt) {
    let row = document.getElementById('row_' + code);
    if (!row) {
        row = document.createElement('div');
        row.className = 'price-line';
        row.id = 'row_' + code;
        document.getElementById('addonRowsContainer').appendChild(row);
    }
    row.style.display = 'flex';
    row.innerHTML = `<span>${label}</span><span>${CS}${amt.toFixed(2)}</span>`;
}
function removeRow(code) {
    const row = document.getElementById('row_' + code);
    if (row) row.style.display = 'none';
}
function syncHidden() {
    document.getElementById('flexiTripValue').value = addonTotals['flexi'] ? '1' : '0';
    const codes = Object.keys(addonTotals).filter(k => k !== 'flexi' && k !== 'bintanTour');
    document.getElementById('addonCodesValue').value = codes.join(',');
    document.getElementById('addonOwnersValue').value = JSON.stringify(addonOwners);
    document.getElementById('addonTotalValue').value = Object.values(addonTotals).reduce((a,b)=>a+b,0).toFixed(2);
const bintanTourAmt = addonTotals['bintanTour'] ?? 0;
document.getElementById('bintanTourSelected').value = bintanTourAmt > 0 ? '1' : '0';
document.getElementById('bintanTourPax').value = bintanTourAmt > 0 ? '<?= $passengers ?>' : '0';
}
function refreshTotal() {
    const sum = Object.values(addonTotals).reduce((a,b)=>a+b,0);
    document.getElementById('grandTotalAmount').textContent = CS + (baseTotal + sum).toFixed(2);
}

// ===================== NEWSLETTER =====================
document.getElementById('subscribeBtn').addEventListener('click', function() {
    const inp = document.getElementById('subscribeEmail');
    const msg = document.getElementById('subscribeMessage');
    const email = inp.value.trim(); msg.innerHTML = '';
    if (!email || !/^[^\s@]+@([^\s@]+\.)+[^\s@]+$/.test(email)) {
        msg.innerHTML = '<div class="alert alert-danger mb-0">Please enter a valid email address.</div>';
        return;
    }
    this.textContent = 'Sending...'; this.disabled = true; inp.disabled = true;
    fetch(window.location.href, {
        method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
        body:'ajax_subscribe=1&subscriber_email='+encodeURIComponent(email)
    }).then(r=>r.text()).then(d=>{
        if (d.includes('SUCCESS_SUBSCRIBE')) { msg.innerHTML='<div class="alert alert-success mb-0">Thank you for subscribing!</div>'; inp.value=''; }
        else { msg.innerHTML='<div class="alert alert-danger mb-0">Something went wrong. Please try again.</div>'; }
    }).catch(()=>{ msg.innerHTML='<div class="alert alert-danger mb-0">Network error.</div>'; })
    .finally(()=>{ this.textContent='Subscribe Now'; this.disabled=false; inp.disabled=false; });
});
document.getElementById('subscribeEmail').addEventListener('keypress', e => { if(e.key==='Enter'){e.preventDefault();document.getElementById('subscribeBtn').click();} });
</script>

<?php
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && isset($_POST['ajax_subscribe']) && $_POST['ajax_subscribe'] == '1') {
    header('Content-Type: text/plain; charset=utf-8');
    $email = filter_var(trim($_POST['subscriber_email'] ?? ''), FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo "INVALID_EMAIL"; exit; }
    $admin_email = "hello@vtltravel.com";
    $admin_subject = "New Newsletter Subscription - Bintan Ferry Tickets";
    $admin_headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: noreply@bintanferry.com\r\nReply-To: " . $email . "\r\n";
    $admin_message = "<html><body><h2>New Subscriber</h2><p>Email: " . htmlspecialchars($email) . "</p><p>Date: " . date("F j, Y, g:i a") . "</p></body></html>";
    $thankyou_subject = "Welcome to Bintan Ferry Newsletter!";
    $thankyou_headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: Bintan Ferry Tickets Team <hello@vtltravel.com>\r\nReply-To: hello@vtltravel.com\r\n";
    $thankyou_message = "<html><body><p>Thank you for subscribing to the Bintan Ferry Newsletter!</p></body></html>";
    $a = mail($admin_email, $admin_subject, $admin_message, $admin_headers);
    $b = mail($email, $thankyou_subject, $thankyou_message, $thankyou_headers);
    echo ($a || $b) ? "SUCCESS_SUBSCRIBE" : "EMAIL_FAILED";
    exit;
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>var url='https://prod-crmb2b.s3.ap-southeast-1.amazonaws.com/widget/64c917df6f3bf83bd6d2b5b3/69954aba97bf000e7bb2baf4.js?v='+Date.now();var s=document.createElement('script');s.type='text/javascript';s.async=true;s.src=url;s.onload=function(){CreateWhatsappChatWidget();};var x=document.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);</script>
</body>
</html>