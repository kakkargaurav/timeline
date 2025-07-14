<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/classes/TimelineManager.php';

try {
    $timeline = new TimelineManager();
    $timelineData = $timeline->getTimelineData();
    
    echo json_encode([
        'success' => true,
        'data' => $timelineData,
        'count' => count($timelineData)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}