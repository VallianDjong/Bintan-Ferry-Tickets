<?php
session_start();

function checkBalance() {
    $url = 'https://api.brf.com.sg/api/CheckBalance/';

    // Get OAuth token first
    $tokenCh = curl_init('https://api.brf.com.sg/token');
    curl_setopt_array($tokenCh, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'grant_type' => 'password',
            'username'   => 'vtapi',
            'password'   => 'cHeqE7I7Kn36XnB',
        ]),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT        => 15,
    ]);
    $tokenResponse = curl_exec($tokenCh);
    $tokenCode     = curl_getinfo($tokenCh, CURLINFO_HTTP_CODE);
    $tokenErr      = curl_error($tokenCh);
    curl_close($tokenCh);

    if ($tokenErr || $tokenCode !== 200) {
        return ['success' => false, 'error' => 'Authentication failed: ' . ($tokenErr ?: "HTTP $tokenCode")];
    }

    $tokenData = json_decode($tokenResponse, true);
    $token = $tokenData['access_token'] ?? '';
    if (empty($token)) {
        return ['success' => false, 'error' => 'Could not retrieve access token'];
    }

    // Call CheckBalance
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode([
            'Username' => 'vtapi',
            'Password' => 'JOznzhbU',
        ]),
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        return ['success' => false, 'error' => 'Connection error: ' . $curlErr];
    }

    $data = json_decode($response, true);

    if ($httpCode === 200 && isset($data['Status']) && $data['Status'] === 'Success') {
        return ['success' => true, 'data' => $data];
    }

    $msg = $data['Message'] ?? "HTTP $httpCode";
    return ['success' => false, 'error' => $msg, 'raw' => $data];
}

$result    = checkBalance();
$fetchTime = date('d M Y, g:i:s a');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Account Balance — Bintan Ferry Tickets</title>
<link rel="icon" type="image/x-icon" href="capital-favicon.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
  body {
    background: #f0f4f8;
    font-family: 'Segoe UI', sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
  }

  .card-wrap {
    width: 100%;
    max-width: 480px;
  }

  /* ── Balance card ── */
  .balance-card {
    background: linear-gradient(135deg, #021320 0%, #0a3d62 100%);
    border-radius: 24px;
    padding: 36px 32px 28px;
    color: #fff;
    box-shadow: 0 20px 60px rgba(2, 19, 32, 0.3);
    position: relative;
    overflow: hidden;
  }

  .balance-card::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 200px; height: 200px;
    background: rgba(53, 157, 215, 0.12);
    border-radius: 50%;
  }

  .balance-card::after {
    content: '';
    position: absolute;
    bottom: -80px; left: -40px;
    width: 240px; height: 240px;
    background: rgba(53, 157, 215, 0.07);
    border-radius: 50%;
  }

  .card-label {
    font-size: 0.78rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.55);
    margin-bottom: 6px;
  }

  .card-title {
    font-size: 1rem;
    font-weight: 600;
    color: rgba(255,255,255,0.85);
    margin-bottom: 28px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .card-title .dot {
    width: 8px; height: 8px;
    background: #28a745;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 0 3px rgba(40,167,69,0.25);
  }

  .balance-amount {
    font-size: 3.2rem;
    font-weight: 800;
    letter-spacing: -1px;
    line-height: 1;
    margin-bottom: 6px;
    position: relative;
    z-index: 1;
  }

  .balance-amount .currency {
    font-size: 1.4rem;
    font-weight: 600;
    vertical-align: super;
    margin-right: 4px;
    color: #359DD7;
  }

  .balance-sub {
    font-size: 0.82rem;
    color: rgba(255,255,255,0.45);
    margin-bottom: 32px;
  }

  .card-divider {
    border-color: rgba(255,255,255,0.1);
    margin: 0 0 20px;
  }

  .card-meta {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    position: relative;
    z-index: 1;
  }

  .meta-item .meta-label {
    font-size: 0.72rem;
    color: rgba(255,255,255,0.4);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 3px;
  }

  .meta-item .meta-value {
    font-size: 0.9rem;
    font-weight: 600;
    color: rgba(255,255,255,0.85);
  }

  /* ── Error state ── */
  .error-card {
    background: #fff;
    border-radius: 24px;
    padding: 36px 32px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.08);
    text-align: center;
  }

  /* ── Info strip below card ── */
  .info-strip {
    background: #fff;
    border-radius: 16px;
    padding: 16px 20px;
    margin-top: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }

  .info-strip .fetch-time {
    font-size: 0.78rem;
    color: #8a9ab0;
  }

  .btn-refresh {
    background: #359DD7;
    color: #fff;
    border: none;
    border-radius: 50px;
    padding: 6px 18px;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background 0.2s;
  }

  .btn-refresh:hover {
    background: #2a87bb;
    color: #fff;
  }

  .btn-home {
    display: block;
    text-align: center;
    margin-top: 12px;
    font-size: 0.82rem;
    color: #8a9ab0;
    text-decoration: none;
  }

  .btn-home:hover { color: #359DD7; }
</style>
</head>
<body>

<div class="card-wrap">

  <?php if ($result['success']): ?>
    <?php $d = $result['data']; ?>

    <!-- Balance card -->
    <div class="balance-card">
      <div class="card-label">Bintan Ferry Tickets</div>
      <div class="card-title">
        <span class="dot"></span> Agent Account
      </div>

      <div class="balance-amount">
        <span class="currency">S$</span><?= number_format((float)$d['BalanceAmount'], 2) ?>
      </div>
      <div class="balance-sub">Available balance</div>

      <hr class="card-divider">

      <div class="card-meta">
        <div class="meta-item">
          <div class="meta-label">Agent Number</div>
          <div class="meta-value"><?= htmlspecialchars($d['AgentNumber'] ?? '—') ?></div>
        </div>
        <div class="meta-item" style="text-align:right;">
          <div class="meta-label">Card Type</div>
          <div class="meta-value"><?= htmlspecialchars($d['CardType'] ?? '—') ?></div>
        </div>
      </div>
    </div>

  <?php else: ?>

    <!-- Error card -->
    <div class="error-card">
      <div style="font-size:3rem;color:#dc3545;margin-bottom:16px;">
        <i class="bi bi-exclamation-circle"></i>
      </div>
      <h5 class="fw-bold mb-2" style="color:#1a1a2e;">Unable to Fetch Balance</h5>
      <p class="text-muted mb-0" style="font-size:0.9rem;">
        <?= htmlspecialchars($result['error'] ?? 'Unknown error') ?>
      </p>
    </div>

  <?php endif; ?>

  <!-- Info strip -->
  <div class="info-strip">
    <span class="fetch-time">
      <i class="bi bi-clock me-1"></i> Last updated: <?= $fetchTime ?>
    </span>
    <a href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="btn-refresh">
      <i class="bi bi-arrow-clockwise"></i> Refresh
    </a>
  </div>

  <a href="index.php" class="btn-home">
    <i class="bi bi-arrow-left me-1"></i> Back to home
  </a>

</div>

</body>
</html>