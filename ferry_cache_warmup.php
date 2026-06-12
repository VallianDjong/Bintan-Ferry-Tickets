<?php
/**
 * ferry_cache_warmup.php
 * Cron-driven cache pre-warming script.
 *
 * Add to crontab (run at 05:55, 09:55, 13:55, 19:55 SGT = UTC times below):
 *   55 21 * * * php /path/to/your/site/ferry_cache_warmup.php >> /var/log/ferry_cache.log 2>&1
 *   55 1  * * * php /path/to/your/site/ferry_cache_warmup.php >> /var/log/ferry_cache.log 2>&1
 *   55 5  * * * php /path/to/your/site/ferry_cache_warmup.php >> /var/log/ferry_cache.log 2>&1
 *   55 11 * * * php /path/to/your/site/ferry_cache_warmup.php >> /var/log/ferry_cache.log 2>&1
 *
 * Or if your hosting uses SGT crontab:
 *   55 5,9,13,19 * * * php /path/to/your/site/ferry_cache_warmup.php
 *
 * What it does:
 * - Pre-caches schedules for all active routes for the next 14 days
 * - Caches both Economy and Emerald seat classes
 * - Skips routes/dates that are already fresh
 * - Sleeps 200ms between API calls to avoid rate limiting
 */

// Prevent web access
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script must be run from command line or cron.');
}

require_once __DIR__ . '/ferry_cache_manager.php';

echo date('[Y-m-d H:i:s]') . " Ferry cache warmup starting...\n";
$startTime = microtime(true);

// ─── Define the routes to warm ────────────────────────────────────────────────
// Option A: Hardcode known routes (recommended for speed, no extra API call)
$routeResult = get_routes_cached(true);
$knownRoutes = array_column($routeResult['routes'], 'RouteCode');

// Option B: Fetch routes dynamically from API (slower, uses 1 extra API call)
// $routeResult = get_routes_cached(true);
// $knownRoutes = array_column($routeResult['routes'], 'RouteCode');

$seatCategories = ['Economy', 'Emerald'];
$daysAhead      = 42; // Cache 14 days ahead

// ─── Run the warmup ───────────────────────────────────────────────────────────
$stats = warm_schedule_cache($daysAhead, $knownRoutes, $seatCategories);

$elapsed = round(microtime(true) - $startTime, 1);
echo date('[Y-m-d H:i:s]') . " Warmup complete in {$elapsed}s | ";
echo "cached={$stats['cached']} skipped={$stats['skipped']} errors={$stats['errors']}\n";

// ─── Also refresh routes cache ────────────────────────────────────────────────
echo date('[Y-m-d H:i:s]') . " Refreshing routes cache...\n";
$routesResult = get_routes_cached(true);
echo date('[Y-m-d H:i:s]') . " Routes cached: " . count($routesResult['routes']) . " routes\n";

exit($stats['errors'] > 0 ? 1 : 0);
