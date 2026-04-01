-- ============================================================
-- KaamKhoji - Job Portal Database Setup
-- Run this file in phpMyAdmin or MySQL CLI to set up the DB
-- ============================================================

-- Create and select the database
CREATE DATABASE IF NOT EXISTS kaamkhoji CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kaamkhoji;

-- ============================================================
-- USERS TABLE
-- Stores all users: job seekers, employers, and admins
-- Role: 'seeker', 'employer', 'admin'
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,           -- hashed with password_hash()
    role        ENUM('seeker','employer','admin') NOT NULL DEFAULT 'seeker',
    phone       VARCHAR(20),
    location    VARCHAR(100),
    bio         TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- JOBS TABLE
-- Job listings posted by employers
-- Status: 'active', 'closed'
-- ============================================================
CREATE TABLE IF NOT EXISTS jobs (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    employer_id  INT NOT NULL,                   -- references users.id
    title        VARCHAR(200) NOT NULL,
    company      VARCHAR(150) NOT NULL,
    location     VARCHAR(100) NOT NULL,
    type         ENUM('full-time','part-time','remote','contract','internship') NOT NULL DEFAULT 'full-time',
    salary       VARCHAR(100),                   -- e.g. "Rs. 50,000 - 80,000"
    description  TEXT NOT NULL,
    requirements TEXT,
    status       ENUM('active','closed') NOT NULL DEFAULT 'active',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- APPLICATIONS TABLE
-- Job applications submitted by seekers
-- Status: 'pending', 'reviewed', 'accepted', 'rejected'
-- ============================================================
CREATE TABLE IF NOT EXISTS applications (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    job_id       INT NOT NULL,                   -- references jobs.id
    seeker_id    INT NOT NULL,                   -- references users.id
    cover_letter TEXT,
    status       ENUM('pending','reviewed','accepted','rejected') NOT NULL DEFAULT 'pending',
    applied_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (seeker_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_application (job_id, seeker_id)  -- prevent duplicate applications
);

-- ============================================================
-- SAVED JOBS TABLE
-- Jobs bookmarked by seekers
-- ============================================================
CREATE TABLE IF NOT EXISTS saved_jobs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    seeker_id  INT NOT NULL,
    job_id     INT NOT NULL,
    saved_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seeker_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_saved (seeker_id, job_id)         -- prevent duplicate saves
);

-- ============================================================
-- SEED DATA - Default admin account
-- Password: admin123 (hashed below)
-- ============================================================
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@kaamkhoji.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Note: The hashed password above is for 'password' (Laravel default hash)
-- For 'admin123', run: echo password_hash('admin123', PASSWORD_DEFAULT); in PHP
-- and update this INSERT, OR just register via the signup page and manually set role='admin'

-- Sample employer
INSERT INTO users (name, email, password, role, location) VALUES
('Tech Corp HR', 'employer@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer', 'Kathmandu');

-- Sample seeker
INSERT INTO users (name, email, password, role, location) VALUES
('Ram Sharma', 'seeker@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seeker', 'Kathmandu');

-- Sample jobs (employer_id = 2 which is Tech Corp HR above)
INSERT INTO jobs (employer_id, title, company, location, type, salary, description, requirements) VALUES
(2, 'PHP Developer', 'Tech Corp', 'Kathmandu', 'full-time', 'Rs. 40,000 - 60,000',
 'We are looking for an experienced PHP developer to join our team. You will build and maintain web applications.',
 'PHP, MySQL, HTML, CSS, JavaScript. 1+ year experience.'),

(2, 'UI/UX Designer', 'Tech Corp', 'Remote', 'remote', 'Rs. 30,000 - 50,000',
 'Looking for a creative UI/UX designer to create beautiful user interfaces for our products.',
 'Figma, Adobe XD, basic HTML/CSS. Portfolio required.'),

(2, 'Backend Intern', 'Tech Corp', 'Lalitpur', 'internship', 'Rs. 10,000 - 15,000',
 'Internship opportunity for fresh graduates. Learn real-world backend development.',
 'Python or PHP basics. Currently enrolled in CS or IT program.');

-- ============================================================
-- DONE! Your database is ready.
-- Default demo password for all seeded accounts is: 'password'
-- Change passwords after first login.
-- ============================================================
