<?php
// hitpay-webhook-verifier.php real
define('HITPAY_SALT', 'rLW8UxWdQ64oWHif8geTeDl2GHo1GXVG1CfyWqCrP98fqdE469mfssHwRlhYBYfj');

// hitpay-webhook-verifier.php testing
 //define('HITPAY_SALT', '6GsyUkkmpgqewus5w4SbpzIuQLLpSvDmBk3zgMYpzG54QZ6xPkSOKpILdjg7qQuK');

function verifyHitPayWebhook($rawPostData, $signature) {
    // Compute HMAC SHA256
    $computedSignature = hash_hmac('sha256', $rawPostData, HITPAY_SALT);
    
    // Compare signatures
    return hash_equals($computedSignature, $signature);
}
?>