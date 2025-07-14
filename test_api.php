<?php
/**
 * API Testing Script
 * 
 * This script helps test the timeline API endpoints
 */

header('Content-Type: text/html; charset=UTF-8');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline API Tester</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .test-section h3 { margin-top: 0; color: #333; }
        .form-group { margin: 10px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .result { margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 4px; white-space: pre-wrap; font-family: monospace; font-size: 14px; }
        .success { border-left: 4px solid #28a745; }
        .error { border-left: 4px solid #dc3545; }
        .info { border-left: 4px solid #17a2b8; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Timeline API Tester</h1>
        <p>Use this page to test the timeline API endpoints without needing external tools.</p>

        <!-- Test Database Connection -->
        <div class="test-section">
            <h3>🔗 Test Database Connection</h3>
            <button onclick="testConnection()">Test Connection</button>
            <div id="connection-result" class="result" style="display: none;"></div>
        </div>

        <!-- Add Caption -->
        <div class="test-section">
            <h3>➕ Add Caption</h3>
            <div class="form-group">
                <label for="add-timestamp">Timestamp (YYYYMMDD_HHMMSS):</label>
                <input type="text" id="add-timestamp" placeholder="20250714_193205" value="20250714_193205">
            </div>
            <div class="form-group">
                <label for="add-text">Caption Text:</label>
                <textarea id="add-text" rows="3" placeholder="Enter caption text here...">Sample caption for testing</textarea>
            </div>
            <button onclick="addCaption()">Add Caption</button>
            <div id="add-result" class="result" style="display: none;"></div>
        </div>

        <!-- Get Caption -->
        <div class="test-section">
            <h3>🔍 Get Specific Caption</h3>
            <div class="form-group">
                <label for="get-timestamp">Timestamp:</label>
                <input type="text" id="get-timestamp" placeholder="20250714_193205" value="20250714_193205">
            </div>
            <button onclick="getCaption()">Get Caption</button>
            <div id="get-result" class="result" style="display: none;"></div>
        </div>

        <!-- Get All Captions -->
        <div class="test-section">
            <h3>📋 Get All Captions</h3>
            <button onclick="getAllCaptions()">Get All Captions</button>
            <div id="getall-result" class="result" style="display: none;"></div>
        </div>

        <!-- Get Statistics -->
        <div class="test-section">
            <h3>📊 Get Statistics</h3>
            <button onclick="getStats()">Get Statistics</button>
            <div id="stats-result" class="result" style="display: none;"></div>
        </div>

        <!-- Get Timeline Data -->
        <div class="test-section">
            <h3>⏰ Get Timeline Data</h3>
            <button onclick="getTimeline()">Get Timeline Data</button>
            <div id="timeline-result" class="result" style="display: none;"></div>
        </div>

        <!-- Search Captions -->
        <div class="test-section">
            <h3>🔍 Search Captions</h3>
            <div class="form-group">
                <label for="search-term">Search Term:</label>
                <input type="text" id="search-term" placeholder="Enter search term..." value="test">
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" id="search-exact"> Exact Match
                </label>
                <label style="margin-left: 15px;">
                    <input type="checkbox" id="search-case"> Case Sensitive
                </label>
            </div>
            <button onclick="searchCaptions()">Search Captions</button>
            <div id="search-result" class="result" style="display: none;"></div>
        </div>

        <!-- Get Search Suggestions -->
        <div class="test-section">
            <h3>💡 Search Suggestions</h3>
            <div class="form-group">
                <label for="suggestion-term">Partial Term:</label>
                <input type="text" id="suggestion-term" placeholder="Enter partial term..." value="te">
            </div>
            <button onclick="getSearchSuggestions()">Get Suggestions</button>
            <div id="suggestions-result" class="result" style="display: none;"></div>
        </div>

        <!-- Delete Caption -->
        <div class="test-section">
            <h3>🗑️ Delete Caption</h3>
            <div class="form-group">
                <label for="delete-timestamp">Timestamp:</label>
                <input type="text" id="delete-timestamp" placeholder="20250714_193205">
            </div>
            <button onclick="deleteCaption()" style="background: #dc3545;">Delete Caption</button>
            <div id="delete-result" class="result" style="display: none;"></div>
        </div>
    </div>

    <script>
        // Test database connection
        async function testConnection() {
            const resultDiv = document.getElementById('connection-result');
            resultDiv.style.display = 'block';
            resultDiv.textContent = 'Testing connection...';
            resultDiv.className = 'result info';

            try {
                // Try to get stats as a connection test
                const response = await fetch('api/captions.php?stats=1');
                const result = await response.json();
                
                if (result.success) {
                    resultDiv.textContent = 'Database connection successful!\n' + JSON.stringify(result, null, 2);
                    resultDiv.className = 'result success';
                } else {
                    resultDiv.textContent = 'Connection test failed:\n' + JSON.stringify(result, null, 2);
                    resultDiv.className = 'result error';
                }
            } catch (error) {
                resultDiv.textContent = 'Connection test error:\n' + error.message;
                resultDiv.className = 'result error';
            }
        }

        // Add caption
        async function addCaption() {
            const timestamp = document.getElementById('add-timestamp').value;
            const text = document.getElementById('add-text').value;
            const resultDiv = document.getElementById('add-result');
            
            resultDiv.style.display = 'block';
            resultDiv.textContent = 'Adding caption...';
            resultDiv.className = 'result info';

            try {
                const response = await fetch('api/captions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ timestamp, text })
                });
                
                const result = await response.json();
                resultDiv.textContent = JSON.stringify(result, null, 2);
                resultDiv.className = result.success ? 'result success' : 'result error';
            } catch (error) {
                resultDiv.textContent = 'Error: ' + error.message;
                resultDiv.className = 'result error';
            }
        }

        // Get specific caption
        async function getCaption() {
            const timestamp = document.getElementById('get-timestamp').value;
            const resultDiv = document.getElementById('get-result');
            
            resultDiv.style.display = 'block';
            resultDiv.textContent = 'Getting caption...';
            resultDiv.className = 'result info';

            try {
                const response = await fetch(`api/captions.php?timestamp=${encodeURIComponent(timestamp)}`);
                const result = await response.json();
                resultDiv.textContent = JSON.stringify(result, null, 2);
                resultDiv.className = result.success ? 'result success' : 'result error';
            } catch (error) {
                resultDiv.textContent = 'Error: ' + error.message;
                resultDiv.className = 'result error';
            }
        }

        // Get all captions
        async function getAllCaptions() {
            const resultDiv = document.getElementById('getall-result');
            
            resultDiv.style.display = 'block';
            resultDiv.textContent = 'Getting all captions...';
            resultDiv.className = 'result info';

            try {
                const response = await fetch('api/captions.php');
                const result = await response.json();
                resultDiv.textContent = JSON.stringify(result, null, 2);
                resultDiv.className = result.success ? 'result success' : 'result error';
            } catch (error) {
                resultDiv.textContent = 'Error: ' + error.message;
                resultDiv.className = 'result error';
            }
        }

        // Get statistics
        async function getStats() {
            const resultDiv = document.getElementById('stats-result');
            
            resultDiv.style.display = 'block';
            resultDiv.textContent = 'Getting statistics...';
            resultDiv.className = 'result info';

            try {
                const response = await fetch('api/captions.php?stats=1');
                const result = await response.json();
                resultDiv.textContent = JSON.stringify(result, null, 2);
                resultDiv.className = result.success ? 'result success' : 'result error';
            } catch (error) {
                resultDiv.textContent = 'Error: ' + error.message;
                resultDiv.className = 'result error';
            }
        }

        // Get timeline data
        async function getTimeline() {
            const resultDiv = document.getElementById('timeline-result');
            
            resultDiv.style.display = 'block';
            resultDiv.textContent = 'Getting timeline data...';
            resultDiv.className = 'result info';

            try {
                const response = await fetch('get_timeline.php');
                const result = await response.json();
                resultDiv.textContent = JSON.stringify(result, null, 2);
                resultDiv.className = result.success ? 'result success' : 'result error';
            } catch (error) {
                resultDiv.textContent = 'Error: ' + error.message;
                resultDiv.className = 'result error';
            }
        }

        // Delete caption
        async function deleteCaption() {
            const timestamp = document.getElementById('delete-timestamp').value;
            const resultDiv = document.getElementById('delete-result');
            
            if (!confirm('Are you sure you want to delete this caption?')) {
                return;
            }
            
            resultDiv.style.display = 'block';
            resultDiv.textContent = 'Deleting caption...';
            resultDiv.className = 'result info';

            try {
                const response = await fetch(`api/captions.php?timestamp=${encodeURIComponent(timestamp)}`, {
                    method: 'DELETE'
                });
                
                const result = await response.json();
                resultDiv.textContent = JSON.stringify(result, null, 2);

        // Search captions
        async function searchCaptions() {
            const searchTerm = document.getElementById('search-term').value;
            const exactMatch = document.getElementById('search-exact').checked;
            const caseSensitive = document.getElementById('search-case').checked;
            const resultDiv = document.getElementById('search-result');
            
            resultDiv.style.display = 'block';
            resultDiv.textContent = 'Searching captions...';
            resultDiv.className = 'result info';

            try {
                const params = new URLSearchParams({
                    q: searchTerm,
                    exact: exactMatch,
                    case: caseSensitive,
                    limit: 20
                });

                const response = await fetch(`api/search.php?${params}`);
                const result = await response.json();
                resultDiv.textContent = JSON.stringify(result, null, 2);
                resultDiv.className = result.success ? 'result success' : 'result error';
            } catch (error) {
                resultDiv.textContent = 'Error: ' + error.message;
                resultDiv.className = 'result error';
            }
        }

        // Get search suggestions
        async function getSearchSuggestions() {
            const searchTerm = document.getElementById('suggestion-term').value;
            const resultDiv = document.getElementById('suggestions-result');
            
            resultDiv.style.display = 'block';
            resultDiv.textContent = 'Getting suggestions...';
            resultDiv.className = 'result info';

            try {
                const response = await fetch(`api/search.php?suggestions=1&q=${encodeURIComponent(searchTerm)}&limit=10`);
                const result = await response.json();
                resultDiv.textContent = JSON.stringify(result, null, 2);
                resultDiv.className = result.success ? 'result success' : 'result error';
            } catch (error) {
                resultDiv.textContent = 'Error: ' + error.message;
                resultDiv.className = 'result error';
            }
        }
                resultDiv.className = result.success ? 'result success' : 'result error';
            } catch (error) {
                resultDiv.textContent = 'Error: ' + error.message;
                resultDiv.className = 'result error';
            }
        }
    </script>
</body>
</html>