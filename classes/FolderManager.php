<?php

/**
 * Folder Manager Class
 * 
 * Manages multiple image folders and provides folder discovery functionality
 */
class FolderManager
{
    private $baseImagePath;
    
    public function __construct($baseImagePath = 'images')
    {
        $this->baseImagePath = rtrim($baseImagePath, '/');
    }
    
    /**
     * Scan for all image folders in the base directory
     */
    public function getImageFolders()
    {
        $folders = [];
        $basePath = __DIR__ . '/../' . $this->baseImagePath;
        
        if (!is_dir($basePath)) {
            return $folders;
        }
        
        $directories = scandir($basePath);
        
        foreach ($directories as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            
            $fullPath = $basePath . '/' . $dir;
            
            if (is_dir($fullPath)) {
                $folderInfo = $this->getFolderInfo($dir, $fullPath);
                if ($folderInfo) {
                    $folders[] = $folderInfo;
                }
            }
        }
        
        // Sort folders by name
        usort($folders, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        return $folders;
    }
    
    /**
     * Get information about a specific folder
     */
    private function getFolderInfo($folderName, $folderPath)
    {
        $images = $this->getImagesInFolder($folderPath);
        $imageCount = count($images);
        
        if ($imageCount === 0) {
            return null; // Skip empty folders
        }
        
        // Get the latest image for thumbnail
        $latestImage = $this->getLatestImage($images);
        
        // Get folder statistics
        $firstImage = $this->getFirstImage($images);
        $totalSize = $this->calculateFolderSize($images);
        
        return [
            'name' => $folderName,
            'display_name' => $this->formatFolderName($folderName),
            'path' => $this->baseImagePath . '/' . $folderName,
            'image_count' => $imageCount,
            'latest_image' => $latestImage,
            'first_image' => $firstImage,
            'total_size' => $totalSize,
            'folder_stats' => $this->getFolderStats($images)
        ];
    }
    
    /**
     * Get all images in a folder
     */
    private function getImagesInFolder($folderPath)
    {
        $images = [];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!is_dir($folderPath)) {
            return $images;
        }
        
        $files = scandir($folderPath);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $filePath = $folderPath . '/' . $file;
            
            if (is_file($filePath)) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                
                if (in_array($extension, $allowedExtensions)) {
                    $images[] = [
                        'filename' => $file,
                        'path' => $filePath,
                        'relative_path' => $this->baseImagePath . '/' . basename($folderPath) . '/' . $file,
                        'size' => filesize($filePath),
                        'modified' => filemtime($filePath),
                        'timestamp' => $this->extractTimestampFromFilename($file)
                    ];
                }
            }
        }
        
        // Sort by timestamp or filename
        usort($images, function($a, $b) {
            if ($a['timestamp'] && $b['timestamp']) {
                return strcmp($b['timestamp'], $a['timestamp']); // Newest first
            }
            return strcmp($b['filename'], $a['filename']);
        });
        
        return $images;
    }
    
    /**
     * Extract timestamp from filename (same logic as TimelineManager)
     */
    private function extractTimestampFromFilename($filename)
    {
        // Remove extension first
        $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
        
        // Pattern: foldername_YYYYMMDD_HHMMSS (any folder name)
        if (preg_match('/\w+_(\d{8}_\d{6})/', $nameWithoutExt, $matches)) {
            return $matches[1];
        }
        
        // Pattern: just timestamp YYYYMMDD_HHMMSS
        if (preg_match('/^(\d{8}_\d{6})/', $nameWithoutExt, $matches)) {
            return $matches[1];
        }
        
        // Pattern: IMG_YYYYMMDD_HHMMSS or similar
        if (preg_match('/(\d{8}_\d{6})/', $nameWithoutExt, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Get the latest image in the folder
     */
    private function getLatestImage($images)
    {
        if (empty($images)) {
            return null;
        }
        
        return $images[0]; // Already sorted with newest first
    }
    
    /**
     * Get the first (oldest) image in the folder
     */
    private function getFirstImage($images)
    {
        if (empty($images)) {
            return null;
        }
        
        return end($images); // Last in sorted array is oldest
    }
    
    /**
     * Calculate total folder size
     */
    private function calculateFolderSize($images)
    {
        $totalSize = 0;
        
        foreach ($images as $image) {
            $totalSize += $image['size'];
        }
        
        return $totalSize;
    }
    
    /**
     * Get folder statistics
     */
    private function getFolderStats($images)
    {
        if (empty($images)) {
            return null;
        }
        
        $stats = [
            'total_images' => count($images),
            'date_range' => $this->getDateRange($images),
            'avg_size' => $this->calculateFolderSize($images) / count($images),
            'extensions' => $this->getFileExtensions($images)
        ];
        
        return $stats;
    }
    
    /**
     * Get date range from images
     */
    private function getDateRange($images)
    {
        $timestamps = array_filter(array_column($images, 'timestamp'));
        
        if (empty($timestamps)) {
            return null;
        }
        
        sort($timestamps);
        
        return [
            'first' => $this->formatTimestamp($timestamps[0]),
            'last' => $this->formatTimestamp(end($timestamps))
        ];
    }
    
    /**
     * Get file extensions in folder
     */
    private function getFileExtensions($images)
    {
        $extensions = [];
        
        foreach ($images as $image) {
            $ext = strtolower(pathinfo($image['filename'], PATHINFO_EXTENSION));
            $extensions[$ext] = ($extensions[$ext] ?? 0) + 1;
        }
        
        return $extensions;
    }
    
    /**
     * Format folder name for display
     */
    private function formatFolderName($folderName)
    {
        // Convert folder name to title case and replace underscores/hyphens
        return ucwords(str_replace(['_', '-'], ' ', $folderName));
    }
    
    /**
     * Format timestamp for display
     */
    private function formatTimestamp($timestamp)
    {
        if (!$timestamp || strlen($timestamp) !== 15) {
            return null;
        }
        
        $year = substr($timestamp, 0, 4);
        $month = substr($timestamp, 4, 2);
        $day = substr($timestamp, 6, 2);
        $hour = substr($timestamp, 9, 2);
        $minute = substr($timestamp, 11, 2);
        $second = substr($timestamp, 13, 2);
        
        return "$day/$month/$year $hour:$minute:$second";
    }
    
    /**
     * Format file size in human readable format
     */
    public function formatFileSize($bytes)
    {
        if ($bytes === 0) return '0 Bytes';
        
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        
        return round(($bytes / pow($k, $i)), 2) . ' ' . $sizes[$i];
    }
    
    /**
     * Check if a folder exists and is valid
     */
    public function folderExists($folderName)
    {
        $folderPath = __DIR__ . '/../' . $this->baseImagePath . '/' . $folderName;
        return is_dir($folderPath);
    }
    
    /**
     * Get folder path for timeline manager
     */
    public function getFolderPath($folderName)
    {
        return $this->baseImagePath . '/' . $folderName;
    }
}