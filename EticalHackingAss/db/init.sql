-- schema.sql
-- Database initialization script for MyEduConnect
-- Secure remediation based on the OWASP Password Storage Cheat Sheet.
-- Plaintext seed passwords were removed and replaced with bcrypt hashes.

CREATE DATABASE IF NOT EXISTS `myeduconnect`;
USE `myeduconnect`;

-- 1. Create Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL, -- Bcrypt hashes only; see OWASP Password Storage Cheat Sheet
    `email` VARCHAR(100) NOT NULL,
    `role` VARCHAR(20) NOT NULL,
    `profile_pic` VARCHAR(255) DEFAULT NULL,
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
-- Remediated seed data: passwords are stored as bcrypt hashes only.
INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `bio`) VALUES
(1, 'admin_mmu', '$2y$10$8G5kHqFYL/MIiozCglseV.4ZgS6YWiNRLhch8Jip3as73EZ2jt7Pq', 'admin@mmu.edu.my', 'admin', 'System Administrator for MMU Portal.'),
(2, 'student01', '$2y$10$vhSeAvW7Y7fVLD1pxggigeFcjtz06LzH6lKlpVbYIqS8mCw0UBZru', 'student01@student.mmu.edu.my', 'student', 'First year Multimedia Engineering student. Looking forward to learning cyber security!');

-- Insert sample academic grade records for student01 (ID: 2)
INSERT INTO `grades` (`student_id`, `course_name`, `grade`) VALUES
(2, 'SWE2013 - Software Engineering', 'A-'),
(2, 'CYB3024 - Ethical Hacking & Pentesting', 'B+'),
(2, 'MAT1012 - Discrete Mathematics', 'A');

-- Least privilege account for the web app: no global admin rights, only schema-scoped DML.
CREATE USER IF NOT EXISTS 'myedu_app'@'%' IDENTIFIED BY 'Str0ngAppP@ssw0rd!2026#';
GRANT SELECT, INSERT, UPDATE, DELETE ON `myeduconnect`.* TO 'myedu_app'@'%';
FLUSH PRIVILEGES;
