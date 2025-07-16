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
    <title>Driveway Timeline</title>
    <link rel="stylesheet" href="css/timeline.css">
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📷</text></svg>">
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <header class="header">
            <div class="header-content">
                <div class="header-title">
                    <h1>📷 Driveway Timeline</h1>
                    <p>Visual timeline of driveway images with timestamps and captions</p>
                </div>
                <div class="user-info">
                    <span class="welcome-text">Welcome, <?php echo htmlspecialchars(Auth::getUsername()); ?>!</span>
                    <a href="logout.php" class="logout-btn">🚪 Logout</a>
                </div>
            </div>
            
            <!-- Statistics -->
            <div id="stats" class="stats">
                <div class="stat-item">
                    <span class="stat-number">-</span>
                    <span class="stat-label">Loading...</span>
                </div>
            </div>
            
            <!-- Search Interface -->
            <div class="search-container">
                <div class="search-form">
                    <div class="search-suggestions">
                        <input type="text" id="search-input" class="search-input" placeholder="Search captions..." autocomplete="off">
                        <div id="suggestions-dropdown" class="suggestions-dropdown"></div>
                    </div>
                    <button id="search-btn" class="search-btn">
                        🔍 Search
                    </button>
                    <button id="clear-search-btn" class="clear-btn">
                        Clear
                    </button>
                </div>
                
                <!-- Date Filter Controls -->
                <div class="date-filter-controls">
                    <div class="date-filter-group">
                        <label for="date-filter" class="date-label">📅 Filter by Date:</label>
                        <select id="date-filter" class="date-select">
                            <option value="">All Dates</option>
                        </select>
                    </div>
                    <div class="date-range-group">
                        <label for="date-from" class="date-label">From:</label>
                        <input type="date" id="date-from" class="date-input">
                        <label for="date-to" class="date-label">To:</label>
                        <input type="date" id="date-to" class="date-input">
                        <button id="apply-date-range" class="date-btn">Apply Range</button>
                    </div>
                </div>
                
                <div class="search-options">
                    <label class="search-checkbox">
                        <input type="checkbox" id="exact-match">
                        Exact match
                    </label>
                    <label class="search-checkbox">
                        <input type="checkbox" id="case-sensitive">
                        Case sensitive
                    </label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="actions" style="margin: 20px 0;">
                <button id="refresh-btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); padding: 10px 20px; border-radius: 6px; cursor: pointer; font-size: 14px;">
                    🔄 Refresh Timeline
                </button>
            </div>
        </header>
        
        <!-- Timeline Container -->
        <main class="timeline-container">
            <!-- Search Results Header -->
            <div id="search-results-header" class="search-results-header" style="display: none;">
                <!-- Will be populated by JavaScript -->
            </div>
            
            <div id="timeline" class="timeline">
                <!-- Timeline items will be dynamically loaded here -->
                <div class="loading">
                    <div class="loading-spinner"></div>
                    <p>Loading timeline...</p>
                </div>
            </div>
        </main>
        
        <!-- Footer -->
        <footer style="text-align: center; margin-top: 40px; padding: 20px; color: rgba(255,255,255,0.7);">
            <p>Built with ❤️ for driveway monitoring</p>
        </footer>
    </div>
    
    <!-- JavaScript -->
    <script src="js/timeline.js"></script>
    
    <!-- Additional JavaScript for enhanced functionality -->
    <script>
        // Enhanced error handling
        window.addEventListener('unhandledrejection', function(event) {
            console.error('Unhandled promise rejection:', event.reason);
            
            // Show user-friendly error if timeline app is available
            if (window.timelineApp) {
                timelineApp.showError('An unexpected error occurred. Please refresh the page.');
            }
        });
        
        // Service worker registration for better performance (optional)
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                // Uncomment to enable service worker
                // navigator.serviceWorker.register('/sw.js').then(function(registration) {
                //     console.log('SW registered: ', registration);
                // }).catch(function(registrationError) {
                //     console.log('SW registration failed: ', registrationError);
                // });
            });
        }
        
        // Keyboard shortcuts help
        document.addEventListener('keydown', function(e) {
            if (e.key === 'h' && (e.ctrlKey || e.metaKey)) {
                e.preventDefault();
                alert(`Keyboard Shortcuts:
                
Ctrl/Cmd + R - Refresh timeline
Ctrl/Cmd + H - Show this help
                
API Endpoints:
• POST /api/captions.php - Add caption
• GET /api/captions.php - Get captions
• DELETE /api/captions.php - Delete caption`);
            }
        });
    </script>
    
    <!-- PWA Meta Tags (for mobile app-like experience) -->
    <meta name="theme-color" content="#667eea">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Driveway Timeline">
</body>
</html>