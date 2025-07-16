<?php
require_once __DIR__ . '/classes/Auth.php';

// Protect this page but allow API access
Auth::protectWebPage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Date Filter Test - Timeline</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .result { margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 4px; white-space: pre-wrap; font-family: monospace; font-size: 14px; }
        input { padding: 8px; margin: 5px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📅 Date Filter Testing</h1>
        <p>Test the timeline date filtering functionality</p>

        <!-- Get Available Dates -->
        <div class="test-section">
            <h3>📋 Available Dates</h3>
            <button onclick="getAvailableDates()">Get Available Dates</button>
            <div id="dates-result" class="result" style="display: none;"></div>
        </div>

        <!-- Filter by Specific Date -->
        <div class="test-section">
            <h3>📅 Filter by Date</h3>
            <input type="date" id="filter-date" placeholder="Select date">
            <button onclick="filterByDate()">Filter Timeline</button>
            <div id="date-filter-result" class="result" style="display: none;"></div>
        </div>

        <!-- Filter by Date Range -->
        <div class="test-section">
            <h3>📆 Filter by Date Range</h3>
            <input type="date" id="range-from" placeholder="From date">
            <input type="date" id="range-to" placeholder="To date">
            <button onclick="filterByRange()">Filter by Range</button>
            <div id="range-filter-result" class="result" style="display: none;"></div>
        </div>

        <!-- Get All Timeline -->
        <div class="test-section">
            <h3>🔄 Full Timeline</h3>
            <button onclick="getFullTimeline()">Get All Timeline Data</button>
            <div id="full-timeline-result" class="result" style="display: none;"></div>
        </div>
    </div>

    <script>
        // Get available dates
        async function getAvailableDates() {
            const resultDiv = document.getElementById('dates-result');
            resultDiv.style.display = 'block';
            resultDiv.textContent = 'Loading available dates...';

            try {
                const response = await fetch('api/timeline.php?available_dates=1');
                const result = await response.json();
                resultDiv.textContent = JSON.stringify(result, null, 2);
            } catch (error) {
                resultDiv.textContent = 'Error: ' + error.message;
            }
        }

        // Filter by specific date
        async function filterByDate() {
            const date = document.getElementById('filter-date').value;
            const resultDiv = document.getElementById('date-filter-result');
            
            if (!date) {
                alert('Please select a date');
                return;
            }
            
            resultDiv.style.display = 'block';
            resultDiv.textContent = `Filtering by date: ${date}...`;

            try {
                const response = await fetch(`api/timeline.php?date=${encodeURIComponent(date)}`);
                const result = await response.json();
                resultDiv.textContent = JSON.stringify(result, null, 2);
            } catch (error) {
                resultDiv.textContent = 'Error: ' + error.message;
            }
        }

        // Filter by date range
        async function filterByRange() {
            const dateFrom = document.getElementById('range-from').value;
            const dateTo = document.getElementById('range-to').value;
            const resultDiv = document.getElementById('range-filter-result');
            
            if (!dateFrom && !dateTo) {
                alert('Please select at least one date');
                return;
            }
            
            resultDiv.style.display = 'block';
            resultDiv.textContent = `Filtering by range: ${dateFrom} to ${dateTo}...`;

            try {
                const params = new URLSearchParams();
                if (dateFrom) params.append('date_from', dateFrom);
                if (dateTo) params.append('date_to', dateTo);
                
                const response = await fetch(`api/timeline.php?${params}`);
                const result = await response.json();
                resultDiv.textContent = JSON.stringify(result, null, 2);
            } catch (error) {
                resultDiv.textContent = 'Error: ' + error.message;
            }
        }

        // Get full timeline
        async function getFullTimeline() {
            const resultDiv = document.getElementById('full-timeline-result');
            resultDiv.style.display = 'block';
            resultDiv.textContent = 'Loading full timeline...';

            try {
                const response = await fetch('api/timeline.php');
                const result = await response.json();
                resultDiv.textContent = JSON.stringify(result, null, 2);
            } catch (error) {
                resultDiv.textContent = 'Error: ' + error.message;
            }
        }
    </script>
</body>
</html>