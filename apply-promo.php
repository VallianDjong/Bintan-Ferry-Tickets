<?php
// apply-promo.php

session_start();

// Store original referrer
$referrer = $_SERVER['HTTP_REFERER'] ?? 'booking-checking.php';

// Check if promo code was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promo_code'])) {
    $promoCode = trim($_POST['promo_code']);
    
    // Clear previous promo messages
    unset($_SESSION['promo_success'], $_SESSION['promo_error'], $_SESSION['promo_code']);
    
    if (empty($promoCode)) {
        // Remove promo if empty code submitted
        unset($_SESSION['promo_discount']);
        $_SESSION['promo_success'] = "Promo code removed.";
    } else {
        // Validate and apply promo code with trip type context
        $tripType = $_SESSION['search_params']['trip_type'] ?? 'round_trip';
        
        // Get route information from session
        $selectedDeparture = $_SESSION['selected_departure'] ?? [];
        $selectedReturn = $_SESSION['selected_return'] ?? null;
        $searchResults = $_SESSION['search_results'] ?? [];
        
        // Get departure and return trip details
        $departTrips = $searchResults['departTrips'] ?? [];
        $returnTrips = $searchResults['returnTrips'] ?? [];
        
        // Find selected departure details
        $departureDetails = null;
        foreach ($departTrips as $trip) {
            if ($trip['id'] == ($selectedDeparture['trip_id'] ?? '')) {
                $departureDetails = $trip;
                break;
            }
        }
        
        // Find selected return details (if round trip)
        $returnDetails = null;
        if ($selectedReturn && !empty($returnTrips)) {
            foreach ($returnTrips as $trip) {
                if ($trip['id'] == ($selectedReturn['trip_id'] ?? '')) {
                    $returnDetails = $trip;
                    break;
                }
            }
        }
        
        // Get route information
        $departOrigin = $departureDetails['portOriginName'] ?? '';
        $departDestination = $departureDetails['portDestinationName'] ?? '';
        $returnOrigin = $returnDetails['portOriginName'] ?? '';
        $returnDestination = $returnDetails['portDestinationName'] ?? '';
        
        $promoResult = validatePromoCode($promoCode, $tripType, $departOrigin, $departDestination, $returnOrigin, $returnDestination);
        
        if ($promoResult['valid']) {
            $_SESSION['promo_code'] = $promoCode;
            $_SESSION['promo_discount'] = $promoResult['discount'];
            $_SESSION['promo_type'] = $promoResult['type'];
            $_SESSION['promo_message'] = $promoResult['message'];
            $_SESSION['promo_success'] = $promoResult['message'];
        } else {
            $_SESSION['promo_error'] = $promoResult['message'];
        }
    }
}

// Redirect back to the booking page
header("Location: $referrer");
exit;

function validatePromoCode($code, $tripType = 'round_trip', $departOrigin = '', $departDestination = '', $returnOrigin = '', $returnDestination = '') {
    // Normalize the promo code
    $code = strtoupper(trim($code));
    
    // Helper function to check if Gold Coast is in the route
    function isGoldCoastRoute($origin, $destination) {
        $goldCoastTerms = ['GOLD COAST', 'GOLDCOAST', 'GC'];
        $originUpper = strtoupper($origin);
        $destinationUpper = strtoupper($destination);
        
        foreach ($goldCoastTerms as $term) {
            if (strpos($originUpper, $term) !== false || strpos($destinationUpper, $term) !== false) {
                return true;
            }
        }
        return false;
    }
    
    // Define valid promo codes with their discounts
    $validPromoCodes = [
        'VTLTRAVEL' => [
            'discount' => 0.10, // 10% discount
            'type' => 'percentage',
            'message' => '10% discount applied!',
            'allowed_trip_types' => ['round_trip', 'one_way', 'open_trip'], // All trip types
            'route_required' => null // No route restriction
        ],
        'VTLTRAVELTEST' => [
            'discount' => 0.02, // 2% discount
            'type' => 'percentage',
            'message' => '2% discount applied!',
            'allowed_trip_types' => ['round_trip', 'one_way', 'open_trip'],
            'route_required' => null
        ],
        'SAVE20' => [
            'discount' => 0.20, // 20% discount
            'type' => 'percentage',
            'message' => '20% discount applied!',
            'allowed_trip_types' => ['round_trip', 'one_way', 'open_trip'],
            'route_required' => null
        ],
        'FLAT50' => [
            'discount' => 50.00, // $50 flat discount
            'type' => 'fixed',
            'message' => '$50 discount applied!',
            'allowed_trip_types' => ['round_trip', 'one_way', 'open_trip'],
            'route_required' => null
        ],
        'SINDO5' => [
            'discount' => 0.05, // 5% discount
            'type' => 'percentage',
            'message' => '5% discount applied!',
            'allowed_trip_types' => ['round_trip', 'one_way', 'open_trip'],
            'route_required' => null
        ],
        'ONEWAY' => [
            'discount' => 2.00, // $2 fixed discount for one-way trips
            'type' => 'fixed',
            'message' => '$2 discount applied for one-way trip!',
            'allowed_trip_types' => ['one_way'], // ONLY one-way trips
            'route_required' => null
        ],
        'GOLDCOAST' => [
            'discount' => 6.00, // $6 fixed discount
            'type' => 'fixed',
            'message' => '$6 discount applied for Gold Coast route!',
            'allowed_trip_types' => ['round_trip', 'one_way', 'open_trip'], // All trip types
            'route_required' => 'gold_coast' // Special route requirement
        ]
    ];
    
    // Check if code exists
    if (isset($validPromoCodes[$code])) {
        $promoData = $validPromoCodes[$code];
        
        // Check if this promo code has route restrictions
        if (isset($promoData['route_required']) && $promoData['route_required'] === 'gold_coast') {
            // Check if either departure or return route includes Gold Coast
            $departureIsGoldCoast = isGoldCoastRoute($departOrigin, $departDestination);
            $returnIsGoldCoast = isGoldCoastRoute($returnOrigin, $returnDestination);
            
            if (!$departureIsGoldCoast && !$returnIsGoldCoast) {
                return [
                    'valid' => false,
                    'message' => 'The GOLDCOAST promo code is only valid for trips to/from Gold Coast. Please select a Gold Coast route to use this $6 discount.'
                ];
            }
        }
        
        // Check if this promo code is allowed for the current trip type
        if (!in_array($tripType, $promoData['allowed_trip_types'])) {
            // Special message for ONEWAY code when used on non-one-way trips
            if ($code === 'ONEWAY') {
                return [
                    'valid' => false,
                    'message' => 'The ONEWAY promo code is only valid for one-way trips ($2 off). Please change your trip type to one-way to use this code.'
                ];
            }
            
            return [
                'valid' => false,
                'message' => 'This promo code is not valid for ' . str_replace('_', ' ', $tripType) . ' trips.'
            ];
        }
        
        return [
            'valid' => true,
            'discount' => $promoData['discount'],
            'type' => $promoData['type'],
            'message' => $promoData['message']
        ];
    }
    
    // Check for test/expired codes
    $expiredCodes = ['TEST2023', 'OLDCODE', 'EXPIRED'];
    if (in_array($code, $expiredCodes)) {
        return [
            'valid' => false,
            'message' => 'This promo code has expired.'
        ];
    }
    
    return [
        'valid' => false,
        'message' => 'Invalid promo code. Please try again.'
    ];
}
?>