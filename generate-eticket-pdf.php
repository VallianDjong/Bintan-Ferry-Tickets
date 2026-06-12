<?php
// generate-eticket-pdf.php
// ─────────────────────────────────────────────────────────────────────────────
// FIXES vs original file:
//
//  1. Removed session_start() — this file is include()d from inside
//     sendBookingConfirmationEmail() where the session is already active.
//     Calling session_start() again causes a PHP notice and can corrupt output.
//
//  2. Removed header('Location:') redirect — header() calls are swallowed
//     by ob_start() but they also prevent the HTML from being returned;
//     the caller already guarantees booking_confirmed is set.
//
//  3. Fixed trip lookup key: old code used lowercase 'departTrips' / 'returnTrips'
//     and matched by $trip['id']. Koobysae API stores them as 'DepartTrips'
//     (capital D) and uses a composite key: RouteCode . '_' . DepartureTime.
//
//  4. Fixed field names on trip details:
//       OLD → NEW
//       vesselName      → ShipCode
//       portOriginName  → read from search_params departure_route split
//       portDestinationName → same
//       departDate      → DepartureDate
//       etd             → DepartureTime
//       (return equivalents use ReturnDate / ReturnTime)
//
//  5. Fixed passenger name field: old code read name_{i}, new booking-review.php
//     saves first_name_{i} + last_name_{i} separately.
//
//  6. Fixed undefined $grandTotal variable in promo discount block.
// ─────────────────────────────────────────────────────────────────────────────

// ── Session data ──────────────────────────────────────────────────────────────
$bookingResult   = $_SESSION['booking_result']    ?? [];
$bookingFormData = $_SESSION['booking_form_data'] ?? [];
$searchResults   = $_SESSION['search_results']    ?? [];
$searchParams    = $_SESSION['search_params']     ?? [];
$selectedDep     = $_SESSION['selected_departure'] ?? [];
$selectedRet     = $_SESSION['selected_return']   ?? null;
$confirmData     = $_SESSION['confirmation_data'] ?? [];

$tripType        = $searchParams['trip_type'] ?? 'round_trip';

// ── Passenger counts ──────────────────────────────────────────────────────────
$adultQty        = (int)($searchParams['adultQty']  ?? 1);
$childQty        = (int)($searchParams['childQty']  ?? 0);
$infantQty       = (int)($searchParams['infantQty'] ?? 0);
$totalPassengers = $adultQty + $childQty + $infantQty;

// ── FIX 3 & 4: Trip lookup using Koobysae composite key + capital key name ───
$departTripsList = $searchResults['DepartTrips'] ?? [];   // ← capital D
$depTrip = $retTrip = null;

foreach ($departTripsList as $trip) {
    // Departure trip: match by RouteCode_DepartureTime
    if (!$depTrip) {
        $depKey = ($trip['RouteCode'] ?? '') . '_' . ($trip['DepartureTime'] ?? '');
        if ($depKey === ($selectedDep['trip_id'] ?? '')) {
            $depTrip = $trip;
        }
    }
    // Return trip: match by RouteCode_ReturnTime
    if (!$retTrip && $selectedRet) {
        $retKey = ($trip['RouteCode'] ?? '') . '_' . ($trip['ReturnTime'] ?? '');
        if ($retKey === ($selectedRet['trip_id'] ?? '')) {
            $retTrip = $trip;
        }
    }
    if ($depTrip && ($retTrip || !$selectedRet)) break;
}

// ── FIX 3 & 4: Route names come from search_params, not trip fields ───────────
$depParts = explode('|', $searchParams['departure_route'] ?? '');
$retParts = explode('|', $searchParams['return_route']    ?? '');

// ── FIX 4: Field names for departure ─────────────────────────────────────────
$departOperator    = $depTrip['ShipCode']      ?? 'Batam Fast';      // was vesselName
$departOrigin      = $depParts[1]              ?? 'Singapore';        // was portOriginName
$departDestination = $depParts[2]              ?? 'Batam';            // was portDestinationName
$departDateRaw     = $depTrip['DepartureDate'] ?? '';                 // was departDate
$departTime        = $depTrip['DepartureTime'] ?? '';                 // was etd
$departDate        = $departDateRaw ? date('d M Y', strtotime(substr($departDateRaw, 0, 10))) : '';

// ── FIX 4: Field names for return ─────────────────────────────────────────────
$returnOperator    = '';
$returnOrigin      = '';
$returnDestination = '';
$returnDate        = '';
$returnTime        = '';
$hasReturn         = false;

if ($retTrip && in_array($tripType, ['round_trip', 'open_trip'])) {
    $hasReturn         = true;
    $returnOperator    = $retTrip['ShipCode']  ?? 'Batam Fast';      // was vesselName
    $returnOrigin      = $retParts[1]          ?? 'Batam';            // was portOriginName
    $returnDestination = $retParts[2]          ?? 'Singapore';        // was portDestinationName
    // ✅ FIX — use user's selected date, fall back to API field
$returnDateRaw = $searchParams['return_date'] ?? ($retTrip['ReturnDate'] ?? '');
$returnDate    = $returnDateRaw ? date('d M Y', strtotime(substr($returnDateRaw, 0, 10))) : '';
    $returnTime        = $retTrip['ReturnTime'] ?? '';                // was etd
    
}

// ── FIX 5: Passenger names — read first_name_{i} + last_name_{i} ─────────────
$passengerDetails = [];
for ($i = 1; $i <= $totalPassengers; $i++) {
    $firstName = trim($bookingFormData["first_name_{$i}"] ?? '');  // was name_{i}
    $lastName  = trim($bookingFormData["last_name_{$i}"]  ?? '');
    $fullName  = strtoupper(trim("$firstName $lastName")) ?: "PASSENGER $i";

    $passengerDetails[] = [
        'name'       => $fullName,
        'passportNo' => strtoupper($bookingFormData["passportNo_{$i}"] ?? ''),
        'type'       => $bookingFormData["passenger_type_{$i}"] ?? 'adult',
    ];
}

// ── Booking summary ───────────────────────────────────────────────────────────
$bookingId    = $bookingResult['id']          ?? $confirmData['booking_id'] ?? 'N/A';
$paymentRef   = $confirmData['payment_ref']   ?? $_SESSION['payment_request_id'] ?? 'N/A';
$bookingDate  = date('d M Y', strtotime($confirmData['confirmed_at'] ?? 'now'));
$contactName  = $bookingFormData['full_name'] ?? 'Guest User';
$contactEmail = $bookingFormData['email']     ?? '';

// ── Prices ────────────────────────────────────────────────────────────────────
$depFare       = floatval($depTrip['AdultPrice'] ?? $selectedDep['price'] ?? 0);
$depSurcharge  = floatval($depTrip['Surcharge']  ?? 0);
$retFare       = 0;
$retSurcharge  = 0;

if ($tripType === 'round_trip' && $retTrip) {
    $retFare      = floatval($retTrip['AdultPrice'] ?? 0);
    $retSurcharge = floatval($retTrip['Surcharge']  ?? 0);
} elseif ($tripType === 'open_trip') {
    $retFare      = $depFare;
    $retSurcharge = $depSurcharge;
}

$currency       = $selectedDep['currency'] ?? 'SGD';
$currencySymbol = ($currency === 'SGD') ? 'S$' : 'Rp ';

$depChildFare = floatval($depTrip['ChildPrice'] ?? $depFare);
$retChildFare = floatval($retTrip['ChildPrice'] ?? $retFare);

$depTotal   = (($depFare + $depSurcharge + 6) * $adultQty) + (($depChildFare + $depSurcharge + 6) * $childQty);
$retTotal   = (($retFare + $retSurcharge + 6) * $adultQty) + (($retChildFare + $retSurcharge + 6) * $childQty);
$grandTotal = $depTotal + $retTotal;

// ── FIX 6: $grandTotal is now defined before it's used in promo block ─────────
$promoDiscount = 0;
$promoCode     = '';
if (!empty($_SESSION['promo_code']) && isset($_SESSION['promo_discount'])) {
    $promoCode   = $_SESSION['promo_code'];
    $pVal        = $_SESSION['promo_discount'];
    $pType       = $_SESSION['promo_type'] ?? 'percentage';
    $promoDiscount = ($pType === 'percentage')
        ? $grandTotal * $pVal
        : min($pVal, $grandTotal);
}

// Use session-stored total if available (most accurate — set in booking-checking.php)
$paymentAmount = floatval($_SESSION['grand_total_after_discount'] ?? ($grandTotal - $promoDiscount));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>E-Ticket #<?= htmlspecialchars($bookingId) ?></title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.4;
            font-size: 13px;
        }
        .header {
            background-color: #e34e4a;
            color: white;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .header h1 { margin: 0; font-size: 22px; }
        .header p  { margin: 5px 0 0; font-size: 14px; }
        .section {
            margin-bottom: 18px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        .section-title {
            background-color: #f5f5f5;
            padding: 8px 14px;
            font-weight: bold;
            font-size: 14px;
            border-bottom: 1px solid #ddd;
        }
        .section-content { padding: 12px 14px; }
        .label { font-weight: bold; color: #666; font-size: 11px; margin-bottom: 2px; }
        .value { font-weight: bold; font-size: 13px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table th { background-color: #f5f5f5; padding: 7px 10px; text-align: left; font-size: 11px; border: 1px solid #ddd; }
        table td { padding: 7px 10px; border: 1px solid #ddd; font-size: 12px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
        .journey-box { background-color: #f9f9f9; padding: 12px; border: 1px solid #eee; border-radius: 4px; }
        .journey-title { font-weight: bold; color: #e34e4a; margin-bottom: 8px; font-size: 13px; }
        .total-row { background-color: #e34e4a; color: white; padding: 10px 14px; font-weight: bold; font-size: 16px; text-align: right; }
        .payment-info { background-color: #e8f9ee; padding: 8px 12px; border-radius: 3px; margin-top: 8px; font-size: 12px; }
        .boarding-info { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 12px 14px; margin-top: 8px; border-radius: 0 4px 4px 0; }
        .boarding-info h3 { margin: 0 0 8px; color: #856404; font-size: 14px; }
        .boarding-info p  { margin: 6px 0; color: #856404; font-size: 12px; line-height: 1.5; }
        .address-box { background-color: #fff; border: 1px solid #ffe69c; padding: 10px 12px; border-radius: 4px; margin-top: 8px; font-size: 12px; }
        .address-box strong { color: #e34e4a; }
        .info-section { margin-bottom: 12px; }
        .info-section h3 { font-size: 13px; margin: 0 0 6px; color: #333; }
        .info-section ul { margin: 0; padding-left: 18px; font-size: 12px; }
        .info-section li { margin-bottom: 4px; }
        .footer { margin-top: 20px; text-align: center; font-size: 11px; color: #999; border-top: 1px solid #eee; padding-top: 14px; }
    </style>
</head>
<body>

<div class="header">
    <h1>E-TICKET — Bintanferrytickets.com</h1>
    <p>Booking #<?= htmlspecialchars($bookingId) ?></p>
</div>

<div class="grid-2">
    <div class="section">
        <div class="section-title">Passenger Information</div>
        <div class="section-content">
            <div class="label">Name</div>
            <div class="value"><?= htmlspecialchars($contactName) ?></div>
            <div class="label">Email</div>
            <div class="value"><?= htmlspecialchars($contactEmail) ?></div>
            <div class="label">Total Passengers</div>
            <div class="value"><?= $totalPassengers ?> (Adult: <?= $adultQty ?>, Child: <?= $childQty ?>, Infant: <?= $infantQty ?>)</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Booking Information</div>
        <div class="section-content">
            <div class="label">Booking Date</div>
            <div class="value"><?= $bookingDate ?></div>
            <div class="label">Payment Reference</div>
            <div class="value"><?= htmlspecialchars($paymentRef) ?></div>
            <div class="label">Status</div>
            <div class="value" style="color:#28a745;">CONFIRMED</div>
        </div>
    </div>
</div>

<!-- Departure -->
<div class="section">
    <div class="section-title">Departure Journey</div>
    <div class="section-content">
        <div class="journey-box">
            <div class="journey-title"><?= htmlspecialchars($departOrigin) ?> - <?= htmlspecialchars($departDestination) ?></div>
            <div class="grid-2" style="margin-bottom:0;">
                <div>
                    <div class="label">Date</div>
                    <div class="value"><?= $departDate ?: '—' ?></div>
                </div>
                <div>
                    <div class="label">Time (SGT)</div>
                    <div class="value"><?= $departTime ?: '—' ?></div>
                </div>
            </div>
            <div class="label">Gate</div>
            <div class="value">Check-in at least 90 min before departure</div>
        </div>
    </div>
</div>

<!-- Return (only if applicable) -->
<?php if ($hasReturn): ?>
<div class="section">
    <div class="section-title">Return Journey<?= $tripType === 'open_trip' ? ' (Open Return)' : '' ?></div>
    <div class="section-content">
        <div class="journey-box">
            <div class="journey-title"><?= htmlspecialchars($returnOrigin) ?> - <?= htmlspecialchars($returnDestination) ?></div>
            <div class="grid-2" style="margin-bottom:0;">
                <div>
                    <div class="label">Date</div>
                    <div class="value"><?= $returnDate ?: ($tripType === 'open_trip' ? 'Open (flexible)' : '—') ?></div>
                </div>
                <div>
                    <div class="label">Time (WIB)</div>
                    <div class="value"><?= $returnTime ?: '—' ?></div>
                </div>
            </div>
            <div class="label">Gate</div>
            <div class="value">Check-in at least 90 min before departure</div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Passenger list -->
<div class="section">
    <div class="section-title">Passenger List</div>
    <div class="section-content">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Passport No.</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($passengerDetails as $i => $pax): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($pax['name']) ?></td>
                    <td><?= htmlspecialchars($pax['passportNo']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($pax['type'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Payment summary -->
<div class="section">
    <div class="section-title">Payment Summary</div>
    <div class="section-content">

        <?php if ($promoDiscount > 0): ?>
        <div class="label" style="color:#28a745;">Promo Discount (<?= htmlspecialchars($promoCode) ?>)</div>
        <div class="value" style="color:#28a745;"> <?= $currencySymbol . number_format($promoDiscount, 2) ?></div>
        <?php endif; ?>
    </div>
    <div class="total-row">
        Total Paid: <?= $currencySymbol . number_format($paymentAmount, 2) ?>
    </div>
    <div class="section-content">
        <div class="payment-info">
            <strong>Payment Status:</strong> PAID — Thank you for your payment.
        </div>
    </div>
</div>

<!-- How to board -->
<div class="section">
    <div class="section-title">How to Get Your Boarding Pass</div>
    <div class="section-content">
        <div class="boarding-info">
            <p>This is your electronic ticket. Present it at the BRF counter at least <strong>90 minutes before departure</strong>. Your boarding pass will be issued upon verification with your passport.</p>
            <div class="address-box">
                <strong>Singapore — Tanah Merah Ferry Terminal</strong><br>
                50 Tanah Merah Ferry Road #01-21<br>
                Singapore 498833
            </div>
            <div class="address-box">
                <strong>Bintan — Bandar Bentan Telani Ferry Terminal</strong><br>
                Jl. Raja Haji Teluk Sebong, Lagoi Bintan Utara<br>
                Tanjung Uban 29155
            </div>
            <p style="margin-top:10px;">
                <strong>Reminders:</strong><br>
                • Arrive at least 90 min before departure<br>
                • Bring your passport for verification<br>
                • Present this e-ticket (printed or mobile)
            </p>
        </div>
    </div>
</div>

<!-- Travel information -->
<div class="section">
    <div class="section-title">Important Travel Information</div>
    <div class="section-content">
        <div class="info-section">
            <h3>Required Documents</h3>
            <ul>
                <li>Valid passport with minimum 6 months validity from date of travel.</li>
                <li>Passengers are responsible for all entry/exit visas and travel documents required by law.</li>
                <li>BRF reserves the right to refuse boarding to non-compliant passengers.</li>
            </ul>
        </div>
        <div class="info-section">
            <h3>Baggage</h3>
            <ul>
                <li>Each passenger is entitled to <strong>20 kg</strong> of baggage.</li>
                <li>Do not bring liquids, dangerous or prohibited goods on board.</li>
            </ul>
        </div>
        <div class="info-section">
            <h3>Check-in Times</h3>
            <ul>
                <li>Present travel documents at the BRF counter <strong>90 min before departure</strong>.</li>
                <li>Proceed to departure/immigration gate <strong>60 min before departure</strong>.</li>
                <li>Immigration gate closes <strong>30 min before departure</strong>.</li>
            </ul>
        </div>
        <div class="info-section">
            <h3>Terms & Conditions</h3>
            <ul>
                <li>Bintan Ferry Resort reserves the right to cancel or vary schedules without prior notice.</li>
                <li>This booking is <strong>non-refundable and non-changeable</strong>.</li>
            </ul>
        </div>
        <p style="margin-top:10px;font-size:12px;">We look forward to welcoming you onboard. For assistance: <strong>hello@vtltravel.com</strong></p>
    </div>
</div>

<div class="footer">
    <p>For assistance: hello@vtltravel.com | www.bintanferrytickets.com</p>
    <p>© <?= date('Y') ?> Bintan Ferry Tickets. VTL Travel Private Limited. Travel Agent Licence TA03084.</p>
</div>

</body>
</html>