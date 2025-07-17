<?php

require_once __DIR__ . '/Database.php';

/**
 * Timeline Manager Class
 * 
 * Handles image scanning, timestamp parsing, and timeline data processing
 */
class TimelineManager
{
    private $db;
    private $imagesPath;
    private $folderName;

    public function __construct($imagesPath = 'images/driveway')
    {
        $this->db = new Database();
        $this->imagesPath = $imagesPath;
        
        // Extract folder name from path for database queries
        $pathParts = explode('/', $this->imagesPath);
        $this->folderName = end($pathParts);
    }

    /**
     * Scan images folder and get all driveway images with timestamps
     */
    public function scanImages()
    {
        $images = [];
        $basePath = __DIR__ . '/../' . $this->imagesPath;
        
        // Create directory if it doesn't exist
        if (!is_dir($basePath)) {
            mkdir($basePath, 0755, true);
        }

        // Scan for image files - support multiple patterns
        $patterns = [
            $basePath . '/' . $this->folderName . '_*.*',  // folder_YYYYMMDD_HHMMSS.ext
            $basePath . '/*_*.*',                           // any_YYYYMMDD_HHMMSS.ext
            $basePath . '/*.*'                              // all image files
        ];
        
        $files = [];
        foreach ($patterns as $pattern) {
            $matchedFiles = glob($pattern);
            if ($matchedFiles) {
                $files = array_merge($files, $matchedFiles);
            }
        }
        
        // Remove duplicates and filter to only image files
        $files = array_unique($files);
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $files = array_filter($files, function($file) use ($imageExtensions) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            return in_array($ext, $imageExtensions) && is_file($file);
        });
        
        foreach ($files as $file) {
            $filename = basename($file);
            $timestamp = $this->extractTimestamp($filename);
            
            if ($timestamp) {
                $images[] = [
                    'filename' => $filename,
                    'filepath' => str_replace(__DIR__ . '/../', '', $file),
                    'timestamp' => $timestamp,
                    'datetime' => $this->parseTimestamp($timestamp),
                    'filesize' => filesize($file),
                    'extension' => pathinfo($file, PATHINFO_EXTENSION)
                ];
            }
        }

        // Sort by timestamp (newest first)
        usort($images, function($a, $b) {
            return strcmp($b['timestamp'], $a['timestamp']);
        });

        return $images;
    }

    /**
     * Extract timestamp from filename (format: driveway_YYYYMMDD_HHMMSS.ext)
     */
    private function extractTimestamp($filename)
    {
        // Pattern: foldername_20250714_193205 (any folder name)
        if (preg_match('/\w+_(\d{8}_\d{6})/', $filename, $matches)) {
            return $matches[1];
        }
        
        // Pattern: just timestamp 20250714_193205
        if (preg_match('/^(\d{8}_\d{6})/', $filename, $matches)) {
            return $matches[1];
        }
        
        // Pattern: IMG_20250714_193205 or similar
        if (preg_match('/(\d{8}_\d{6})/', $filename, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Parse timestamp string to readable datetime
     */
    private function parseTimestamp($timestamp)
    {
        if (preg_match('/(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2})/', $timestamp, $matches)) {
            $year = $matches[1];
            $month = $matches[2];
            $day = $matches[3];
            $hour = $matches[4];
            $minute = $matches[5];
            $second = $matches[6];
            
            return [
                'formatted' => "{$day}/{$month}/{$year} {$hour}:{$minute}:{$second}",
                'iso' => "{$year}-{$month}-{$day} {$hour}:{$minute}:{$second}",
                'timestamp' => mktime($hour, $minute, $second, $month, $day, $year)
            ];
        }
        return null;
    }

    /**
     * Get timeline data with images and captions
     */
    public function getTimelineData($filters = [])
    {
        $images = $this->scanImages();
        
        // Apply date filters if provided
        if (!empty($filters)) {
            $images = $this->filterImagesByDate($images, $filters);
        }
        
        $timeline = [];

        foreach ($images as $image) {
            $caption = $this->getCaption($image['timestamp']);
            
            $timeline[] = [
                'image' => $image,
                'caption' => $caption,
                'position' => count($timeline) % 2 === 0 ? 'left' : 'right' // Alternate positions
            ];
        }

        return $timeline;
    }

    /**
     * Filter images by date range
     */
    private function filterImagesByDate($images, $filters)
    {
        $filteredImages = [];
        
        foreach ($images as $image) {
            $imageDate = $this->extractDateFromTimestamp($image['timestamp']);
            
            // Check date filters
            if (isset($filters['date']) && !empty($filters['date'])) {
                if ($imageDate !== $filters['date']) {
                    continue;
                }
            }
            
            if (isset($filters['date_from']) && !empty($filters['date_from'])) {
                if ($imageDate < $filters['date_from']) {
                    continue;
                }
            }
            
            if (isset($filters['date_to']) && !empty($filters['date_to'])) {
                if ($imageDate > $filters['date_to']) {
                    continue;
                }
            }
            
            $filteredImages[] = $image;
        }
        
        return $filteredImages;
    }

    /**
     * Extract date (YYYY-MM-DD) from timestamp
     */
    private function extractDateFromTimestamp($timestamp)
    {
        if (preg_match('/(\d{4})(\d{2})(\d{2})_\d{6}/', $timestamp, $matches)) {
            return $matches[1] . '-' . $matches[2] . '-' . $matches[3];
        }
        return null;
    }

    /**
     * Get available dates in the timeline
     */
    public function getAvailableDates()
    {
        $images = $this->scanImages();
        $dates = [];
        
        foreach ($images as $image) {
            $date = $this->extractDateFromTimestamp($image['timestamp']);
            if ($date && !in_array($date, $dates)) {
                $dates[] = $date;
            }
        }
        
        // Sort dates in descending order (newest first)
        rsort($dates);
        
        return $dates;
    }

    /**
     * Get timeline data for a specific date
     */
    public function getTimelineDataByDate($date)
    {
        return $this->getTimelineData(['date' => $date]);
    }

    /**
     * Get timeline data for a date range
     */
    public function getTimelineDataByDateRange($dateFrom, $dateTo)
    {
        return $this->getTimelineData([
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
    }

    /**
     * Get caption for specific timestamp
     */
    public function getCaption($timestamp)
    {
        try {
            $result = $this->db->fetch(
                "SELECT * FROM captions WHERE timestamp = ? AND folder_name = ?",
                [$timestamp, $this->folderName]
            );
            return $result ?: ['text' => '', 'created_at' => null];
        } catch (Exception $e) {
            return ['text' => '', 'created_at' => null];
        }
    }

    /**
     * Get all captions
     */
    public function getAllCaptions()
    {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM captions WHERE folder_name = ? ORDER BY timestamp DESC",
                [$this->folderName]
            );
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Save or update caption
     */
    public function saveCaption($timestamp, $text)
    {
        try {
            // Check if caption exists for this folder
            $existing = $this->db->fetch(
                "SELECT id FROM captions WHERE timestamp = ? AND folder_name = ?",
                [$timestamp, $this->folderName]
            );

            if ($existing) {
                // Update existing caption
                $this->db->query(
                    "UPDATE captions SET text = ?, updated_at = CURRENT_TIMESTAMP WHERE timestamp = ? AND folder_name = ?",
                    [$text, $timestamp, $this->folderName]
                );
            } else {
                // Insert new caption with folder name
                $this->db->insert(
                    "INSERT INTO captions (timestamp, text, folder_name) VALUES (?, ?, ?)",
                    [$timestamp, $text, $this->folderName]
                );
            }
            
            return true;
        } catch (Exception $e) {
            throw new Exception("Failed to save caption: " . $e->getMessage());
        }
    }

    /**
     * Delete caption
     */
    public function deleteCaption($timestamp)
    {
        try {
            $rowCount = $this->db->rowCount(
                "DELETE FROM captions WHERE timestamp = ? AND folder_name = ?",
                [$timestamp, $this->folderName]
            );
            return $rowCount > 0;
        } catch (Exception $e) {
            throw new Exception("Failed to delete caption: " . $e->getMessage());
        }
    }

    /**
     * Search captions by text and return matching timeline data
     */
    public function searchCaptions($searchTerm, $options = [])
    {
        try {
            $searchTerm = trim($searchTerm);
            
            if (empty($searchTerm)) {
                return [];
            }
            
            // Default search options
            $options = array_merge([
                'exact_match' => false,
                'case_sensitive' => false,
                'limit' => 50,
                'offset' => 0
            ], $options);
            
            // Build search query
            if ($options['exact_match']) {
                $searchCondition = $options['case_sensitive'] ?
                    "text = ?" :
                    "LOWER(text) = LOWER(?)";
                $searchParams = [$searchTerm];
            } else {
                $searchCondition = $options['case_sensitive'] ?
                    "text LIKE ?" :
                    "LOWER(text) LIKE LOWER(?)";
                $searchParams = ['%' . $searchTerm . '%'];
            }
            
            $sql = "SELECT * FROM captions WHERE folder_name = ? AND {$searchCondition} ORDER BY timestamp DESC";
            array_unshift($searchParams, $this->folderName);
            
            if ($options['limit'] > 0) {
                $sql .= " LIMIT " . intval($options['limit']);
                if ($options['offset'] > 0) {
                    $sql .= " OFFSET " . intval($options['offset']);
                }
            }
            
            $matchingCaptions = $this->db->fetchAll($sql, $searchParams);
            
            // Get all images for matching
            $allImages = $this->scanImages();
            $imagesByTimestamp = [];
            foreach ($allImages as $image) {
                $imagesByTimestamp[$image['timestamp']] = $image;
            }
            
            // Build timeline data for matching captions
            $searchResults = [];
            foreach ($matchingCaptions as $index => $caption) {
                if (isset($imagesByTimestamp[$caption['timestamp']])) {
                    $searchResults[] = [
                        'image' => $imagesByTimestamp[$caption['timestamp']],
                        'caption' => $caption,
                        'position' => $index % 2 === 0 ? 'left' : 'right',
                        'match_relevance' => $this->calculateRelevance($searchTerm, $caption['text'], $options)
                    ];
                }
            }
            
            return $searchResults;
            
        } catch (Exception $e) {
            throw new Exception("Search failed: " . $e->getMessage());
        }
    }
    
    /**
     * Calculate search relevance score
     */
    private function calculateRelevance($searchTerm, $text, $options)
    {
        $searchTerm = $options['case_sensitive'] ? $searchTerm : strtolower($searchTerm);
        $text = $options['case_sensitive'] ? $text : strtolower($text);
        
        // Exact match gets highest score
        if ($text === $searchTerm) {
            return 100;
        }
        
        // Starts with search term
        if (strpos($text, $searchTerm) === 0) {
            return 90;
        }
        
        // Contains search term as whole word
        if (preg_match('/\b' . preg_quote($searchTerm, '/') . '\b/', $text)) {
            return 80;
        }
        
        // Contains search term anywhere
        if (strpos($text, $searchTerm) !== false) {
            return 70;
        }
        
        // Calculate based on word similarity
        $searchWords = explode(' ', $searchTerm);
        $textWords = explode(' ', $text);
        $matchingWords = 0;
        
        foreach ($searchWords as $searchWord) {
            foreach ($textWords as $textWord) {
                if (strpos($textWord, $searchWord) !== false) {
                    $matchingWords++;
                    break;
                }
            }
        }
        
        return ($matchingWords / count($searchWords)) * 60;
    }
    
    /**
     * Get search suggestions based on existing captions
     */
    public function getSearchSuggestions($searchTerm, $limit = 10)
    {
        try {
            $searchTerm = trim($searchTerm);
            
            if (empty($searchTerm)) {
                return [];
            }
            
            $sql = "SELECT DISTINCT text FROM captions
                    WHERE folder_name = ? AND LOWER(text) LIKE LOWER(?)
                    ORDER BY LENGTH(text), text
                    LIMIT ?";
            
            $results = $this->db->fetchAll($sql, [$this->folderName, '%' . $searchTerm . '%', $limit]);
            
            return array_map(function($row) {
                return $row['text'];
            }, $results);
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get popular search terms from captions
     */
    public function getPopularSearchTerms($limit = 20)
    {
        try {
            $sql = "SELECT text, COUNT(*) as frequency
                    FROM captions
                    WHERE folder_name = ? AND text != ''
                    GROUP BY text
                    ORDER BY frequency DESC, text
                    LIMIT ?";
            
            return $this->db->fetchAll($sql, [$this->folderName, $limit]);
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Advanced search with multiple criteria
     */
    public function advancedSearch($criteria)
    {
        try {
            $conditions = ["folder_name = ?"];
            $params = [$this->folderName];
            
            // Text search
            if (!empty($criteria['text'])) {
                $conditions[] = "LOWER(text) LIKE LOWER(?)";
                $params[] = '%' . $criteria['text'] . '%';
            }
            
            // Date range search
            if (!empty($criteria['date_from'])) {
                $conditions[] = "timestamp >= ?";
                $params[] = $criteria['date_from'];
            }
            
            if (!empty($criteria['date_to'])) {
                $conditions[] = "timestamp <= ?";
                $params[] = $criteria['date_to'];
            }
            
            // Minimum text length
            if (!empty($criteria['min_length'])) {
                $conditions[] = "LENGTH(text) >= ?";
                $params[] = intval($criteria['min_length']);
            }
            
            if (count($conditions) <= 1) {
                // Only folder condition, no actual search criteria
                return [];
            }
            
            $sql = "SELECT * FROM captions WHERE " . implode(' AND ', $conditions) . " ORDER BY timestamp DESC";
            
            if (!empty($criteria['limit'])) {
                $sql .= " LIMIT " . intval($criteria['limit']);
            }
            
            $matchingCaptions = $this->db->fetchAll($sql, $params);
            
            // Convert to timeline format
            $allImages = $this->scanImages();
            $imagesByTimestamp = [];
            foreach ($allImages as $image) {
                $imagesByTimestamp[$image['timestamp']] = $image;
            }
            
            $results = [];
            foreach ($matchingCaptions as $index => $caption) {
                if (isset($imagesByTimestamp[$caption['timestamp']])) {
                    $results[] = [
                        'image' => $imagesByTimestamp[$caption['timestamp']],
                        'caption' => $caption,
                        'position' => $index % 2 === 0 ? 'left' : 'right'
                    ];
                }
            }
            
            return $results;
            
        } catch (Exception $e) {
            throw new Exception("Advanced search failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get statistics
     */
    public function getStats()
    {
        $images = $this->scanImages();
        $captions = $this->getAllCaptions();
        
        return [
            'total_images' => count($images),
            'total_captions' => count($captions),
            'images_with_captions' => count(array_filter($images, function($img) {
                $caption = $this->getCaption($img['timestamp']);
                return !empty($caption['text']);
            })),
            'latest_image' => !empty($images) ? $images[0] : null,
            'oldest_image' => !empty($images) ? end($images) : null
        ];
    }
}