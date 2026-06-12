<?php
require_once __DIR__ . '/ferry_cache_manager.php';

$routeCode = 'TMTBBT';
$date = '2026-05-21';

// Check one-way cache
$owFile = schedule_cache_key($routeCode, $date);
$owPath = SCHEDULE_CACHE_DIR . $owFile;
echo "ONE-WAY KEY: $owFile\n";
echo "FILE EXISTS: " . (file_exists($owPath) ? 'YES' : 'NO') . "\n";
if (file_exists($owPath)) {
    $raw = json_decode(file_get_contents($owPath), true);
    $age = time() - ($raw['fetched_at'] ?? 0);
    echo "AGE: {$age}s\n";
    echo "SLOT IN FILE: " . ($raw['slot'] ?? 'missing') . "\n";
    echo "CURRENT SLOT: " . current_cache_slot() . "\n";
    echo "IS FRESH: " . (($raw['slot'] ?? 0) >= current_cache_slot() ? 'YES' : 'NO') . "\n";
    echo "IS USABLE: " . ($age < SCHEDULE_STALE_TTL ? 'YES' : 'NO') . "\n";
}

echo "\n";

// Check round-trip cache
$rtFile = schedule_cache_key($routeCode, 'rt_' . $date);
$rtPath = SCHEDULE_CACHE_DIR . $rtFile;
echo "ROUND-TRIP KEY: $rtFile\n";
echo "FILE EXISTS: " . (file_exists($rtPath) ? 'YES' : 'NO') . "\n";
if (file_exists($rtPath)) {
    $raw = json_decode(file_get_contents($rtPath), true);
    $age = time() - ($raw['fetched_at'] ?? 0);
    echo "AGE: {$age}s\n";
    echo "SLOT IN FILE: " . ($raw['slot'] ?? 'missing') . "\n";
    echo "CURRENT SLOT: " . current_cache_slot() . "\n";
    echo "IS FRESH: " . (($raw['slot'] ?? 0) >= current_cache_slot() ? 'YES' : 'NO') . "\n";
    echo "IS USABLE: " . ($age < SCHEDULE_STALE_TTL ? 'YES' : 'NO') . "\n";
}

// List all cache files
echo "\n\nALL CACHE FILES:\n";
$files = glob(SCHEDULE_CACHE_DIR . '*.json') ?: [];
foreach ($files as $f) {
    $raw = json_decode(file_get_contents($f), true);
    $age = time() - ($raw['fetched_at'] ?? 0);
    echo basename($f) . " — age={$age}s slot=" . ($raw['slot'] ?? '?') . "\n";
}