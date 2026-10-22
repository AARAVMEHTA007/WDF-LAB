-- ============================================================
-- Practical 8: MySQL Schema Design for StudentHub
-- StudentHub Database - Students, Events, Registrations
-- ============================================================

CREATE DATABASE IF NOT EXISTS studenthub;
USE studenthub;

-- ------------------------------------------------------------
-- Table: students
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS students (
    student_id   INT AUTO_INCREMENT PRIMARY KEY,
    full_name    VARCHAR(100) NOT NULL,
    email        VARCHAR(100) NOT NULL UNIQUE,
    phone        VARCHAR(15),
    department   VARCHAR(50) NOT NULL,
    enrolled_on  DATE NOT NULL
);

-- ------------------------------------------------------------
-- Table: events
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS events (
    event_id     INT AUTO_INCREMENT PRIMARY KEY,
    event_name   VARCHAR(150) NOT NULL,
    event_date   DATE NOT NULL,
    venue        VARCHAR(100) NOT NULL,
    capacity     INT NOT NULL DEFAULT 50,
    description  TEXT
);

-- ------------------------------------------------------------
-- Table: registrations (junction table)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS registrations (
    reg_id       INT AUTO_INCREMENT PRIMARY KEY,
    student_id   INT NOT NULL,
    event_id     INT NOT NULL,
    reg_date     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status       ENUM('confirmed','pending','cancelled') DEFAULT 'pending',
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id)   REFERENCES events(event_id)   ON DELETE CASCADE,
    UNIQUE KEY uq_student_event (student_id, event_id)
);

-- ------------------------------------------------------------
-- Seed Data: students
-- ------------------------------------------------------------
INSERT INTO students (full_name, email, phone, department, enrolled_on) VALUES
('Aarav Mehta',   'aarav.mehta@student.edu',   '9876543210', 'Computer Science', '2023-07-15'),
('Priya Sharma',  'priya.sharma@student.edu',  '9123456780', 'Information Tech', '2023-07-15'),
('Rohan Verma',   'rohan.verma@student.edu',   '9988776655', 'Electronics',      '2022-07-20'),
('Sneha Patil',   'sneha.patil@student.edu',   '9001122334', 'Computer Science', '2024-07-10'),
('Karan Joshi',   'karan.joshi@student.edu',   '9765432109', 'Mechanical',       '2023-07-15');

-- ------------------------------------------------------------
-- Seed Data: events
-- ------------------------------------------------------------
INSERT INTO events (event_name, event_date, venue, capacity, description) VALUES
('Tech Fest 2024',        '2024-11-15', 'Main Auditorium', 200, 'Annual technical festival with competitions and workshops.'),
('Web Dev Workshop',      '2024-10-20', 'Lab Block A',      40, 'Hands-on workshop covering HTML, CSS, JS and PHP.'),
('Cultural Night',        '2024-12-05', 'Open Air Theatre', 500,'Annual cultural event with music, dance, and drama.'),
('Hackathon 2024',        '2024-11-01', 'Innovation Hub',   80, '24-hour coding challenge for all students.'),
('Career Fair',           '2024-10-28', 'College Ground',  300, 'Interaction with top recruiters and placement drive.');

-- ------------------------------------------------------------
-- Seed Data: registrations
-- ------------------------------------------------------------
INSERT INTO registrations (student_id, event_id, status) VALUES
(1, 1, 'confirmed'),
(1, 2, 'confirmed'),
(2, 1, 'pending'),
(2, 4, 'confirmed'),
(3, 3, 'confirmed'),
(4, 2, 'confirmed'),
(4, 5, 'pending'),
(5, 4, 'cancelled'),
(5, 1, 'confirmed');
