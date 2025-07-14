<?php
/**
 * Database Fix Script
 * 
 * This script fixes the timestamp column size issue
 * Run this script once to update your existing database
 */

require_once __DIR__ . '/classes/Database.php';

try {
    $db = new Database();
    
    echo "<h1>Database Fix Script</h1>\n";
    echo "<pre>\n";
    
    // Check current table structure
    echo "Checking current table structure...\n";
    $result = $db->fetchAll("DESCRIBE captions");
    
    foreach ($result as $row) {
        if ($row['Field'] === 'timestamp') {
            echo "Current timestamp column: " . $row['Type'] . "\n";
            
            if ($row['Type'] === 'varchar(14)') {
                echo "Fixing timestamp column size...\n";
                
                // Fix the timestamp column
                $db->query("ALTER TABLE captions MODIFY COLUMN timestamp VARCHAR(20) NOT NULL COMMENT 'Format: YYYYMMDD_HHMMSS extracted from image filename'");
                
                echo "✓ Timestamp column updated to VARCHAR(20)\n";
            } else {
                echo "✓ Timestamp column size is already correct: " . $row['Type'] . "\n";
            }
            break;
        }
    }
    
    // Test inserting a sample caption
    echo "\nTesting caption insertion...\n";
    
    try {
        require_once __DIR__ . '/classes/TimelineManager.php';
        $timeline = new TimelineManager();
        
        $testTimestamp = '20250714_193205';
        $testText = 'Test caption after database fix';
        
        $timeline->saveCaption($testTimestamp, $testText);
        echo "✓ Test caption saved successfully\n";
        
        // Retrieve the test caption
        $caption = $timeline->getCaption($testTimestamp);
        if ($caption && !empty($caption['text'])) {
            echo "✓ Test caption retrieved successfully: " . $caption['text'] . "\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Error testing caption: " . $e->getMessage() . "\n";
    }
    
    echo "\nDatabase fix completed!\n";
    echo "You can now use the API to add captions.\n";
    echo "</pre>\n";
    
} catch (Exception $e) {
    echo "<h1>Database Fix Error</h1>\n";
    echo "<pre style='color: red;'>\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nPlease check your database configuration in config/database.php\n";
    echo "</pre>\n";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Fix Complete</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <p><a href="index.php">← Back to Timeline</a> | <a href="test_api.php">Test API</a></p>
</body>
</html>