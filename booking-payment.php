<?php
// booking-payment.php - Add this at the very top
session_start();

// Include HitPay integration
require_once 'hitpay-integration.php';

// Check if this is a callback from HitPay
$paymentId = $_GET['payment_id'] ?? '';
$paymentRequestId = $_GET['payment_request_id'] ?? '';
$status = $_GET['status'] ?? '';
$referenceNumber = $_GET['reference_number'] ?? '';

if ($status && $paymentId) {
    // This is a callback from HitPay
    $_SESSION['payment_status'] = $status;
    $_SESSION['payment_id'] = $paymentId;
    $_SESSION['payment_request_id'] = $paymentRequestId;
    
    // If payment was successful, update booking status
    if ($status === 'completed') {
        // Here you would typically:
        // 1. Update your database to mark booking as paid
        // 2. Generate booking confirmation
        // 3. Send confirmation email
        
        // For demo, we'll generate a booking reference if not exists
        if (!isset($_SESSION['booking_ref'])) {
            $_SESSION['booking_ref'] = 'SG-BTM-' . strtoupper(substr(uniqid(), -6));
        }
        
        // Store payment success in session
        $_SESSION['payment_success'] = true;
    }
}

// Get booking data from session
$bookingResult = $_SESSION['booking_result'] ?? [];
$bookingFormData = $_SESSION['booking_form_data'] ?? [];
$selectedDeparture = $_SESSION['selected_departure'] ?? [];
$selectedReturn = $_SESSION['selected_return'] ?? null;
$paymentStatus = $_SESSION['payment_status'] ?? '';

// If no payment was made, redirect to booking-checking
if (!$paymentStatus && empty($bookingResult)) {
    header('Location: booking-checking.php');
    exit;
}

// Determine what to display
$isPaymentSuccessful = ($paymentStatus === 'completed' || isset($_SESSION['payment_success']));
$bookingRef = $_SESSION['booking_ref'] ?? 'SG-BTM-8F29QX';
$customerEmail = $bookingFormData['email'] ?? 'john_smith@gmail.com';
$customerName = $bookingFormData['full_name'] ?? 'John Smith';

// Get booking details
$departOrigin = $_SESSION['departOrigin'] ?? 'Harbourfront (HFC)';
$departDestination = $_SESSION['departDestination'] ?? 'Batam (BTC)';
$departDate = $_SESSION['departDate'] ?? '2026-01-17';
$departTime = $_SESSION['departTime'] ?? '09:00';

$returnOrigin = $_SESSION['returnOrigin'] ?? 'Batam (BTC)';
$returnDestination = $_SESSION['returnDestination'] ?? 'Harbourfront (HFC)';
$returnDate = $_SESSION['returnDate'] ?? '2026-01-18';
$returnTime = $_SESSION['returnTime'] ?? '18:00';

// Calculate prices
$departurePrice = $selectedDeparture['price'] ?? 0;
$returnPrice = $selectedReturn['price'] ?? 0;
$passengers = $selectedDeparture['passengers'] ?? 2;
$currency = $selectedDeparture['currency'] ?? 'SGD';

$departureTotal = $departurePrice * $passengers;
$returnTotal = $returnPrice * $passengers;
$subtotal = $departureTotal + $returnTotal;

// Additional fees
$surcharge = 8.00;
$terminalFee = 10.00;
$confirmationFee = 0.00;

$departureWithFees = $departureTotal + $surcharge + $terminalFee + $confirmationFee;
$returnWithFees = $returnTotal + $surcharge + $terminalFee + $confirmationFee;
$grandTotal = $departureWithFees + $returnWithFees;

// Get passenger details
$passengerDetails = [];
for ($i = 1; $i <= $passengers; $i++) {
    if (isset($bookingFormData["name_$i"])) {
        $passengerDetails[] = [
            'name' => $bookingFormData["name_$i"] ?? '',
            'passportNo' => $bookingFormData["passportNo_$i"] ?? '',
            'nationality' => $bookingFormData["nationalityId_$i"] ?? '',
            'dob' => isset($bookingFormData["birthDate_year_$i"], $bookingFormData["birthDate_month_$i"], $bookingFormData["birthDate_day_$i"]) 
                    ? $bookingFormData["birthDate_year_$i"] . '-' . $bookingFormData["birthDate_month_$i"] . '-' . $bookingFormData["birthDate_day_$i"]
                    : '1992-06-12',
        ];
    }
}

// If no passenger details, use defaults
if (empty($passengerDetails)) {
    $passengerDetails = [
        ['name' => 'John Smith', 'passportNo' => '****5678', 'nationality' => 'SG', 'dob' => '1992-06-12'],
        ['name' => 'Jane Smith', 'passportNo' => '****5679', 'nationality' => 'SG', 'dob' => '1992-06-12']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<!-- Google tag (gtag.js) - Combined -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-S8024PF2N0"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  // Google Analytics 4
  gtag('config', 'G-S8024PF2N0');

  // Google Ads
  gtag('config', 'AW-10870335725');
</script>
    <meta charset="UTF-8">
    <title>Batam Ferry Ticket</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="favicon_logo.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* Keep your existing styles */
        body { background-color: #f0f4f8; font-family: 'Segoe UI', sans-serif; }
        
        .navbar { background: white; border-bottom: 1px solid #ddd; }
        /* Confirmation Section Styling */
        .success-container { text-align: center; padding: 60px 20px; }
        .success-icon-circle {
            width: 100px; height: 100px; background-color: #fce4e2; 
            border: 4px solid #df4d45; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 25px;
        }
        .success-icon-circle i { font-size: 50px; color: #df4d45; }
        
        .ref-pill {
            display: flex; align-items: center; border: 1px solid #df4d45;
            border-radius: 50px; padding: 6px 18px; background: white; gap: 12px; width: 250px; margin: 0 auto; justify-content: space-between;
        }
        .copy-icon-box {
            background-color: #df4d45; color: white; border-radius: 6px;
            width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;
            cursor: pointer;
        }
        
        /* Payment status styling */
        .payment-status {
            padding: 10px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .status-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-light bg-white mb-4">
        <div class="container d-flex justify-content-between">
            <img src="horizontal_logo.svg" style="height: 30px;">
            <div>
                <span class="me-3"><img src="https://flagcdn.com/w20/sg.png" width="20"> SGD <i class="bi bi-chevron-down"></i></span>
                <button class="btn btn-outline-danger btn-sm rounded-pill px-3"><i class="bi bi-person-circle"></i> Login</button>
            </div>
        </div>
    </nav>

<div class="container py-5">
    
    <!-- Display payment status message -->
    <?php if (!$isPaymentSuccessful): ?>
        <div class="payment-status status-failed">
            <h5><i class="bi bi-exclamation-triangle"></i> Payment Not Completed</h5>
            <p>Your payment was not completed. Status: <?= htmlspecialchars($paymentStatus) ?></p>
            <a href="booking-checking.php" class="btn btn-danger btn-sm">Try Payment Again</a>
        </div>
    <?php endif; ?>
    
    <?php if ($isPaymentSuccessful): ?>
    <div class="card details-card shadow-sm mt-4 success-container">
        <div class="success-icon-circle">
            <i class="bi bi-check-lg"></i>
        </div>
        <h2 class="fw-bold">Booking Confirmed</h2>
        <p class="text-muted">Your ferry ticket is secured.</p>

        <div class="ref-pill mb-3">
            <div class="text-start">
                <small class="text-muted d-block" style="font-size: 0.6rem;">Booking Ref</small>
                <span id="bookingRef" class="fw-bold small"><?= htmlspecialchars($bookingRef) ?></span>
            </div>
            <div class="copy-icon-box" onclick="copyBookingRef()" title="Copy to clipboard">
                <i class="bi bi-copy" style="font-size: 0.75rem;"></i>
            </div>
        </div>
        <div class="text-muted small"><?= $selectedReturn ? 'Round Trip' : 'One Way' ?></div>

        <div class="alert bg-white border mt-4 mx-auto" style="max-width: 550px; border-radius: 12px; font-size: 0.85rem;">
            A confirmation email has been sent to your inbox <br>
            <strong>(<?= htmlspecialchars($customerEmail) ?>)</strong>, containing all the details of your ticket.
        </div>
    </div>

    <div class="card details-card shadow-sm mt-4">
        <div class="card-header-toggle p-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">View Booking Details</h6>
            <i class="bi bi-chevron-down text-muted"></i>
        </div>
        
        <div class="card-body p-4">
            <div class="row g-4 mb-4">
                <!-- Departure Trip -->
                <div class="col-md-6 border-end">
                    <div class="mb-4">
                        <div class="itinerary-label mb-1"><i class="bi bi-ferry"></i> Depart</div>
                        <div class="fw-bold"><?= htmlspecialchars($departOrigin) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars(substr($departOrigin, 0, 3)) ?></div>
                        <div class="mt-2 small fw-bold">
                            <?= date('D, j M Y', strtotime($departDate)) ?> - <?= date('H:i', strtotime($departTime)) ?> SGT
                        </div>
                        <div class="text-muted small mt-1">
                            <i class="bi bi-clock"></i> Gate: Open <strong>1 hour before departure</strong>
                        </div>
                    </div>
                    <div>
                        <div class="itinerary-label mb-1"><i class="bi bi-geo-alt"></i> Arrive</div>
                        <div class="fw-bold"><?= htmlspecialchars($departDestination) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars(substr($departDestination, 0, 3)) ?></div>
                        <div class="mt-2 small fw-bold">
                            Arrival: <?= date('D, j M Y', strtotime($departDate)) ?> - <?= date('H:i', strtotime($departTime . ' +1 hour')) ?> WIB
                        </div>
                    </div>
                </div>

                <!-- Return Trip (if exists) -->
                <?php if ($selectedReturn): ?>
                <div class="col-md-6">
                    <div class="mb-4">
                        <div class="itinerary-label mb-1"><i class="bi bi-ferry"></i> Depart</div>
                        <div class="fw-bold"><?= htmlspecialchars($returnOrigin) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars(substr($returnOrigin, 0, 3)) ?></div>
                        <div class="mt-2 small fw-bold">
                            <?= date('D, j M Y', strtotime($returnDate)) ?> - <?= date('H:i', strtotime($returnTime)) ?> WIB
                        </div>
                        <div class="text-muted small mt-1">
                            <i class="bi bi-clock"></i> Gate: Open <strong>1 hour before departure</strong>
                        </div>
                    </div>
                    <div>
                        <div class="itinerary-label mb-1"><i class="bi bi-geo-alt"></i> Arrive</div>
                        <div class="fw-bold"><?= htmlspecialchars($returnDestination) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars(substr($returnDestination, 0, 3)) ?></div>
                        <div class="mt-2 small fw-bold">
                            Arrival: <?= date('D, j M Y', strtotime($returnDate)) ?> - <?= date('H:i', strtotime($returnTime . ' +1 hour')) ?> SGT
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <hr class="my-4">

            <div class="d-flex align-items-center flex-wrap gap-4 mb-5">
                <button class="btn btn-danger rounded-pill px-4 fw-bold" style="background-color: #df4d45; border:none;">
                    View Boarding Pass
                </button>
                <a href="#" class="action-link" onclick="window.print()">Download as PDF</a>
                <div class="vr d-none d-md-block" style="height: 20px;"></div>
                <a href="mailto:<?= htmlspecialchars($customerEmail) ?>?subject=Booking%20Confirmation%20<?= htmlspecialchars($bookingRef) ?>&body=Your%20booking%20details%20are%20attached" class="action-link">Send to Email</a>
            </div>

            <div class="row">
                <div class="col-md-7 border-end">
                    <div class="itinerary-label mb-3"><i class="bi bi-person-circle"></i> Passenger Details (<?= $passengers ?> PAX)</div>
                    <?php foreach ($passengerDetails as $index => $passenger): ?>
                    <div class="ps-3 mb-3">
                        <div class="fw-bold"><?= ($index + 1) ?>. <?= htmlspecialchars(strtoupper($passenger['name'])) ?></div>
                        <div class="text-muted small">
                            Passport: <?= htmlspecialchars(substr($passenger['passportNo'], 0, 4)) ?>**** | 
                            Nationality: <?= htmlspecialchars($passenger['nationality']) ?> | 
                            DOB: <?= date('j M Y', strtotime($passenger['dob'])) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="mt-3 small text-muted">
                        Name mismatch? <a href="#" class="text-danger fw-bold text-decoration-none">Contact Support</a>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="itinerary-label mb-3"><i class="bi bi-credit-card"></i> Payment Summary</div>
                    <div class="ps-3">
                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="text-muted">Total:</span> 
                            <strong><?= $currency == 'SGD' ? 'SGD' : 'IDR' ?><?= number_format($grandTotal, 2) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="text-muted">Method:</span> 
                            <strong>HitPay Payment</strong>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">Transaction:</span> 
                            <strong><?= $_SESSION['payment_id'] ?? 'TXN-' . substr(uniqid(), -8) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between small mt-2">
                            <span class="text-muted">Status:</span> 
                            <span class="badge bg-success">Paid</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
</div>

<?php if ($isPaymentSuccessful): ?>
<div class="container mb-5">
    <div class="card shadow-sm border-0 py-4 px-4" style="border-radius: 15px; background-color: #fff;">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-info-circle-fill text-secondary me-2"></i>
                    <h6 class="fw-bold mb-0">What Happen Next</h6>
                </div>
                <ul class="list-unstyled small text-muted ps-4">
                    <li class="mb-2">• Arrive 60 mins early</li>
                    <li class="mb-2">• Bring passport + ticket</li>
                    <li class="mb-2">• Boarding subject to clearance</li>
                    <li class="mb-2">• Schedule may change</li>
                </ul>
            </div>

            <div class="col-md-4">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                    <h6 class="fw-bold mb-0 text-danger">TRAVEL REQUIREMENTS</h6>
                </div>
                <ul class="list-unstyled small text-muted ps-4">
                    <li class="mb-2">• Passport valid 6+ months</li>
                    <li class="mb-2">• Name must match passport</li>
                    <li class="mb-2">• Immigration clearance needed</li>
                    <li class="mb-2">• Visa depends on nationality</li>
                    <li class="mb-2">• Check-in closes 30-45 mins</li>
                </ul>
            </div>

            <div class="col-md-4">
                <h6 class="fw-bold mb-3">Need Help?</h6>
                <div class="ps-2">
                    <a href="#" class="d-flex align-items-center text-decoration-none text-muted small mb-3">
                        <i class="bi bi-info-circle me-3 fs-5"></i>
                        <span>Change/Cancel Policy</span>
                    </a>
                    <a href="#" class="d-flex align-items-center text-decoration-none text-muted small mb-3">
                        <i class="bi bi-whatsapp text-success me-3 fs-5"></i>
                        <span>Chat on WhatsApp</span>
                    </a>
                    <a href="#" class="d-flex align-items-center text-decoration-none text-muted small">
                        <i class="bi bi-headset me-3 fs-5"></i>
                        <span>Contact Support</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    // Copy booking reference to clipboard
    function copyBookingRef() {
        const bookingRef = document.getElementById('bookingRef').innerText;
        navigator.clipboard.writeText(bookingRef).then(() => {
            alert('Booking reference copied to clipboard: ' + bookingRef);
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    }

    // Toggle booking details
    document.querySelector('.card-header-toggle').addEventListener('click', function() {
        const cardBody = this.nextElementSibling;
        const icon = this.querySelector('i');
        
        if (cardBody.style.display === 'none') {
            cardBody.style.display = 'block';
            icon.className = 'bi bi-chevron-down text-muted';
        } else {
            cardBody.style.display = 'none';
            icon.className = 'bi bi-chevron-up text-muted';
        }
    });
</script>

</body>
</html>