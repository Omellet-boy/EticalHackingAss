-- schema.sql
-- Database initialization script for MyEduConnect

CREATE DATABASE IF NOT EXISTS `myeduconnect`;
USE `myeduconnect`;

-- 1. Create Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL, -- Stored weakly as plaintext/MD5 for educational demo
    `email` VARCHAR(100) NOT NULL,
    `role` VARCHAR(20) NOT NULL,
    `bio` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Create Grades Table (for API and data verification components)
CREATE TABLE IF NOT EXISTS `grades` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `course_name` VARCHAR(100) NOT NULL,
    `grade` VARCHAR(5) NOT NULL,
    FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Create Feedback Table with user_id reference
CREATE TABLE IF NOT EXISTS `feedback` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `name` VARCHAR(100),
    `message` TEXT,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Insert Seed Data
-- Plaintext password hashes (or weak MD5 hashes) can be used to show weak password storage.
-- Here we insert plaintext as requested.
INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `bio`) VALUES
(1, 'admin_mmu', 'SuperSecureAdmin2026!', 'admin@mmu.edu.my', 'admin', 'System Administrator for MMU Portal.'),
(2, 'student01', 'studentpass', 'student01@student.mmu.edu.my', 'student', 'First year Multimedia Engineering student. Looking forward to learning cyber security!');

-- Insert sample academic grade records for student01 (ID: 2)
INSERT INTO `grades` (`student_id`, `course_name`, `grade`) VALUES
(2, 'SWE2013 - Software Engineering', 'A-'),
(2, 'CYB3024 - Ethical Hacking & Pentesting', 'B+'),
(2, 'MAT1012 - Discrete Mathematics', 'A');
