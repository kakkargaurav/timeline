-- Fix database constraints to allow same timestamp in different folders
-- Remove the old unique_timestamp constraint that prevents folder independence

-- Drop the old unique timestamp constraint
ALTER TABLE captions DROP INDEX unique_timestamp;

-- Also drop idx_timestamp_created if it exists (seems to be duplicate)
ALTER TABLE captions DROP INDEX IF EXISTS idx_timestamp_created;

-- Verify the unique_folder_timestamp constraint exists (this should allow same timestamp in different folders)
-- If it doesn't exist, create it
ALTER TABLE captions ADD CONSTRAINT unique_folder_timestamp UNIQUE (folder_name, timestamp);

-- Show final table structure
SHOW INDEX FROM captions;
DESCRIBE captions;