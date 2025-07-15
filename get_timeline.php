<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/classes/TimelineManager.php';

try {
    $imagesPath = isset($_GET['folder']) ? $_GET['folder'] : null;

    if (!$imagesPath) {
        throw new Exception("No image folder specified.");
    }

    // Basic validation
    $imagesPath = 'images/' . basename($imagesPath);

    $timeline = new TimelineManager($imagesPath);
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