<?php
/**
 * index_cache_integration.php
 *
 * INSTRUCTIONS:
 * 1. Place ferry_cache_manager.php in your site root (same folder as index.php)
 * 2. In index.php, replace the top of your file (after session_start()) with:
 *    require_once __DIR__ . '/ferry_cache_manager.php';
 * 3. Replace the fetchSectors() call and POST handler with the code below.
 *
 * This file shows ONLY the parts that change. Keep everything else in index.php as-is.
 */

// ─── At the TOP of index.php, replace all the old function definitions ────────
// Just add this one line after session_start():
//   require_once __DIR__ . '/ferry_cache_manager.php';
//
// Then remove: getApiToken(), makeApiCall(), fetchSectors(), fetchSectorsFromAPI(),
//              getCachedSchedule(), saveScheduleCache(), buildScheduleCacheKey()
// All replaced by ferry_cache_manager.php functions.

// ─── Replace fetchSectors() call ─────────────────────────────────────────────
// OLD:  $sectorsResult = fetchSectors();
// NEW:
$routesResult = get_routes_cached();
$sectors = $routesResult['routes'] ?? [];

// Filter unwanted routes (keep your existing filter logic)
$excludedRoutes = [
    'Pengelih - TanahMerah', 'TanahMerah - Pengelih',
    'TanahMerah - Desaru',   'Desaru - TanahMerah',
    'Pasir Gudang - Batam Center', 'Batam Center - Pasir Gudang',
];
$sectors = array_values(array_filter($sectors, function($s) use ($excludedRoutes) {
    $name = ($s['Outward'] ?? '') . ' - ' . ($s['Arrival'] ?? '');
    return !in_array($name, $excludedRoutes);
}));

if (!empty($sectors)) {
    $_SESSION['sectors'] = $sectors;
}

// ─── Replace the POST handler ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['departure_route'])) {

    unset($_SESSION['search_results'], $_SESSION['search_params'],
          $_SESSION['selected_departure'], $_SESSION['selected_return']);

    $departureRoute = $_POST['departure_route'] ?? '';
    $returnRoute    = $_POST['return_route']    ?? '';
    $adultQty       = max(1, (int)($_POST['adult_count']  ?? 1));
    $childQty       = max(0, (int)($_POST['child_count']  ?? 0));
    $infantQty      = 0;
    $tripType       = $_POST['trip_type'] ?? 'round_trip';
    $seatClass      = $_POST['seat_class'] ?? 'economy';
    $seatCategory   = ucfirst($seatClass); // "Economy" | "Emerald"

    $rawDepartDate = $_POST['journey_date'] ?? '';
    $rawReturnDate = $_POST['return_date']  ?? '';
    $departDate    = !empty($rawDepartDate) ? date('Y-m-d', strtotime(str_replace(',', '', $rawDepartDate))) : date('Y-m-d');
    $returnDate    = !empty($rawReturnDate) ? date('Y-m-d', strtotime(str_replace(',', '', $rawReturnDate))) : null;

    // Parse route parts: RouteCode|Origin|Destination|OriginCode|DestCode
    $departParts = explode('|', $departureRoute);
    $returnParts = explode('|', $returnRoute);

    $departRouteCode = $departParts[0] ?? '';
    $returnRouteCode = $returnParts[0] ?? '';

    $isRoundTrip  = ($tripType === 'round_trip' || $tripType === 'open_trip') ? 1 : 0;
    $isReturnOpen = ($tripType === 'open_trip') ? 1 : 0;

    // Validation
    $errors = [];
    if (empty($departRouteCode))              $errors[] = 'Please select a valid departure route';
    if ($isRoundTrip && empty($returnRouteCode)) $errors[] = 'Please select a return route';
    if ($tripType === 'round_trip' && empty($returnDate)) $errors[] = 'Please select a return date';
    if ($adultQty < 1)                        $errors[] = 'At least 1 adult is required';

    if (!empty($errors)) {
        $_SESSION['search_error']    = implode('<br>', $errors);
        $_SESSION['last_api_request'] = $_POST;
        header('Location: index.php');
        exit;
    }

    $totalPassengers = $adultQty + $childQty + $infantQty;

    // ── Fetch schedules using cache ───────────────────────────────────────────
    if ($isRoundTrip && $returnDate && !$isReturnOpen) {
        // Round trip: fetch both directions independently (each cached separately)
        $scheduleData = get_round_trip_schedules(
            $departRouteCode, $departDate,
            $returnRouteCode, $returnDate,
            $seatCategory
        );
        $departTrips = $scheduleData['depart'];
        $returnTrips = $scheduleData['return'];

        $_SESSION['search_results'] = [
            'DepartTrips'  => $departTrips,
            'ReturnTrips'  => $returnTrips,
            'departDate'   => $departDate,
            'returnDate'   => $returnDate,
            'isRoundTrip'  => 1,
            'isReturnOpen' => 0,
            'cache_source' => $scheduleData['source'],
        ];

    } else {
        // One-way or open trip: only departure needed
        $scheduleData = get_schedule_cached($departRouteCode, $departDate, $seatCategory);
        $departTrips  = $scheduleData['trips'];

        $_SESSION['search_results'] = [
            'DepartTrips'  => $departTrips,
            'ReturnTrips'  => [],
            'departDate'   => $departDate,
            'returnDate'   => $returnDate,
            'isRoundTrip'  => $isReturnOpen ? 1 : 0,
            'isReturnOpen' => $isReturnOpen,
            'cache_source' => $scheduleData['source'],
        ];
    }

    // Check if we got any trips
    if (empty($departTrips)) {
        $_SESSION['search_error'] = isset($scheduleData['error'])
            ? $scheduleData['error']
            : 'No trips found for your selected route and date. Please try different options.';
        header('Location: index.php');
        exit;
    }

    $_SESSION['search_params'] = [
        'departure_route'  => $departureRoute,
        'return_route'     => $returnRoute,
        'depart_date'      => $departDate,
        'return_date'      => $returnDate,
        'trip_type'        => $tripType,
        'adultQty'         => $adultQty,
        'childQty'         => $childQty,
        'infantQty'        => $infantQty,
        'total_passengers' => $totalPassengers,
        'seat_class'       => $seatClass,
    ];

    header('Location: search-result.php');
    exit;
}

// ─── END OF REPLACED SECTION ─────────────────────────────────────────────────
// Everything else in index.php (HTML, navbar, hero, booking card, etc.) stays unchanged.
