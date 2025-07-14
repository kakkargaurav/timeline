<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../classes/TimelineManager.php';

try {
    $timeline = new TimelineManager();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGet($timeline);
            break;
            
        case 'POST':
            handlePost($timeline);
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
 * Handle GET requests - Simple search and suggestions
 */
function handleGet($timeline)
{
    if (isset($_GET['suggestions'])) {
        // Get search suggestions
        $searchTerm = $_GET['q'] ?? '';
        $limit = intval($_GET['limit'] ?? 10);
        
        $suggestions = $timeline->getSearchSuggestions($searchTerm, $limit);
        echo json_encode([
            'success' => true,
            'data' => $suggestions,
            'count' => count($suggestions)
        ]);
        
    } elseif (isset($_GET['popular'])) {
        // Get popular search terms
        $limit = intval($_GET['limit'] ?? 20);
        
        $popular = $timeline->getPopularSearchTerms($limit);
        echo json_encode([
            'success' => true,
            'data' => $popular,
            'count' => count($popular)
        ]);
        
    } elseif (isset($_GET['q'])) {
        // Simple search
        $searchTerm = $_GET['q'];
        $options = [
            'exact_match' => isset($_GET['exact']) && $_GET['exact'] === 'true',
            'case_sensitive' => isset($_GET['case']) && $_GET['case'] === 'true',
            'limit' => intval($_GET['limit'] ?? 50),
            'offset' => intval($_GET['offset'] ?? 0)
        ];
        
        $results = $timeline->searchCaptions($searchTerm, $options);
        
        echo json_encode([
            'success' => true,
            'data' => $results,
            'count' => count($results),
            'search_term' => $searchTerm,
            'options' => $options
        ]);
        
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing search parameter "q"']);
    }
}

/**
 * Handle POST requests - Advanced search
 */
function handlePost($timeline)
{
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        return;
    }
    
    if (isset($input['advanced_search'])) {
        // Advanced search
        $criteria = $input['criteria'] ?? [];
        
        try {
            $results = $timeline->advancedSearch($criteria);
            
            echo json_encode([
                'success' => true,
                'data' => $results,
                'count' => count($results),
                'criteria' => $criteria
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Advanced search failed: ' . $e->getMessage()]);
        }
        
    } elseif (isset($input['search_term'])) {
        // Standard search with options
        $searchTerm = $input['search_term'];
        $options = $input['options'] ?? [];
        
        try {
            $results = $timeline->searchCaptions($searchTerm, $options);
            
            echo json_encode([
                'success' => true,
                'data' => $results,
                'count' => count($results),
                'search_term' => $searchTerm,
                'options' => $options
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Search failed: ' . $e->getMessage()]);
        }
        
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required search parameters']);
    }
}