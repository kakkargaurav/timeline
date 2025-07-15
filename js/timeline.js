/**
 * Timeline Application JavaScript
 * Handles API interactions and dynamic timeline updates with search functionality
 */

class TimelineApp {
    constructor() {
        this.apiBaseUrl = 'api/captions.php';
        this.searchApiUrl = 'api/search.php';
        this.timelineData = [];
        this.searchResults = [];
        this.isSearchMode = false;
        this.searchTimeout = null;
        this.currentFolder = null;
        this.init();
    }

    /**
     * Initialize the application
     */
    init() {
        this.setupEventListeners();
    }

    /**
     * Load timeline for a specific folder
     */
    async loadTimelineByFolder(folder) {
        this.currentFolder = folder;
        document.getElementById('folder-selection').style.display = 'none';
        document.getElementById('timeline-section').style.display = 'block';

        // Update header
        const headerTitle = document.querySelector('.header-title h1');
        if (headerTitle) {
            headerTitle.textContent = `📷 ${folder.charAt(0).toUpperCase() + folder.slice(1)} Timeline`;
        }

        await this.loadTimeline();
        await this.loadStats();
    }

    /**
     * Load timeline data from server
     */
    async loadTimeline() {
        if (!this.currentFolder) return;

        try {
            this.showLoading();
            
            const response = await fetch(`get_timeline.php?folder=${this.currentFolder}`);
            const result = await response.json();
            
            if (result.success) {
                this.timelineData = result.data;
                this.renderTimeline();
            } else {
                throw new Error(result.error || 'Failed to load timeline');
            }
        } catch (error) {
            this.showError(`Failed to load timeline for ${this.currentFolder}: ` + error.message);
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Load application statistics
     */
    async loadStats() {
        try {
            const response = await fetch(this.apiBaseUrl + '?stats=1');
            const result = await response.json();
            
            if (result.success) {
                this.renderStats(result.data);
            }
        } catch (error) {
            console.warn('Failed to load stats:', error);
        }
    }

    /**
     * Render timeline items
     */
    renderTimeline() {
        const timelineContainer = document.getElementById('timeline');
        
        if (!this.timelineData || this.timelineData.length === 0) {
            this.showEmptyState();
            return;
        }
        
        timelineContainer.innerHTML = '';
        
        this.timelineData.forEach((item, index) => {
            const timelineItem = this.createTimelineItem(item, index);
            timelineContainer.appendChild(timelineItem);
        });
        
        // Trigger animations
        setTimeout(() => {
            const items = document.querySelectorAll('.timeline-item');
            items.forEach((item, index) => {
                setTimeout(() => {
                    item.style.opacity = '1';
                }, index * 100);
            });
        }, 100);
    }

    /**
     * Create timeline item element
     */
    createTimelineItem(item, index) {
        const div = document.createElement('div');
        div.className = `timeline-item ${item.position}`;
        div.style.opacity = '0';
        
        const captionText = item.caption.text || 'No caption available';
        const isEmptyCaption = !item.caption.text;
        
        // Format file size
        const fileSize = this.formatFileSize(item.image.filesize);
        
        div.innerHTML = `
            <div class="timeline-content">
                <img src="${item.image.filepath}" alt="Driveway ${item.image.datetime.formatted}" class="timeline-image" loading="lazy">
                <div class="timeline-info">
                    <div class="timeline-timestamp">${item.image.datetime.formatted}</div>
                    <div class="timeline-caption ${isEmptyCaption ? 'no-caption' : ''}" data-timestamp="${item.image.timestamp}">
                        ${captionText}
                    </div>
                    <div class="file-info">
                        <span class="filename">${item.image.filename}</span>
                        <span class="file-size">${fileSize}</span>
                    </div>
                </div>
            </div>
            <div class="timeline-dot"></div>
        `;
        
        return div;
    }

    /**
     * Render application statistics
     */
    renderStats(stats) {
        const statsContainer = document.getElementById('stats');
        if (!statsContainer) return;
        
        statsContainer.innerHTML = `
            <div class="stat-item">
                <span class="stat-number">${stats.total_images}</span>
                <span class="stat-label">Total Images</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">${stats.total_captions}</span>
                <span class="stat-label">Captions</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">${stats.images_with_captions}</span>
                <span class="stat-label">With Captions</span>
            </div>
        `;
    }

    /**
     * Save caption via API
     */
    async saveCaption(timestamp, text) {
        try {
            const response = await fetch(this.apiBaseUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ timestamp, text })
            });
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.error || 'Failed to save caption');
            }
            
            return result;
        } catch (error) {
            throw new Error('Failed to save caption: ' + error.message);
        }
    }

    /**
     * Delete caption via API
     */
    async deleteCaption(timestamp) {
        try {
            const response = await fetch(this.apiBaseUrl + '?timestamp=' + encodeURIComponent(timestamp), {
                method: 'DELETE'
            });
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.error || 'Failed to delete caption');
            }
            
            return result;
        } catch (error) {
            throw new Error('Failed to delete caption: ' + error.message);
        }
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Folder links
        const folderLinks = document.querySelectorAll('.folder-link');
        folderLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const folder = link.dataset.folder;
                this.loadTimelineByFolder(folder);
            });
        });

        // Refresh button
        const refreshBtn = document.getElementById('refresh-btn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.refresh();
            });
        }
        
        // Setup search listeners
        this.setupSearchListeners();
        
        // Image lazy loading error handling
        document.addEventListener('error', (e) => {
            if (e.target.tagName === 'IMG') {
                e.target.style.display = 'none';
                const placeholder = document.createElement('div');
                placeholder.className = 'image-placeholder';
                placeholder.textContent = 'Image not found';
                placeholder.style.cssText = `
                    height: 300px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: #f5f5f5;
                    color: #999;
                    font-style: italic;
                `;
                e.target.parentNode.replaceChild(placeholder, e.target);
            }
        }, true);
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.key === 'r' && (e.ctrlKey || e.metaKey)) {
                e.preventDefault();
                this.refresh();
            }
        });
    }

    /**
     * Show loading state
     */
    showLoading() {
        const timelineContainer = document.getElementById('timeline');
        timelineContainer.innerHTML = `
            <div class="loading">
                <div class="loading-spinner"></div>
                <p>Loading timeline...</p>
            </div>
        `;
    }

    /**
     * Hide loading state
     */
    hideLoading() {
        const loading = document.querySelector('.loading');
        if (loading) {
            loading.remove();
        }
    }

    /**
     * Show error message
     */
    showError(message) {
        const timelineContainer = document.getElementById('timeline');
        timelineContainer.innerHTML = `
            <div class="error">
                <h3>Error</h3>
                <p>${message}</p>
                <button onclick="timelineApp.loadTimeline()" style="margin-top: 10px; padding: 8px 16px; background: #d32f2f; color: white; border: none; border-radius: 4px; cursor: pointer;">
                    Try Again
                </button>
                <a href="/" class="back-link">Select another timeline</a>
            </div>
        `;
    }

    /**
     * Show empty state
     */
    showEmptyState() {
        const timelineContainer = document.getElementById('timeline');
        timelineContainer.innerHTML = `
            <div class="empty-state">
                <h3>No Images Found</h3>
                <p>No images were found in the selected folder.</p>
                <p>Add some images with the format: driveway_YYYYMMDD_HHMMSS.jpg</p>
                <button onclick="timelineApp.loadTimeline()" style="margin-top: 20px; padding: 10px 20px; background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); border-radius: 6px; cursor: pointer;">
                    Refresh
                </button>
                 <a href="/" class="back-link">Select another timeline</a>
            </div>
        `;
    }

    /**
     * Format file size in human readable format
     */
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    /**
     * Format timestamp for display
     */
    formatTimestamp(timestamp) {
        const year = timestamp.substr(0, 4);
        const month = timestamp.substr(4, 2);
        const day = timestamp.substr(6, 2);
        const hour = timestamp.substr(9, 2);
        const minute = timestamp.substr(11, 2);
        const second = timestamp.substr(13, 2);
        
        return `${day}/${month}/${year} ${hour}:${minute}:${second}`;
    }

    /**
     * Search captions with specified term and options
     */
    async searchCaptions(searchTerm, options = {}) {
        try {
            if (!searchTerm.trim()) {
                this.clearSearch();
                return;
            }

            this.showSearchLoading();

            const params = new URLSearchParams({
                q: searchTerm,
                exact: options.exact_match || false,
                case: options.case_sensitive || false,
                limit: options.limit || 50,
                offset: options.offset || 0
            });

            const response = await fetch(`${this.searchApiUrl}?${params}`);
            const result = await response.json();

            if (result.success) {
                this.searchResults = result.data;
                this.isSearchMode = true;
                this.renderSearchResults(searchTerm);
                this.updateSearchResultsHeader(result.count, searchTerm);
            } else {
                throw new Error(result.error || 'Search failed');
            }

        } catch (error) {
            this.showError('Search failed: ' + error.message);
        } finally {
            this.hideSearchLoading();
        }
    }

    /**
     * Render search results
     */
    renderSearchResults(searchTerm) {
        const timelineContainer = document.getElementById('timeline');
        
        if (!this.searchResults || this.searchResults.length === 0) {
            this.showNoResults(searchTerm);
            return;
        }
        
        timelineContainer.innerHTML = '';
        
        this.searchResults.forEach((item, index) => {
            const timelineItem = this.createSearchResultItem(item, index, searchTerm);
            timelineContainer.appendChild(timelineItem);
        });
        
        // Trigger animations
        setTimeout(() => {
            const items = document.querySelectorAll('.timeline-item');
            items.forEach((item, index) => {
                setTimeout(() => {
                    item.style.opacity = '1';
                }, index * 100);
            });
        }, 100);
    }

    /**
     * Create search result timeline item
     */
    createSearchResultItem(item, index, searchTerm) {
        const div = document.createElement('div');
        div.className = `timeline-item ${item.position}`;
        div.style.opacity = '0';
        
        // Highlight search term in caption
        let captionText = item.caption.text || 'No caption available';
        if (searchTerm && searchTerm !== 'Advanced Search') {
            const regex = new RegExp(`(${searchTerm})`, 'gi');
            captionText = captionText.replace(regex, '<span class="search-term-highlight">$1</span>');
        }
        
        const isEmptyCaption = !item.caption.text;
        const fileSize = this.formatFileSize(item.image.filesize);
        const relevanceScore = item.match_relevance || 0;
        
        div.innerHTML = `
            <div class="timeline-content">
                <img src="${item.image.filepath}" alt="Driveway ${item.image.datetime.formatted}" class="timeline-image" loading="lazy">
                <div class="timeline-info">
                    <div class="timeline-timestamp">${item.image.datetime.formatted}</div>
                    <div class="timeline-caption ${isEmptyCaption ? 'no-caption' : ''}" data-timestamp="${item.image.timestamp}">
                        ${captionText}
                    </div>
                    <div class="file-info">
                        <span class="filename">${item.image.filename}</span>
                        <span class="file-size">${fileSize}</span>
                    </div>
                    ${relevanceScore > 0 ? `<div class="relevance-score">${Math.round(relevanceScore)}%</div>` : ''}
                </div>
            </div>
            <div class="timeline-dot"></div>
        `;
        
        return div;
    }

    /**
     * Update search results header
     */
    updateSearchResultsHeader(count, searchTerm) {
        const header = document.getElementById('search-results-header');
        if (header) {
            header.innerHTML = `
                <h3>Search Results</h3>
                <div class="search-results-count">
                    Found ${count} result${count !== 1 ? 's' : ''} for "${searchTerm}"
                    <button class="clear-btn" onclick="timelineApp.clearSearch()">Show All</button>
                </div>
            `;
            header.style.display = 'flex';
        }
    }

    /**
     * Clear search and show all timeline
     */
    clearSearch() {
        this.isSearchMode = false;
        this.searchResults = [];
        
        // Clear search input
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.value = '';
        }
        
        // Hide search results header
        const header = document.getElementById('search-results-header');
        if (header) {
            header.style.display = 'none';
        }
        
        // Show full timeline
        this.renderTimeline();
    }

    /**
     * Show search loading state
     */
    showSearchLoading() {
        const timelineContainer = document.getElementById('timeline');
        timelineContainer.innerHTML = `
            <div class="search-loading">
                <div class="search-spinner"></div>
                <p>Searching...</p>
            </div>
        `;
    }

    /**
     * Hide search loading state
     */
    hideSearchLoading() {
        const loading = document.querySelector('.search-loading');
        if (loading) {
            loading.remove();
        }
    }

    /**
     * Show no results message
     */
    showNoResults(searchTerm) {
        const timelineContainer = document.getElementById('timeline');
        timelineContainer.innerHTML = `
            <div class="no-results">
                <h3>No Results Found</h3>
                <p>No images found matching "${searchTerm}"</p>
                <button onclick="timelineApp.clearSearch()" class="clear-btn" style="margin-top: 15px;">
                    Show All Images
                </button>
            </div>
        `;
    }

    /**
     * Setup search event listeners
     */
    setupSearchListeners() {
        const searchInput = document.getElementById('search-input');
        const searchBtn = document.getElementById('search-btn');
        
        if (searchInput) {
            // Search on Enter key
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.performSearch();
                }
            });
        }
        
        if (searchBtn) {
            searchBtn.addEventListener('click', () => this.performSearch());
        }
    }

    /**
     * Perform search with current input
     */
    performSearch() {
        const searchInput = document.getElementById('search-input');
        const exactMatch = document.getElementById('exact-match');
        const caseSensitive = document.getElementById('case-sensitive');
        
        if (!searchInput) return;
        
        const searchTerm = searchInput.value.trim();
        const options = {
            exact_match: exactMatch ? exactMatch.checked : false,
            case_sensitive: caseSensitive ? caseSensitive.checked : false
        };
        
        this.searchCaptions(searchTerm, options);
    }

    /**
     * Refresh timeline data
     */
    async refresh() {
        if (this.isSearchMode) {
            const searchInput = document.getElementById('search-input');
            if (searchInput && searchInput.value.trim()) {
                this.performSearch();
            } else {
                this.clearSearch();
            }
        } else {
            await this.loadTimeline();
        }
        await this.loadStats();
    }
}

// Initialize application when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.timelineApp = new TimelineApp();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TimelineApp;
}