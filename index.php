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
            <p>Timeline Application • Images are read from <code>images/driveway/</code> folder</p>
            <p style="font-size: 0.9rem; margin-top: 10px;">
                Expected filename format: <code>driveway_YYYYMMDD_HHMMSS.ext</code>
            </p>
            
            <!-- API Usage Instructions -->
            <details style="margin-top: 20px; text-align: left; background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px;">
                <summary style="cursor: pointer; font-weight: bold; margin-bottom: 10px;">🔧 API Usage</summary>
                <div style="font-family: monospace; font-size: 0.85rem; line-height: 1.4;">
                    <p><strong>Caption Management:</strong></p>
                    <code>POST api/captions.php</code><br>
                    <code>{"timestamp": "20250714_193205", "text": "Your caption here"}</code>
                    
                    <p style="margin-top: 15px;"><strong>Get Caption:</strong></p>
                    <code>GET api/captions.php?timestamp=20250714_193205</code>
                    
                    <p style="margin-top: 15px;"><strong>Get All Captions:</strong></p>
                    <code>GET api/captions.php</code>
                    
                    <p style="margin-top: 15px;"><strong>Delete Caption:</strong></p>
                    <code>DELETE api/captions.php?timestamp=20250714_193205</code>
                    
                    <p style="margin-top: 15px;"><strong>Get Statistics:</strong></p>
                    <code>GET api/captions.php?stats=1</code>
                    
                    <p style="margin-top: 15px;"><strong>🔍 Search Functions:</strong></p>
                    <code>GET api/search.php?q=search_term</code>
                    
                    <p style="margin-top: 10px;"><strong>Search with Options:</strong></p>
                    <code>GET api/search.php?q=term&exact=true&case=true&limit=20</code>
                    
                    <p style="margin-top: 10px;"><strong>Get Search Suggestions:</strong></p>
                    <code>GET api/search.php?suggestions=1&q=partial_term</code>
                    
                    <p style="margin-top: 10px;"><strong>Advanced Search:</strong></p>
                    <code>POST api/search.php</code><br>
                    <code>{"advanced_search": true, "criteria": {"text": "term", "date_from": "20250701_000000"}}</code>
                </div>
            </details>
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