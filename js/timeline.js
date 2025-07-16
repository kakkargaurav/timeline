/**
 * Timeline Application JavaScript
 * Handles API interactions and dynamic timeline updates with search functionality
 */

class TimelineApp {
    constructor() {
        this.apiBaseUrl = 'api/captions.php';
        this.searchApiUrl = 'api/search.php';
        this.timelineApiUrl = 'api/timeline.php';
        this.timelineData = [];
        this.searchResults = [];
        this.availableDates = [];
        this.isSearchMode = false;
        this.isDateFilterMode = false;
        this.currentDateFilter = null;
        this.searchTimeout = null;
        this.init();
    }

    /**
     * Initialize the application
     */
    async init() {
        try {
            await this.loadAvailableDates();
            await this.loadTimeline();
            await this.loadStats();
            this.setupEventListeners();
            this.setupDateFilterListeners();
        } catch (error) {
            this.showError('Failed to initialize application: ' + error.message);
        }
    }

    /**
     * Load timeline data from server
     */
    async loadTimeline() {
        try {
            this.showLoading();
            
            const response = await fetch('get_timeline.php');
            const result = await response.json();
            
            if (result.success) {
                this.timelineData = result.data;
                this.renderTimeline();
            } else {
                throw new Error(result.error || 'Failed to load timeline');
            }
        } catch (error) {
            this.showError('Failed to load timeline: ' + error.message);
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
     * Setup event listeners
     */
    setupEventListeners() {
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
                <p>No images were found in the driveway folder.</p>
                <p>Add some images with the format: driveway_YYYYMMDD_HHMMSS.jpg</p>
                <button onclick="timelineApp.loadTimeline()" style="margin-top: 20px; padding: 10px 20px; background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); border-radius: 6px; cursor: pointer;">
                    Refresh
                </button>
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
     * Load available dates for filtering
     */
    async loadAvailableDates() {
        try {
            const response = await fetch(`${this.timelineApiUrl}?available_dates=1`);
            const result = await response.json();
            
            if (result.success) {
                this.availableDates = result.data;
                this.populateDateSelect();
            }
        } catch (error) {
            console.warn('Failed to load available dates:', error);
        }
    }

    /**
     * Populate the date select dropdown
     */
    populateDateSelect() {
        const dateSelect = document.getElementById('date-filter');
        if (!dateSelect) return;
        
        // Clear existing options except "All Dates"
        dateSelect.innerHTML = '<option value="">All Dates</option>';
        
        // Add available dates
        this.availableDates.forEach(date => {
            const option = document.createElement('option');
            option.value = date;
            option.textContent = this.formatDateForDisplay(date);
            dateSelect.appendChild(option);
        });
    }

    /**
     * Format date for display (convert YYYY-MM-DD to readable format)
     */
    formatDateForDisplay(date) {
        try {
            const dateObj = new Date(date + 'T00:00:00');
            return dateObj.toLocaleDateString('en-AU', {
                weekday: 'short',
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        } catch (e) {
            return date;
        }
    }

    /**
     * Filter timeline by specific date
     */
    async filterByDate(date) {
        try {
            this.showLoading();
            this.isDateFilterMode = true;
            this.currentDateFilter = date;
            
            let url = this.timelineApiUrl;
            if (date) {
                url += `?date=${encodeURIComponent(date)}`;
            }
            
            const response = await fetch(url);
            const result = await response.json();
            
            if (result.success) {
                this.timelineData = result.data;
                this.renderTimeline();
                this.updateDateFilterIndicator(date);
            } else {
                throw new Error(result.error || 'Failed to filter timeline');
            }
        } catch (error) {
            this.showError('Failed to filter timeline: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Filter timeline by date range
     */
    async filterByDateRange(dateFrom, dateTo) {
        try {
            this.showLoading();
            this.isDateFilterMode = true;
            
            const params = new URLSearchParams();
            if (dateFrom) params.append('date_from', dateFrom);
            if (dateTo) params.append('date_to', dateTo);
            
            const response = await fetch(`${this.timelineApiUrl}?${params}`);
            const result = await response.json();
            
            if (result.success) {
                this.timelineData = result.data;
                this.renderTimeline();
                this.updateDateRangeIndicator(dateFrom, dateTo);
            } else {
                throw new Error(result.error || 'Failed to filter timeline');
            }
        } catch (error) {
            this.showError('Failed to filter timeline: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Update date filter indicator
     */
    updateDateFilterIndicator(date) {
        let indicator = document.getElementById('date-filter-indicator');
        
        if (date) {
            if (!indicator) {
                indicator = document.createElement('div');
                indicator.id = 'date-filter-indicator';
                indicator.className = 'date-filter-indicator';
                
                const searchContainer = document.querySelector('.search-container');
                searchContainer.appendChild(indicator);
            }
            
            indicator.innerHTML = `
                📅 Filtered by: ${this.formatDateForDisplay(date)}
                <button class="clear-date-filter" onclick="timelineApp.clearDateFilter()">✕</button>
            `;
        } else {
            if (indicator) {
                indicator.remove();
            }
        }
    }

    /**
     * Update date range filter indicator
     */
    updateDateRangeIndicator(dateFrom, dateTo) {
        let indicator = document.getElementById('date-filter-indicator');
        
        if (dateFrom || dateTo) {
            if (!indicator) {
                indicator = document.createElement('div');
                indicator.id = 'date-filter-indicator';
                indicator.className = 'date-filter-indicator';
                
                const searchContainer = document.querySelector('.search-container');
                searchContainer.appendChild(indicator);
            }
            
            let text = '📅 Date Range: ';
            if (dateFrom && dateTo) {
                text += `${this.formatDateForDisplay(dateFrom)} to ${this.formatDateForDisplay(dateTo)}`;
            } else if (dateFrom) {
                text += `From ${this.formatDateForDisplay(dateFrom)}`;
            } else if (dateTo) {
                text += `Until ${this.formatDateForDisplay(dateTo)}`;
            }
            
            indicator.innerHTML = `
                ${text}
                <button class="clear-date-filter" onclick="timelineApp.clearDateFilter()">✕</button>
            `;
        } else {
            if (indicator) {
                indicator.remove();
            }
        }
    }

    /**
     * Clear date filter and show all timeline
     */
    clearDateFilter() {
        this.isDateFilterMode = false;
        this.currentDateFilter = null;
        
        // Reset date controls
        const dateSelect = document.getElementById('date-filter');
        const dateFrom = document.getElementById('date-from');
        const dateTo = document.getElementById('date-to');
        
        if (dateSelect) dateSelect.value = '';
        if (dateFrom) dateFrom.value = '';
        if (dateTo) dateTo.value = '';
        
        // Remove indicator
        this.updateDateFilterIndicator(null);
        
        // Reload full timeline
        this.loadTimeline();
    }

    /**
     * Setup date filter event listeners
     */
    setupDateFilterListeners() {
        const dateSelect = document.getElementById('date-filter');
        const dateFrom = document.getElementById('date-from');
        const dateTo = document.getElementById('date-to');
        const applyRangeBtn = document.getElementById('apply-date-range');
        
        if (dateSelect) {
            dateSelect.addEventListener('change', (e) => {
                const selectedDate = e.target.value;
                if (selectedDate) {
                    // Clear date range inputs when using date select
                    if (dateFrom) dateFrom.value = '';
                    if (dateTo) dateTo.value = '';
                    this.filterByDate(selectedDate);
                } else {
                    this.clearDateFilter();
                }
            });
        }
        
        if (applyRangeBtn) {
            applyRangeBtn.addEventListener('click', () => {
                const fromDate = dateFrom ? dateFrom.value : '';
                const toDate = dateTo ? dateTo.value : '';
                
                if (fromDate || toDate) {
                    // Clear date select when using range
                    if (dateSelect) dateSelect.value = '';
                    this.filterByDateRange(fromDate, toDate);
                } else {
                    this.clearDateFilter();
                }
            });
        }
        
        // Also apply range on Enter key in date inputs
        if (dateFrom) {
            dateFrom.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    applyRangeBtn.click();
                }
            });
        }
        
        if (dateTo) {
            dateTo.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    applyRangeBtn.click();
                }
            });
        }
    }

    /**
     * Refresh timeline data
     */
    async refresh() {
        if (this.isSearchMode) {
            // If in search mode, just refresh the search
            const searchInput = document.getElementById('search-input');
            if (searchInput && searchInput.value.trim()) {
                this.performSearch();
            } else {
                this.clearSearch();
            }
        } else if (this.isDateFilterMode) {
            // If in date filter mode, refresh the current filter
            if (this.currentDateFilter) {
                this.filterByDate(this.currentDateFilter);
            } else {
                // Check if date range is active
                const dateFrom = document.getElementById('date-from');
                const dateTo = document.getElementById('date-to');
                if ((dateFrom && dateFrom.value) || (dateTo && dateTo.value)) {
                    this.filterByDateRange(dateFrom.value, dateTo.value);
                } else {
                    await this.loadTimeline();
                    await this.loadStats();
                }
            }
        } else {
            await this.loadTimeline();
            await this.loadStats();
        }
        
        // Always refresh available dates
        await this.loadAvailableDates();
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