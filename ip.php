<?php
echo "<h2>Server IP Information</h2>";

echo "<h3>Local IPs:</h3>";
echo "<pre>";
echo "SERVER_ADDR: " . ($_SERVER['SERVER_ADDR'] ?? 'Not set') . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'Not set') . "\n";
echo "REMOTE_ADDR: " . ($_SERVER['REMOTE_ADDR'] ?? 'Not set') . "\n";

// Get all local IPs
$hostname = gethostname();
$ip = gethostbyname($hostname);
echo "Hostname IP: " . $ip . "\n";

// Get all IPs associated with this server
echo "\nAll network interfaces:\n";
$interfaces = net_get_interfaces();
if ($interfaces) {
    foreach ($interfaces as $name => $interface) {
        if (isset($interface['unicast'][0]['address'])) {
            echo $name . ": " . $interface['unicast'][0]['address'] . "\n";
        }
    }
}
echo "</pre>";

echo "<h3>Public IP:</h3>";
echo "<pre>";
// Try multiple services in case one fails
$publicIPs = [];
$services = [
    'https://api.ipify.org',
    'https://ifconfig.me/ip',
    'https://icanhazip.com',
    'https://checkip.amazonaws.com'
];

foreach ($services as $service) {
    $ip = @file_get_contents($service);
    if ($ip && filter_var(trim($ip), FILTER_VALIDATE_IP)) {
        $publicIPs[] = trim($ip);
    }
}

if (!empty($publicIPs)) {
    echo "Public IP: " . $publicIPs[0] . "\n";
    if (count($publicIPs) > 1) {
        echo "Alternative: " . implode(", ", array_unique($publicIPs)) . "\n";
    }
} else {
    echo "Could not determine public IP automatically\n";
}
echo "</pre>";

// Test connection to BRF API
echo "<h3>Connection Test to BRF API:</h3>";
echo "<pre>";
$connection = @fsockopen("api.brf.com.sg", 443, $errno, $errstr, 5);
if ($connection) {
    echo "✅ Successfully connected to api.brf.com.sg:443\n";
    fclose($connection);
} else {
    echo "❌ Cannot connect to api.brf.com.sg:443\n";
    echo "Error: $errstr ($errno)\n";
    echo "\nThis explains why your API calls are timing out!\n";
    echo "Your server IP " . ($publicIPs[0] ?? 'unknown') . " may need to be whitelisted.\n";
}
echo "</pre>";
?>