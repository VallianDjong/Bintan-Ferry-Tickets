<?php
/**
 * ferry_cache_admin.php
 * Admin dashboard for cache monitoring and management.
 * 
 * PROTECT THIS FILE — add IP restriction or basic auth in .htaccess:
 *   <Files "ferry_cache_admin.php">
 *     AuthType Basic
 *     AuthName "Admin"
 *     AuthUserFile /path/to/.htpasswd
 *     Require valid-user
 *   </Files>
 *
 * Or restrict by IP:
 *   <Files "ferry_cache_admin.php">
 *     Require ip 203.0.113.0/24
 *   </Files>
 */

require_once __DIR__ . '/ferry_cache_manager.php';

if ($action === 'list_cache') {
    $files = glob(SCHEDULE_CACHE_DIR . '*.json') ?: [];
    $message = "Cache files (" . count($files) . "):<br>";
    foreach ($files as $f) {
        $message .= basename($f) . " (" . round(filesize($f)/1024, 1) . "KB, " . date('H:i:s', filemtime($f)) . ")<br>";
    }
}

// Handle admin actions
$action  = $_GET['action'] ?? '';
$message = '';

if ($action === 'clear_schedules') {
    $deleted = clear_schedule_cache();
    $message = "✅ Cleared $deleted schedule cache files.";
}

if ($action === 'clear_routes') {
    $routeFile = routes_cache_file();
    if (file_exists($routeFile)) {
        unlink($routeFile);
        $message = "✅ Routes cache cleared.";
    } else {
        $message = "ℹ️ Routes cache was already empty.";
    }
}

if ($action === 'warmup') {
    set_time_limit(600); // 10 minutes — we're doing 2x the calls now

    // Pull actual routes from API
    $routesResult = get_routes_cached(true);
    $allRoutes    = $routesResult['routes'] ?? [];

    $knownRoutes = array_values(array_unique(array_filter(
        array_map(fn($r) => $r['RouteCode'] ?? '', $allRoutes)
    )));

    if (empty($knownRoutes)) {
        $message = "❌ Could not load routes from API. Check API credentials.";
    } else {
        $daysAhead = 7;
        $today     = new DateTime('today');
        $stats     = ['cached' => 0, 'skipped' => 0, 'errors' => 0];

        foreach ($knownRoutes as $routeCode) {
            for ($i = 0; $i < $daysAhead; $i++) {
                $date = (clone $today)->modify("+$i days")->format('Y-m-d');

                // ── One-way cache ─────────────────────────────────────────
                $owFile    = schedule_cache_key($routeCode, $date);
                $owCached  = schedule_cache_read($owFile);

                if ($owCached && !$owCached['stale']) {
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
                    usleep(300000); // 300ms pause between calls
                }

                // ── Round trip cache ──────────────────────────────────────
                // Cache with next-day return — covers all return time slots
                // get_round_trip_schedules() will filter by user's chosen return date
                $rtFile    = schedule_cache_key($routeCode, 'rt_' . $date);
                $rtCached  = schedule_cache_read($rtFile);

                if ($rtCached && !$rtCached['stale']) {
                    $stats['skipped']++;
                } else {
                    $nextDay  = (clone $today)->modify("+$i days +1 day")->format('Y-m-d');
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

        cache_log('INFO', "Admin warmup: cached={$stats['cached']} skipped={$stats['skipped']} errors={$stats['errors']}");
        $message = "✅ Warmup complete for " . count($knownRoutes) . " routes ("
                 . implode(', ', $knownRoutes) . "): "
                 . "{$stats['cached']} cached (one-way + round trip), "
                 . "{$stats['skipped']} skipped, {$stats['errors']} errors.";
    }
}

if ($action === 'refresh_routes') {
    $result = get_routes_cached(true);
    $count  = count($result['routes']);
    $message = "✅ Routes refreshed: $count routes cached.";
}

// Fetch stats
$stats = get_cache_stats();

// Read recent log entries
$logLines = [];
if (file_exists(CACHE_LOG_FILE)) {
    $allLines  = file(CACHE_LOG_FILE);
    $logLines  = array_slice($allLines, -50); // Last 50 lines
    $logLines  = array_reverse($logLines);
}

// Count pending refresh jobs
$pendingJobs = count(glob(CACHE_BASE_DIR . 'refresh_jobs/*.job') ?: []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ferry Cache Admin — Batam Ferry Tickets</title>
<style>
  body { font-family: Inter, sans-serif; background: #f5f7fa; color: #222; margin: 0; padding: 20px; }
  .container { max-width: 1100px; margin: 0 auto; }
  h1 { color: #0d1b2a; font-size: 1.6rem; border-bottom: 2px solid #359DD7; padding-bottom: 8px; }
  h2 { font-size: 1.1rem; color: #333; margin-top: 30px; }

  .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; margin: 20px 0; }
  .stat-card { background: white; border-radius: 10px; padding: 18px; box-shadow: 0 2px 8px rgba(0,0,0,.07); }
  .stat-card .label { font-size: .8rem; color: #888; text-transform: uppercase; letter-spacing: .5px; }
  .stat-card .value { font-size: 1.6rem; font-weight: 700; color: #0d1b2a; margin-top: 4px; }
  .stat-card .sub { font-size: .75rem; color: #aaa; margin-top: 2px; }
  .stat-card.green .value { color: #28a745; }
  .stat-card.orange .value { color: #fd7e14; }
  .stat-card.red .value { color: #dc3545; }
  .stat-card.blue .value { color: #359DD7; }

  .actions { display: flex; flex-wrap: wrap; gap: 12px; margin: 20px 0; }
  .btn { display: inline-block; padding: 10px 20px; border-radius: 8px; text-decoration: none;
         font-weight: 600; font-size: .9rem; border: none; cursor: pointer; }
  .btn-primary { background: #359DD7; color: white; }
  .btn-warning { background: #fd7e14; color: white; }
  .btn-danger  { background: #dc3545; color: white; }
  .btn-success { background: #28a745; color: white; }
  .btn:hover   { opacity: .88; }

  .message { background: #d4edda; color: #155724; border: 1px solid #c3e6cb;
             border-radius: 8px; padding: 12px 18px; margin-bottom: 20px; font-weight: 500; }

  .log-box { background: #1a1a2e; color: #c8f7c5; font-family: monospace; font-size: .78rem;
             border-radius: 10px; padding: 16px; max-height: 350px; overflow-y: auto; }
  .log-box .log-line { margin-bottom: 3px; line-height: 1.5; }
  .log-box .log-ERROR { color: #ff6b6b; }
  .log-box .log-WARN  { color: #ffd93d; }
  .log-box .log-INFO  { color: #6bcb77; }
  .log-box .log-DEBUG { color: #888; }

  .slot-info { background: white; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px;
               box-shadow: 0 2px 8px rgba(0,0,0,.07); font-size: .9rem; }
  .slot-info strong { color: #359DD7; }
  table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px;
          overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.07); font-size: .9rem; }
  th { background: #359DD7; color: white; padding: 10px 14px; text-align: left; }
  td { padding: 9px 14px; border-bottom: 1px solid #f0f0f0; }
  tr:last-child td { border-bottom: none; }
</style>
</head>
<body>
<div class="container">

  <h1>🚢 Ferry Cache Admin — Batam Ferry Tickets</h1>

  <?php if ($message): ?>
  <div class="message"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <div class="slot-info">
    <strong>Current Cache Slot:</strong> <?= htmlspecialchars($stats['current_slot_time']) ?> (SGT) &nbsp;|&nbsp;
    <strong>Server Time:</strong> <?= date('Y-m-d H:i:s T') ?> &nbsp;|&nbsp;
    <strong>Pending Refresh Jobs:</strong> <?= $pendingJobs ?>
  </div>

  <div class="stats-grid">
    <div class="stat-card blue">
      <div class="label">Total Schedule Files</div>
      <div class="value"><?= $stats['schedule_files'] ?></div>
      <div class="sub"><?= $stats['schedule_size_kb'] ?> KB on disk</div>
    </div>
    <div class="stat-card green">
      <div class="label">Fresh (current slot)</div>
      <div class="value"><?= $stats['schedule_fresh'] ?></div>
      <div class="sub">Served instantly from cache</div>
    </div>
    <div class="stat-card orange">
      <div class="label">Stale (bg refresh)</div>
      <div class="value"><?= $stats['schedule_stale'] ?></div>
      <div class="sub">Served + refreshing in bg</div>
    </div>
    <div class="stat-card red">
      <div class="label">Expired (unusable)</div>
      <div class="value"><?= $stats['schedule_expired'] ?></div>
      <div class="sub">Will fetch from API live</div>
    </div>
    <div class="stat-card <?= $stats['routes_cached'] ? 'green' : 'red' ?>">
      <div class="label">Routes Cache</div>
      <div class="value"><?= $stats['routes_cached'] ? 'LIVE' : 'MISSING' ?></div>
      <div class="sub"><?= $stats['routes_cached'] ? 'Age: ' . $stats['routes_age_minutes'] . ' min' : 'Will fetch from API' ?></div>
    </div>
    <div class="stat-card blue">
  <div class="label">Warmed Routes</div>
  <div class="value"><?php
    $files = glob(CACHE_BASE_DIR . 'schedules/*.json') ?: [];
    $routeCodes = [];
    foreach ($files as $f) {
        $data = json_decode(file_get_contents($f), true);
        if (!empty($data['route'])) $routeCodes[$data['route']] = 1;
    }
    echo count($files);
  ?></div>
  <div class="sub"><?= implode(', ', array_keys($routeCodes)) ?></div>
</div>
  </div>


  <div class="actions">
    <a href="?action=warmup" class="btn btn-success">⚡ Warm Cache (1 weeks)</a>
    <a href="?action=refresh_routes" class="btn btn-primary">🔄 Refresh Routes</a>
    <a href="?action=clear_schedules" class="btn btn-warning"
       onclick="return confirm('Clear all schedule cache files?')">🗑️ Clear Schedule Cache</a>
    <a href="?action=clear_routes" class="btn btn-danger"
       onclick="return confirm('Clear routes cache?')">🗑️ Clear Routes Cache</a>
    <a href="?" class="btn btn-primary">↻ Refresh Stats</a>
    <a href="?action=list_cache" class="btn btn-primary">📋 List Cache Files</a>
  </div>

  <h2>How Caching Works</h2>
  <table>
    <thead><tr><th>Step</th><th>Action</th><th>Source</th><th>Latency</th></tr></thead>
    <tbody>
      <tr><td>1. User searches</td><td>Get schedule for route + date + seat class</td><td>Cache (if fresh/stale) or BRF API</td><td>&lt;5ms (cache) / 2–4s (API)</td></tr>
      <tr><td>2. Results shown</td><td>Display trips from cache</td><td>Cache</td><td>Instant</td></tr>
      <tr><td>3. User selects trip</td><td>CheckTripCapacity called</td><td>BRF API — ALWAYS LIVE</td><td>~1s</td></tr>
      <tr><td>4. Stale cache</td><td>Serve stale + background refresh</td><td>Old cache + API in bg</td><td>Instant for user</td></tr>
      <tr><td>5. Cron warmup</td><td>Pre-cache all routes × 14 days</td><td>BRF API (scheduled)</td><td>Runs at 06/10/14/20 SGT</td></tr>
    </tbody>
  </table>

  <h2>Cron Setup (add to crontab)</h2>
  <div class="log-box" style="color: #c8f7c5; font-size: .85rem; max-height: 120px;">
    <div class="log-line"># Run 5 minutes BEFORE each slot boundary (SGT = UTC+8)</div>
    <div class="log-line">55 21 * * * /usr/bin/php <?= htmlspecialchars(__DIR__) ?>/ferry_cache_warmup.php >> /var/log/ferry_cache.log 2>&1</div>
    <div class="log-line">55  1 * * * /usr/bin/php <?= htmlspecialchars(__DIR__) ?>/ferry_cache_warmup.php >> /var/log/ferry_cache.log 2>&1</div>
    <div class="log-line">55  5 * * * /usr/bin/php <?= htmlspecialchars(__DIR__) ?>/ferry_cache_warmup.php >> /var/log/ferry_cache.log 2>&1</div>
    <div class="log-line">55 11 * * * /usr/bin/php <?= htmlspecialchars(__DIR__) ?>/ferry_cache_warmup.php >> /var/log/ferry_cache.log 2>&1</div>
  </div>

  <h2>Recent Cache Log (last 50 entries)</h2>
  <div class="log-box">
    <?php foreach ($logLines as $line): ?>
      <?php
        $cls = 'log-line';
        if (str_contains($line, '[ERROR]')) $cls .= ' log-ERROR';
        elseif (str_contains($line, '[WARN]'))  $cls .= ' log-WARN';
        elseif (str_contains($line, '[INFO]'))  $cls .= ' log-INFO';
        else                                    $cls .= ' log-DEBUG';
      ?>
      <div class="<?= $cls ?>"><?= htmlspecialchars(rtrim($line)) ?></div>
    <?php endforeach; ?>
    <?php if (empty($logLines)): ?>
      <div class="log-line log-DEBUG">No log entries yet.</div>
    <?php endif; ?>
  </div>

</div>
</body>
</html>
