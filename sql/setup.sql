-- Timeline Application Database Setup
-- Run this script on your existing MySQL database

-- Create database if it doesn't exist (optional)
CREATE DATABASE IF NOT EXISTS timeline_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE timeline_db;

-- Create captions table for storing image captions with timestamps
CREATE TABLE IF NOT EXISTS captions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    timestamp VARCHAR(20) NOT NULL COMMENT 'Format: YYYYMMDD_HHMMSS extracted from image filename',
    text TEXT NOT NULL COMMENT 'Caption text for the image',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_timestamp (timestamp),
    INDEX idx_timestamp (timestamp),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data for testing (optional)
INSERT IGNORE INTO captions (timestamp, text) VALUES 
('20250714_193205', 'Sample driveway image from evening'),
('20250714_120000', 'Midday driveway check'),
('20250713_080000', 'Morning driveway view');

-- Create indexes for better performance
ALTER TABLE captions ADD INDEX idx_timestamp_created (timestamp, created_at);

-- Show table structure
DESCRIBE captions;

-- Display sample data
SELECT * FROM captions ORDER BY timestamp DESC LIMIT 5;