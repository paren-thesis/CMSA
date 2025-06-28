-- Church Management System Database Schema
-- Created for CMSA Project

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS church_management_system;
USE church_management_system;

-- Drop tables if they exist (for clean setup)
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS meetings;
DROP TABLE IF EXISTS members;
DROP TABLE IF EXISTS admins;

-- 1. Admins table for authentication
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Members table
CREATE TABLE members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    location VARCHAR(100) NOT NULL,
    program_of_study VARCHAR(100),
    program_level VARCHAR(50),
    member_role ENUM('Member', 'Executive') DEFAULT 'Member',
    active BOOLEAN DEFAULT 0,
    contact_number VARCHAR(20),
    email VARCHAR(100),
    date_of_birth DATE,
    join_date DATE DEFAULT (CURRENT_DATE),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (first_name, last_name),
    INDEX idx_location (location),
    INDEX idx_birthday (date_of_birth),
    INDEX idx_role (member_role),
    INDEX idx_program_level (program_level),
    INDEX idx_active (active)
);

-- 3. Meetings table
CREATE TABLE meetings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_date DATE NOT NULL,
    meeting_type VARCHAR(50) NOT NULL,
    topic VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_meeting_date (meeting_date),
    INDEX idx_meeting_type (meeting_type)
);

-- 4. Attendance table (junction table)
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    meeting_id INT NOT NULL,
    attended BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (member_id, meeting_id),
    INDEX idx_member_meeting (member_id, meeting_id)
);

-- Insert default admin user (password: admin123)
INSERT INTO admins (username, email, password_hash) VALUES 
('admin', 'admin@church.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample members for testing
INSERT INTO members (first_name, last_name, location, program_of_study, program_level, member_role, contact_number, email, date_of_birth) VALUES
('John', 'Doe', 'Downtown', 'Computer Science', 'Freshman', 'Executive', '+1234567890', 'john.doe@email.com', '1990-05-15'),
('Jane', 'Smith', 'Uptown', 'Business Administration', 'Continuing (2)', 'Member', '+1234567891', 'jane.smith@email.com', '1985-08-22'),
('Mike', 'Johnson', 'Westside', 'Engineering', 'Continuing (3)', 'Member', '+1234567892', 'mike.johnson@email.com', '1992-12-10'),
('Sarah', 'Williams', 'Eastside', 'Medicine', 'Final Year (3)', 'Executive', '+1234567893', 'sarah.williams@email.com', '1988-03-28'),
('David', 'Brown', 'Downtown', 'Law', 'Final Year (4)', 'Member', '+1234567894', 'david.brown@email.com', '1995-07-14');

-- Insert sample meetings
INSERT INTO meetings (meeting_date, meeting_type, topic) VALUES
(CURDATE(), 'Sunday Service', 'Weekly Worship Service'),
(DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'Sunday Service', 'Weekly Worship Service'),
(DATE_SUB(CURDATE(), INTERVAL 14 DAY), 'Sunday Service', 'Weekly Worship Service'),
(CURDATE(), 'Bible Study', 'Book of John Chapter 3'),
(DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'Bible Study', 'Book of John Chapter 2');

-- Insert sample attendance records
INSERT INTO attendance (member_id, meeting_id, attended) VALUES
(1, 1, 1), (2, 1, 1), (3, 1, 0), (4, 1, 1), (5, 1, 1),
(1, 2, 1), (2, 2, 0), (3, 2, 1), (4, 2, 1), (5, 2, 0),
(1, 3, 1), (2, 3, 1), (3, 3, 1), (4, 3, 0), (5, 3, 1),
(1, 4, 1), (2, 4, 1), (3, 4, 0), (4, 4, 1), (5, 4, 1),
(1, 5, 0), (2, 5, 1), (3, 5, 1), (4, 5, 1), (5, 5, 0); 