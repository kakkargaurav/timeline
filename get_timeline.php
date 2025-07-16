<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/classes/TimelineManager.php';

try {
    $timeline = new TimelineManager();
    
    // Check for date filters
    $filters = [];
    if (isset($_GET['date']) && !empty($_GET['date'])) {
        $filters['date'] = $_GET['date'];
    }
    if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
        $filters['date_from'] = $_GET['date_from'];
    }
    if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
        $filters['date_to'] = $_GET['date_to'];
    }
    
    $timelineData = $timeline->getTimelineData($filters);
    
    echo json_encode([
        'success' => true,
        'data' => $timelineData,
        'count' => count($timelineData),
        'filters' => $filters
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}