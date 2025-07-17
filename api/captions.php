<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../classes/TimelineManager.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            // Get folder parameter for GET requests
            $folder = $_GET['folder'] ?? 'driveway';
            $folder = preg_replace('/[^a-zA-Z0-9_-]/', '', $folder);
            $timeline = new TimelineManager('images/' . $folder);
            handleGet($timeline);
            break;
            
        case 'POST':
            handlePost();
            break;
            
        case 'PUT':
            handlePut();
            break;
            
        case 'DELETE':
            // Get folder parameter for DELETE requests
            $folder = $_GET['folder'] ?? 'driveway';
            $folder = preg_replace('/[^a-zA-Z0-9_-]/', '', $folder);
            $timeline = new TimelineManager('images/' . $folder);
            handleDelete($timeline);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Handle GET requests - Retrieve captions
 */
function handleGet($timeline)
{
    if (isset($_GET['timestamp'])) {
        // Get specific caption by timestamp
        $timestamp = $_GET['timestamp'];
        $caption = $timeline->getCaption($timestamp);
        
        if ($caption && !empty($caption['text'])) {
            echo json_encode(['success' => true, 'data' => $caption]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Caption not found']);
        }
    } elseif (isset($_GET['stats'])) {
        // Get statistics
        $stats = $timeline->getStats();
        echo json_encode(['success' => true, 'data' => $stats]);
    } else {
        // Get all captions
        $captions = $timeline->getAllCaptions();
        echo json_encode(['success' => true, 'data' => $captions]);
    }
}

/**
 * Handle POST requests - Create new caption
 */
function handlePost()
{
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['timestamp']) || !isset($input['text'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields: timestamp and text']);
        return;
    }
    
    $timestamp = trim($input['timestamp']);
    $text = trim($input['text']);
    
    // Get folder parameter from JSON body, default to 'driveway'
    $folder = $input['folder'] ?? 'driveway';
    $folder = preg_replace('/[^a-zA-Z0-9_-]/', '', $folder); // Sanitize folder name
    
    // Create timeline manager for the specified folder
    $timeline = new TimelineManager('images/' . $folder);
    
    // Validate timestamp format (YYYYMMDD_HHMMSS)
    if (!preg_match('/^\d{8}_\d{6}$/', $timestamp)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid timestamp format. Expected: YYYYMMDD_HHMMSS']);
        return;
    }
    
    try {
        $timeline->saveCaption($timestamp, $text);
        echo json_encode([
            'success' => true,
            'message' => 'Caption saved successfully',
            'data' => [
                'timestamp' => $timestamp,
                'text' => $text,
                'folder' => $folder
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save caption: ' . $e->getMessage()]);
    }
}

/**
 * Handle PUT requests - Update existing caption
 */
function handlePut()
{
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['timestamp']) || !isset($input['text'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields: timestamp and text']);
        return;
    }
    
    $timestamp = trim($input['timestamp']);
    $text = trim($input['text']);
    
    // Get folder parameter from JSON body, default to 'driveway'
    $folder = $input['folder'] ?? 'driveway';
    $folder = preg_replace('/[^a-zA-Z0-9_-]/', '', $folder); // Sanitize folder name
    
    // Create timeline manager for the specified folder
    $timeline = new TimelineManager('images/' . $folder);
    
    try {
        $timeline->saveCaption($timestamp, $text);
        echo json_encode([
            'success' => true,
            'message' => 'Caption updated successfully',
            'data' => [
                'timestamp' => $timestamp,
                'text' => $text,
                'folder' => $folder
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update caption: ' . $e->getMessage()]);
    }
}

/**
 * Handle DELETE requests - Remove caption
 */
function handleDelete($timeline)
{
    if (!isset($_GET['timestamp'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing timestamp parameter']);
        return;
    }
    
    $timestamp = $_GET['timestamp'];
    
    try {
        $deleted = $timeline->deleteCaption($timestamp);
        
        if ($deleted) {
            echo json_encode(['success' => true, 'message' => 'Caption deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Caption not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete caption: ' . $e->getMessage()]);
    }
}