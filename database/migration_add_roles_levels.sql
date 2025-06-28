-- Migration script to add member roles and program levels
-- Run this script to update existing databases

USE church_management_system;

-- Add member_role column with default value
ALTER TABLE members 
ADD COLUMN member_role ENUM('Member', 'Executive') DEFAULT 'Member' AFTER program_of_study;

-- Add program_level column
ALTER TABLE members 
ADD COLUMN program_level VARCHAR(50) AFTER member_role;

-- Add indexes for better performance
ALTER TABLE members 
ADD INDEX idx_role (member_role),
ADD INDEX idx_program_level (program_level);

-- Update existing sample data with roles and levels (optional)
UPDATE members SET 
    member_role = 'Executive',
    program_level = 'Freshman'
WHERE id = 1;

UPDATE members SET 
    member_role = 'Member',
    program_level = 'Continuing (2)'
WHERE id = 2;

UPDATE members SET 
    member_role = 'Member',
    program_level = 'Continuing (3)'
WHERE id = 3;

UPDATE members SET 
    member_role = 'Executive',
    program_level = 'Final Year (3)'
WHERE id = 4;

UPDATE members SET 
    member_role = 'Member',
    program_level = 'Final Year (4)'
WHERE id = 5;

-- Verify the changes
SELECT id, first_name, last_name, member_role, program_level FROM members; 