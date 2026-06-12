<?php
// Start session and include token management
session_start();

require_once __DIR__ . '/ferry_cache_manager.php';

// Display search error if exists
$searchError = $_SESSION['search_error'] ?? null;
if ($searchError) {
    unset($_SESSION['search_error']);
}

// Force error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Unset all session variables
session_unset();

$currentPage = basename($_SERVER['PHP_SELF']);

// Function to get API token (FIXED)
function getApiToken() {
    // Check if token exists in session and is still valid (not expired)
    if (isset($_SESSION['api_token']) && isset($_SESSION['token_expiry']) && time() < $_SESSION['token_expiry']) {
        return $_SESSION['api_token'];
    }
    
    // If no valid token exists, fetch a new one
    $url = "http://apitest-koobysae.brf.com.sg/token";
    
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

// Get token for use in this page
$apiToken = getApiToken();

// Function to make API calls using the token
// Function to make API calls using the token
function makeApiCall($endpoint, $method = 'GET', $data = []) {
    $token = getApiToken();
    if (!$token) {
        return ['error' => 'Unable to authenticate with API'];
    }
    
    $url = "http://apitest-koobysae.brf.com.sg/api/" . ltrim($endpoint, '/');
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    
    // IMPORTANT: The GetSchedule endpoint requires Username and Password in the request body
    // Build the request data with credentials FIRST
    $apiData = [
        'Username' => 'vtapi',
        'Password' => 'Vtl123456'
    ];
    
    // Map your internal data structure to what the API expects
    if (isset($data['departPortOriginId']) && isset($data['departPortDestinationId'])) {
        // For Bintan to Singapore routes, use the RouteCode directly
        // Based on your sectors, BBTTMT is Bintan->Singapore, TMTBBT is Singapore->Bintan
        if ($data['departPortOriginId'] == 'BBT' && $data['departPortDestinationId'] == 'TMT') {
            $apiData['Route'] = 'BBTTMT';
        } elseif ($data['departPortOriginId'] == 'TMT' && $data['departPortDestinationId'] == 'BBT') {
            $apiData['Route'] = 'TMTBBT';
        } else {
            // If you have more routes, add them here
            $apiData['Route'] = $data['departPortOriginId'] . $data['departPortDestinationId'];
        }
    }
    
    // Add departure date
    if (isset($data['departDate'])) {
        $apiData['DepartureDate'] = $data['departDate'];
    }
    
    // Add return date for round trips
    if (isset($data['isRoundTrip']) && $data['isRoundTrip'] == 1 && isset($data['returnDate'])) {
        $apiData['ReturnDate'] = $data['returnDate'];
    }
    
    // Add journey type: 0 = one way, 1 = round trip
    $apiData['JourneyType'] = (isset($data['isRoundTrip']) && $data['isRoundTrip'] == 1) ? 1 : 0;
    
    // Encode as JSON
    $postData = json_encode($apiData);
    
    error_log("GetSchedule Request Data: " . $postData);
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_VERBOSE, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        error_log("API Call Error: " . $error_msg);
        return ['error' => 'API call failed: ' . $error_msg];
    }
    
    curl_close($ch);
    
    $decoded = json_decode($response, true);
    
    error_log("GetSchedule API Response (HTTP $httpCode): " . print_r($decoded, true));
    
    if ($httpCode == 401) {
        unset($_SESSION['api_token'], $_SESSION['token_expiry']);
        return makeApiCall($endpoint, $method, $data);
    }
    
    return [
        'status' => $httpCode,
        'data' => $decoded
    ];
}

// Function to fetch sectors from API
// Cache configuration
define('CACHE_DIR', __DIR__ . '/cache/');
define('ROUTES_CACHE_FILE', CACHE_DIR . 'routes_cache.json');
define('CACHE_DURATION', 86400); // 24 hours in seconds

// Ensure cache directory exists
if (!file_exists(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0755, true);
}

/**
 * Fetch sectors with daily file-based caching
 * API only called once every 24 hours
 */
function fetchSectors() {
    // Check if we have a valid cache file
    if (file_exists(ROUTES_CACHE_FILE)) {
        $cacheData = json_decode(file_get_contents(ROUTES_CACHE_FILE), true);
        
        // Check if cache is still valid (less than 24 hours old)
        if (isset($cacheData['timestamp']) && isset($cacheData['data'])) {
            $cacheAge = time() - $cacheData['timestamp'];
            
            if ($cacheAge < CACHE_DURATION) {
                error_log("Routes cache hit - Age: " . round($cacheAge/60, 1) . " minutes");
                return [
                    'status' => 200,
                    'data' => $cacheData['data'],
                    'cached' => true
                ];
            } else {
                error_log("Routes cache expired - Age: " . round($cacheAge/60, 1) . " minutes");
            }
        }
    }
    
    error_log("Routes cache miss - Fetching from API");
    
    // No valid cache, fetch from API
    $result = fetchSectorsFromAPI();
    
    // If successful, save to cache
    if (isset($result['data']) && is_array($result['data']) && !isset($result['error'])) {
        $cacheData = [
            'timestamp' => time(),
            'expires_at' => time() + CACHE_DURATION,
            'data' => $result['data']
        ];
        
        file_put_contents(ROUTES_CACHE_FILE, json_encode($cacheData));
        error_log("Routes cached successfully until " . date('Y-m-d H:i:s', time() + CACHE_DURATION));
        
        // Add cache info to result
        $result['cached'] = false;
    }
    
    return $result;
}

/**
 * Original API call function (renamed from fetchSectors)
 */
function fetchSectorsFromAPI() {
    // First, get a valid token
    $token = getApiToken();
    if (!$token) {
        return ['error' => 'Unable to authenticate with API'];
    }
    
    $url = "http://apitest-koobysae.brf.com.sg/api/GetRoutes/";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    
    // Send credentials in JSON body along with the Bearer token
    $postData = json_encode([
        'grant_type' => 'password',
        'username'   => 'vtapi',
        'password'   => 'Vtl123456'
    ]);
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_VERBOSE, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        error_log("GetRoutes API Error: " . $error_msg);
        return ['error' => 'API call failed: ' . $error_msg];
    }
    
    curl_close($ch);
    
    error_log("GetRoutes Response HTTP Code: " . $httpCode);
    
    $decoded = json_decode($response, true);
    
    // If token expired (401), clear token and retry once
    if ($httpCode == 401) {
        unset($_SESSION['api_token'], $_SESSION['token_expiry']);
        return fetchSectorsFromAPI(); // Retry once
    }
    
    // Check if we got a valid response
    if ($decoded) {
        if (isset($decoded['Status']) && $decoded['Status'] === 'Success') {
            // Success - extract routes
            $routes = isset($decoded['Routes']) ? $decoded['Routes'] : [];
            return [
                'status' => $httpCode,
                'data' => $routes
            ];
        } else {
            // API returned an error
            $errorMsg = isset($decoded['Message']) ? $decoded['Message'] : 'Unknown API error';
            $errorCode = isset($decoded['ErrorCode']) ? $decoded['ErrorCode'] : 'Unknown';
            error_log("API Error: Code $errorCode - $errorMsg");
            return [
                'error' => "API Error: $errorMsg (Code: $errorCode)",
                'status' => $httpCode,
                'data' => []
            ];
        }
    }
    
    return [
        'status' => $httpCode,
        'data' => [],
        'error' => 'Invalid response from API'
    ];
}

/**
 * Optional: Function to manually clear the cache (useful for admin panel)
 */
function clearRoutesCache() {
    if (file_exists(ROUTES_CACHE_FILE)) {
        unlink(ROUTES_CACHE_FILE);
        return true;
    }
    return false;
}

/**
 * Optional: Display cache status (for debugging)
 */
function getCacheStatus() {
    if (!file_exists(ROUTES_CACHE_FILE)) {
        return "No cache file exists";
    }
    
    $cacheData = json_decode(file_get_contents(ROUTES_CACHE_FILE), true);
    if (!isset($cacheData['timestamp'])) {
        return "Invalid cache file";
    }
    
    $age = time() - $cacheData['timestamp'];
    $expiresIn = CACHE_DURATION - $age;
    
    return sprintf(
        "Cache created: %s ago | Expires in: %s | Routes count: %d",
        secondsToHumanReadable($age),
        secondsToHumanReadable($expiresIn),
        count($cacheData['data'] ?? [])
    );
}

function secondsToHumanReadable($seconds) {
    if ($seconds < 60) return $seconds . " seconds";
    if ($seconds < 3600) return round($seconds/60, 1) . " minutes";
    if ($seconds < 86400) return round($seconds/3600, 1) . " hours";
    return round($seconds/86400, 1) . " days";
}

// Fetch sectors data
$sectorsResult = fetchSectors();
$sectors = [];

// Debug: Log the result
error_log("Sectors Result: " . print_r($sectorsResult, true));

if (isset($sectorsResult['data']) && is_array($sectorsResult['data']) && !isset($sectorsResult['error'])) {
    $sectors = $sectorsResult['data'];
    
    // Store in session for use in other pages if needed
    $_SESSION['sectors'] = $sectors;
} else {
    // Debug: Show error if any
    echo "<!-- Error fetching sectors: " . htmlspecialchars(json_encode($sectorsResult)) . " -->";
}

// Default selected route (can be from POST/GET or first sector)
$defaultRouteId = 'HFC-BTC'; // The sector ID we discovered
$selectedRouteId = $_POST['route_id'] ?? $_GET['route_id'] ?? $defaultRouteId;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['departure_route'])) {

    // Clear previous session data to start fresh
    unset($_SESSION['search_results'], $_SESSION['search_params'], $_SESSION['selected_departure'], $_SESSION['selected_return']);
    
    // Collect form data
    $departureRoute = $_POST['departure_route'] ?? '';
    $returnRoute = $_POST['return_route'] ?? '';

    // 1. Capture Inputs
    $adultQty = isset($_POST['adult_count']) ? (int)$_POST['adult_count'] : 1;
    $childQty = isset($_POST['child_count']) ? (int)$_POST['child_count'] : 0;
    $infantQty = 0;
    
    $rawDepartDate = $_POST['journey_date'] ?? '';
    $rawReturnDate = $_POST['return_date'] ?? '';

    // Convert dates from format "d M, Y" to YYYY-MM-DD
    $departDate = !empty($rawDepartDate) ? date('Y-m-d', strtotime(str_replace(',', '', $rawDepartDate))) : date('Y-m-d');
    
    $tripType = $_POST['trip_type'] ?? 'round_trip';
    
    // Parse route data
    $departParts = explode('|', $departureRoute);
    
        // Determine trip type flags
    if ($tripType === 'one_way') {
        $isRoundTrip = 0;
        $isReturnOpen = 0;
        $returnDate = null;
        $returnParts = [];
    } elseif ($tripType === 'open_trip') {
        $isRoundTrip = 1;  // Open trip IS a round trip
        $isReturnOpen = 1;  // Flag for open return date
        $returnDate = null; // No specific return date for open trips
        $returnParts = explode('|', $returnRoute);
    } else { // round_trip (default)
        $isRoundTrip = 1;
        $isReturnOpen = 0;
        $returnDate = !empty($rawReturnDate) ? date('Y-m-d', strtotime(str_replace(',', '', $rawReturnDate))) : null;
        $returnParts = explode('|', $returnRoute);
    }

    // Validate required fields based on trip type
    $errors = [];
    
    // Validate departure route
    if (empty($departParts) || count($departParts) < 5) {
        $errors[] = "Please select a valid departure route";
    }
    
    // Validate return route for round trips and open trips
    if (($isRoundTrip || $tripType === 'open_trip') && (empty($returnParts) || count($returnParts) < 5)) {
        $errors[] = "Please select a return route";
    }
    
    // Validate return date only for non-open round trips
    if ($tripType === 'round_trip' && empty($returnDate)) {
        $errors[] = "Please select a return date for round trip";
    }
    
    // For open trips, we don't require return date
    if ($tripType === 'open_trip') {
        // Open trip doesn't need a specific return date
        $returnDate = null;
    }
    
    // Validate passenger counts
    if ($adultQty < 1) {
        $errors[] = "At least 1 adult passenger is required";
    }
    
    // If there are errors, redirect back with error message
    if (!empty($errors)) {
        $_SESSION['search_error'] = implode("<br>", $errors);
        $_SESSION['last_api_request'] = $_POST;
        header('Location: index.php');
        exit;
    }

    // 2. Build the API Array - following the exact structure from your example
       // 2. Build the API Array - following the exact structure from your example
    $apiData = [
        'departDate' => $departDate,
        'departPortOriginId' => $departParts[3] ?? '', // port origin ID
        'departPortDestinationId' => $departParts[4] ?? '', // port destination ID
        'isRoundTrip' => $isRoundTrip,
        'isReturnOpen' => $isReturnOpen,
        'adultQty' => $adultQty,
        'childQty' => $childQty,
        'infantQty' => $infantQty
    ];

    // Add return data for round trips (including open trips)
    if ($isRoundTrip) {
        $apiData['returnPortOriginId'] = $returnParts[3] ?? '';
        $apiData['returnPortDestinationId'] = $returnParts[4] ?? '';
        
        // Only add returnDate if it's NOT an open trip
        if (!$isReturnOpen && $returnDate) {
            $apiData['returnDate'] = $returnDate;
        }
    }

    // Debug: Log the API request
    error_log("Trip Type: " . $tripType);
    error_log("isRoundTrip: " . $isRoundTrip);
    error_log("isReturnOpen: " . $isReturnOpen);
    error_log("API Request Data: " . print_r($apiData, true));

// Build a normalized cache key
// Use ferry_cache_manager.php instead of direct API calls
require_once __DIR__ . '/ferry_cache_manager.php';

$departRouteCode = $departParts[0] ?? '';
$returnRouteCode = $returnParts[0] ?? '';
$seatClass = $_POST['seat_class'] ?? 'economy';
$seatCategoryName = ucfirst($seatClass);

if ($isRoundTrip && $returnDate && !$isReturnOpen) {
    $scheduleData = get_round_trip_schedules(
        $departRouteCode, $departDate,
        $returnRouteCode, $returnDate,
        $seatCategoryName
    );
    $searchResult = [
        'data' => array_merge(
            $scheduleData['depart_raw'] ?? [],
            [
                'DepartTrips'  => $scheduleData['depart'] ?? [],
                'isRoundTrip'  => 1,
                'isReturnOpen' => 0,
            ]
        ),
        'status' => 200,
    ];
} else {
    $scheduleData = get_schedule_cached($departRouteCode, $departDate, $seatCategoryName);
    $searchResult = [
        'data' => [
            'DepartTrips'  => $scheduleData['trips'] ?? [],
            'isRoundTrip'  => $isRoundTrip,
            'isReturnOpen' => $isReturnOpen,
        ],
        'status' => 200,
    ];
}
    
    // Debug: Log the API response
    error_log("API Response: " . print_r($searchResult, true));
    
    if (isset($searchResult['data']) && is_array($searchResult['data'])) {
        // Store search results in session for search-result.php
        $_SESSION['search_results'] = $searchResult['data'];
        
        // Save parameters to session
        $_SESSION['search_params'] = [
            'departure_route' => $departureRoute,
            'return_route' => $returnRoute,
            'depart_date' => $departDate,
            'return_date' => $returnDate,
            'trip_type' => $tripType,
            'adultQty' => $adultQty,
            'childQty' => $childQty,
            'infantQty' => $infantQty,
            'total_passengers' => $adultQty + $childQty + $infantQty,
            'seat_class' => $_POST['seat_class'] ?? 'economy'  // Add this line
        ];

        // Also store the API data for reference
        $_SESSION['last_api_request'] = $apiData;
        
        // Store trip type flags in session for the results page
        $_SESSION['search_results']['isRoundTrip'] = $isRoundTrip;
        $_SESSION['search_results']['isReturnOpen'] = $isReturnOpen;
        
        header('Location: search-result.php');
        exit;
    } else {
        // Handle error
        $errorMessage = isset($searchResult['data']['message']) ? $searchResult['data']['message'] : 'No trips found';
        if (isset($searchResult['error'])) {
            $errorMessage = $searchResult['error'];
        }
        $_SESSION['search_error'] = $errorMessage;
        $_SESSION['last_api_request'] = $apiData;
        $_SESSION['last_api_response'] = $searchResult;
        
        header('Location: index.php');
        exit;
    }
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
})(window,document,'script','dataLayer','GTM-5SVD439S');</script>
<!-- End Google Tag Manager -->
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bintan Ferry Tickets | Book Singapore to Bintan Ferry Online | Bintan Resort Ferries Tickets – BintanFerryTickets.com</title>
    <meta name="description" content="Book Singapore to Bintan ferry tickets online and skip the counter queue. Enjoy instant confirmation, secure payment, fast, easy & reliable booking. Bintan Resort Ferries Tickets.">
    <meta name="keywords" content="bintan ferry tickets, bintan fast online booking, singapore to bintan vtl, singapore to bintan ferry, ferry tickets online, online booking bintan ferry tickets, singapore and bintan, ferry services, ferry schedules, fast ferry, bintan fast ferry, bintan centre ferry terminal, bintan centre">
    <meta name="author" content="VTL Travel">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://bintanferrytickets.com/">
    <!-- Open Graph (Facebook) -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Bintan Ferry Tickets | Book Singapore to Bintan Ferry Online | Bintan Resort Ferries Tickets – BintanFerryTickets.com">
    <meta property="og:description" content="Book Singapore to Bintan ferry tickets online and skip the counter queue. Enjoy instant confirmation, secure payment, fast, easy & reliable booking. Bintan Resort Ferries Tickets.">
    <meta property="og:url" content="https://bintanferrytickets.com/">
    <meta property="og:site_name" content="Bintan Ferry Tickets">
    <meta property="og:image" content="https://bintanferrytickets.com/BookSingaporetoBintanFerryTicketsonline.webp">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://bintanferrytickets.com/">
    <meta name="twitter:title" content="Bintan Ferry Tickets | Book Singapore to Bintan Ferry Online | Bintan Resort Ferries Tickets – BintanFerryTickets.com">
    <meta name="twitter:description" content="Book Singapore to Bintan ferry tickets online and skip the counter queue. Enjoy instant confirmation, secure payment, fast, easy & reliable booking. Bintan Resort Ferries Tickets.">
    <meta name="twitter:image" content="https://bintanferrytickets.com/BookSingaporetoBintanFerryTicketsonline.webp">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="capital-favicon.png">
    <!-- Performance Hints -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="preconnect" href="https://cdn.jsdelivr.net" />
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" />
    <!-- Google Analytics + Google Ads (Combined) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-S8024PF2N0"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        // Google Analytics 4
        gtag('config', 'G-S8024PF2N0');
        // Google Ads Conversion Tracking
        gtag('config', 'AW-10870335725');
    </script>

    <!-- Structured Data (Schema.org - TravelAgency) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "TravelAgency",
        "name": "Bintan Ferry Tickets",
        "image": "https://bintanferrytickets.com/horizontal_logo.svg",
        "url": "https://bintanferrytickets.com/",
        "telephone": "+6582955180",
        "priceRange": "$$",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "15 Beach Road, #02-01, Beach Centre",
            "addressLocality": "Singapore",
            "postalCode": "189677",
            "addressCountry": "SG"
        },
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.8",
            "reviewCount": "150"
        }
    }
    </script> 

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

     <!-- All Plugins CSS -->
    <link rel="stylesheet" href="./assets/css/plugins/bootstrap.min.css" />
    <link rel="stylesheet" href="./assets/css/plugins/aos.css" />
    <link rel="stylesheet" href="./assets/css/plugins/nice-select.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <!-- Custom Css -->
    <link rel="stylesheet" href="./assets/css/helper.css" />
    <link rel="stylesheet" href="./assets/css/style.css" />
    <link rel="stylesheet" href="./assets/css/responsive.css" />
    <!-- <script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script>
</head>
<body>
<main class=" main-body">
<body>
    <!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TWBQ2KLF"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<?php require './assets/includes/navbar.php'; ?>

<?php if ($searchError): ?>
<div class="container mt-3">
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= htmlspecialchars($searchError) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
<?php endif; ?>

<section class="hero-section">
  <!-- Trusted badge with people icon (image from people-icon.png) -->
  <div class="badge-trusted">
    <div class="avatar-group">
      <!-- people icon as requested: using provided people-icon.png -->
      <img src="people-icon.png" alt="people icon" onerror="this.src='https://placehold.co/28x28?text=👥'">
    </div>
    Trusted by 100K+ Travellers
  </div>

  <h1 class="hero-title mb-1">
    Book Your Bintan Ferry Tickets<br>
    Bintan Resort Ferries Tickets <br>
    Get Yours Here Instantly
  </h1>

  <!-- The promo sentence that should be hidden on mobile version 
  <p class="hero-offer" id="booking-card">Enjoy 10% Off Per Person with Promo Code "VTLTRAVEL" *First 50 customers per day*</p> -->

    <!-- Replace your booking card section with this Safari-compatible version -->
<div class="booking-card">
    <form action="" method="POST" novalidate>
    <div id="view-book-trip">
        <div class="d-flex gap-4 mb-3">
    <label class="custom-radio">
        <input type="radio" name="trip_type" value="round_trip" checked onclick="updateTripType('round_trip')">
        <span class="radio-label">Round trip</span>
    </label>
    <label class="custom-radio">
        <input type="radio" name="trip_type" value="one_way" onclick="updateTripType('one_way')">
        <span class="radio-label">One way</span>
    </label>
    <input type="hidden" name="trip_type" id="tripTypeInput" value="round_trip">

    <!-- Add this new header row with View Schedule link -->
    <div></div> <!-- Empty div for spacing -->
    <a href="schedule.php" class="text-decoration-none" style="font-size: 0.9rem; font-weight: 500; color: #359DD7;">
        <i class="fas fa-calendar-alt me-1"></i> View Schedule
    </a>
    <a href="group-booking.php" class="text-decoration-none" style="font-size: 0.9rem; font-weight: 500; color: #359DD7;">
        <i class="fa-solid fa-people-group"></i> Group Booking
    </a>
</div>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="input-group-custom position-relative" id="departureSectorField">
                    <img src="ferry-icon.png" alt="icon" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); z-index: 1;">
                    <div class="custom-separate" style="margin-left: 45px;">
                        <span class="label-mini">Depart Sector</span>
                        <div class="fw-bold" id="departureRouteDisplay">
    <?php 
if (!empty($sectors)) {
    $defaultSector = $sectors[0];
    foreach ($sectors as $s) {
        $arrivalCode = strtoupper($s['ArrivalCode'] ?? '');
        $arrivalName = strtolower($s['Arrival'] ?? '');
        if ($arrivalCode === 'BBT' || strpos($arrivalName, 'bintan') !== false) {
            $defaultSector = $s;
            break;
        }
    }
    echo htmlspecialchars(($defaultSector['Outward'] ?? 'Unknown') . ' - ' . ($defaultSector['Arrival'] ?? 'Unknown'));
} else {
    echo 'No routes available';
}
?>
</div>
                    </div>
                   <select name="departure_route" class="full-clickable-select" id="departureRouteSelect" required>
    <?php if (empty($sectors)): ?>
        <option value="">No routes available</option>
    <?php else: ?>
        <?php foreach ($sectors as $sector): ?>
            <?php 
            // Use the correct field names from API
            $origin = $sector['Outward'] ?? 'Unknown';
            $destination = $sector['Arrival'] ?? 'Unknown';
            $originCode = $sector['OutwardCode'] ?? '';
            $destinationCode = $sector['ArrivalCode'] ?? '';
            $routeCode = $sector['RouteCode'] ?? '';
            $routeText = $origin . ' (' . $originCode . ') - ' . $destination . ' (' . $destinationCode . ')';
            $value = $routeCode . '|' . $origin . '|' . $destination . '|' . $originCode . '|' . $destinationCode;
            ?>
            <?php
// Select this option if destination is Bintan (Singapore → Bintan)
$isDefault = (strtoupper($destinationCode) === 'BBT') || 
             (stripos($destination, 'bintan') !== false);
?>
<option value="<?= htmlspecialchars($value) ?>" <?= $isDefault ? 'selected' : '' ?>>
    <?= htmlspecialchars($routeText) ?>
</option>
        <?php endforeach; ?>
    <?php endif; ?>
</select>
                </div>
            </div>

            <div class="col-md-4" id="return-sector-column">
                <div class="input-group-custom position-relative" id="returnSectorField">
                    <img src="ferry-icon.png" alt="icon" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); z-index: 1;">
                    <div class="custom-separate" style="margin-left: 45px;">
                        <span class="label-mini">Return Sector</span>
                        <div class="fw-bold" id="returnRouteDisplay">
   <?php 
if (!empty($sectors)) {
    $defaultSector = $sectors[0];
    foreach ($sectors as $s) {
        $arrivalCode = strtoupper($s['ArrivalCode'] ?? '');
        $arrivalName = strtolower($s['Arrival'] ?? '');
        if ($arrivalCode === 'BBT' || strpos($arrivalName, 'bintan') !== false) {
            $defaultSector = $s;
            break;
        }
    }
    // Return display swaps origin/destination
    echo htmlspecialchars(($defaultSector['Arrival'] ?? 'Unknown') . ' - ' . ($defaultSector['Outward'] ?? 'Unknown'));
} else {
    echo 'No routes available';
}
?>
</div>
                    </div>
                   <select name="return_route" class="full-clickable-select" id="returnRouteSelect" data-required-for="round_trip">
    <?php if (empty($sectors)): ?>
        <option value="">No routes available</option>
    <?php else: ?>
        <?php foreach ($sectors as $sector): ?>
            <?php 
            // For return trips, reverse the origin and destination
           // Return sector options — fix the route code
$origin        = $sector['Arrival']     ?? 'Unknown';   // Bintan
$destination   = $sector['Outward']     ?? 'Unknown';   // Singapore
$originCode    = $sector['ArrivalCode'] ?? '';           // BBT
$destinationCode = $sector['OutwardCode'] ?? '';         // TMT

// Build the return route code by reversing origin+destination codes
$returnRouteCode = $originCode . $destinationCode;  // BBTTMT (not TMTBBT)

$routeText = $origin . ' (' . $originCode . ') - ' . $destination . ' (' . $destinationCode . ')';
$value     = $returnRouteCode . '|' . $origin . '|' . $destination . '|' . $originCode . '|' . $destinationCode;
            ?>
            <?php
// Select this option if origin is Bintan (Bintan → Singapore)
$isDefault = (strtoupper($originCode) === 'BBT') || 
             (stripos($origin, 'bintan') !== false);
?>
<option value="<?= htmlspecialchars($value) ?>" <?= $isDefault ? 'selected' : '' ?>>
    <?= htmlspecialchars($routeText) ?>
</option>
        <?php endforeach; ?>
    <?php endif; ?>
</select>
                </div>
            </div>

            <div class="col-md-4">
    <div class="input-group-custom position-relative">
        <img src="ferry-icon.png" alt="icon" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); z-index: 1; pointer-events: none;">
        <div class="custom-separate" style="margin-left: 45px;">
            <span class="label-mini">Seat Class</span>
            <div class="fw-bold" id="seatClassDisplay">Economy</div>
        </div>
        <select name="seat_class" class="full-clickable-select" id="seatClassSelect" onchange="document.getElementById('seatClassDisplay').textContent = this.options[this.selectedIndex].text;">
    <option value="economy" selected>Economy</option>
    <option value="emerald">Emerald</option>
</select>
    </div>
</div>
            
            <div class="col-md-3 journey-date-col">
                <div class="input-group-custom position-relative" id="departureDateField">
                    <img src="calendar-icon.png" alt="icon" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); z-index: 1;">
                    <div class="custom-separate" style="margin-left: 45px;">
                        <span class="label-mini">Date of Departure</span>
                        <input type="text" id="journey_date" name="journey_date" placeholder="Select Date" readonly style="margin-top: 5px; font-weight: bold; cursor: pointer; background: transparent; border: none; width: 100%;">
                    </div>
                </div>
            </div>

            <div class="col-md-3" id="return-date-column">
                <div class="input-group-custom position-relative" id="returnDateField">
                    <img src="calendar-icon.png" alt="icon" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); z-index: 1;">
                    <div class="custom-separate" style="margin-left: 45px;">
                        <span class="label-mini">Date of Return</span>
                        <input type="text" id="return_date" name="return_date" placeholder="Optional" readonly style="margin-top: 5px; font-weight: bold; cursor: pointer; background: transparent; border: none; width: 100%;" data-required-for="round_trip">
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="input-group-custom justify-content-between">
                    <div class="d-flex align-items-center">
                        <img src="adult-icon.png" style="margin-top: 5px;">
                        <div class="custom-separate">
                            <span class="" style="font-size: 14px; font-weight: bold; display: block; margin-bottom: -5px; color: black;">Adult</span>
                            <span style="font-size: 0.7rem; color: #aaa;">12+ years</span>
                        </div>
                    </div>
                    <div class="counter-box">
                        <button type="button" class="counter-btn" onclick="updateCounter('adult-qty', -1)">-</button>
                        <span class="mx-2 fw-bold" style="color: #eb5757" id="adult-qty">01</span>
                        <button type="button" class="counter-btn" onclick="updateCounter('adult-qty', 1)">+</button>
                        <input type="hidden" name="adult_count" id="adult-input" value="1">
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="input-group-custom justify-content-between">
                    <div class="d-flex align-items-center">
                        <img src="child-icon.png" style="margin-top: 5px;">
                        <div class="custom-separate">
                            <span class="" style="font-size: 14px; font-weight: bold; display: block; margin-bottom: -5px; color: black;">Child</span>
                            <span style="font-size: 0.7rem; color: #aaa;">0 - 12 years</span>
                        </div>
                    </div>
                    <div id="child-container">
                        <button type="button" class="btn btn-light btn-sm fw-bold" onclick="showChildCounter()">+ Add</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add inside your <form>, on the Search button -->
<button type="submit" class="btn-search" id="searchBtn" onclick="showLoading()">
    <i class="fas fa-search"></i> <span id="btnText">Search Ferries</span>
</button>
    </div>
    </form>
</div>

        <div id="view-buy-tickets" class="d-none">
    <div class="d-flex gap-4 mb-3">
        <label class="custom-radio">
            <input type="radio" name="trip2" checked> 
            <span class="radio-label">Round trip</span>
        </label>
        <label class="custom-radio">
            <input type="radio" name="trip2"> 
            <span class="radio-label">One way</span>
        </label>
    </div>

    </div>
</section>


<div style="height: 100px;"></div> 

<!-- all resort section -->

    <section class="customContainer">
      <div class="resorts-section-header">
        <strong class="resorts-subtitle">From an extensive range of accommodation options, 4 scenic championship golf
          courses.</strong>
        <a href="https://holidaysfromsingapore.com/bintan/" target="_blank" class="resorts-btn-all">ALL RESORTS</a>
      </div>

      <div class="resorts-carousel-wrapper">
        <div class="swiper-button-prev-custom">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
          </svg>
        </div>
        <div class="swiper-button-next-custom">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </div>

        <div class="swiper resortsSwiper">
          <div class="swiper-wrapper">

            <div class="swiper-slide">
              <a href="https://holidaysfromsingapore.com/trip/the-anmon-resort-bintan-2d1n/" target="_blank" class="resort-card">
                <div class="resort-img-wrapper">
                  <img src="assets/images/Anmon.webp" alt="Anmon">
                </div>
                <h3 class="resort-title">The ANMON Resort Bintan</h3>
              </a>
            </div>

            <div class="swiper-slide">
               <a href="https://holidaysfromsingapore.com/trip/four-points-by-sheraton-bintan-2d1n/" target="_blank" class="resort-card">
                <div class="resort-img-wrapper">
                  <img src="assets/images/Four Points Sheraton Bintan.webp" alt="Four Points Sheraton Bintan">
                </div>
                <h3 class="resort-title">Four Points by Sheraton Bintan</h3>
              </a>
            </div>

            <div class="swiper-slide">
               <a href="https://holidaysfromsingapore.com/trip/holiday-inn-resort-bintan-lagoi-beach-by-ihg-2d1n/" target="_blank" class="resort-card">
                <div class="resort-img-wrapper">
                  <img src="assets/images/Holiday Inn.avif" alt="Holiday Inn">
                </div>
                <h3 class="resort-title">Holiday Inn Resort Bintan</h3>
              </a>
            </div>

            <div class="swiper-slide">
               <a href="https://holidaysfromsingapore.com/trip/hotel-indigo-bintan-lagoi-beach-by-ihg-2d1n/" target="_blank" class="resort-card">
                <div class="resort-img-wrapper">
                  <img src="assets/images/Hotel Indigo.webp" alt="Hotel Indigo">
                </div>
                <h3 class="resort-title">Hotel Indigo Bintan Lagoi Beach</h3>
              </a>
            </div>

            <div class="swiper-slide">
               <a href="https://holidaysfromsingapore.com/trip/movenpick-resort-bintan-lagoon-2d1n/" target="_blank" class="resort-card">
                <div class="resort-img-wrapper">
                  <img src="assets/images/Movenpick.webp" alt="Movenpick">
                </div>
                <h3 class="resort-title">Mövenpick Resort & Spa Bintan Lagoon</h3>
              </a>
            </div>

            <div class="swiper-slide">
               <a href="https://holidaysfromsingapore.com/trip/the-residence-bintan-by-cenizaro-2d1n/" target="_blank" class="resort-card">
                <div class="resort-img-wrapper">
                  <img src="assets/images/The Residence Bintan.webp" alt="The Residence Bintan">
                </div>
                <h3 class="resort-title">The Residence Bintan</h3>
              </a>
            </div>

          </div>
        </div>
      </div>
    </section>

     <!-- testimonial section -->
      <section class="testimonial-section py-5">
        <div class="customContainer">
          <!-- Elfsight Google Reviews | Batam Ferry Ticket -->
<script src="https://elfsightcdn.com/platform.js" async></script>
<div class="elfsight-app-1cd6fdd9-b280-41fb-a4d5-a3632972cec5" data-elfsight-app-lazy></div>
        </div>
      </section>
      <!-- brand section -->
      <section class="brand-section pb_100 pt_100">
        <div class="customContainer">
          <div class="section-header text-center mb_60">
            <h2 class="section-title" data-aos="fade-up" data-aos-delay="100">Partners That Work With Us</h2>
            <p class="text-muted mb-3" data-aos="fade-up" data-aos-delay="100">We work with leading ferry operators to provide the best travel experience.</p>
          </div>
        </div>
        <div class="brand-slider-wrapper" data-aos="fade-up">
          <div class="swiper brand-slider">
            <div class="swiper-wrapper">
              <div class="swiper-slide">
                <div class="brand-card">
                  <img src="assets/images/batam fast.png" alt="Batam Fast">
																																								</div>
																																							</div>
																																							<div class=" swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Wonderful-Indonesia-Logo.webp" alt="Wonderful Indonesia">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Trip-Logo.webp" alt="Trip.com">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Ctrip-Logo.webp" alt="Ctrip">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Global-Tix-Logo.webp" alt="GlobalTix">
                  </div>
                </div>
                <!-- Duplicate for seamless loop -->
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Viator-Logo.webp" alt="Viator">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Tripadvisor-Logo.webp" alt="Tripadvisor">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Agoda-Logo.webp" alt="Agoda">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Royal-Caribbean-Logo.webp" alt="Royal Caribbean">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Expedia-Logo.webp" alt="Expedia">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Indonesian-Tourist-Guide-Association-Logo.webp" alt="Indonesian Tourist Guide Association">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/klook-logo.webp" alt="Klook">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Skyscanner-Logo.webp" alt="Skyscanner">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Voyagin-Logo.webp" alt="Voyagin">
                  </div>
                </div>
                <!-- Duplicate for seamless loop -->
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/changi-recommends-holidays-from-singapore.webp" alt="Changi Recommends Holidays From Singapore">
																																																						</div>
																																																					</div>
																																																					<div class=" swiper-slide">
                    <div class="brand-card">
                      <img src="assets/images/G-Adventures-Logo.webp" alt="G Adventures">
                    </div>
                  </div>
                  <div class="swiper-slide">
                    <div class="brand-card">
                      <img src="assets/images/Traveloka-Logo.webp" alt="Traveloka">
                    </div>
                  </div>
                  <div class="swiper-slide">
                    <div class="brand-card">
                      <img src="assets/images/Get-Your-Guide-Logo.webp" alt="Get Your Guide">
                    </div>
                  </div>
                </div>
              </div>
              <!-- Custom Navigation -->
              <div class="brand-nav brand-prev">
                <i class="fa-solid fa-arrow-left"></i>
              </div>
              <div class="brand-nav brand-next">
                <i class="fa-solid fa-arrow-right"></i>
              </div>
            </div>
          </div>
      
      <!-- brand section -->
      <section class="brand-section pb_100 pt_100">
        <div class="customContainer">
          <div class="section-header text-center mb_60">
            <h2 class="section-title" data-aos="fade-up" data-aos-delay="100">Trusted By More Than 200 Corporates Companies</h2>
            <p class="text-muted mb-3" data-aos="fade-up" data-aos-delay="100">We work with leading brands to provide the best travel experience</p>
          </div>
        </div>
        <div class="brand-slider-wrapper" data-aos="fade-up">
          <div class="swiper brand-slider">
            <div class="swiper-wrapper">
              <div class="swiper-slide">
                <div class="brand-card">
                  <img src="assets/images/Google-Logo.webp" alt="Google">
                </div>
              </div>
              <div class="swiper-slide">
                <div class="brand-card">
                  <img src="assets/images/Mandiri-Logo.webp" alt="Mandiri">
                </div>
              </div>
              <div class="swiper-slide">
                <div class="brand-card">
                  <img src="assets/images/Ferrari-Logo.webp" alt="Ferrari.com" ">
																																																											</div>
																																																										</div>
																																																										<div class=" swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Coca-cola-Logo.webp" alt="Coca Cola">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Ricoh-Logo.webp" alt="Ricoh">
                  </div>
                </div>
                <!-- Duplicate for seamless loop -->
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/ByteDance-Logo.webp" alt="ByteDance">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Fullerton-Health-Logo.webp" alt="Fullerton Health">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Changi-Airport-Group-Logo.webp" alt="Changi Airport Group">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Capitaland-Logo.webp" alt="Capitaland">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/National-University-of-Singapore-Logo.webp" alt="National University of Singapore">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Singlife-Logo.webp" alt="Singlife">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Cisco-Logo.webp" alt="Cisco">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Cycle-Carriage-Logo.webp" alt="Cycle Carriage">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Lazada-Logo.webp" alt="Lazada">
                  </div>
                </div>
                <!-- Duplicate for seamless loop -->
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Total-Logo.webp" alt="Total">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Meta-Logo.webp" alt="Meta">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/Peoples-Association-Logo.webp" alt="Peoples Association">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="brand-card">
                    <img src="assets/images/GIC-Logo.webp" alt="GIC">
                  </div>
                </div>
              </div>
            </div>
            <!-- Custom Navigation -->
            <div class="brand-nav brand-prev">
              <i class="fa-solid fa-arrow-left"></i>
            </div>
            <div class="brand-nav brand-next">
              <i class="fa-solid fa-arrow-right"></i>
            </div>
          </div>
        </div>
      </section>
       </section>

    <!-- grid section -->


    <section class="customContainer">
      <div class="glance-grid-container">

        <div class="glance-card glance-title-card">
          <h2 class="glance-main-title">Bintan At A Glance</h2>
          <a href="#" class="glance-link">Things To Do In Bintan</a>
        </div>

        <div class="glance-card glance-image-card">
          <img src="assets/images/spa.jpg" alt="Spa & Relax" class="glance-img">
          <div class="glance-overlay">
            <h3 class="glance-card-title">Spa & Relax</h3>
          </div>
        </div>

        <div class="glance-card glance-image-card">
          <img src="assets/images/Nature&wildlife.png" alt="Nature & Wildlife" class="glance-img">
          <div class="glance-overlay">
            <h3 class="glance-card-title">Nature & Wildlife.</h3>
          </div>
        </div>

        <div class="glance-card glance-image-card">
          <img src="assets/images/golf.jpg" alt="Golf" class="glance-img">
          <div class="glance-overlay">
            <h3 class="glance-card-title">Golf</h3>
          </div>
        </div>

        <div class="glance-card glance-image-card">
          <img src="assets/images/Culture&Heritage.webp" alt="Outdoor Tours" class="glance-img">
          <div class="glance-overlay">
            <h3 class="glance-card-title">Outdoor Tours</h3>
          </div>
        </div>

        <div class="glance-card glance-image-card">
          <img src="assets/images/food.jpg" alt="Food Drink" class="glance-img">
          <div class="glance-overlay">
            <h3 class="glance-card-title">Food Drink</h3>
          </div>
        </div>

      </div>
    </section>

    <!-- even section -->

    <section class="customContainer2">
      <div class="services-section customContainer  ">

      <div class="news-header">
          <h2 class="news-main-title">Bintan Group Tours</h2>
        </div>

        <div class="services-grid">

          <div href="#" class="service-card">
            <img src="assets/images/Meeting Venue.webp" alt="Meeting Venue" class="service-bg-img">
            <div class="service-overlay">
              <h3 class="service-title">MEETING VENUE</h3>
            </div>
          </div>

          <div href="#" class="service-card">
            <img src="assets/images/Incentives.webp" alt="Incentive" class="service-bg-img">
            <div class="service-overlay">
              <h3 class="service-title">INCENTIVE</h3>
            </div>
          </div>

          <div href="#" class="service-card">
            <img src="assets/images/Bintan_Teambuilding.webp" alt="Bintan Teambuilding" class="service-bg-img">
            <div class="service-overlay">
              <h3 class="service-title">Bintan Teambuilding</h3>
            </div>
          </div>

          <div href="#" class="service-card">
            <img src="assets/images/Events.webp" alt="Events" class="service-bg-img">
            <div class="service-overlay">
              <h3 class="service-title">EVENTS</h3>
            </div>
          </div>

        </div>
      </div>
    </section>


    <!-- promotion section -->
    <section class="customContainer">
      <div class="promo-section-header">
        <div class="promo-title-block">
          <span class="promo-badge">Events</span>
          <h2 class="promo-main-title">Promotion & Event</h2>
        </div>
        <a href="promotion.php" class="promo-btn-all">View ALL</a>
      </div>

      <div class="promo-carousel-wrapper">
        <div class="swiper-button-next-promo">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </div>

        <div class="swiper promoSwiper">
          <div class="swiper-wrapper">

            <div class="swiper-slide">
              <div class="promo-card">
                <div class="promo-img-wrapper">
                  <img src="assets/images/p1.png" alt="Refresh & Recharge Promo">
                </div>
                <h3 class="promo-card-title">Refresh & Recharge with the Wet & Sweet Promo at Mövenpick Bintan</h3>
              </div>
            </div>

            <div class="swiper-slide">
              <div class="promo-card">
                <div class="promo-img-wrapper">
                  <img src="assets/images/p2.png" alt="Refresh & Recharge Promo">
                </div>
                <h3 class="promo-card-title">Refresh & Recharge with the Wet & Sweet Promo at Mövenpick Bintan</h3>
              </div>
            </div>

            <div class="swiper-slide">
              <div class="promo-card">
                <div class="promo-img-wrapper">
                  <img src="assets/images/p3.png" alt="Refresh & Recharge Promo">
                </div>
                <h3 class="promo-card-title">Refresh & Recharge with the Wet & Sweet Promo at Mövenpick Bintan</h3>
              </div>
            </div>

            <div class="swiper-slide">
              <div class="promo-card">
                <div class="promo-img-wrapper">
                  <img src="assets/images/p4.png" alt="Refresh & Recharge Promo">
                </div>
                <h3 class="promo-card-title">Refresh & Recharge with the Wet & Sweet Promo at Mövenpick Bintan</h3>
              </div>
            </div>

            <div class="swiper-slide">
              <div class="promo-card">
                <div class="promo-img-wrapper">
                  <img src="assets/images/p1.png" alt="Refresh & Recharge Promo">
                </div>
                <h3 class="promo-card-title">Refresh & Recharge with the Wet & Sweet Promo at Mövenpick Bintan</h3>
              </div>
            </div>

            <div class="swiper-slide">
              <div class="promo-card">
                <div class="promo-img-wrapper">
                  <img src="assets/images/p2.png" alt="Refresh & Recharge Promo">
                </div>
                <h3 class="promo-card-title">Refresh & Recharge with the Wet & Sweet Promo at Mövenpick Bintan</h3>
              </div>
            </div>

            <div class="swiper-slide">
              <div class="promo-card">
                <div class="promo-img-wrapper">
                  <img src="assets/images/p3.png" alt="Refresh & Recharge Promo">
                </div>
                <h3 class="promo-card-title">Refresh & Recharge with the Wet & Sweet Promo at Mövenpick Bintan</h3>
              </div>
            </div>

            <div class="swiper-slide">
              <div class="promo-card">
                <div class="promo-img-wrapper">
                  <img src="assets/images/p4.png" alt="Refresh & Recharge Promo">
                </div>
                <h3 class="promo-card-title">Refresh & Recharge with the Wet & Sweet Promo at Mövenpick Bintan</h3>
              </div>
            </div>

          </div>
        </div>
      </div>
    </section>

     <!-- About Batam Fast Ferry Section -->
      <section class="py-5">
        <div class="container">
          <div class="row align-items-center">
            <div class="col-lg-12">
              <h2 class="fw-bold mb-4 text-center">Bintan Resort Ferries: A Trusted Ferry Operator Since 1985</h2>
              <p class="text-muted mb-4 text-center">For over 40 years since 1985, Bintan Resort Ferries has been a popular ferry operator for travel from Singapore to Bintan. As a daily ferry operator,  accommodates over 300 passengers each day between Singapore and Bintan. Starting with two high-speed passenger ferries, Bintan Resort Ferries now owns 21 high-speed ferries and operates more than 32 daily return trips across Bintan and Singapore.</p>
            </div>
          </div>
        </div>

      <!-- How to Book Section -->
      <section class="py-0 bg-white">
        <div class="container">
          <div class="row">
            <div class="col-lg-12">
              <h2 class="fw-bold mb-4">Singapore to Bintan Ferry Ticket Prices</h2>
              <p class="text-muted mb-3">The price of ferry tickets for travel from Singapore to Bintan typically ranges from SGD $100-$110, depending on the ferry operator and chosen route. Prices may vary based on demand, ferry schedules, and time of travel, with higher rates during weekends and public holidays. </p>
              <p class="text-muted mb-3">For those looking to book cheap ferry tickets to Bintan, booking online is the easiest method. Booking online provides the opportunity to choose your preferred time of travel, thereby avoiding long queues at the terminal, which can sometimes lead to higher prices.</p>
              <p class="text-muted mb-3">To book your ferry ticket online through Bintan Ferry Tickets, visit our website https://bintanferrytickets.com/. Simply choose your ferry route, select the date, and enter the number of passengers. Bintan Ferry Tickets will display the BRF Ferry schedule for your selected date and route, whether you opt for a one-way or round trip. Once details are complete, proceed to the checkout page for payment method. Fill in your information to finalize the booking online and use available payment method options such as credit/debit card or PayNow through a secure gateway. After the process, you will receive your e-tickets by email. </p>
            </div>
          </div>
        </div>

      <!-- About VTL Travel Section -->
      <section class="py-0 bg-white">
        <div class="container">
          <div class="row">
            <div class="col-lg-12">
              <h2 class="fw-bold mb-4">Book Bintan Resort Ferries Online Booking with VTL Travel</h2>
              <p class="text-muted mb-3">VTL Travel Pte Ltd is a licensed STB travel agency in Singapore, operating since 2015. This travel company received the Tripadvisor "Best of the Best" Award in 2023, recognizing top-performing travel businesses based on traveler reviews and service quality. As a licensed travel agency working closely with tourism partners across the region, VTL Travel has built a strong reputation for providing reliable and well-organized ferry services. </p>
              <p class="text-muted mb-3">Since its inception, VTL Travel has focused on creating private tour packages and corporate tour experiences across several destinations in Singapore, Indonesia, and Malaysia. The company has organized numerous corporate retreats and team-building packages, especially in Bintan, known as a popular destination for corporate tours from Singapore. Bintan offers a combination of unique local food, attractive locations, and exciting group activities, making it an ideal place for corporate events and short getaway trips.</p>
              <p class="text-muted mb-3">In partnership with BRF Ferry, VTL Travel expanded its ferry services in 2026 by developing an online booking system for ferry tickets. Travelers can now book Bintan fast tickets online quickly and securely with real-time ferry schedules and instant confirmation. Our commitment to a smooth booking online experience is supported by secure payment method processing and responsive customer service, ensuring every traveler can plan their trip with confidence.</p>
              <div>
                <p class="text-muted m-0 p-0">Beyond selling ferry tickets on Bintan Ferry Tickets, VTL Travel also offers a range of travel services, including:</p>
                <ul class="text-muted m-0" style="list-style-type: decimal; padding-left: 1.2rem; list-style-position: outside;">
                  <li class="m-0 p-0 text-start">Corporate retreats and tour services</li>
                  <li class="m-0 p-0 text-start">Cruise ticketing</li>
                  <li class="m-0 p-0 text-start">Visa application services</li>
                  <li class="m-0 p-0 text-start">Sports event ticketing</li>
                </ul>
                <p class="text-muted m-0 p-0">When you book ferry tickets online on Bintan Ferry Tickets, you will experience our excellent customer service for booking support, secure payment method processing, and instant e-ticket confirmation via email, making your journey between Singapore and Bintan simple, efficient, and hassle-free.</p>
              </div>
            </div>
          </div>
        </div>
      </section>
       </section>
        </section>
         </section>
      <!-- faq section -->

        <!-- START: FAQ Section Layout Wrapper -->
        <section class="customContainer">
            <div class="faq-section-container">

                <!-- Main Section Header Text -->
                <h2 class="faq-main-title text-center">FAQs</h2>

                <!-- Accordion Wrapper List Track -->
                <div class="faq-accordion-group">

                    <!-- Accordion Item 1 (Open by Default matching image_b262c8.png) -->
                    <div class="faq-item active">
                        <button class="faq-trigger" aria-expanded="true">
                            <span class="faq-question">Where do ferries to Bintan depart from in Singapore??</span>
                            <span class="faq-icon-toggle"></span>
                        </button>
                        <div class="faq-panel">
                            <div class="faq-panel-content">
                                <p>Ferries to Bintan depart from Tanah Merah Ferry Terminal, located in the eastern part of Singapore,  at 50 Tanah Merah Ferry Road. It's a short taxi or bus ride from Tanah Merah MRT, and close to Changi Airport. Note: Bintan ferries do not run from HarbourFront — that terminal serves Batam and other routes.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Item 2 -->
                    <div class="faq-item">
                        <button class="faq-trigger" aria-expanded="false">
                            <span class="faq-question">Which terminal will I arrive at in Bintan?</span>
                            <span class="faq-icon-toggle"></span>
                        </button>
                        <div class="faq-panel">
                            <div class="faq-panel-content">
                                <p>There are two arrival points. Ferries from Singapore arrive at either Bandar Bentan Telani ferry terminal or Tanjung Pinang ferry terminal in Bintan. The first one is closer to the resort areas in the north, while the latter one serves the southern part of the island.  If you're staying in the Lagoi/Bintan Resorts area, you'll arrive at Bandar Bentan Telani (BBT).</p>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Item 3 -->
                    <div class="faq-item">
                        <button class="faq-trigger" aria-expanded="false">
                            <span class="faq-question">How long is the ferry to Bintan?</span>
                            <span class="faq-icon-toggle"></span>
                        </button>
                        <div class="faq-panel">
                            <div class="faq-panel-content">
                                <p>For the resort area, it takes around 1 hour and 10 minutes from Tanah Merah Ferry Terminal to Bandar Bentan Telani Ferry Terminal.  Ferries to Tanjung Pinang in the south take a little longer, usually around 1 hour 45 minutes.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Item 4 -->
                    <div class="faq-item">
                        <button class="faq-trigger" aria-expanded="false">
                            <span class="faq-question">Which ferry operators run the Singapore–Bintan route?</span>
                            <span class="faq-icon-toggle"></span>
                        </button>
                        <div class="faq-panel">
                            <div class="faq-panel-content">
                                <p>The route is served by Sindo Ferry, Bintan Resort Ferries and Majestic Fast Ferry.  Bintan Resort Ferries (BRF) is the main operator into the Lagoi resort area.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Item 5 -->
                    <div class="faq-item">
                        <button class="faq-trigger" aria-expanded="false">
                            <span class="faq-question">How many sailings are there per day?</span>
                            <span class="faq-icon-toggle"></span>
                        </button>
                        <div class="faq-panel">
                            <div class="faq-panel-content">
                                <p>There are multiple departures daily, so you can pick a sailing that fits your plans. We recommend booking the return leg in advance for weekends and public holidays, when popular timings sell out.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Item 6 -->
                    <div class="faq-item">
                        <button class="faq-trigger" aria-expanded="false">
                            <span class="faq-question">Do I need a passport and how long must it be valid?</span>
                            <span class="faq-icon-toggle"></span>
                        </button>
                        <div class="faq-panel">
                            <div class="faq-panel-content">
                                <p>Yes. All foreign travelers to Indonesia must hold a passport valid for at least six (6) months from the date of arrival, and have proof of onward or return passage.  Keep your return ferry ticket handy, as immigration may ask for it.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Item 7 -->
                    <div class="faq-item">
                        <button class="faq-trigger" aria-expanded="false">
                            <span class="faq-question">Do Singapore citizens need a visa for Bintan?</span>
                            <span class="faq-icon-toggle"></span>
                        </button>
                        <div class="faq-panel">
                            <div class="faq-panel-content">
                                <p>No. Singaporean citizens can enter Indonesia visa-free for up to 30 days.  Other ASEAN nationalities also enjoy visa-free entry for short stays.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Item 8 -->
                    <div class="faq-item">
                        <button class="faq-trigger" aria-expanded="false">
                            <span class="faq-question">What about Singapore PRs?</span>
                            <span class="faq-icon-toggle"></span>
                        </button>
                        <div class="faq-panel">
                            <div class="faq-panel-content">
                                <p>Singapore PRs holding a Blue NRIC get a special arrangement. They can enter visa-free for up to 4 days at designated ports including Bandar Bentan Telani (BBT) and Tanjung Pinang, with this policy effective from 31 January 2025.  Bring your Blue NRIC — without it, you'll need to pay for a Visa on Arrival.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Item 9 -->
                    <div class="faq-item">
                        <button class="faq-trigger" aria-expanded="false">
                            <span class="faq-question">Do I need a Visa on Arrival, and how much is it?</span>
                            <span class="faq-icon-toggle"></span>
                        </button>
                        <div class="faq-panel">
                            <div class="faq-panel-content">
                                <p>Only if your nationality isn't visa-exempt. A 7-day VOA for the Riau Islands costs IDR 250,000, while the standard 30-day VOA (extendable once) costs IDR 500,000.  The VOA is purchased at the terminal and must be paid in cash (Indonesian Rupiah).</p>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Item 10 -->
                    <div class="faq-item">
                        <button class="faq-trigger" aria-expanded="false">
                            <span class="faq-question">What is the e-Arrival Card and is it required?</span>
                            <span class="faq-icon-toggle"></span>
                        </button>
                        <div class="faq-panel">
                            <div class="faq-panel-content">
                                <p>Yes, it's now mandatory for everyone. From 1 October 2025, all travellers to Bintan must complete the All Indonesia e-Arrival Digital Card.  It's a free online form that takes about 3 minutes and should be completed within 72 hours before arrival  — you'll get a QR code/barcode to show at immigration. It is not a visa.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Item 11 -->
                    <div class="faq-item">
                        <button class="faq-trigger" aria-expanded="false">
                            <span class="faq-question">What time should I arrive at the terminal?</span>
                            <span class="faq-icon-toggle"></span>
                        </button>
                        <div class="faq-panel">
                            <div class="faq-panel-content">
                                <p>We recommend arriving at least 1 hour before departure to allow time for check-in and immigration.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Item 12 -->
                    <div class="faq-item">
                        <button class="faq-trigger" aria-expanded="false">
                            <span class="faq-question">Is there a time difference in Bintan?</span>
                            <span class="faq-icon-toggle"></span>
                        </button>
                        <div class="faq-panel">
                            <div class="faq-panel-content">
                                <p>Yes — Bintan is one hour behind Singapore time.  Double-check your return sailing against the local clock so you don't miss it.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Item 13 -->
                    <div class="faq-item">
                        <button class="faq-trigger" aria-expanded="false">
                            <span class="faq-question">Can I reschedule or cancel my ticket?</span>
                            <span class="faq-icon-toggle"></span>
                        </button>
                        <div class="faq-panel">
                            <div class="faq-panel-content">
                                <p>This depends on the operator and fare type. Many tickets can be rescheduled or cancelled up to 24 hours before departure.  Check the conditions at booking.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Item 14 -->
                    <div class="faq-item">
                        <button class="faq-trigger" aria-expanded="false">
                            <span class="faq-question">Can I bring pets?</span>
                            <span class="faq-icon-toggle"></span>
                        </button>
                        <div class="faq-panel">
                            <div class="faq-panel-content">
                                <p>Generally no. Pets are normally not allowed on board ferries from Singapore to Bintan.</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Centered Action Control Block matching image_b2631e.png perfectly -->
                <div class="faq-footer-action">
                    <a href="contact-us.php" class="faq-contact-btn">Contact Us</a>
                </div>

            </div>
        </section>
        <!-- END: FAQ Section Layout Wrapper -->


      <!-- Elfsight Cookie Consent | Untitled Cookie Consent -->
<script src="https://elfsightcdn.com/platform.js" async></script>
<div class="elfsight-app-8d1313bb-ed09-4a32-b95a-50abc1baa9ae" data-elfsight-app-lazy></div>

<div class="vtl-separator"></div>


        <?php require './assets/includes/footer.php'; ?>

  </main>

        <!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5SVD439S"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
        <!-- Javascript Links -->
    <script src="./assets/js/jquery-3.7.1.min.js"></script>
    <script src="./assets/js/plugins.js"></script>
    <script src="./assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="./assets/js/index.js"></script>
<script>
$(document).ready(function(){
    // initialize slick slider: single row, infinite loop, responsive slidesToShow based on screen size
    $('.partner-slider').slick({
      slidesToShow: 6,      // default desktop shows 6 logos
      slidesToScroll: 2,    // scroll 2 at a time (smooth)
      autoplay: true,
      autoplaySpeed: 2800,
      speed: 700,
      pauseOnHover: true,
      pauseOnFocus: false,
      infinite: true,
      arrows: true,
      dots: false,          // clean look; we can add dots if needed, but minimal per request
      responsive: [
        {
          breakpoint: 1200,
          settings: {
            slidesToShow: 5,
            slidesToScroll: 2
          }
        },
        {
          breakpoint: 992,
          settings: {
            slidesToShow: 4,
            slidesToScroll: 2
          }
        },
        {
          breakpoint: 768,
          settings: {
            slidesToShow: 3,
            slidesToScroll: 1,
            arrows: true,
          }
        },
        {
          breakpoint: 480,
          settings: {
            slidesToShow: 2,
            slidesToScroll: 1,
            arrows: true,
          }
        }
      ],
      // ensure accessibility and edge cases
      adaptiveHeight: false,
      lazyLoad: 'ondemand',
      swipe: true,
      touchMove: true,
      prevArrow: '<button type="button" class="slick-prev" aria-label="Previous">‹</button>',
      nextArrow: '<button type="button" class="slick-next" aria-label="Next">›</button>',
    });
  });

  $(document).ready(function(){
    // Initialize slick slider for clients: single row, infinite, responsive based on screen width
    $('.client-slider').slick({
      slidesToShow: 6,      // desktop shows 6 client logos
      slidesToScroll: 2,    // scroll 2 items at a time for smooth navigation
      autoplay: true,
      autoplaySpeed: 2800,
      speed: 700,
      pauseOnHover: true,
      pauseOnFocus: false,
      infinite: true,
      arrows: true,
      dots: false,          // clean look (no dots, only arrows as requested)
      responsive: [
        {
          breakpoint: 1200,
          settings: {
            slidesToShow: 5,
            slidesToScroll: 2
          }
        },
        {
          breakpoint: 992,
          settings: {
            slidesToShow: 4,
            slidesToScroll: 2
          }
        },
        {
          breakpoint: 768,
          settings: {
            slidesToShow: 3,
            slidesToScroll: 1,
            arrows: true,
          }
        },
        {
          breakpoint: 480,
          settings: {
            slidesToShow: 2,
            slidesToScroll: 1,
            arrows: true,
          }
        }
      ],
      adaptiveHeight: false,
      lazyLoad: 'ondemand',
      swipe: true,
      touchMove: true,
    });
  });

function updateCounter(id, change) {
    // Get the span element and the hidden input
    const display = document.getElementById(id);
    const hiddenInput = document.getElementById(id.replace('-qty', '-input'));
    
    // Get current value
    let currentValue = parseInt(display.innerText);
    
    // Calculate new value
    let newValue = currentValue + change;
    
    // Validation rules
    if (id === 'adult-qty') {
        // Adults: Min 1, Max 9
        if (newValue < 1) return;
        if (newValue > 9) return;
    } else if (id === 'child-qty') {
        // Children: Min 0, Max 9
        if (newValue < 0) return;
        if (newValue > 9) return;
    }

    // Update the display (formatted with leading zero if less than 10)
    display.innerText = newValue < 10 ? '0' + newValue : newValue;
    
    // Update hidden input for PHP form submission
    if(hiddenInput) {
        hiddenInput.value = newValue;
    }
    
    // Optional: Update total passengers display
    updateTotalPassengers();
}

function showChildCounter() {
    const container = document.getElementById('child-container');
    container.innerHTML = `
        <div class="counter-box">
            <button type="button" class="counter-btn" onclick="updateCounter('child-qty', -1)">-</button>
            <span class="mx-2 fw-bold" style="color: #eb5757" id="child-qty">01</span>
            <button type="button" class="counter-btn" onclick="updateCounter('child-qty', 1)">+</button>
            <input type="hidden" name="child_count" id="child-input" value="1">
        </div>
    `;
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Journey Date Picker
    const journeyPicker = flatpickr("#journey_date", {
        dateFormat: "d M, Y",
        minDate: "today", // Cannot pick past dates
        defaultDate: "today", // Sets today as default
        onChange: function(selectedDates, dateStr, instance) {
            // When Journey Date changes, update the minimum possible Return Date
            returnPicker.set('minDate', dateStr);
        }
    });

    // Initialize Return Date Picker
    const returnPicker = flatpickr("#return_date", {
        dateFormat: "d M, Y",
        minDate: "today", // Initial min date is today
        // Allows user to clear the return date since it's optional
        allowInput: true,
        clickOpens: true
    });
});

    // Add this function to automatically update return route when departure changes
document.addEventListener('DOMContentLoaded', function() {
    const departureSelect = document.getElementById('departureRouteSelect');
    const returnSelect = document.getElementById('returnRouteSelect');
    const departureDisplay = document.getElementById('departureRouteDisplay');
    const returnDisplay = document.getElementById('returnRouteDisplay');
    
    // Store all available routes for quick lookup
    const routes = {};
    
    // Build a lookup object of all routes
    Array.from(returnSelect.options).forEach(option => {
        if (option.value) {
            routes[option.textContent.trim()] = option.value;
        }
    });
    
    // Function to find reverse route
    function findReverseRoute(departureText) {
        // Split the departure route (e.g., "HarbourFront - Batam Center")
        const parts = departureText.split(' - ');
        if (parts.length === 2) {
            const reverseText = `${parts[1].trim()} - ${parts[0].trim()}`;
            return {
                text: reverseText,
                value: routes[reverseText] || null
            };
        }
        return null;
    }
    
    // Function to update return route based on departure
// Function to update return route based on departure (make it globally accessible)
function updateReturnRoute() {
    const departureSelect = document.getElementById('departureRouteSelect');
    const returnSelect = document.getElementById('returnRouteSelect');
    const returnDisplay = document.getElementById('returnRouteDisplay');
    
    if (!departureSelect || !returnSelect) return;
    
    const selectedOption = departureSelect.options[departureSelect.selectedIndex];
    if (!selectedOption || !selectedOption.value) return;
    
    const departureText = selectedOption.textContent.trim();
    const parts = departureText.split(' - ');
    
    if (parts.length === 2) {
        const origin = parts[0].trim();
        const destination = parts[1].trim();
        
        const singaporePorts = ['HarbourFront', 'TanahMerah'];
        const indonesiaPorts = ['Batam Center', 'Sekupang', 'Gold Coast', 'Nongsa', 'Bengkong'];
        const malaysiaPorts = ['Pengelih', 'Desaru', 'Pasir Gudang'];
        
        const isDepartureFromSingapore = singaporePorts.some(port => origin.includes(port));
        const isDepartureFromMalaysia = malaysiaPorts.some(port => origin.includes(port));
        const isDestinationSingapore = singaporePorts.some(port => destination.includes(port));
        const isDestinationMalaysia = malaysiaPorts.some(port => destination.includes(port));
        
        // Build the reverse route text
        const reverseText = `${destination} - ${origin}`;
        
        // First try to find exact reverse match
        let found = false;
        for (let i = 0; i < returnSelect.options.length; i++) {
            const option = returnSelect.options[i];
            const optionText = option.textContent.trim();
            
            if (optionText === reverseText) {
                option.selected = true;
                if (returnDisplay) returnDisplay.textContent = reverseText;
                found = true;
                break;
            }
        }
        
        // If no exact match, find any route from the correct origin country based on route type
        if (!found) {
            for (let i = 0; i < returnSelect.options.length; i++) {
                const option = returnSelect.options[i];
                const optionText = option.textContent.trim();
                const optionParts = optionText.split(' - ');
                
                if (optionParts.length === 2) {
                    const optOrigin = optionParts[0].trim();
                    
                    // CASE 1: Departure from Singapore
                    if (isDepartureFromSingapore) {
                        if (isDestinationMalaysia) {
                            // Singapore → Malaysia: Return should start from Malaysia
                            if (malaysiaPorts.some(port => optOrigin.includes(port))) {
                                option.selected = true;
                                if (returnDisplay) returnDisplay.textContent = optionText;
                                found = true;
                                break;
                            }
                        } else {
                            // Singapore → Indonesia: Return should start from Indonesia
                            if (indonesiaPorts.some(port => optOrigin.includes(port))) {
                                option.selected = true;
                                if (returnDisplay) returnDisplay.textContent = optionText;
                                found = true;
                                break;
                            }
                        }
                    }
                    // CASE 2: Departure from Malaysia
                    else if (isDepartureFromMalaysia) {
                        if (isDestinationSingapore) {
                            // Malaysia → Singapore: Return should start from Singapore
                            if (singaporePorts.some(port => optOrigin.includes(port))) {
                                option.selected = true;
                                if (returnDisplay) returnDisplay.textContent = optionText;
                                found = true;
                                break;
                            }
                        } else {
                            // Malaysia → Indonesia: Return should start from Indonesia
                            if (indonesiaPorts.some(port => optOrigin.includes(port))) {
                                option.selected = true;
                                if (returnDisplay) returnDisplay.textContent = optionText;
                                found = true;
                                break;
                            }
                        }
                    }
                    // CASE 3: Departure from Indonesia
                    else {
                        if (isDestinationMalaysia) {
                            // Indonesia → Malaysia: Return should start from Malaysia
                            if (malaysiaPorts.some(port => optOrigin.includes(port))) {
                                option.selected = true;
                                if (returnDisplay) returnDisplay.textContent = optionText;
                                found = true;
                                break;
                            }
                        } else {
                            // Indonesia → Singapore: Return should start from Singapore
                            if (singaporePorts.some(port => optOrigin.includes(port))) {
                                option.selected = true;
                                if (returnDisplay) returnDisplay.textContent = optionText;
                                found = true;
                                break;
                            }
                        }
                    }
                }
            }
        }
    }
}
    
    // Function to initialize displays
function initializeDisplays() {
    // PHP has already set the correct 'selected' option via the selected attribute.
    // Just sync the display text to match the pre-selected option.

    if (departureSelect.options.length > 0) {
        const selectedDep = departureSelect.options[departureSelect.selectedIndex];
        if (selectedDep) {
            departureDisplay.textContent = selectedDep.textContent.trim();
        }
    }

    if (returnSelect.options.length > 0) {
        const selectedRet = returnSelect.options[returnSelect.selectedIndex];
        if (selectedRet) {
            returnDisplay.textContent = selectedRet.textContent.trim();
        }
    }
}
    
    // Event listener for departure change
    departureSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption) {
            departureDisplay.textContent = selectedOption.textContent.trim();
            updateReturnRoute();
        }
    });
    
    // Event listener for manual return change
    returnSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption) {
            returnDisplay.textContent = selectedOption.textContent.trim();
        }
    });
    
    // Initialize on page load
    initializeDisplays();
});

function updateTripType(type) {
    const tripTypeInput = document.getElementById('tripTypeInput');
    const returnDateInput = document.getElementById('return_date');
    
    // Get elements
    const returnSectorDiv = document.getElementById('return-sector-column');
    const returnDateColumn = document.getElementById('return-date-column');
    const departureSectorColumn = document.querySelector('.departure-sector-col');
    const journeyDateColumn = document.querySelector('.journey-date-col');
    const returnSelect = document.getElementById('returnRouteSelect');
    const departureSelect = document.getElementById('departureRouteSelect');
    
    // Update hidden input
    if (tripTypeInput) {
        tripTypeInput.value = type;
    }

    // Handle UI changes based on trip type
    if (type === 'one_way') {
        // Hide return fields
        if (returnSectorDiv) returnSectorDiv.style.display = 'none';
        if (returnDateColumn) returnDateColumn.style.display = 'none';
        
        // Make departure sector full width
        if (departureSectorColumn) {
            departureSectorColumn.className = 'departure-sector-col col-md-8';
        }
        
        // Make journey date half width
        if (journeyDateColumn) {
            journeyDateColumn.className = 'journey-date-col col-md-6';
        }
        
        // Remove required attributes for one-way
        if (returnSelect) {
            returnSelect.required = false;
            returnSelect.disabled = true; // Disable the select completely
        }
        if (returnDateInput) {
            returnDateInput.required = false;
            returnDateInput.disabled = true; // Disable date input
            returnDateInput.placeholder = 'Not Required';
            returnDateInput.value = '';
        }
        
        // Ensure departure select is enabled and required
        if (departureSelect) {
            departureSelect.required = true;
            departureSelect.disabled = false;
        }
        
    } else if (type === 'open_trip') {
        // Show return fields
        if (returnSectorDiv) returnSectorDiv.style.display = 'block';
        if (returnDateColumn) returnDateColumn.style.display = 'block';
        
        // Reset widths
        if (departureSectorColumn) {
            departureSectorColumn.className = 'departure-sector-col col-md-4';
        }
        if (journeyDateColumn) {
            journeyDateColumn.className = 'journey-date-col col-md-3';
        }
        
        // Return route is required, but return date is optional for open trip
        if (returnSelect) {
            returnSelect.required = true;
            returnSelect.disabled = false;
        }
        if (returnDateInput) {
            returnDateInput.required = false;
            returnDateInput.disabled = true; // Disabled because open trip doesn't need specific date
            returnDateInput.value = '';
            returnDateInput.placeholder = 'Open Return';
            returnDateInput.style.color = '#888';
        }
        
        if (departureSelect) {
            departureSelect.required = true;
            departureSelect.disabled = false;
        }
        
    } else { // round_trip
        // Show return fields
        if (returnSectorDiv) returnSectorDiv.style.display = 'block';
        if (returnDateColumn) returnDateColumn.style.display = 'block';
        
        // Reset widths
        if (departureSectorColumn) {
            departureSectorColumn.className = 'departure-sector-col col-md-4';
        }
        if (journeyDateColumn) {
            journeyDateColumn.className = 'journey-date-col col-md-3';
        }
        
        // Both return route and date are required
        if (returnSelect) {
            returnSelect.required = true;
            returnSelect.disabled = false;
        }
        if (returnDateInput) {
            returnDateInput.required = true;
            returnDateInput.disabled = false;
            returnDateInput.placeholder = 'Select Return Date';
            returnDateInput.style.color = '';
        }
        
        if (departureSelect) {
            departureSelect.required = true;
            departureSelect.disabled = false;
        }
    }
}

// Add event listeners to radio buttons when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Get all radio buttons
    const roundTripRadio = document.querySelector('input[name="trip_type"][value="round_trip"]');
    const oneWayRadio = document.querySelector('input[name="trip_type"][value="one_way"]');
    const openTripRadio = document.querySelector('input[name="trip_type"][value="open_trip"]');
    
    // Add click listeners
    if (roundTripRadio) {
        roundTripRadio.addEventListener('click', function() {
            updateTripType('round_trip');
        });
    }
    
    if (oneWayRadio) {
        oneWayRadio.addEventListener('click', function() {
            updateTripType('one_way');
        });
    }
    
    if (openTripRadio) {
        openTripRadio.addEventListener('click', function() {
            updateTripType('open_trip');
        });
    }
    
    // Initialize with round trip
    updateTripType('round_trip');
});

function openChat() {
  document.getElementById('chat-modal').style.display = 'block';
}

function closeChat() {
  document.getElementById('chat-modal').style.display = 'none';
}

// Close modal when clicking outside (optional)
document.addEventListener('click', function(event) {
  var modal = document.getElementById('chat-modal');
  var button = document.querySelector('[onclick="openChat()"]');
  
  if (modal.style.display === 'block' && 
      !modal.contains(event.target) && 
      event.target !== button) {
    closeChat();
  }
});

// Add to your existing JavaScript section
document.addEventListener('DOMContentLoaded', function() {
    // Fix footer icon sizing on mobile
    function fixFooterIcons() {
        const isMobile = window.innerWidth <= 768;
        const footerIcons = document.querySelectorAll('.footer-custom-icon');
        
        footerIcons.forEach(icon => {
            if (isMobile) {
                icon.style.width = '20px';
                icon.style.height = '20px';
            } else {
                icon.style.width = '';
                icon.style.height = '';
            }
        });
    }
    
    // Run on load and resize
    fixFooterIcons();
    window.addEventListener('resize', fixFooterIcons);
});

// FAQ Toggle Function
    function toggleFAQ(element) {
        const answer = element.nextElementSibling;
        const icon = element.querySelector('i');
        
        // Toggle active class
        element.classList.toggle('active');
        
        // Toggle answer visibility
        if (answer.classList.contains('show')) {
            answer.classList.remove('show');
        } else {
            answer.classList.add('show');
        }
    }

    // Store both route sets
const singaporeToBatamRoutes = [
    ["Harbourfront - Batam Center", ["07:40", "08:40", "09:30", "10:50", "12:30", "14:20", "15:30", "16:50", "18:00", "19:10", "20:20", "21:40"]],
    ["Harbourfront - Sekupang", ["07:40", "08:40|#00d084", "08:40|#9b51e0", "10:50", "08:40|#9b51e0", "08:40|#f78b00", "15:30", "08:40|#9b51e0", "18:00"]],
    ["Harbourfront - Gold Coast", ["07:40", "08:40|#9b51e0", "09:30", "08:40|#9b51e0", "12:30"]],
    ["Tanah Merah - Batam Center", ["08:40|#9b51e0", "08:40", "08:40|#9b51e0", "10:50", "12:30", "08:40|#f1c40f"]],
    ["Tanah Merah - Nongsapura", ["07:40", "08:40", "09:30", "10:50", "12:30", "14:20", "15:30", "16:50"]]
];

const batamToSingaporeRoutes = [
    ["Batam Center - Harbourfront", ["07:40", "08:40", "09:30", "10:50", "12:30", "14:20", "15:30", "16:50", "18:00", "19:10", "20:20", "21:40"]],
    ["Sekupang - Harbourfront", ["07:40", "08:40|#00d084", "08:40|#9b51e0", "10:50", "08:40|#9b51e0", "08:40|#f78b00", "15:30", "08:40|#9b51e0", "18:00"]],
    ["Gold Coast - Harbourfront", ["07:40", "08:40|#9b51e0", "09:30", "08:40|#9b51e0", "12:30"]],
    ["Batam Center - Tanah Merah", ["08:40|#9b51e0", "08:40", "08:40|#9b51e0", "10:50", "12:30", "08:40|#f1c40f"]],
    ["Nongsapura - Tanah Merah", ["07:40", "08:40", "09:30", "10:50", "12:30", "14:20", "15:30", "16:50"]]
];

// Function to render schedule cards
function renderSchedule(routes) {
    const container = document.getElementById('schedule-container');
    if (!container) return;
    
    let html = '';
    routes.forEach(route => {
        html += '<div class="schedule-card">';
        html += '  <div class="route-info d-flex align-items-center gap-3">';
        html += '      <img src="ferry-icon.png" width="35" style="opacity:0.7;">';
        html += '      <div>';
        html += '          <h5 class="route-name">' + route[0] + '</h5>';
        html += '          <p class="route-sub">SG Time (GMT +8)</p>';
        html += '      </div>';
        html += '  </div>';
        html += '  <div class="time-grid">';
        
        route[1].forEach(timeStr => {
            const parts = timeStr.split('|');
            const dot = parts[1] ? '<span class="dot" style="background-color:' + parts[1] + '"></span>' : '';
            html += "<div class='time-box'>" + dot + " " + parts[0] + "</div>";
        });
        
        html += '  </div>';
        html += '</div>';
    });
    
    container.innerHTML = html;
}

// Function to switch direction
function switchDirection(direction) {
    const sgBtn = document.querySelector('.btn-sg');
    const batamBtn = document.querySelector('.btn-batam');
    
    if (direction === 'sg-to-batam') {
        // Make Singapore button active, Batam button inactive
        sgBtn.classList.remove('btn-inactive');
        sgBtn.classList.add('btn-active');
        batamBtn.classList.remove('btn-active');
        batamBtn.classList.add('btn-inactive');
        
        // Render Singapore to Batam routes
        renderSchedule(singaporeToBatamRoutes);
        
    } else {
        // Make Batam button active, Singapore button inactive
        batamBtn.classList.remove('btn-inactive');
        batamBtn.classList.add('btn-active');
        sgBtn.classList.remove('btn-active');
        sgBtn.classList.add('btn-inactive');
        
        // Render Batam to Singapore routes
        renderSchedule(batamToSingaporeRoutes);
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Get the buttons
    const sgBtn = document.querySelector('.btn-sg');
    const batamBtn = document.querySelector('.btn-batam');
    
    // Add click event listeners
    if (sgBtn) {
        sgBtn.addEventListener('click', function() {
            switchDirection('sg-to-batam');
        });
    }
    
    if (batamBtn) {
        batamBtn.addEventListener('click', function() {
            switchDirection('batam-to-sg');
        });
    }
});

// Testimonial Slider functionality
let slideIndex = 1;
let slideInterval;
const slideDelay = 5000; // 5 seconds between slides

// Function to show specific slide
function showSlide(n) {
    const slides = document.getElementsByClassName('testimonial-slide');
    const dots = document.getElementsByClassName('slider-dots')[0].getElementsByClassName('dot');
    
    // Handle wrapping
    if (n > slides.length) {
        slideIndex = 1;
    }
    if (n < 1) {
        slideIndex = slides.length;
    }
    
    // Hide all slides
    for (let i = 0; i < slides.length; i++) {
        slides[i].classList.remove('active');
    }
    
    // Remove active class from all dots
    for (let i = 0; i < dots.length; i++) {
        dots[i].classList.remove('active');
    }
    
    // Show current slide and activate corresponding dot
    slides[slideIndex - 1].classList.add('active');
    dots[slideIndex - 1].classList.add('active');
}

// Change slide by n (next/previous)
function changeSlide(n) {
    stopAutoSlide();
    showSlide(slideIndex += n);
    startAutoSlide();
}

// Go to specific slide
function currentSlide(n) {
    stopAutoSlide();
    showSlide(slideIndex = n);
    startAutoSlide();
}

// Auto slide function
function startAutoSlide() {
    slideInterval = setInterval(() => {
        changeSlide(1);
    }, slideDelay);
}

// Stop auto slide
function stopAutoSlide() {
    if (slideInterval) {
        clearInterval(slideInterval);
    }
}

// Initialize slider when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Show first slide
    showSlide(slideIndex);
    
    // Start auto slide
    startAutoSlide();
    
    // Touch support for mobile
    let touchStartX = 0;
    let touchEndX = 0;
    
    sliderContainer.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
        stopAutoSlide();
    }, { passive: true });
    
    sliderContainer.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
        startAutoSlide();
    }, { passive: true });
    
    function handleSwipe() {
        const swipeThreshold = 50;
        if (touchEndX < touchStartX - swipeThreshold) {
            // Swipe left - next slide
            changeSlide(1);
        } else if (touchEndX > touchStartX + swipeThreshold) {
            // Swipe right - previous slide
            changeSlide(-1);
        }
    }
});

// Add this to your existing JavaScript (around line 1900-2000 in your script section)
document.addEventListener('DOMContentLoaded', function() {
    // Make date fields clickable by creating overlay click handlers
    const journeyDateField = document.getElementById('journey_date');
    const returnDateField = document.getElementById('return_date');
    
    // Create a wrapper div to make the entire date field clickable
    if (journeyDateField) {
        const journeyParent = journeyDateField.closest('.input-group-custom');
        if (journeyParent) {
            journeyParent.style.cursor = 'pointer';
            journeyParent.addEventListener('click', function(e) {
                // Trigger the flatpickr calendar
                if (journeyDateField._flatpickr) {
                    journeyDateField._flatpickr.open();
                }
            });
        }
    }
    
    if (returnDateField) {
        const returnParent = returnDateField.closest('.input-group-custom');
        if (returnParent) {
            returnParent.style.cursor = 'pointer';
            returnParent.addEventListener('click', function(e) {
                // Trigger the flatpickr calendar
                if (returnDateField._flatpickr) {
                    returnDateField._flatpickr.open();
                }
            });
        }
    }
    
    // Initialize Journey Date Picker
    const journeyPicker = flatpickr("#journey_date", {
        dateFormat: "d M, Y",
        minDate: "today",
        defaultDate: "today",
        onChange: function(selectedDates, dateStr, instance) {
            // When Journey Date changes, update the minimum possible Return Date
            if (returnPicker) {
                returnPicker.set('minDate', dateStr);
            }
        }
    });
    
    // Initialize Return Date Picker
    const returnPicker = flatpickr("#return_date", {
        dateFormat: "d M, Y",
        minDate: "today",
        allowInput: true,
        clickOpens: true
    });
});

// Replace the date field click handlers with this updated version
document.addEventListener('DOMContentLoaded', function() {
    
    // Helper function to make date fields work on mobile Safari
    function makeDateFieldClickableForMobile(dateInput, containerElement) {
        if (!dateInput || !containerElement) return;
        
        // Store reference to the original input
        const originalInput = dateInput;
        
        // Create a wrapper div to handle clicks
        const wrapper = document.createElement('div');
        wrapper.style.position = 'absolute';
        wrapper.style.top = '0';
        wrapper.style.left = '0';
        wrapper.style.width = '100%';
        wrapper.style.height = '100%';
        wrapper.style.cursor = 'pointer';
        wrapper.style.zIndex = '10';
        wrapper.style.background = 'transparent';
        
        // Make sure the container has position relative
        if (getComputedStyle(containerElement).position === 'static') {
            containerElement.style.position = 'relative';
        }
        
        // Add the wrapper if it doesn't exist
        if (!containerElement.querySelector('.date-click-wrapper')) {
            wrapper.className = 'date-click-wrapper';
            containerElement.appendChild(wrapper);
            
            // Handle click on wrapper
            wrapper.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // For mobile Safari, we need to trigger the calendar differently
                if (originalInput && originalInput._flatpickr) {
                    // Force open the calendar
                    originalInput._flatpickr.open();
                    
                    // On mobile, sometimes we need to focus the input first
                    if (window.innerWidth <= 768) {
                        setTimeout(function() {
                            originalInput._flatpickr.open();
                        }, 50);
                    }
                } else if (originalInput) {
                    // If flatpickr isn't initialized yet, trigger focus
                    originalInput.focus();
                    
                    // For mobile, try to show native date picker if flatpickr fails
                    if (window.innerWidth <= 768 && !originalInput._flatpickr) {
                        originalInput.click();
                    }
                }
                return false;
            });
        }
        
        return originalInput;
    }
    
    // Make journey date clickable
    const journeyDateInput = document.getElementById('journey_date');
    const journeyDateField = document.getElementById('departureDateField');
    if (journeyDateInput && journeyDateField) {
        makeDateFieldClickableForMobile(journeyDateInput, journeyDateField);
    }
    
    // Make return date clickable
    const returnDateInput = document.getElementById('return_date');
    const returnDateField = document.getElementById('returnDateField');
    if (returnDateInput && returnDateField) {
        makeDateFieldClickableForMobile(returnDateInput, returnDateField);
    }
    
    // Initialize flatpickr with mobile-friendly options
    let journeyPicker, returnPicker;
    
    // Get the actual input elements
    const actualJourneyInput = document.getElementById('journey_date');
    const actualReturnInput = document.getElementById('return_date');
    
    if (actualJourneyInput) {
        journeyPicker = flatpickr(actualJourneyInput, {
            dateFormat: "d M, Y",
            minDate: "today",
            defaultDate: "today",
            disableMobile: false, // Allow mobile native picker fallback
            onReady: function(selectedDates, dateStr, instance) {
                // On mobile, ensure the input is clickable
                if (window.innerWidth <= 768) {
                    const container = actualJourneyInput.closest('.input-group-custom');
                    if (container) {
                        container.style.cursor = 'pointer';
                    }
                }
            },
            onChange: function(selectedDates, dateStr, instance) {
                if (returnPicker) {
                    returnPicker.set('minDate', dateStr);
                }
            }
        });
    }
    
    if (actualReturnInput) {
        returnPicker = flatpickr(actualReturnInput, {
            dateFormat: "d M, Y",
            minDate: "today",
            disableMobile: false, // Allow mobile native picker fallback
            allowInput: true,
            clickOpens: true,
            onReady: function(selectedDates, dateStr, instance) {
                // On mobile, ensure the input is clickable
                if (window.innerWidth <= 768) {
                    const container = actualReturnInput.closest('.input-group-custom');
                    if (container) {
                        container.style.cursor = 'pointer';
                    }
                }
            }
        });
    }
    
    // Additional touch event handlers for mobile
    if (window.innerWidth <= 768) {
        // For journey date
        if (journeyDateField) {
            journeyDateField.addEventListener('touchstart', function(e) {
                e.preventDefault();
                if (actualJourneyInput && actualJourneyInput._flatpickr) {
                    actualJourneyInput._flatpickr.open();
                }
                return false;
            });
        }
        
        // For return date
        if (returnDateField) {
            returnDateField.addEventListener('touchstart', function(e) {
                e.preventDefault();
                if (actualReturnInput && actualReturnInput._flatpickr) {
                    actualReturnInput._flatpickr.open();
                }
                return false;
            });
        }
    }
});

// Add form validation before submission
// Add form validation before submission
document.querySelector('form').addEventListener('submit', function(e) {
    const tripType = document.getElementById('tripTypeInput').value;
    const departureSelect = document.getElementById('departureRouteSelect');
    const returnSelect = document.getElementById('returnRouteSelect');
    const journeyDate = document.getElementById('journey_date');
    const returnDate = document.getElementById('return_date');
    
    // Validate departure route
    if (!departureSelect.value) {
        e.preventDefault();
        alert('Please select a departure route');
        return false;
    }
    
    // Validate journey date
    if (!journeyDate.value) {
        e.preventDefault();
        alert('Please select a departure date');
        return false;
    }
    
    // Validate based on trip type
    if (tripType === 'round_trip') {
        if (!returnSelect.value) {
            e.preventDefault();
            alert('Please select a return route for round trip');
            return false;
        }
        if (!returnDate.value) {
            e.preventDefault();
            alert('Please select a return date for round trip');
            return false;
        }
    } else if (tripType === 'open_trip') {
        if (!returnSelect.value) {
            e.preventDefault();
            alert('Please select a return route for open trip');
            return false;
        }
        // Open trip does NOT require a specific return date
    }
    // For one_way, no additional validation needed
    
    return true;
});

// Function to update return route based on departure (make it globally accessible)
function updateReturnRoute() {
    const departureSelect = document.getElementById('departureRouteSelect');
    const returnSelect = document.getElementById('returnRouteSelect');
    const returnDisplay = document.getElementById('returnRouteDisplay');
    
    if (!departureSelect || !returnSelect) return;
    
    const selectedOption = departureSelect.options[departureSelect.selectedIndex];
    if (!selectedOption || !selectedOption.value) return;
    
    const departureText = selectedOption.textContent.trim();
    const parts = departureText.split(' - ');
    
    if (parts.length === 2) {
        const origin = parts[0].trim();
        const destination = parts[1].trim();
        
        const singaporePorts = ['HarbourFront', 'TanahMerah'];
        const isDepartureFromSingapore = singaporePorts.some(port => origin.includes(port));
        
        // Build the reverse route text
        const reverseText = `${destination} - ${origin}`;
        
        // First try to find exact reverse match
        let found = false;
        for (let i = 0; i < returnSelect.options.length; i++) {
            const option = returnSelect.options[i];
            const optionText = option.textContent.trim();
            
            if (optionText === reverseText) {
                option.selected = true;
                if (returnDisplay) returnDisplay.textContent = reverseText;
                found = true;
                break;
            }
        }
        
        // If no exact match, find any route from the correct origin country
        if (!found) {
            for (let i = 0; i < returnSelect.options.length; i++) {
                const option = returnSelect.options[i];
                const optionText = option.textContent.trim();
                const optionParts = optionText.split(' - ');
                
                if (optionParts.length === 2) {
                    const optOrigin = optionParts[0].trim();
                    
                    if (isDepartureFromSingapore) {
                        // Return should start from Indonesia
                        const indonesiaPorts = ['Batam Center', 'Sekupang', 'Gold Coast', 'Nongsa'];
                        if (indonesiaPorts.some(port => optOrigin.includes(port))) {
                            option.selected = true;
                            if (returnDisplay) returnDisplay.textContent = optionText;
                            found = true;
                            break;
                        }
                    } else {
                        // Return should start from Singapore
                        const singaporePorts = ['HarbourFront', 'TanahMerah'];
                        if (singaporePorts.some(port => optOrigin.includes(port))) {
                            option.selected = true;
                            if (returnDisplay) returnDisplay.textContent = optionText;
                            found = true;
                            break;
                        }
                    }
                }
            }
        }
    }
}

// Return route validation with reset functionality
// Return route validation with reset functionality
const returnRouteSelect = document.getElementById('returnRouteSelect');
const departureRouteSelect = document.getElementById('departureRouteSelect');

if (returnRouteSelect && departureRouteSelect) {
    returnRouteSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (!selectedOption || !selectedOption.value) return;
        
        const returnText = selectedOption.textContent.trim();
        const returnParts = returnText.split(' - ');
        
        // Get departure info
        const departureOption = departureRouteSelect.options[departureRouteSelect.selectedIndex];
        const departureText = departureOption.textContent.trim();
        const departureParts = departureText.split(' - ');
        
        if (departureParts.length === 2 && returnParts.length === 2) {
            const departureOrigin = departureParts[0].trim();
            const departureDestination = departureParts[1].trim();
            const returnOrigin = returnParts[0].trim();
            
            const singaporePorts = ['HarbourFront', 'TanahMerah'];
            const indonesiaPorts = ['Batam Center', 'Sekupang', 'Gold Coast', 'Nongsa', 'Bengkong'];
            const malaysiaPorts = ['Pengelih', 'Desaru', 'Pasir Gudang'];
            
            const isDepartureFromSingapore = singaporePorts.some(port => departureOrigin.includes(port));
            const isDepartureFromMalaysia = malaysiaPorts.some(port => departureOrigin.includes(port));
            const isDepartureFromIndonesia = indonesiaPorts.some(port => departureOrigin.includes(port));
            
            const isDestinationSingapore = singaporePorts.some(port => departureDestination.includes(port));
            const isDestinationMalaysia = malaysiaPorts.some(port => departureDestination.includes(port));
            const isDestinationIndonesia = indonesiaPorts.some(port => departureDestination.includes(port));
            
            // Determine where the return should start from
            let isValid = false;
            let expectedOrigin = '';
            
            // CASE 1: Departure from Singapore
            if (isDepartureFromSingapore) {
                if (isDestinationMalaysia) {
                    // Singapore → Malaysia: Return should start from Malaysia
                    expectedOrigin = 'Malaysia';
                    isValid = malaysiaPorts.some(port => returnOrigin.includes(port));
                } else if (isDestinationIndonesia) {
                    // Singapore → Indonesia: Return should start from Indonesia
                    expectedOrigin = 'Indonesia';
                    isValid = indonesiaPorts.some(port => returnOrigin.includes(port));
                }
            }
            // CASE 2: Departure from Malaysia
            else if (isDepartureFromMalaysia) {
                if (isDestinationIndonesia) {
                    // Malaysia → Indonesia: Return should start from Indonesia
                    expectedOrigin = 'Indonesia';
                    isValid = indonesiaPorts.some(port => returnOrigin.includes(port));
                } else if (isDestinationSingapore) {
                    // Malaysia → Singapore: Return should start from Singapore
                    expectedOrigin = 'Singapore';
                    isValid = singaporePorts.some(port => returnOrigin.includes(port));
                }
            }
            // CASE 3: Departure from Indonesia
            else if (isDepartureFromIndonesia) {
                if (isDestinationMalaysia) {
                    // Indonesia → Malaysia: Return should start from Malaysia
                    expectedOrigin = 'Malaysia';
                    isValid = malaysiaPorts.some(port => returnOrigin.includes(port));
                } else if (isDestinationSingapore) {
                    // Indonesia → Singapore: Return should start from Singapore
                    expectedOrigin = 'Singapore';
                    isValid = singaporePorts.some(port => returnOrigin.includes(port));
                }
            }
            
            if (!isValid && expectedOrigin) {
                // Show alert message
                alert(`Please select a valid return route. The return trip should start from ${expectedOrigin}.`);
                
                // Reset to correct route using the global function
                updateReturnRoute();
            } else {
                // Update display if valid
                const returnDisplay = document.getElementById('returnRouteDisplay');
                if (returnDisplay) {
                    returnDisplay.textContent = returnText;
                }
            }
        }
    });
}

// Initialize seat class display
const seatClassSelect = document.getElementById('seatClassSelect');
const seatClassDisplay = document.getElementById('seatClassDisplay');

if (seatClassSelect && seatClassDisplay) {
    const selectedOption = seatClassSelect.options[seatClassSelect.selectedIndex];
    if (selectedOption) {
        seatClassDisplay.textContent = selectedOption.textContent;
    }
    
    seatClassSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption) {
            seatClassDisplay.textContent = selectedOption.textContent;
        }
    });
}

// In index.php, update your showLoading() function:
function showLoading() {
    document.getElementById('btnText').textContent = 'Searching...';
    document.getElementById('searchBtn').style.opacity = '0.7';
    document.getElementById('searchBtn').style.pointerEvents = 'none';
    
    // Show a full-screen overlay
    const overlay = document.createElement('div');
    overlay.id = 'loadingOverlay';
    overlay.innerHTML = `
        <div style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.92);
                    z-index:9999;display:flex;flex-direction:column;align-items:center;justify-content:center;">
            <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;" role="status"></div>
            <h5 style="color:#333;font-weight:600;">Searching available ferries...</h5>
            <p class="text-muted" style="font-size:14px;">This usually takes 3-5 seconds</p>
        </div>`;
    document.body.appendChild(overlay);
}


</script>
<script src="https://t.contentsquare.net/uxa/29c582d1e631f.js"></script>
<script>			var url = 'https://prod-crmb2b.s3.ap-southeast-1.amazonaws.com/widget/64c917df6f3bf83bd6d2b5b3/69954aba97bf000e7bb2baf4.js?v=' + Date.now();			var s = document.createElement('script');			s.type = 'text/javascript';			s.async = true;			s.src = url;			s.onload = function() {				CreateWhatsappChatWidget();			};			var x = document.getElementsByTagName('script')[0];			x.parentNode.insertBefore(s, x);		</script>
</body>
</html> 