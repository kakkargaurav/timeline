<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../classes/TimelineManager.php';

try {
    $timeline = new TimelineManager();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['available_dates'])) {
            // Get available dates
            $dates = $timeline->getAvailableDates();
            echo json_encode([
                'success' => true,
                'data' => $dates,
                'count' => count($dates)
            ]);
            
        } elseif (isset($_GET['date'])) {
            // Get timeline for specific date
            $date = $_GET['date'];
            $timelineData = $timeline->getTimelineDataByDate($date);
            
            echo json_encode([
                'success' => true,
                'data' => $timelineData,
                'count' => count($timelineData),
                'filter' => ['date' => $date]
            ]);
            
        } elseif (isset($_GET['date_from']) || isset($_GET['date_to'])) {
            // Get timeline for date range
            $dateFrom = $_GET['date_from'] ?? null;
            $dateTo = $_GET['date_to'] ?? null;
            
            $filters = [];
            if ($dateFrom) $filters['date_from'] = $dateFrom;
            if ($dateTo) $filters['date_to'] = $dateTo;
            
            $timelineData = $timeline->getTimelineData($filters);
            
            echo json_encode([
                'success' => true,
                'data' => $timelineData,
                'count' => count($timelineData),
                'filter' => $filters
            ]);
            
        } else {
            // Get all timeline data (default)
            $timelineData = $timeline->getTimelineData();
            
            echo json_encode([
                'success' => true,
                'data' => $timelineData,
                'count' => count($timelineData)
            ]);
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}