<?php
session_start();
require_once 'index.php'; // Include your main file to get the functions

header('Content-Type: application/json');

try {
    $date = $_GET['date'] ?? date('Y-m-d');
    
    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        throw new Exception('Invalid date format');
    }
    
    // Fetch all schedule data
    $allSchedules = getAllSchedules($date);
    
    // Calculate statistics
    $stats = [
        'sg_to_batam_routes' => count($allSchedules['sg_to_batam'] ?? []),
        'batam_to_sg_routes' => count($allSchedules['batam_to_sg'] ?? []),
        'sg_departures' => 0,
        'batam_departures' => 0,
        'total_trips' => 0
    ];
    
    foreach ($allSchedules['sg_to_batam'] ?? [] as $route) {
        $stats['sg_departures'] += count($route['times'] ?? []);
    }
    
    foreach ($allSchedules['batam_to_sg'] ?? [] as $route) {
        $stats['batam_departures'] += count($route['times'] ?? []);
    }
    
    $stats['total_trips'] = $stats['sg_departures'] + $stats['batam_departures'];
    
    echo json_encode([
        'success' => true,
        'date' => $date,
        'data' => $allSchedules,
        'stats' => $stats,
        'last_updated' => date('H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => time()
    ]);
}