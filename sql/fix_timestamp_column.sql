-- Fix timestamp column size issue
-- Run this script if you're getting "Data too long for column 'timestamp'" error

USE timeline_db;

-- Check current table structure
DESCRIBE captions;

-- Alter timestamp column to be larger to accommodate any format variations
ALTER TABLE captions MODIFY COLUMN timestamp VARCHAR(20) NOT NULL COMMENT 'Format: YYYYMMDD_HHMMSS extracted from image filename';

-- Show updated structure
DESCRIBE captions;

-- Test with sample data
INSERT IGNORE INTO captions (timestamp, text) VALUES 
('20250714_193205', 'Test caption after fix');

SELECT * FROM captions WHERE timestamp = '20250714_193205';