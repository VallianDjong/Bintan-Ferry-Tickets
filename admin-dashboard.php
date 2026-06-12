<?php
session_start();

// API Credentials
$apiUsername = 'V0014';
$apiPassword = 'wdh17lfnxfncTjUf';
$apiKey = '83a49a9e9cd14ae3a59a834830a718e6';

// Set timezone to Singapore
date_default_timezone_set('Asia/Singapore');

// Simple authentication (you can change these credentials)
$admin_username = 'hello@vtltravel.com';
$admin_password = 'Vtltravel1!';

// Check if already logged in
if (!isset($_SESSION['admin_logged_in'])) {
    // Check login form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        if ($_POST['username'] === $admin_username && $_POST['password'] === $admin_password) {
            $_SESSION['admin_logged_in'] = true;
        } else {
            $login_error = 'Invalid username or password';
        }
    }
    
    // If not logged in, show login form
    if (!isset($_SESSION['admin_logged_in'])) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin Login - Batam Ferry Tickets</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
            <style>
                body {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .login-card {
                    background: white;
                    border-radius: 15px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
                    padding: 40px;
                    max-width: 400px;
                    width: 100%;
                }
                .login-header {
                    text-align: center;
                    margin-bottom: 30px;
                }
                .login-header h2 {
                    color: #333;
                    font-weight: 700;
                }
                .login-header p {
                    color: #666;
                }
            </style>
        </head>
        <body>
            <div class="login-card">
                <div class="login-header">
                    <i class="bi bi-shield-lock" style="font-size: 3rem; color: #764ba2;"></i>
                    <h2>Admin Dashboard</h2>
                    <p>Please login to continue</p>
                </div>
                
                <?php if (isset($login_error)): ?>
                    <div class="alert alert-danger"><?= $login_error ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin-dashboard.php');
    exit;
}

// Handle API Booking Lookup
$apiBookingResult = null;
$apiBookingError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lookup_booking'])) {
    $bookingIdToLookup = $_POST['booking_id'] ?? '';
    
    if (!empty($bookingIdToLookup)) {
        // Get OAuth2 token
        $oauthToken = getOAuth2Token($apiUsername, $apiPassword);
        
        if (!empty($oauthToken)) {
            // Fetch booking details from API
            $apiBookingResult = getBookingDetailsFromAPI($bookingIdToLookup, $oauthToken, $apiKey);
            
            if (!$apiBookingResult['success']) {
                $apiBookingError = $apiBookingResult['error'];
            }
        } else {
            $apiBookingError = 'Failed to get OAuth2 token';
        }
    } else {
        $apiBookingError = 'Please enter a booking ID';
    }
}

// Handle actions for file-based bookings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_booking'])) {
        $bookingsFile = 'confirmed_bookings.json';
        if (file_exists($bookingsFile)) {
            $bookings = json_decode(file_get_contents($bookingsFile), true) ?: [];
            $updatedBookings = array_filter($bookings, function($booking) {
                return $booking['id'] != $_POST['booking_id'];
            });
            file_put_contents($bookingsFile, json_encode(array_values($updatedBookings), JSON_PRETTY_PRINT));
            $success_message = "Booking deleted successfully";
        }
    } elseif (isset($_POST['export_csv'])) {
        exportBookingsToCSV();
        exit;
    } elseif (isset($_POST['clear_all'])) {
        $bookingsFile = 'confirmed_bookings.json';
        file_put_contents($bookingsFile, json_encode([], JSON_PRETTY_PRINT));
        $success_message = "All bookings cleared";
    }
}

// Read bookings from file
$bookingsFile = 'confirmed_bookings.json';
$bookings = [];
if (file_exists($bookingsFile)) {
    $content = file_get_contents($bookingsFile);
    if (!empty($content)) {
        $bookings = json_decode($content, true) ?: [];
    }
}

// Sort by confirmed date (newest first)
usort($bookings, function($a, $b) {
    return strtotime($b['confirmed_at']) - strtotime($a['confirmed_at']);
});

// Get current Singapore date for statistics
$singaporeNow = new DateTime('now', new DateTimeZone('Asia/Singapore'));
$todayDate = $singaporeNow->format('Y-m-d');

// Calculate statistics
$totalBookings = count($bookings);
$totalRevenue = array_sum(array_column($bookings, 'total_amount'));

// Use Singapore timezone for today's bookings comparison
$todayBookings = array_filter($bookings, function($booking) use ($singaporeNow) {
    $bookingDate = new DateTime($booking['confirmed_at'], new DateTimeZone('Asia/Singapore'));
    return $bookingDate->format('Y-m-d') === $singaporeNow->format('Y-m-d');
});
$todayRevenue = array_sum(array_column($todayBookings, 'total_amount'));

// ==================== API FUNCTIONS ====================

function getOAuth2Token($username, $password) {
    $apiUrl = 'http://54.251.107.43/agentapi/oauth2/token';
    
    error_log("Getting OAuth2 token for admin dashboard");
    
    // Prepare request data
    $postData = http_build_query([
        'username' => $username,
        'password' => $password,
        'grant_type' => 'password'
    ]);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error || $httpCode !== 200) {
        error_log("OAuth2 Token Failed: $error");
        return '';
    }
    
    $data = json_decode($response, true);
    
    if (isset($data['access_token']) && isset($data['token_type'])) {
        return $data['token_type'] . ' ' . $data['access_token'];
    }
    
    return '';
}

function getBookingDetailsFromAPI($bookingId, $oauthToken, $apiKey) {
    $apiUrl = 'http://54.251.107.43/agentapi/booking/' . urlencode($bookingId);
    
    error_log("Fetching booking details from API: $apiUrl");
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPGET => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: ' . $oauthToken,
            'Accept: application/json',
            'X-API-Key: ' . $apiKey
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'error' => 'Connection error: ' . $error
        ];
    }
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        return [
            'success' => true,
            'data' => $data,
            'http_code' => $httpCode
        ];
    } else {
        $errorMessage = "HTTP Error $httpCode";
        $responseData = json_decode($response, true);
        
        if (isset($responseData['message'])) {
            $errorMessage = $responseData['message'];
        } elseif (isset($responseData['error'])) {
            $errorMessage = $responseData['error'];
        }
        
        return [
            'success' => false,
            'error' => $errorMessage,
            'http_code' => $httpCode
        ];
    }
}

function exportBookingsToCSV() {
    global $bookings;
    
    $filename = 'bookings_export_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Headers
    fputcsv($output, [
        'Booking ID',
        'Confirmation Code',
        'Payment Reference',
        'Confirmed Date',
        'Customer Name',
        'Customer Email',
        'Customer Phone',
        'Trip Type',
        'Adults',
        'Children',
        'Infants',
        'Total Passengers',
        'Departure Origin',
        'Departure Destination',
        'Departure Date',
        'Departure Time',
        'Return Origin',
        'Return Destination',
        'Return Date',
        'Return Time',
        'Total Amount',
        'Currency',
        'Status',
        'Email Sent'
    ]);
    
    // Data
    foreach ($bookings as $booking) {
        $returnDetails = $booking['return_details'] ?? null;
        
        fputcsv($output, [
            $booking['id'],
            $booking['confirmation_code'],
            $booking['payment_ref'],
            $booking['confirmed_at'],
            $booking['customer_name'],
            $booking['customer_email'],
            $booking['customer_phone'],
            $booking['trip_type'],
            $booking['adult_qty'],
            $booking['child_qty'],
            $booking['infant_qty'],
            $booking['adult_qty'] + $booking['child_qty'] + $booking['infant_qty'],
            $booking['departure_details']['origin'] ?? 'N/A',
            $booking['departure_details']['destination'] ?? 'N/A',
            $booking['departure_details']['date'] ?? 'N/A',
            $booking['departure_details']['time'] ?? 'N/A',
            $returnDetails['origin'] ?? 'N/A',
            $returnDetails['destination'] ?? 'N/A',
            $returnDetails['date'] ?? 'N/A',
            $returnDetails['time'] ?? 'N/A',
            $booking['total_amount'],
            $booking['currency'],
            $booking['status'],
            $booking['email_sent'] ? 'Yes' : 'No'
        ]);
    }
    
    fclose($output);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Confirmed Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .dashboard-container {
            padding: 20px;
        }
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            color: #667eea;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
        }
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .booking-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .booking-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }
        .booking-details {
            display: none;
            background: #f8f9fa;
            padding: 20px;
            margin: 10px;
            border-radius: 8px;
        }
        .booking-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .booking-row:hover {
            background-color: #f5f5f5;
        }
        .badge-confirmed {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
        }
        .badge-email-sent {
            background-color: #17a2b8;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
        }
        .badge-email-failed {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
        }
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .action-buttons {
            position: sticky;
            top: 20px;
            z-index: 100;
        }
        .lookup-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        .passenger-table {
            font-size: 0.9rem;
        }
        .passenger-table th {
            background: #e9ecef;
        }
        .api-response {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            max-height: 400px;
            overflow-y: auto;
        }
        .nav-tabs .nav-link {
            color: #495057;
        }
        .nav-tabs .nav-link.active {
            font-weight: 600;
            color: #667eea;
            border-bottom: 2px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="dashboard-header d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="bi bi-speedometer2 me-2"></i>Booking Dashboard</h1>
                <p class="mb-0">View and manage all confirmed bookings</p>
            </div>
            <div>
                <span class="me-3"><i class="bi bi-person-circle"></i> Admin</span>
                <a href="?logout=1" class="btn btn-light btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </div>

        <!-- Success Message -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= $success_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- API Booking Lookup Section -->
        <div class="lookup-card">
            <h5 class="mb-3"><i class="bi bi-search me-2"></i>API Booking Lookup</h5>
            <form method="POST" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-hash"></i></span>
                        <input type="text" class="form-control" name="booking_id" placeholder="Enter Booking ID (e.g., 5969667)" required>
                        <button type="submit" name="lookup_booking" class="btn btn-primary">
                            <i class="bi bi-cloud-download"></i> Fetch from API
                        </button>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i> 
                        Fetches real-time booking data directly from the API
                    </small>
                </div>
            </form>

            <?php if ($apiBookingError): ?>
                <div class="alert alert-danger mt-3">
                    <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($apiBookingError) ?>
                </div>
            <?php endif; ?>

            <?php if ($apiBookingResult && $apiBookingResult['success']): ?>
                <?php $bookingData = $apiBookingResult['data']; ?>
                <div class="mt-4">
                    <ul class="nav nav-tabs" id="apiBookingTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="summary-tab" data-bs-toggle="tab" data-bs-target="#summary" type="button" role="tab">
                                <i class="bi bi-info-circle"></i> Summary
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="passengers-tab" data-bs-toggle="tab" data-bs-target="#passengers" type="button" role="tab">
                                <i class="bi bi-people"></i> Passengers (<?= count($bookingData['paxs'] ?? []) ?>)
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content p-3 border border-top-0 rounded-bottom bg-white" id="apiBookingTabsContent">
                        <!-- Summary Tab -->
                        <div class="tab-pane fade show active" id="summary" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="border-bottom pb-2">Booking Information</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Booking ID:</th>
                                            <td><strong>#<?= $bookingData['id'] ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>Booking Mode:</th>
                                            <td><?= $bookingData['bookingMode'] ?? 'N/A' ?></td>
                                        </tr>
                                        <tr>
                                            <th>Cabin Class:</th>
                                            <td><?= $bookingData['cabinClass'] ?? 'N/A' ?></td>
                                        </tr>
                                        <tr>
                                            <th>Customer:</th>
                                            <td><?= htmlspecialchars($bookingData['customerName'] ?? 'N/A') ?></td>
                                        </tr>
                                        <tr>
                                            <th>Payment Date:</th>
                                            <td><?= $bookingData['payDate'] ?? 'N/A' ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <h6 class="border-bottom pb-2">Departure Trip</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Route:</th>
                                            <td><?= htmlspecialchars($bookingData['departPortOriginName'] ?? 'N/A') ?> → <?= htmlspecialchars($bookingData['departPortDestinationName'] ?? 'N/A') ?></td>
                                        </tr>
                                        <tr>
                                            <th>Date:</th>
                                            <td><?= $bookingData['departDate'] ?? 'N/A' ?> at <?= $bookingData['departETD'] ?? 'N/A' ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <?php if ($bookingData['isRoundTrip'] ?? false): ?>
                                <div class="col-md-6">
                                    <h6 class="border-bottom pb-2">Return Trip</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Route:</th>
                                            <td><?= htmlspecialchars($bookingData['returnPortOriginName'] ?? 'N/A') ?> → <?= htmlspecialchars($bookingData['returnPortDestinationName'] ?? 'N/A') ?></td>
                                        </tr>
                                        <tr>
                                            <th>Date:</th>
                                            <td><?= $bookingData['returnDate'] ?? 'N/A' ?> at <?= $bookingData['returnETD'] ?? 'N/A' ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6 class="border-bottom pb-2">Passenger Summary</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Adults:</th>
                                            <td><?= $bookingData['adultQty'] ?? 0 ?> (Fare: <?= $bookingData['currencyId'] ?? 'S$' ?> <?= number_format($bookingData['fareAdult'] ?? 0, 2) ?> each)</td>
                                        </tr>
                                        <tr>
                                            <th>Children:</th>
                                            <td><?= $bookingData['childQty'] ?? 0 ?> (Fare: <?= $bookingData['currencyId'] ?? 'S$' ?> <?= number_format($bookingData['fareChild'] ?? 0, 2) ?> each)</td>
                                        </tr>
                                        <tr>
                                            <th>Infants:</th>
                                            <td><?= $bookingData['infantQty'] ?? 0 ?> (Fare: <?= $bookingData['currencyId'] ?? 'S$' ?> <?= number_format($bookingData['fareInfant'] ?? 0, 2) ?> each)</td>
                                        </tr>
                                        <tr>
                                            <th>Total Passengers:</th>
                                            <td><strong><?= $bookingData['paxCount'] ?? 0 ?></strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                        </div>
                        
                        <!-- Passengers Tab -->
                        <div class="tab-pane fade" id="passengers" role="tabpanel">
                            <?php if (!empty($bookingData['paxs'])): ?>
                                <?php foreach ($bookingData['paxs'] as $paxIndex => $pax): ?>
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <strong>Passenger <?= $paxIndex + 1 ?>: <?= htmlspecialchars($pax['name'] ?? 'N/A') ?></strong>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <table class="table table-sm table-borderless">
                                                        <tr>
                                                            <th>ID:</th>
                                                            <td><?= $pax['id'] ?? 'N/A' ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Passport No:</th>
                                                            <td><?= htmlspecialchars($pax['passportNo'] ?? 'N/A') ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Passport Issue:</th>
                                                            <td><?= htmlspecialchars($pax['passportPlaceIssue'] ?? 'N/A') ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Passport Expiry:</th>
                                                            <td><?= $pax['passportExpiry'] ?? 'N/A' ?></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <table class="table table-sm table-borderless">
                                                        <tr>
                                                            <th>Gender:</th>
                                                            <td><?= $pax['gender'] ?? 'N/A' ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Birth Date:</th>
                                                            <td><?= $pax['birthDate'] ?? 'N/A' ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Nationality:</th>
                                                            <td><?= htmlspecialchars($pax['nationalityName'] ?? 'N/A') ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Residence:</th>
                                                            <td><?= htmlspecialchars($pax['countryResidenceName'] ?? 'N/A') ?></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                            
                                            <?php if (!empty($pax['paxTrips'])): ?>
                                                <h6 class="mt-2">Trip Details</h6>
                                                <table class="table table-sm table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Trip</th>
                                                            <th>Route</th>
                                                            <th>Date/Time</th>
                                                            <th>Ticket No</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($pax['paxTrips'] as $trip): ?>
                                                            <tr>
                                                                <td><?= $trip['isOpenTrip'] ? 'Open' : 'Fixed' ?></td>
                                                                <td><?= htmlspecialchars($trip['departPortOriginName'] ?? 'N/A') ?> → <?= htmlspecialchars($trip['departPortDestinationName'] ?? 'N/A') ?></td>
                                                                <td><?= $trip['departDate'] ?? 'N/A' ?> <?= $trip['departETD'] ?? '' ?></td>
                                                                <td><?= $trip['ticketNo'] ?? 'N/A' ?></td>
                                                                <td>
                                                                    <span class="badge bg-<?= ($trip['status'] ?? '') == 'CF' ? 'success' : 'warning' ?>">
                                                                        <?= $trip['statusName'] ?? $trip['status'] ?? 'N/A' ?>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No passenger details available</p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Raw API Response Tab -->
                        <div class="tab-pane fade" id="raw" role="tabpanel">
                            <pre class="api-response"><?= htmlspecialchars(json_encode($bookingData, JSON_PRETTY_PRINT)) ?></pre>
                            <button class="btn btn-sm btn-outline-secondary mt-2" onclick="copyRawResponse()">
                                <i class="bi bi-clipboard"></i> Copy to Clipboard
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Filter and Actions -->
        <div class="filter-section">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5><i class="bi bi-funnel me-2"></i>Filters</h5>
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Search by name, email, booking ID...">
                        <select class="form-select form-select-sm" id="dateFilter" style="width: auto;">
                            <option value="all">All Dates</option>
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="thisweek">This Week</option>
                            <option value="thismonth">This Month</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <form method="POST" class="d-inline">
                    </form>
                    <button class="btn btn-primary btn-sm" onclick="printDashboard()">
                        <i class="bi bi-printer"></i> Print
                    </button>
                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to clear all bookings? This action cannot be undone.');">
                        <button type="submit" name="clear_all" class="btn btn-danger btn-sm">
                            <i class="bi bi-trash"></i> Clear All
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="booking-table">
            <table class="table table-hover mb-0" id="bookingsTable">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Date/Time</th>
                        <th>Passengers</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="bi bi-inbox" style="font-size: 2rem; color: #ccc;"></i>
                                <p class="mt-2 text-muted">No confirmed bookings yet</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $index => $booking): ?>
                            <tr class="booking-row" onclick="toggleDetails(<?= $index ?>)">
                                <td>
                                    <strong>#<?= htmlspecialchars($booking['id']) ?></strong>
                                    <br>
                                    <small class="text-muted">Ref: <?= substr(htmlspecialchars($booking['payment_ref']), 0, 8) ?>...</small>
                                </td>
                                <td>
    <?php 
    $sgDateTime = new DateTime($booking['confirmed_at'], new DateTimeZone('Asia/Singapore'));
    echo $sgDateTime->format('d M Y');
    ?>
    <br>
    <small class="text-muted"><?php echo $sgDateTime->format('H:i'); ?> SGT</small>
</td>
                                <td>
                                    <?= $booking['adult_qty'] + $booking['child_qty'] + $booking['infant_qty'] ?> PAX
                                    <br>
                                    <small class="text-muted">
                                        A:<?= $booking['adult_qty'] ?> C:<?= $booking['child_qty'] ?> I:<?= $booking['infant_qty'] ?>
                                    </small>
                                </td>
                                 <td>
                                    <div class="btn-group btn-group-sm" onclick="event.stopPropagation()">
                                        <button class="btn btn-outline-danger" onclick="deleteBooking(<?= $booking['id'] ?>, '<?= addslashes(htmlspecialchars($booking['customer_name'])) ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr id="details-<?= $index ?>" class="booking-details">
                                <td colspan="8">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6><i class="bi bi-person"></i> Customer Information</h6>
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th>Name:</th>
                                                    <td><?= htmlspecialchars($booking['customer_name']) ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Email:</th>
                                                    <td><?= htmlspecialchars($booking['customer_email']) ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Phone:</th>
                                                    <td><?= htmlspecialchars($booking['customer_phone']) ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <h6><i class="bi bi-credit-card"></i> Payment Information</h6>
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th>Payment Ref:</th>
                                                    <td><?= htmlspecialchars($booking['payment_ref']) ?></td>
                                                </tr>
                                                <tr>
                                                   <th>Confirmed At (SGT):</th>
<td>
    <?php 
    $sgDateTime = new DateTime($booking['confirmed_at'], new DateTimeZone('Asia/Singapore'));
    echo $sgDateTime->format('d M Y H:i:s'); 
    ?>
</td>
                                                </tr>
                                                <?php if (isset($booking['promo_code'])): ?>
                                                <tr>
                                                    <th>Promo Code:</th>
                                                    <td><?= $booking['promo_code'] ?> (<?= $booking['promo_discount'] ?>% off)</td>
                                                </tr>
                                                <?php endif; ?>
                                            </table>
                                        </div>
                                    </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Hidden delete form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="delete_booking" value="1">
        <input type="hidden" name="booking_id" id="deleteBookingId">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle booking details
        function toggleDetails(index) {
            const detailsRow = document.getElementById('details-' + index);
            if (detailsRow.style.display === 'table-row') {
                detailsRow.style.display = 'none';
            } else {
                detailsRow.style.display = 'table-row';
            }
        }

        // Delete booking
        function deleteBooking(bookingId, customerName) {
            if (confirm(`Are you sure you want to delete booking #${bookingId} for ${customerName}?`)) {
                document.getElementById('deleteBookingId').value = bookingId;
                document.getElementById('deleteForm').submit();
            }
        }

        // Copy raw response to clipboard
        function copyRawResponse() {
            const rawText = document.querySelector('.api-response').textContent;
            navigator.clipboard.writeText(rawText).then(() => {
                alert('Raw response copied to clipboard!');
            });
        }

        // Copy to local storage (placeholder function)
        function copyToLocal(bookingId) {
            alert('Function to copy booking #' + bookingId + ' to local storage would be implemented here.');
            // You could implement this to save the API result to your confirmed_bookings.json
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('#bookingsTable tbody tr.booking-row');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                    // Also hide details row if visible
                    const index = row.rowIndex - 1;
                    const detailsRow = document.getElementById('details-' + index);
                    if (detailsRow) {
                        detailsRow.style.display = 'none';
                    }
                }
            });
        });

        // Date filter
        document.getElementById('dateFilter').addEventListener('change', function() {
            const filter = this.value;
            const today = new Date().toISOString().split('T')[0];
            const yesterday = new Date(Date.now() - 86400000).toISOString().split('T')[0];
            
            const rows = document.querySelectorAll('#bookingsTable tbody tr.booking-row');
            
            rows.forEach(row => {
                const dateCell = row.cells[1].textContent.trim();
                const bookingDate = dateCell.split('\n')[0].trim();
                
                let show = true;
                
                if (filter === 'today') {
                    show = isSameDay(bookingDate, today);
                } else if (filter === 'yesterday') {
                    show = isSameDay(bookingDate, yesterday);
                } else if (filter === 'thisweek') {
                    show = isThisWeek(bookingDate);
                } else if (filter === 'thismonth') {
                    show = isThisMonth(bookingDate);
                }
                
                if (show) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                    const index = row.rowIndex - 1;
                    const detailsRow = document.getElementById('details-' + index);
                    if (detailsRow) {
                        detailsRow.style.display = 'none';
                    }
                }
            });
        });

        function isSameDay(date1, date2) {
            return new Date(date1).toDateString() === new Date(date2).toDateString();
        }

        function isThisWeek(date) {
            const d = new Date(date);
            const now = new Date();
            const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
            return d >= weekAgo && d <= now;
        }

        function isThisMonth(date) {
            const d = new Date(date);
            const now = new Date();
            return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
        }

        // Print dashboard
        function printDashboard() {
            window.print();
        }

        // Add print styles
        const style = document.createElement('style');
        style.innerHTML = `
            @media print {
                .dashboard-header, .filter-section, .btn-group, .action-buttons, .booking-details, .lookup-card {
                    display: none !important;
                }
                body {
                    background: white;
                }
                .booking-table {
                    box-shadow: none;
                }
            }
        `;
        document.head.appendChild(style);

        // Auto-refresh every 60 seconds
        setTimeout(function() {
            location.reload();
        }, 60000);
    </script>
</body>
</html>