<?php
// generate-eticket-simple.php
session_start();

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if booking is confirmed
if (!isset($_SESSION['booking_confirmed']) || $_SESSION['booking_confirmed'] !== true) {
    header('Location: index.php');
    exit;
}

// Get all session data (keeping all your existing PHP code exactly as is)
$bookingResult = $_SESSION['booking_result'] ?? [];
$bookingFormData = $_SESSION['booking_form_data'] ?? [];
$searchResults = $_SESSION['search_results'] ?? [];
$searchParams = $_SESSION['search_params'] ?? [];
$selectedDeparture = $_SESSION['selected_departure'] ?? [];
$selectedReturn = $_SESSION['selected_return'] ?? null;
$confirmationData = $_SESSION['confirmation_data'] ?? [];

// Get trip type
$tripType = $searchParams['trip_type'] ?? 'round_trip';

// Get passenger counts
$adultQty = $searchParams['adultQty'] ?? 1;
$childQty = $searchParams['childQty'] ?? 0;
$infantQty = $searchParams['infantQty'] ?? 0;
$totalPassengers = $adultQty + $childQty + $infantQty;

// ============ GET TRIP DETAILS ============
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

// ============ FORMAT DATES ============
function formatDateForETicket($dateStr) {
    if (empty($dateStr)) return 'Not specified';
    return date('D, j M Y', strtotime($dateStr));
}

function formatTimeForETicket($timeStr) {
    if (empty($timeStr)) return '';
    return date('H:i', strtotime($timeStr));
}

// ============ PASSENGER DETAILS ============
$passengerDetails = [];
for ($i = 1; $i <= $totalPassengers; $i++) {
    if (isset($bookingFormData["name_$i"])) {
        $nationality = $bookingFormData["nationalityId_$i"] ?? '';
        $customNationality = $bookingFormData["custom_nationality_$i"] ?? '';
        
        $passengerDetails[] = [
            'passportNo' => $bookingFormData["passportNo_$i"] ?? '',
            'name' => strtoupper($bookingFormData["name_$i"] ?? ''),
            'gender' => ($bookingFormData["gender_$i"] ?? 'male') === 'female' ? 'F' : 'M',
            'nationality' => $nationality === 'OTHER' && !empty($customNationality) 
                            ? strtoupper($customNationality) 
                            : ($nationality === 'SG' ? 'SINGAPOREAN' : 
                               ($nationality === 'ID' ? 'INDONESIAN' : 
                                ($nationality === 'MY' ? 'MALAYSIAN' : strtoupper($nationality)))),
            'type' => $bookingFormData["passenger_type_$i"] ?? 'adult'
        ];
    }
}

// If no passenger details found, use default
if (empty($passengerDetails)) {
    for ($i = 1; $i <= $totalPassengers; $i++) {
        $passengerDetails[] = [
            'passportNo' => 'N/A',
            'name' => 'PASSENGER ' . $i,
            'gender' => $i % 2 == 0 ? 'M' : 'F',
            'nationality' => 'SINGAPOREAN',
            'type' => 'adult'
        ];
    }
}

// ============ PRICE CALCULATION ============
$departureBasePrice = $selectedDepartureDetails['adultFare'] ?? 0;
$returnBasePrice = $selectedReturnDetails['adultFare'] ?? 0;
$passengers = $selectedDeparture['passengers'] ?? 1;

// Get departure fees
if (isset($selectedDepartureDetails)) {
    $departureSurcharge = floatval($selectedDepartureDetails['fee1'] ?? 0);
    $departurePdfSg = floatval($selectedDepartureDetails['fee2'] ?? 0);
    $departurePdfBtm = floatval($selectedDepartureDetails['fee3'] ?? 0);
} else {
    $departureSurcharge = 0;
    $departurePdfSg = 0;
    $departurePdfBtm = 0;
}

// Initialize return fees
$returnSurcharge = 0;
$returnPdfSg = 0;
$returnPdfBtm = 0;

if ($tripType === 'round_trip' && $selectedReturn && isset($selectedReturnDetails)) {
    $returnBasePrice = $selectedReturnDetails['adultFare'] ?? 0;
    $returnSurcharge = floatval($selectedReturnDetails['fee1'] ?? 0);
    $returnPdfSg = floatval($selectedReturnDetails['fee2'] ?? 0);
    $returnPdfBtm = floatval($selectedReturnDetails['fee3'] ?? 0);
} elseif ($tripType === 'open_trip') {
    $returnBasePrice = $departureBasePrice;
    $returnSurcharge = $departureSurcharge;
    $returnPdfSg = $departurePdfSg;
    $returnPdfBtm = $departurePdfBtm;
}

// ============ PROMO DISCOUNT ============
$promoDiscount = $_SESSION['promo_discount'] ?? 0;
$promoCode = $_SESSION['promo_code'] ?? '';
$grandTotal = ($departureBasePrice * $passengers) + ($returnBasePrice * $passengers) + 
              ($departureSurcharge + $returnSurcharge + $departurePdfSg + $returnPdfSg + 
               $departurePdfBtm + $returnPdfBtm) * $passengers;
$finalTotal = $grandTotal - ($promoDiscount > 0 ? $grandTotal * $promoDiscount : 0);
$currency = $selectedDeparture['currency'] ?? 'SGD';
$currencySymbol = $currency == 'SGD' ? 'S$' : 'Rp ';

// ============ BOOKING DETAILS ============
$bookingId = $bookingResult['id'] ?? $confirmationData['booking_id'] ?? 'N/A';
$paymentRef = $confirmationData['payment_ref'] ?? $_SESSION['payment_request_id'] ?? 'N/A';
$bookingDate = date('D, j M Y', strtotime($confirmationData['confirmed_at'] ?? 'now'));

// Contact details
$contactName = $bookingFormData['full_name'] ?? 'Guest User';
$contactEmail = $bookingFormData['email'] ?? '';
$contactPhone = $bookingFormData['phone'] ?? '';

// Departure details
$departOperator = $selectedDepartureDetails['vesselName'] ?? 'Sindo Ferry';
$departOrigin = $selectedDepartureDetails['portOriginName'] ?? 'Harbourfront (HFC)';
$departDestination = $selectedDepartureDetails['portDestinationName'] ?? 'Batam (BTC)';
$departDate = $selectedDepartureDetails['departDate'] ?? '';
$departTime = $selectedDepartureDetails['etd'] ?? '';
$departTimeRegion = $searchResults['departTimeRegion'] ?? 'SGT';

// Return details
if ($selectedReturn && $selectedReturnDetails) {
    $returnOperator = $selectedReturnDetails['vesselName'] ?? 'Batam Fast';
    $returnOrigin = $selectedReturnDetails['portOriginName'] ?? 'Batam (BTC)';
    $returnDestination = $selectedReturnDetails['portDestinationName'] ?? 'Harbourfront (HFC)';
    $returnDate = $selectedReturnDetails['departDate'] ?? '';
    $returnTime = $selectedReturnDetails['etd'] ?? '';
    $returnTimeRegion = $searchResults['returnTimeRegion'] ?? 'WIB';
}

// Calculate gate times
$departGateOpen = date('H:i', strtotime($departTime . ' -2 hours'));
$departGateClose = date('H:i', strtotime($departTime . ' -65 minutes'));

if ($selectedReturn && $selectedReturnDetails) {
    $returnGateOpen = date('H:i', strtotime($returnTime . ' -2 hours'));
    $returnGateClose = date('H:i', strtotime($returnTime . ' -65 minutes'));
}

// ============ HELPER FUNCTIONS ============
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
    return 'batam fast.png';
}

function getCountryName($code) {
    $countries = [
        'SG' => 'SINGAPOREAN',
        'ID' => 'INDONESIAN',
        'MY' => 'MALAYSIAN',
        'TH' => 'THAI',
        'VN' => 'VIETNAMESE',
        'PH' => 'FILIPINO',
        'JP' => 'JAPANESE',
        'KR' => 'KOREAN',
        'CN' => 'CHINESE',
        'IN' => 'INDIAN',
        'AU' => 'AUSTRALIAN',
        'GB' => 'BRITISH',
        'US' => 'AMERICAN'
    ];
    return $countries[$code] ?? $code;
}

function getPortCode($portName) {
    if (strpos($portName, 'Harbourfront') !== false) return 'HFC';
    if (strpos($portName, 'Batam') !== false) return 'BTC';
    if (strpos($portName, 'Sekupang') !== false) return 'SKP';
    if (strpos($portName, 'Waterfront') !== false) return 'WFC';
    return strtoupper(substr($portName, 0, 3));
}

function formatPassengerName($name) {
    return strtoupper(trim($name));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-TWBQ2KLF');</script>
<!-- End Google Tag Manager -->
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Batam Ferry Tickets - E-Ticket #<?= htmlspecialchars($bookingId) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Add Bootstrap Icons for better button styling -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* [Keep all your existing CSS styles exactly as they were] */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #ffffff;
            color: #333;
        }

        .confirmation-banner {
            background-color: #e8f9ee;
            border-radius: 15px;
            padding: 30px;
            margin-top: 20px;
        }

        .check-icon {
            background-color: #ff6b6b;
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-right: 20px;
        }

        .info-pill {
            background-color: #f0f2f5;
            border-radius: 50px;
            padding: 10px 25px;
            display: flex;
            align-items: center;
            height: 100%;
        }

        .info-pill-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
        }

        .bg-booking { background-color: #d94a4a; }
        .bg-pax { background-color: #3b82f6; }
        .bg-date { background-color: #22c55e; }

        .label-text {
            font-size: 0.75rem;
            color: #888;
            margin-bottom: 0;
            line-height: 1;
        }

        .value-text {
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 0;
        }

        .journey-section {
            margin-top: 50px;
        }

        .journey-title {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 40px;
        }

        .illustration-img {
            max-width: 100%;
            height: auto;
        }

        .journey-card {
            border: 1px solid #e0e6ed;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            position: relative;
        }

        .trip-badge {
            padding: 5px 25px;
            border-radius: 50px;
            font-weight: 600;
            color: white;
            display: inline-block;
            margin-bottom: 20px;
        }

        .bg-outbound { background-color: #1e3a8a; }
        .bg-return { background-color: #ff8a75; }

        .detail-label {
            font-size: 0.85rem;
            color: #888;
            margin-bottom: 2px;
            display: flex;
            align-items: center;
        }

        .detail-label svg {
            margin-right: 8px;
            color: #ff8a75;
        }

        .terminal-name {
            font-weight: 700;
            font-size: 1.05rem;
            margin-bottom: 0;
        }

        .terminal-code {
            font-size: 0.75rem;
            color: #b0b0b0;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .time-info {
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .gate-info {
            background-color: #f8f9fa;
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.85rem;
            color: #666;
        }

        .gate-info strong {
            color: #333;
            margin: 0 4px;
        }

        .gate-divider {
            margin: 0 8px;
            color: #ccc;
        }

        .section-title {
            font-weight: 700;
            font-size: 1.4rem;
            margin-top: 40px;
            margin-bottom: 20px;
        }

        .passenger-table {
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
            border-radius: 12px;
            border: 1px solid #f0f2f5;
        }

        .passenger-table thead {
            background-color: #e9ecef;
        }

        .passenger-table th {
            font-weight: 600;
            font-size: 0.9rem;
            padding: 12px 20px;
            border: none;
        }

        .passenger-table td {
            padding: 12px 20px;
            font-size: 0.85rem;
            vertical-align: middle;
            border-top: 1px solid #f0f2f5;
        }

        .payment-card {
            border: 1px solid #e0e6ed;
            border-radius: 15px;
            padding: 20px;
            height: 100%;
        }

        .payment-card-title {
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .payment-card-title svg {
            color: #ff8a75;
            margin-right: 10px;
        }

        .payment-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .payment-item {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            margin-bottom: 8px;
            color: #555;
        }

        .status-paid {
            color: #22c55e;
            font-weight: 700;
        }

        .status-na {
            color: #333;
            font-weight: 700;
        }

        .booking-method-box {
            border: 1px solid #e0e6ed;
            border-radius: 15px;
            padding: 20px;
            background-color: #fff;
            max-width: 450px;
        }

        .method-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .method-label {
            color: #888;
            font-size: 0.9rem;
        }

        .method-value {
            font-weight: 700;
            font-size: 0.9rem;
            text-align: right;
        }

        .info-accordion .accordion-item {
            border: 1px solid #e0e6ed;
            border-radius: 10px !important;
            margin-bottom: 15px;
            overflow: hidden;
        }

        .info-accordion .accordion-button {
            font-weight: 700;
            font-size: 1.05rem;
            background-color: #f8f9fa;
            color: #333;
            padding: 15px 20px;
        }

        .info-accordion .accordion-button:focus {
            box-shadow: none;
            border-color: rgba(0,0,0,.125);
        }

        .info-accordion .accordion-button:not(.collapsed) {
            background-color: #f8f9fa;
            color: #333;
            box-shadow: none;
        }

        .info-accordion .accordion-body {
            padding: 20px;
            background-color: #fff;
        }

        .info-list {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .info-list li {
            position: relative;
            padding-left: 20px;
            margin-bottom: 10px;
            font-size: 0.9rem;
            color: #555;
            line-height: 1.6;
        }

        .info-list li::before {
            content: "•";
            position: absolute;
            left: 0;
            color: #888;
            font-weight: bold;
        }

        .location-card {
            background-color: #f1f4f9;
            border-radius: 12px;
            padding: 20px;
            height: 100%;
            border: 1px solid transparent;
        }

        .location-title {
            color: #ff6b6b;
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 12px;
        }

        .location-text {
            font-size: 0.88rem;
            color: #4a5568;
            line-height: 1.6;
            margin-bottom: 0;
        }

        .link-coral {
            color: #ff6b6b;
            text-decoration: none;
            font-weight: 600;
        }

        .link-coral:hover {
            text-decoration: underline;
        }

        .final-footer {
            padding: 40px 0 60px 0;
            color: #333;
        }

        .footer-msg {
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .footer-bottom-links {
            text-align: center;
            font-size: 0.9rem;
            color: #555;
            border-top: 1px solid #eee;
            padding-top: 30px;
        }

        .footer-bottom-links a {
            color: inherit;
            text-decoration: none;
        }

        .footer-divider {
            margin: 0 10px;
            color: #ccc;
        }

        /* PDF download notification */
        .pdf-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #28a745;
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .pdf-notification i {
            font-size: 1.2rem;
        }

        .pdf-notification.fade-out {
            animation: fadeOut 0.5s ease-out forwards;
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                transform: translateY(-20px);
            }
        }

        .manual-download-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 9998;
            padding: 12px 25px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0,123,255,0.3);
            transition: all 0.3s;
        }

        .manual-download-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,123,255,0.4);
        }

        /* Loading overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.9);
            z-index: 10000;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            backdrop-filter: blur(3px);
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #ff6b6b;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text {
            font-size: 1.2rem;
            color: #333;
            font-weight: 600;
        }

        .loading-subtext {
            font-size: 0.9rem;
            color: #666;
            margin-top: 10px;
        }

        @media print {
            .manual-download-btn, .pdf-notification, .loading-overlay {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<!-- Loading Overlay (shown while preparing PDF) -->
<div id="loadingOverlay" class="loading-overlay" style="display: none;">
    <div class="loading-spinner"></div>
    <div class="loading-text">Preparing your E-Ticket PDF...</div>
    <div class="loading-subtext">The print dialog will open automatically</div>
</div>

<!-- PDF Download Notification -->
<div id="pdfNotification" class="pdf-notification" style="display: none;">
    <i class="bi bi-download"></i>
    <span>Opening print dialog to save as PDF...</span>
</div>

<!-- Manual Download Button (optional) -->
<button class="btn btn-primary manual-download-btn" onclick="manualDownload()">
    <i class="bi bi-download"></i> Download E-Ticket PDF
</button>

<div class="container py-5">
    <div class="text-center mb-5">
        <!-- Make sure you have this logo file or replace with appropriate image -->
        <img src="horizontal_logo.svg" alt="Batam Ferry Logo" style="max-height: 60px;" onerror="this.style.display='none'">
    </div>

    <div class="confirmation-banner d-flex align-items-start">
        <div class="check-icon">
            ✓
        </div>
        <div>
            <h2 class="fw-bold mb-1" style="letter-spacing: -0.5px;">YOUR BOOKING HAS BEEN CONFIRMED</h2>
            <p class="mb-0 text-secondary">Dear <?= htmlspecialchars($contactName) ?>,</p>
            <p class="text-secondary">It is our pleasure to confirm your trip booking as follows:</p>
        </div>
    </div>

    <div class="row g-3 mt-4">
        <div class="col-md-4">
            <div class="info-pill">
                <div class="info-pill-icon bg-booking">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/></svg>
                </div>
                <div>
                    <p class="label-text">Booking No.</p>
                    <p class="value-text"><?= htmlspecialchars($bookingId) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-pill">
                <div class="info-pill-icon bg-pax">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg>
                </div>
                <div>
                    <p class="label-text">Passengers</p>
                    <p class="value-text"><?= $totalPassengers ?> PAX</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-pill">
                <div class="info-pill-icon bg-date">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/></svg>
                </div>
                <div>
                    <p class="label-text">Booking date</p>
                    <p class="value-text"><?= htmlspecialchars($bookingDate) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="journey-section">
        <h4 class="journey-title">Your Journey</h4>
        <div class="row align-items-center text-center">
            <div class="col-4">
                <!-- Make sure these image files exist or replace with appropriate images -->
                <img src="left-ferry.png" alt="Origin" class="illustration-img" onerror="this.style.display='none'">
            </div>
            <div class="col-4">
                <img src="middle-ferry.png" alt="Ferry" class="illustration-img" onerror="this.style.display='none'">
            </div>
            <div class="col-4">
                <img src="right-ferry.png" alt="Destination" class="illustration-img" onerror="this.style.display='none'">
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <!-- Departure Journey Card -->
    <div class="journey-card">
        <div class="trip-badge bg-outbound">Outbound</div>
        <div class="row">
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="detail-label">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M7.153 10.364A.5.5 0 0 0 7 10.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1z"/><path d="M0 13a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1H1a1 1 0 0 0-1 1v8zm1-8h14v8H1V5z"/></svg>
                    Depart
                </div>
                <p class="terminal-name"><?= htmlspecialchars($departOrigin) ?></p>
                <p class="terminal-code"><?= getPortCode($departOrigin) ?></p>
                
                <div class="detail-label">Date - Time</div>
                <p class="time-info"><?= formatDateForETicket($departDate) ?> - <?= formatTimeForETicket($departTime) ?> <?= htmlspecialchars($departTimeRegion) ?></p>
                
                <div class="gate-info">
                    <svg width="14" height="14" fill="#888" viewBox="0 0 16 16" class="me-1"><path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/></svg>
                    Gate: Open <strong><?= $departGateOpen ?></strong> <span class="gate-divider">|</span> Close <strong><?= $departGateClose ?></strong>
                </div>
            </div>

            <div class="col-md-6">
                <div class="detail-label">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/></svg>
                    Arrive
                </div>
                <p class="terminal-name"><?= htmlspecialchars($departDestination) ?></p>
                <p class="terminal-code"><?= getPortCode($departDestination) ?></p>
                
                <div class="detail-label">Date - Time</div>
                <p class="time-info"><?= formatDateForETicket($departDate) ?> - <?= formatTimeForETicket($selectedDepartureDetails['eta'] ?? '') ?>  <?= htmlspecialchars($departTimeRegion == 'SGT' ? 'WIB' : $departTimeRegion) ?></p>
            </div>
        </div>
    </div>

    <?php if ($selectedReturn && $selectedReturnDetails): ?>
    <!-- Return Journey Card -->
    <div class="journey-card">
        <div class="trip-badge bg-return">Return</div>
        <div class="row">
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="detail-label">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M7.153 10.364A.5.5 0 0 0 7 10.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1z"/><path d="M0 13a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1H1a1 1 0 0 0-1 1v8zm1-8h14v8H1V5z"/></svg>
                    Depart
                </div>
                <p class="terminal-name"><?= htmlspecialchars($returnOrigin) ?></p>
                <p class="terminal-code"><?= getPortCode($returnOrigin) ?></p>
                
                <div class="detail-label">Date - Time</div>
                <p class="time-info"><?= formatDateForETicket($returnDate) ?> - <?= formatTimeForETicket($returnTime) ?> <?= htmlspecialchars($returnTimeRegion) ?></p>
                
                <div class="gate-info">
                    <svg width="14" height="14" fill="#888" viewBox="0 0 16 16" class="me-1"><path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/></svg>
                    Gate: Open <strong><?= $returnGateOpen ?? '' ?></strong> <span class="gate-divider">|</span> Close <strong><?= $returnGateClose ?? '' ?></strong>
                </div>
            </div>

            <div class="col-md-6">
                <div class="detail-label">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/></svg>
                    Arrive
                </div>
                <p class="terminal-name"><?= htmlspecialchars($returnDestination) ?></p>
                <p class="terminal-code"><?= getPortCode($returnDestination) ?></p>
                
                <div class="detail-label">Date - Time</div>
                <p class="time-info"><?= formatDateForETicket($returnDate) ?> - <?= formatTimeForETicket($selectedReturnDetails['eta'] ?? '') ?>  <?= htmlspecialchars($returnTimeRegion == 'WIB' ? 'SGT' : $returnTimeRegion) ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="container pb-5">
    
    <h3 class="section-title">Passenger Details</h3>
    <div class="table-responsive">
        <table class="table passenger-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Passport No.</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Nationality</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($passengerDetails as $index => $passenger): ?>
                <tr>
                    <td><?= $index + 1 ?>.</td>
                    <td><?= htmlspecialchars($passenger['passportNo']) ?></td>
                    <td><?= htmlspecialchars(formatPassengerName($passenger['name'])) ?></td>
                    <td><?= htmlspecialchars($passenger['gender']) ?></td>
                    <td><?= htmlspecialchars($passenger['nationality']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <h3 class="section-title">Payment Details</h3>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="payment-card">
                <div class="payment-card-title">
                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/></svg>
                    <?= htmlspecialchars(getPortCode($departOrigin)) ?> — <?= htmlspecialchars(getPortCode($departDestination)) ?>
                </div>
                <ul class="payment-list">
                    <li class="payment-item"><span>Adult tickets x <?= $adultQty ?></span> <span class="status-paid">Paid</span></li>
                    <?php if ($childQty > 0): ?>
                    <li class="payment-item"><span>Child tickets x <?= $childQty ?></span> <span class="status-paid">Paid</span></li>
                    <?php endif; ?>
                     <?php if ($infantQty > 0): ?>
                    <li class="payment-item"><span>Infant tickets x <?= $infantQty ?></span> <span class="status-paid">Paid</span></li>
                    <?php endif; ?>
                    <?php if ($departureSurcharge > 0): ?>
                    <li class="payment-item"><span>Surcharge</span> <span class="status-paid">Paid</span></li>
                    <?php endif; ?>
                    <?php if ($departurePdfSg > 0 || $departurePdfBtm > 0): ?>
                    <li class="payment-item"><span>Departure Fee</span> <span class="status-paid">Paid</span></li>
                    <?php endif; ?>
                    <li class="payment-item"><span>Confirmation Fee</span> <span class="status-na">N/A</span></li>
                </ul>
            </div>
        </div>
        <?php if ($selectedReturn && $selectedReturnDetails): ?>
        <div class="col-md-6">
            <div class="payment-card">
                <div class="payment-card-title">
                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/></svg>
                    <?= htmlspecialchars(getPortCode($returnOrigin)) ?> — <?= htmlspecialchars(getPortCode($returnDestination)) ?>
                </div>
                <ul class="payment-list">
                    <li class="payment-item"><span>Adult tickets x <?= $adultQty ?></span> <span class="status-paid">Paid</span></li>
                    <?php if ($childQty > 0): ?>
                    <li class="payment-item"><span>Child tickets x <?= $childQty ?></span> <span class="status-paid">Paid</span></li>
                    <?php endif; ?>
                     <?php if ($infantQty > 0): ?>
                    <li class="payment-item"><span>Infant tickets x <?= $infantQty ?></span> <span class="status-paid">Paid</span></li>
                    <?php endif; ?>
                    <?php if ($returnSurcharge > 0): ?>
                    <li class="payment-item"><span>Surcharge</span> <span class="status-paid">Paid</span></li>
                    <?php endif; ?>
                    <?php if ($returnPdfSg > 0 || $returnPdfBtm > 0): ?>
                    <li class="payment-item"><span>Return Fee</span> <span class="status-paid">Paid</span></li>
                    <?php endif; ?>
                    <li class="payment-item"><span>Confirmation Fee</span> <span class="status-na">N/A</span></li>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <h3 class="section-title">Booking Method</h3>
    <div class="booking-method-box">
        <div class="method-row">
            <span class="method-label">Date</span>
            <span class="method-value"><?= htmlspecialchars($bookingDate) ?></span>
        </div>
        <div class="method-row">
            <span class="method-label">Outstanding Amount</span>
            <span class="method-value">0.0</span>
        </div>
        <div class="method-row">
            <span class="method-label">Remarks</span>
            <span class="method-value">vtl</span>
        </div>
    </div>

</div>

<!-- Accordion Sections (keep as is, they're static) -->
<div class="container pb-5">
    <div class="accordion info-accordion" id="bookingInfo">
        
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    Required Documents
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne">
                <div class="accordion-body">
                    <ul class="info-list">
                        <li>Passengers are responsible for obtaining all entry and exit visa(s), health, and other documents required by law, regulations, order, demands or other requirements.</li>
                        <li>Please ensure that you possess a valid passport, with a minimum of 6 months validity, from date of travel.</li>
                        <li>Sindo Ferry reserves the right to refuse boarding to any passengers who have not complied with the above.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                    Baggage
                </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse show" aria-labelledby="headingTwo">
                <div class="accordion-body">
                    <ul class="info-list">
                        <li>Each passenger will be entitled to 20kg of baggage. For more information, please check with the respective terminal.</li>
                        <li>Do not bring any liquids, dangerous and prohibited goods on board the ferry.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="true" aria-controls="collapseThree">
                    Check-in Time
                </button>
            </h2>
            <div id="collapseThree" class="accordion-collapse collapse show" aria-labelledby="headingThree">
                <div class="accordion-body">
                    <ul class="info-list">
                        <li>Passengers are advised to present their travel documents and collect boarding pass at Batam Fast counter at least 60 minutes before departure time.</li>
                        <li>Please check-in early and proceed to the departure/immigration gate at least 60 minutes before departure time.</li>
                        <li>Immigration gate closes 30 minutes before departure time.</li>
                        <li>We reserve the rights to deny your boarding if you do not comply with the travel requirements and regulations.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="accordion info-accordion mb-3" id="boardingCollection">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBoarding">
                        Please Collect Your Boarding Pass From:
                    </button>
                </h2>
                <div id="collapseBoarding" class="accordion-collapse collapse show">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="location-card">
                                    <div class="location-title">Singapore Counters & Head Office</div>
                                    <p class="location-text">
                                        Harbourfront Centre (1 Maritime Square Harbourfront Centre #02-50/51 Singapore 099253)<br>
                                        Tanah Merah Ferry Terminal (50 Tanah Merah Ferry Road #01-16/17 Singapore 498833)<br>
                                        HarbourFront (1 Harbourfront Place #07-01 Harbourfront Tower One Singapore)
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="location-card">
                                    <div class="location-title">Batam - Indonesia Sales & Operation Office</div>
                                    <p class="location-text">
                                        Batam Centre Ferry Terminal<br>
                                        Sekupang International Ferry Terminal<br>
                                        Nongsapura Ferry Terminal<br>
                                        P.T. Batamfast Indonesia (AMI Building, Jl. Martadinata Lot 02, Sekupang - Batam, Kepulauan Riau, Indonesia 29428)
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="location-card">
                                    <div class="location-title">Johor - Malaysia Sales & Operation Office</div>
                                    <p class="location-text">
                                        Tanjung Pengelih Ferry Terminal (Kompleks Jeti Penumpang & Marina Awam Tanjung Pengelih 81600 Pengerang, Johor Darul Ta’zim)<br>
                                        Desaru Coast Ferry Terminal (Retail Unit 2, Terminal Feri Desaru Coast, Jalan Dermaga, Desaru Coast, 81930 Bandar Penawar, Johor Darul Ta'zim)<br>
                                    </p>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion info-accordion mb-3" id="insuranceAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInsurance">
                        Travel Insurance
                    </button>
                </h2>
                <div id="collapseInsurance" class="accordion-collapse collapse show">
                    <div class="accordion-body">
                        <ul class="info-list">
                            <li>If you require Travel Insurance, please visit our website or <a href="https://buy.allianz-assistance.com.sg/B2C/TRAVEL/SG/step-1" target="_blank" class="link-coral">click here</a>.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion info-accordion mb-3" id="termsAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTerms">
                        Terms & Conditions
                    </button>
                </h2>
                <div id="collapseTerms" class="accordion-collapse collapse show">
                    <div class="accordion-body">
                        <ul class="info-list">
                            <li>Sindo Ferry reserves the full right to cancel, vary its schedule and services, as its absolute direction, without prior notice, and shall not be responsible for any delays arising therefrom.</li>
                            <li>Our full terms and conditions and Fare's Rules apply. For full detail, please visit our website or <a href="https://vtltravel.com/terms-and-conditions/" target="_blank" class="link-coral">click here</a>.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="container final-footer">
    <div class="footer-msg">
        <p>
            We look forward to welcoming you onboard. We hope you have a pleasant journey with us. 
            If you need any further assistance, please 
            <a href="https://wa.me/6582955180?text=Hi%20there!%20I%20have%20some%20questions%20on%20Batam%20Ferry%20Tickets!%20Booking%20ID%3A%20<?= urlencode($bookingId) ?>" id="wa-trigger" target="_blank" class="link-coral">click here</a> for our contact information.
        </p>
        <p class="mt-3">Thank you.</p>
    </div>

    <div class="footer-bottom-links">
        <p>
            <a href="https://www.batamferrytickets.com" target="_blank">www.batamferrytickets.com</a>
            <span class="footer-divider">•</span>
            <a href="https://vtltravel.com/terms-and-conditions/" target="_blank">Terms & Conditions</a>
        </p>
    </div>
</div>

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TWBQ2KLF"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto PDF Download functionality - ALWAYS TRIGGERS
(function() {
    'use strict';
    
    // Configuration
    const BOOKING_ID = '<?php echo htmlspecialchars($bookingId); ?>';
    const AUTO_DOWNLOAD_DELAY = 1500; // Delay in milliseconds before triggering
    
    // Elements
    const loadingOverlay = document.getElementById('loadingOverlay');
    const notification = document.getElementById('pdfNotification');
    
    // Track if print dialog has been triggered
    let printTriggered = false;
    
    // Show loading overlay function
    function showLoadingOverlay() {
        if (loadingOverlay) {
            loadingOverlay.style.display = 'flex';
        }
    }
    
    // Hide loading overlay function
    function hideLoadingOverlay() {
        if (loadingOverlay) {
            loadingOverlay.style.display = 'none';
        }
    }
    
    // Show notification function
    function showNotification(message) {
        if (notification) {
            notification.style.display = 'flex';
            notification.querySelector('span').textContent = message || 'Opening print dialog to save as PDF...';
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                if (notification) {
                    notification.classList.add('fade-out');
                    setTimeout(() => {
                        notification.style.display = 'none';
                        notification.classList.remove('fade-out');
                    }, 500);
                }
            }, 5000);
        }
    }
    
    // Function to trigger PDF download (print dialog)
    function triggerPDFDownload() {
        // Prevent multiple triggers
        if (printTriggered) {
            console.log('Print already triggered, skipping...');
            return;
        }
        
        printTriggered = true;
        console.log('Triggering PDF download for booking: ' + BOOKING_ID);
        
        // Show loading overlay
        showLoadingOverlay();
        
        // Show notification
        
        // Small delay to ensure UI updates
        setTimeout(() => {
            // Trigger browser's print dialog (Save as PDF)
            window.print();
            
            // After print dialog is closed, hide loading overlay
            setTimeout(() => {
                hideLoadingOverlay();
                
                // Update notification
                
                // Reset trigger after a while to allow re-triggering if needed
                setTimeout(() => {
                    printTriggered = false;
                }, 5000);
            }, 1000);
        }, 500);
    }
    
    // Wait for page to fully load
    window.addEventListener('load', function() {
        console.log('Page loaded, preparing auto PDF download...');
        
        // Always trigger PDF download on every page load
        setTimeout(triggerPDFDownload, AUTO_DOWNLOAD_DELAY);
    });
    
    // Manual download function (can be called from button)
    window.manualDownload = function() {
        triggerPDFDownload();
    };
    
    // Optional: Add keyboard shortcut (Ctrl+P) - but prevent default to avoid double trigger
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            triggerPDFDownload();
        }
    });
    
    // Handle page visibility changes (for when returning from print dialog)
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible') {
            console.log('Page became visible again');
            // Optionally do something when returning from print dialog
        }
    });
    
})();

// Keep your existing console log
document.addEventListener('DOMContentLoaded', function() {
    console.log('E-Ticket loaded for booking: <?= htmlspecialchars($bookingId) ?>');
});
</script>

<!-- Add a small style for better print experience -->
<style>
    @media print {
        body {
            padding: 20px;
            background: white;
        }
        
        /* Ensure all content is visible in print */
        .container {
            max-width: 100%;
        }
        
        /* Hide interactive elements in print */
        .btn, .manual-download-btn, .pdf-notification, .loading-overlay {
            display: none !important;
        }
        
        /* Ensure colors print well */
        .confirmation-banner {
            background-color: #e8f9ee !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .check-icon {
            background-color: #ff6b6b !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .bg-outbound {
            background-color: #1e3a8a !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .bg-return {
            background-color: #ff8a75 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .gate-info {
            background-color: #f8f9fa !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .passenger-table thead {
            background-color: #e9ecef !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .status-paid {
            color: #22c55e !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>
</body>
</html>