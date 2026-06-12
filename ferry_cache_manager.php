<?php
/**
 * Ferry Cache Manager - BRF API Caching System
 * Matches exact BRF API documentation field names:
 *   GetRoutes  → returns Routes[].RouteCode, Outward, Arrival, OutwardCode, ArrivalCode
 *   GetSchedule → request uses "Route" (not RouteCode), JourneyType 0/1
 *               → response has DepartTrips[], Status, ErrorCode, Message
 *   CheckTripCapacity → always called real-time, never cached
 */

// ─── Directory Constants ──────────────────────────────────────────────────────
define('CACHE_BASE_DIR',     __DIR__ . '/cache/');
define('ROUTES_CACHE_DIR',   CACHE_BASE_DIR . 'routes/');
define('SCHEDULE_CACHE_DIR', CACHE_BASE_DIR . 'schedules/');
define('LOCK_DIR',           CACHE_BASE_DIR . 'locks/');
define('CACHE_LOG_FILE',     CACHE_BASE_DIR . 'cache.log');

// ─── TTL Constants ────────────────────────────────────────────────────────────
define('ROUTES_CACHE_TTL',   86400);   // 24 hours
define('SCHEDULE_CACHE_TTL', 21600);   // 6 hours per slot
define('SCHEDULE_STALE_TTL', 43200);   // serve stale up to 12 hours
define('LOCK_TTL',           30);      // background refresh lock seconds

// ─── BRF API Credentials ──────────────────────────────────────────────────────
define('BRF_API_BASE',   'https://api.brf.com.sg');
define('BRF_TOKEN_USER', 'vtapi');
define('BRF_TOKEN_PASS', 'cHeqE7I7Kn36XnB');  // /token endpoint password
define('BRF_API_USER',   'vtapi');
define('BRF_API_PASS',   'JOznzhbU');           // API request body password

// ─── Initialise cache directories ────────────────────────────────────────────
function ferry_cache_init(): void {
    foreach ([
        CACHE_BASE_DIR,
        ROUTES_CACHE_DIR,
        SCHEDULE_CACHE_DIR,
        LOCK_DIR,
        CACHE_BASE_DIR . 'refresh_jobs/',
    ] as $dir) {
        if (!is_dir($dir)) mkdir($dir, 0755, true);
    }
}
ferry_cache_init();

// ─── Logging ─────────────────────────────────────────────────────────────────
function cache_log(string $level, string $message): void {
    $line = date('[Y-m-d H:i:s]') . " [$level] $message\n";
    if (file_exists(CACHE_LOG_FILE) && filesize(CACHE_LOG_FILE) > 2 * 1024 * 1024) {
        $lines = array_slice(file(CACHE_LOG_FILE), -500);
        file_put_contents(CACHE_LOG_FILE, implode('', $lines));
    }
    file_put_contents(CACHE_LOG_FILE, $line, FILE_APPEND | LOCK_EX);
    error_log("FerryCache [$level] $message");
}

// ─── Token Management ─────────────────────────────────────────────────────────
function brf_get_token(): ?string {
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (
        isset($_SESSION['brf_token'], $_SESSION['brf_token_expiry']) &&
        time() < $_SESSION['brf_token_expiry'] - 60
    ) {
        return $_SESSION['brf_token'];
    }

    $ch = curl_init(BRF_API_BASE . '/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'grant_type' => 'password',
            'username'   => BRF_TOKEN_USER,
            'password'   => BRF_TOKEN_PASS,
        ]),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 15,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr || $httpCode !== 200) {
        cache_log('ERROR', "Token fetch failed HTTP $httpCode cURL: $curlErr");
        return null;
    }

    $data = json_decode($response, true);
    if (empty($data['access_token'])) {
        cache_log('ERROR', 'Token response missing access_token: ' . $response);
        return null;
    }

    $expiresIn = (int)($data['expires_in'] ?? 1799);
    $_SESSION['brf_token']        = $data['access_token'];
    $_SESSION['brf_token_expiry'] = time() + $expiresIn;
    cache_log('INFO', "New token obtained, expires in {$expiresIn}s");
    return $data['access_token'];
}

// ─── Cache Key Builders ───────────────────────────────────────────────────────

/**
 * Cache key for one-way: route + date (stores ALL seat categories)
 * Cache key for round trip: route + departDate_returnDate
 */
function schedule_cache_key(string $routeCode, string $dateKey): string {
    $raw = strtoupper($routeCode) . '|' . $dateKey;
    return 'sched_' . md5($raw) . '.json';
}

function routes_cache_file(): string {
    return ROUTES_CACHE_DIR . 'routes.json';
}

// ─── Cache Slot (4 refreshes/day at 06/10/14/20 SGT) ────────────────────────
function current_cache_slot(): int {
    $sgt  = time() + (8 * 3600);
    $hour = (int)gmdate('G', $sgt);
    $y    = (int)gmdate('Y', $sgt);
    $m    = (int)gmdate('n', $sgt);
    $d    = (int)gmdate('j', $sgt);

    if ($hour < 6)  return gmmktime(20, 0, 0, $m, $d - 1, $y) - (8 * 3600);
    if ($hour < 10) return gmmktime(6,  0, 0, $m, $d, $y)     - (8 * 3600);
    if ($hour < 14) return gmmktime(10, 0, 0, $m, $d, $y)     - (8 * 3600);
    if ($hour < 20) return gmmktime(14, 0, 0, $m, $d, $y)     - (8 * 3600);
    return gmmktime(20, 0, 0, $m, $d, $y) - (8 * 3600);
}

// ─── Schedule Cache Read / Write ─────────────────────────────────────────────
function schedule_cache_read(string $cacheFile): ?array {
    $path = SCHEDULE_CACHE_DIR . $cacheFile;
    if (!file_exists($path)) return null;

    $raw = file_get_contents($path);
    if (!$raw) return null;

    $cached = json_decode($raw, true);
    if (!isset($cached['slot'], $cached['data'])) return null;

    $slotTimestamp = current_cache_slot();
    $age           = time() - ($cached['fetched_at'] ?? 0);
    $isFresh       = ($cached['slot'] >= $slotTimestamp);
    $isUsable      = ($age < SCHEDULE_STALE_TTL);

    if (!$isFresh && !$isUsable) {
        cache_log('DEBUG', "Cache EXPIRED $cacheFile age={$age}s");
        return null;
    }

    return [
        'data'        => $cached['data'],
        'stale'       => !$isFresh,
        'age_seconds' => $age,
    ];
}

function schedule_cache_write(string $cacheFile, array $data): void {
    $path    = SCHEDULE_CACHE_DIR . $cacheFile;
    $payload = json_encode([
        'fetched_at' => time(),
        'slot'       => current_cache_slot(),
        'data'       => $data,
    ], JSON_UNESCAPED_UNICODE);
    file_put_contents($path, $payload, LOCK_EX);
    cache_log('INFO', "Cached: $cacheFile (" . strlen($payload) . " bytes)");
}

// ─── Lock Helpers ─────────────────────────────────────────────────────────────
function acquire_lock(string $lockKey): bool {
    $lockFile = LOCK_DIR . md5($lockKey) . '.lock';
    if (file_exists($lockFile) && (time() - filemtime($lockFile)) < LOCK_TTL) return false;
    return (bool)file_put_contents($lockFile, time(), LOCK_EX);
}

function release_lock(string $lockKey): void {
    $f = LOCK_DIR . md5($lockKey) . '.lock';
    if (file_exists($f)) unlink($f);
}

// ─── Helper: filter trips by seat category ───────────────────────────────────
function filter_trips_by_seat(array $trips, string $seatCategory): array {
    if (empty($seatCategory) || strtolower($seatCategory) === 'all') {
        return array_values($trips);
    }
    $codeMap = ['Economy' => 'ECO', 'Emerald' => 'EME'];
    $code = $codeMap[$seatCategory] ?? strtoupper($seatCategory);
    return array_values(array_filter($trips, function ($trip) use ($seatCategory, $code) {
        return strcasecmp($trip['SeatCategoryName'] ?? '', $seatCategory) === 0
            || strcasecmp($trip['SeatCategory'] ?? '', $code) === 0;
    }));
}

// ─── BRF API: GetRoutes ───────────────────────────────────────────────────────
/**
 * Per docs response: Routes[].{RouteCode, Outward, Arrival, OutwardCode, ArrivalCode, ReturnRouteCode}
 */
function get_routes_cached(bool $forceRefresh = false): array {
    $cacheFile = routes_cache_file();

    if (!$forceRefresh && file_exists($cacheFile)) {
        $age = time() - filemtime($cacheFile);
        if ($age < ROUTES_CACHE_TTL) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if (!empty($cached['routes'])) {
                cache_log('DEBUG', "Routes cache HIT age={$age}s count=" . count($cached['routes']));
                return ['routes' => $cached['routes'], 'source' => 'cache'];
            }
        }
    }

    $token = brf_get_token();
    if (!$token) {
        cache_log('ERROR', 'No token for GetRoutes');
        return ['routes' => [], 'source' => 'error', 'error' => 'Authentication failed'];
    }

    // Per docs: only Username + Password needed in body
    $payload = json_encode([
        'Username' => BRF_API_USER,
        'Password' => BRF_API_PASS,
    ]);

    cache_log('INFO', "Calling GetRoutes");

    $ch = curl_init(BRF_API_BASE . '/api/GetRoutes/');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 25,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    cache_log('DEBUG', "GetRoutes HTTP $httpCode response: " . substr($response, 0, 500));

    if ($curlErr) {
        cache_log('ERROR', "GetRoutes cURL error: $curlErr");
        return ['routes' => [], 'source' => 'error', 'error' => $curlErr];
    }

    if ($httpCode === 401) {
        unset($_SESSION['brf_token'], $_SESSION['brf_token_expiry']);
        return get_routes_cached($forceRefresh);
    }

    $decoded = json_decode($response, true);

    if ($httpCode === 200 && ($decoded['Status'] ?? '') === 'Success') {
        $routes = $decoded['Routes'] ?? [];  // per docs field is "Routes"
        file_put_contents($cacheFile, json_encode([
            'fetched_at' => time(),
            'routes'     => $routes,
        ]), LOCK_EX);
        cache_log('INFO', "Routes cached: " . count($routes) . " routes");
        return ['routes' => $routes, 'source' => 'api'];
    }

    $msg = $decoded['Message'] ?? 'Unknown error';
    cache_log('ERROR', "GetRoutes failed HTTP $httpCode: $msg");
    return ['routes' => [], 'source' => 'error', 'error' => $msg];
}

// ─── BRF API: GetSchedule (internal) ─────────────────────────────────────────
/**
 * Shared cURL call for both one-way and round trip.
 * Per docs: request field is "Route" (not RouteCode).
 * Returns full decoded response or null on failure.
 */
function _brf_call_get_schedule(array $params): ?array {
    $token = brf_get_token();
    if (!$token) {
        cache_log('ERROR', 'No token for GetSchedule');
        return null;
    }

    // Merge credentials with params
    $payload = json_encode(array_merge([
        'Username' => BRF_API_USER,
        'Password' => BRF_API_PASS,
    ], $params));

    cache_log('DEBUG', "GetSchedule payload: $payload");

    $ch = curl_init(BRF_API_BASE . '/api/GetSchedule/');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 25,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    cache_log('INFO', "GetSchedule HTTP $httpCode");
    cache_log('DEBUG', "GetSchedule response: " . substr($response, 0, 1000));

    if ($curlErr) {
        cache_log('ERROR', "GetSchedule cURL error: $curlErr");
        return null;
    }

    if ($httpCode === 401) {
        unset($_SESSION['brf_token'], $_SESSION['brf_token_expiry']);
        return _brf_call_get_schedule($params); // retry once
    }

    if ($httpCode !== 200) {
        cache_log('ERROR', "GetSchedule HTTP $httpCode body: " . substr($response, 0, 300));
        return null;
    }

    $decoded = json_decode($response, true);
    if (!$decoded) {
        cache_log('ERROR', 'GetSchedule invalid JSON response');
        return null;
    }

    if (($decoded['Status'] ?? '') !== 'Success') {
        $msg  = $decoded['Message']   ?? 'Unknown error';
        $code = $decoded['ErrorCode'] ?? 'Unknown';
        cache_log('ERROR', "GetSchedule API error: $msg (code $code)");
        return null;
    }

    cache_log('INFO', "GetSchedule success: " . count($decoded['DepartTrips'] ?? []) . " trips");
    return $decoded;
}

// ─── PUBLIC: Get one-way schedule with cache ──────────────────────────────────
/**
 * Cache stores ALL seat categories together for a route+date.
 * Filtering by seatCategory happens on retrieval.
 *
 * @param string $routeCode   e.g. "BBTTMT" — the RouteCode from GetRoutes
 * @param string $date        YYYY-MM-DD
 * @param string $seatCategory "Economy" | "Emerald" | "all"
 * @param bool   $forceRefresh bypass cache
 */

function get_schedule_cached(
    string $routeCode,
    string $date,
    string $seatCategory = 'Economy',
    bool   $forceRefresh = false
): array {
    // One cache file per route+date containing all seat classes
    $cacheFile = schedule_cache_key($routeCode, $date);

    if (!$forceRefresh) {
        $cached = schedule_cache_read($cacheFile);
        if ($cached !== null) {
            $allTrips = $cached['data']['DepartTrips'] ?? [];
            $filtered = filter_trips_by_seat($allTrips, $seatCategory);

            if (!$cached['stale']) {
                cache_log('DEBUG', "Cache FRESH $cacheFile — " . count($filtered) . " $seatCategory trips");
                return [
                    'trips'  => $filtered,
                    'raw'    => $cached['data'],
                    'source' => 'cache_fresh',
                    'stale'  => false,
                ];
            }

            cache_log('INFO', "Cache STALE $cacheFile — serving stale + bg refresh");
            trigger_background_refresh($routeCode, $date, $cacheFile);
            return [
                'trips'  => $filtered,
                'raw'    => $cached['data'],
                'source' => 'cache_stale',
                'stale'  => true,
            ];
        }
    }

    // Cache miss — call API
    cache_log('INFO', "Cache MISS $cacheFile — calling API");

    $apiResult = _brf_call_get_schedule([
        'Route'         => $routeCode,  // "Route" per BRF docs
        'DepartureDate' => $date,
        'JourneyType'   => 0,           // 0 = one way per docs
    ]);

    if ($apiResult === null) {
        cache_log('ERROR', "API returned null for Route=$routeCode date=$date");
        return [
            'trips'  => [],
            'raw'    => [],
            'source' => 'api_error',
            'stale'  => false,
            'error'  => 'Unable to retrieve schedule. Please try again.',
        ];
    }

    schedule_cache_write($cacheFile, $apiResult);

    $allTrips = $apiResult['DepartTrips'] ?? [];
    $filtered = filter_trips_by_seat($allTrips, $seatCategory);

    cache_log('INFO', "Returning " . count($filtered) . " $seatCategory trips (of " . count($allTrips) . " total) for Route=$routeCode date=$date");

    return [
        'trips'  => $filtered,
        'raw'    => $apiResult,
        'source' => 'api_fresh',
        'stale'  => false,
    ];
}

// ─── PUBLIC: Get round trip schedules ────────────────────────────────────────
/**
 * Per BRF docs, GetSchedule with JourneyType=1 returns DepartTrips[] where
 * each trip has ReturnTime and ReturnDate populated.
 * The "Route" field uses the DEPARTURE route code.
 *
 * @param string $departRouteCode  e.g. "TMTBBT" (Singapore → Bintan)
 * @param string $departDate       YYYY-MM-DD
 * @param string $returnRouteCode  kept for interface compatibility (not sent to API)
 * @param string $returnDate       YYYY-MM-DD
 * @param string $seatCategory     "Economy" | "Emerald"
 */
function get_round_trip_schedules(
    string $departRouteCode,
    string $departDate,
    string $returnRouteCode,
    string $returnDate,
    string $seatCategory = 'Economy'
): array {
    // Cache key: depart route + depart date ONLY — no return date in key
    $cacheFile = schedule_cache_key($departRouteCode, 'rt_' . $departDate);

    $cached = schedule_cache_read($cacheFile);
    if ($cached !== null) {
        $allTrips = $cached['data']['DepartTrips'] ?? [];
        $filtered = filter_trips_by_seat($allTrips, $seatCategory);

        // DO NOT filter by return date here — serve all return slot combinations
        // The user's chosen return date is only used at CheckTripCapacity time

        if (!$cached['stale']) {
            cache_log('DEBUG', "RT cache FRESH — " . count($filtered) . " $seatCategory trips");
            return [
                'depart'     => $filtered,
                'return'     => [],
                'depart_raw' => $cached['data'],
                'return_raw' => [],
                'source'     => ['depart' => 'cache_fresh', 'return' => 'embedded'],
                'stale'      => ['depart' => false, 'return' => false],
            ];
        }

        cache_log('INFO', "RT cache STALE — serving stale + triggering bg refresh");
        trigger_background_refresh($departRouteCode, 'rt_' . $departDate, $cacheFile);
        return [
            'depart'     => $filtered,
            'return'     => [],
            'depart_raw' => $cached['data'],
            'return_raw' => [],
            'source'     => ['depart' => 'cache_stale', 'return' => 'embedded'],
            'stale'      => ['depart' => true, 'return' => false],
        ];
    }

    // Cache miss — call API with next-day return (populates all return slots)
    $warmReturnDate = date('Y-m-d', strtotime($departDate . ' +1 day'));
    cache_log('INFO', "RT cache MISS — calling API Route=$departRouteCode depart=$departDate");

    $apiResult = _brf_call_get_schedule([
        'Route'         => $departRouteCode,
        'DepartureDate' => $departDate,
        'ReturnDate'    => $warmReturnDate,
        'JourneyType'   => 1,
    ]);

    if ($apiResult === null) {
        cache_log('ERROR', "RT API failed Route=$departRouteCode");
        return [
            'depart' => [], 'return' => [],
            'depart_raw' => [], 'return_raw' => [],
            'error'  => 'Unable to retrieve schedules. Please try again.',
        ];
    }

    schedule_cache_write($cacheFile, $apiResult);
    $allTrips = $apiResult['DepartTrips'] ?? [];
    $filtered = filter_trips_by_seat($allTrips, $seatCategory);
    cache_log('INFO', "RT returning " . count($filtered) . " $seatCategory trips from fresh API");

    return [
        'depart'     => $filtered,
        'return'     => [],
        'depart_raw' => $apiResult,
        'return_raw' => [],
        'source'     => ['depart' => 'api_fresh', 'return' => 'embedded'],
        'stale'      => ['depart' => false, 'return' => false],
    ];
}

// ─── Background Refresh ───────────────────────────────────────────────────────
function trigger_background_refresh(
    string $routeCode,
    string $date,
    string $cacheFile
): void {
    $lockKey = "refresh_{$routeCode}_{$date}";
    if (!acquire_lock($lockKey)) {
        cache_log('DEBUG', "BG refresh already running for $cacheFile");
        return;
    }

    $jobFile = CACHE_BASE_DIR . 'refresh_jobs/' . md5($lockKey) . '.job';
    file_put_contents($jobFile, json_encode([
        'routeCode'  => $routeCode,
        'date'       => $date,
        'cacheFile'  => $cacheFile,
        'lockKey'    => $lockKey,
        'created_at' => time(),
    ]));

    $scriptPath = __DIR__ . '/ferry_cache_refresh.php';
    if (function_exists('exec') && PHP_OS !== 'WINNT' && file_exists($scriptPath)) {
        $cmd = escapeshellcmd(PHP_BINARY)
             . ' ' . escapeshellarg($scriptPath)
             . ' ' . escapeshellarg($routeCode)
             . ' ' . escapeshellarg($date)
             . ' > /dev/null 2>&1 &';
        exec($cmd);
        cache_log('DEBUG', "BG refresh process spawned for $cacheFile");
        return;
    }

    // Fallback: refresh after response is sent to user
    register_shutdown_function(function () use ($routeCode, $date, $cacheFile, $lockKey) {
        if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
        $result = _brf_call_get_schedule([
            'Route'         => $routeCode,
            'DepartureDate' => $date,
            'JourneyType'   => 0,
        ]);
        if ($result !== null) schedule_cache_write($cacheFile, $result);
        release_lock($lockKey);
    });
}

// ─── CheckTripCapacity — ALWAYS real-time, NEVER cached ──────────────────────
/**
 * Must be called when user selects a trip. Never cached per BRF documentation.
 *
 * @return array ['available' => bool, 'seats' => int, 'error' => string|null]
 */
function check_trip_capacity_realtime(
    string $routeCode,
    string $departureDate,
    string $departureTime,
    string $seatCategory,
    int    $passengerCount,
    string $returnDate = '',
    string $returnTime = ''
): array {
    $token = brf_get_token();
    if (!$token) {
        return ['available' => false, 'seats' => 0, 'error' => 'Authentication failed'];
    }

    // Exact field names per BRF docs - note lowercase username/password
    // and "Route" not "RouteCode", no SeatCategoryName or Pax
    $params = [
        'username'      => BRF_API_USER,    // lowercase per docs
        'password'      => BRF_API_PASS,    // lowercase per docs
        'Route'         => $routeCode,
        'JourneyType'   => (!empty($returnDate) && !empty($returnTime)) ? 1 : 0,
        'DepartureDate' => $departureDate,
        'DepartureTime' => $departureTime,
    ];

    // Only add return fields for round trips
    if (!empty($returnDate) && !empty($returnTime)) {
        $params['ReturnDate'] = $returnDate;
        $params['ReturnTime'] = $returnTime;
    }

    $payload = json_encode($params);

    cache_log('INFO', "CheckTripCapacity Route=$routeCode date=$departureDate time=$departureTime seat=$seatCategory pax=$passengerCount");
    cache_log('DEBUG', "CheckTripCapacity payload: $payload");

    $ch = curl_init(BRF_API_BASE . '/api/CheckTripCapacity/');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT        => 15,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    cache_log('DEBUG', "CheckTripCapacity HTTP $httpCode response: $response");

    if ($curlErr) {
        return ['available' => false, 'seats' => 0, 'error' => "Network error: $curlErr"];
    }

    if ($httpCode === 401) {
        unset($_SESSION['brf_token'], $_SESSION['brf_token_expiry']);
        return check_trip_capacity_realtime(
            $routeCode, $departureDate, $departureTime,
            $seatCategory, $passengerCount, $returnDate, $returnTime
        );
    }

    $decoded = json_decode($response, true);

    if ($httpCode !== 200 || ($decoded['Status'] ?? '') !== 'Success') {
        $msg  = $decoded['Message']   ?? 'Unable to verify availability';
        $code = (int)($decoded['ErrorCode'] ?? 0);
        cache_log('ERROR', "CheckTripCapacity error: $msg (code $code)");
        return ['available' => false, 'seats' => 0, 'error' => $msg];
    }

    // Response returns ALL seat categories in DepartTrips[]
    // Find the matching seat category and check FreeCapacity
    $trips = $decoded['DepartTrips'] ?? [];
    $availableSeats = 0;
    $matchFound = false;

    foreach ($trips as $trip) {
        $map = ['Economy' => 'ECO', 'Emerald' => 'EME'];
$code = $map[$seatCategory] ?? $seatCategory;
if (strcasecmp($trip['SeatCategoryName'] ?? '', $seatCategory) === 0
    || strcasecmp($trip['SeatCategory'] ?? '', $code) === 0) {
            $availableSeats = (int)($trip['FreeCapacity'] ?? 0);
            $matchFound = true;
            cache_log('INFO', "CheckTripCapacity found $seatCategory: FreeCapacity=$availableSeats");
            break;
        }
    }

    if (!$matchFound) {
        // Seat category not in response — log all categories found
        $found = array_map(fn($t) => ($t['SeatCategory'] ?? '') . '/' . ($t['SeatCategoryName'] ?? ''), $trips);
        cache_log('WARN', "CheckTripCapacity: $seatCategory not found. Available: " . implode(', ', $found));
        return ['available' => false, 'seats' => 0, 'error' => "$seatCategory class not available for this trip"];
    }

    $isAvailable = $availableSeats >= $passengerCount;
    cache_log('INFO', "CheckTripCapacity result: available=$availableSeats needed=$passengerCount " . ($isAvailable ? 'OK' : 'INSUFFICIENT'));

    return [
        'available' => $isAvailable,
        'seats'     => $availableSeats,
        'error'     => $isAvailable ? null : "Only $availableSeats seat(s) available",
        'raw'       => $decoded,
    ];
}

// ─── Cache Warmup (for cron) ──────────────────────────────────────────────────
/**
 * Pre-warms schedule cache for all routes for N days ahead.
 * Caches all seat classes together in one file per route+date.
 * Run via: php ferry_cache_warmup.php
 */
function warm_schedule_cache(int $daysAhead = 14, array $routes = [], array $ignored = []): array {
    $stats = ['cached' => 0, 'skipped' => 0, 'errors' => 0];

    if (empty($routes)) {
        $result = get_routes_cached();
        foreach ($result['routes'] as $route) {
            $code = $route['RouteCode'] ?? null;
            if ($code) $routes[] = $code;
        }
    }

    if (empty($routes)) {
        cache_log('WARN', 'Cache warmup: no routes found');
        return $stats;
    }

    $today = new DateTime('today');

    foreach ($routes as $routeCode) {
        for ($i = 0; $i < $daysAhead; $i++) {
            $date     = (clone $today)->modify("+$i days")->format('Y-m-d');
            $nextDay  = (clone $today)->modify("+$i days +1 day")->format('Y-m-d');

            // ── One-way ──────────────────────────────────────────────────
            $owFile   = schedule_cache_key($routeCode, $date);
            $owExists = schedule_cache_read($owFile);

            if ($owExists && !$owExists['stale']) {
                $stats['skipped']++;
            } else {
                $owResult = _brf_call_get_schedule([
                    'Route'         => $routeCode,
                    'DepartureDate' => $date,
                    'JourneyType'   => 0,
                ]);
                if ($owResult !== null) {
                    schedule_cache_write($owFile, $owResult);
                    $stats['cached']++;
                } else {
                    $stats['errors']++;
                }
                usleep(300000);
            }

            // ── Round-trip ───────────────────────────────────────────────
            $rtFile   = schedule_cache_key($routeCode, 'rt_' . $date);
            $rtExists = schedule_cache_read($rtFile);

            if ($rtExists && !$rtExists['stale']) {
                $stats['skipped']++;
            } else {
                $rtResult = _brf_call_get_schedule([
                    'Route'         => $routeCode,
                    'DepartureDate' => $date,
                    'ReturnDate'    => $nextDay,
                    'JourneyType'   => 1,
                ]);
                if ($rtResult !== null) {
                    schedule_cache_write($rtFile, $rtResult);
                    $stats['cached']++;
                } else {
                    $stats['errors']++;
                }
                usleep(300000);
            }
        }
    }

    cache_log('INFO', "Warmup: cached={$stats['cached']} skipped={$stats['skipped']} errors={$stats['errors']}");
    return $stats;

    foreach ($routes as $routeCode) {
    for ($i = 0; $i < $daysAhead; $i++) {
        $date      = (clone $today)->modify("+$i days")->format('Y-m-d');
        
        // ── One-way cache (existing) ──────────────────────────────────────
        $cacheFile = schedule_cache_key($routeCode, $date);
        $existing  = schedule_cache_read($cacheFile);

        if ($existing && !$existing['stale']) {
            $stats['skipped']++;
        } else {
            $result = _brf_call_get_schedule([
                'Route'         => $routeCode,
                'DepartureDate' => $date,
                'JourneyType'   => 0,
            ]);
            if ($result !== null) {
                schedule_cache_write($cacheFile, $result);
                $stats['cached']++;
            } else {
                $stats['errors']++;
            }
            usleep(200000);
        }

        // ── Round trip cache (new) ────────────────────────────────────────
        // Warm with next day as return date — this caches all return slots
        $rtCacheFile = schedule_cache_key($routeCode, 'rt_' . $date);
        $rtExisting  = schedule_cache_read($rtCacheFile);

        if ($rtExisting && !$rtExisting['stale']) {
            $stats['skipped']++;
        } else {
            $returnDate = (clone $today)->modify("+$i days +1 day")->format('Y-m-d');
            $rtResult = _brf_call_get_schedule([
                'Route'         => $routeCode,
                'DepartureDate' => $date,
                'ReturnDate'    => $returnDate,
                'JourneyType'   => 1,
            ]);
            if ($rtResult !== null) {
                schedule_cache_write($rtCacheFile, $rtResult);
                $stats['cached']++;
            } else {
                $stats['errors']++;
            }
            usleep(200000);
        }
    }
}
}

// ─── Cache Stats / Admin ──────────────────────────────────────────────────────
function get_cache_stats(): array {
    $scheduleFiles = glob(SCHEDULE_CACHE_DIR . '*.json') ?: [];
    $fresh = $stale = $expired = $totalSize = 0;

    foreach ($scheduleFiles as $file) {
        $totalSize += filesize($file);
        $raw    = json_decode(file_get_contents($file), true);
        $slotTs = current_cache_slot();
        $age    = time() - ($raw['fetched_at'] ?? 0);

        if (($raw['slot'] ?? 0) >= $slotTs) $fresh++;
        elseif ($age < SCHEDULE_STALE_TTL)   $stale++;
        else                                  $expired++;
    }

    return [
        'schedule_files'     => count($scheduleFiles),
        'schedule_fresh'     => $fresh,
        'schedule_stale'     => $stale,
        'schedule_expired'   => $expired,
        'schedule_size_kb'   => round($totalSize / 1024, 1),
        'routes_cached'      => file_exists(routes_cache_file()),
        'routes_age_minutes' => file_exists(routes_cache_file())
            ? round((time() - filemtime(routes_cache_file())) / 60, 1)
            : null,
        'current_slot_time'  => date('Y-m-d H:i:s', current_cache_slot()),
    ];
}

function clear_schedule_cache(): int {
    $files = glob(SCHEDULE_CACHE_DIR . '*.json') ?: [];
    $deleted = 0;
    foreach ($files as $f) {
        if (unlink($f)) $deleted++;
    }
    cache_log('INFO', "Cache cleared: $deleted files deleted");
    return $deleted;
}