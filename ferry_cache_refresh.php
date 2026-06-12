<?php
/**
 * ferry_cache_refresh.php
 * Background worker script — called via exec() from ferry_cache_manager.php
 * 
 * Usage (auto-called, not manual):
 *   php ferry_cache_refresh.php <routeCode> <date> <seatCategory>
 *
 * Place this file in the SAME directory as ferry_cache_manager.php
 */

// Prevent web access
if (PHP_SAPI !== 'cli' && !defined('FERRY_INTERNAL_CALL')) {
    http_response_code(403);
    exit('Forbidden');
}

$routeCode    = $argv[1] ?? null;
$date         = $argv[2] ?? null;
$seatCategory = $argv[3] ?? 'Economy';

if (!$routeCode || !$date) {
    echo "Usage: php ferry_cache_refresh.php <routeCode> <date> [seatCategory]\n";
    exit(1);
}

// Load the cache manager (adjust path if needed)
require_once __DIR__ . '/ferry_cache_manager.php';

$lockKey = "refresh_{$routeCode}_{$date}_{$seatCategory}";

$apiResult = brf_get_schedule_oneway($routeCode, $date, $seatCategory);
if ($apiResult !== null) {
    $cacheFile = schedule_cache_key($routeCode, $date, $seatCategory);
    schedule_cache_write($cacheFile, $apiResult);
    echo "Refreshed: $cacheFile\n";
} else {
    echo "Refresh failed for route=$routeCode date=$date seat=$seatCategory\n";
}

release_lock($lockKey);
exit(0);
