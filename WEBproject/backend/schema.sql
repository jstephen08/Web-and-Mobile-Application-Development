-- NSBM EventHub Database Schema
-- Import this via phpMyAdmin (http://localhost/phpmyadmin) or:
--   mysql -u root -p < schema.sql

CREATE DATABASE IF NOT EXISTS nsbm_eventhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nsbm_eventhub;

-- ---------------------------------------------------------------
-- Users (students + admins)
-- ---------------------------------------------------------------
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    student_id    VARCHAR(30)  NULL UNIQUE,
    name          VARCHAR(120) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('student','admin') NOT NULL DEFAULT 'student',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Events
-- ---------------------------------------------------------------
CREATE TABLE events (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    category    ENUM('Computing','Business','Sports','Cultural') NOT NULL,
    event_date  DATE NOT NULL,
    event_time  VARCHAR(60)  NOT NULL,
    venue       VARCHAR(150) NOT NULL,
    image       VARCHAR(255) DEFAULT 'default-event.png',
    description TEXT NOT NULL,
    created_by  INT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Announcements
-- ---------------------------------------------------------------
CREATE TABLE announcements (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    target      VARCHAR(100) DEFAULT 'All',
    message     TEXT NOT NULL,
    created_by  INT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Registrations (join table: student <-> event)
-- ---------------------------------------------------------------
CREATE TABLE registrations (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    event_id      INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_registration (user_id, event_id),
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Seed data
-- ---------------------------------------------------------------

-- Default admin login: admin@nsbm.ac.lk / admin123
-- Default student login: kamal@student.nsbm.ac.lk / student123
INSERT INTO users (student_id, name, email, password_hash, role) VALUES
(NULL,          'Admin Officer', 'admin@nsbm.ac.lk',           '$2y$10$RH0Sw8yBOKvJmXeXRZQb7.hqfSp8OyQyD1Y5Cq2yqx1E3gk1BLLj6', 'admin'),
('NSBM-10293',  'Kamal Perera',  'kamal@student.nsbm.ac.lk',   '$2y$10$k1oVQwq5wq8VjM6Q0P.APek7fZmT3ZbAqzQEwZ9jvGZ8Q7WQmYzKO', 'student'),
('NSBM-10488',  'Nimali Silva',  'nimali@student.nsbm.ac.lk',  '$2y$10$k1oVQwq5wq8VjM6Q0P.APek7fZmT3ZbAqzQEwZ9jvGZ8Q7WQmYzKO', 'student');
-- NOTE: the hashes above are placeholders. Run backend/hash_seed.php once (see README)
-- to print real bcrypt hashes for "admin123" / "student123" and UPDATE these rows,
-- since bcrypt hashes are salted differently every time they're generated.

INSERT INTO events (title, category, event_date, event_time, venue, image, description, created_by) VALUES
('HackNSBM 2026 - Annual Hackathon', 'Computing', '2026-09-15', '08:00 AM - 08:00 PM', 'Computing Faculty Auditorium', 'hackathon.jpg', '24-hour hackathon organized by the Computing Society to solve real-world problems.', 1),
('NSBM Business Summit 2026', 'Business', '2026-09-20', '09:30 AM - 03:30 PM', 'Main University Hall', 'business.jpg', 'Connect with industry leaders, entrepreneurs, and startup mentors.', 1),
('Inter-Faculty Cricket Championship', 'Sports', '2026-10-02', '08:00 AM - 05:00 PM', 'NSBM Green Grounds', 'cricket.jpg', 'Cheer for your faculty in the annual inter-faculty cricket showdown.', 1);

INSERT INTO announcements (title, target, message, created_by) VALUES
('Venue Changed for HackNSBM 2026', 'Computing', 'Please note that the opening ceremony of HackNSBM 2026 has been shifted to Lab 3.', 1),
('Registration Deadline Extended', 'Business', 'Deadlines for submitting business plan pitches have been extended by 3 days.', 1);

INSERT INTO registrations (user_id, event_id) VALUES
(2, 2); -- Kamal Perera registered for the Business Summit
