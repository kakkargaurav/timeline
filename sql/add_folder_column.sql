-- Add folder column to captions table for multi-folder support
-- This allows captions to be stored per folder (driveway, front, garden, etc.)

-- Add the folder column
ALTER TABLE captions ADD COLUMN folder_name VARCHAR(100) NOT NULL DEFAULT 'driveway' AFTER id;

-- Create a composite index for better performance on folder + timestamp queries
DROP INDEX IF EXISTS idx_timestamp;
CREATE INDEX idx_folder_timestamp ON captions (folder_name, timestamp);

-- Update existing records to have 'driveway' as folder name (backward compatibility)
UPDATE captions SET folder_name = 'driveway' WHERE folder_name = 'driveway';

-- Add a unique constraint on folder_name + timestamp combination
ALTER TABLE captions ADD CONSTRAINT unique_folder_timestamp UNIQUE (folder_name, timestamp);

-- Show the updated table structure
DESCRIBE captions;