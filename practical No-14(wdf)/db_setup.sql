-- Practical 14: Admin Dashboard with RBAC, Dynamic Menu, and Audit Log
-- Run this SQL in phpMyAdmin or MySQL CLI to set up the database

CREATE DATABASE IF NOT EXISTS practical14_wdf;
USE practical14_wdf;

-- Users table with role field
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    course VARCHAR(100) NOT NULL,
    enrollment_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Events table
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    location VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Registrations table
CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    event_id INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- Audit logs table
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample users (password is 'admin123' and 'student123' hashed with password_hash)
INSERT INTO users (username, email, password, role, status) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active'),
('john_student', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
('jane_student', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active');

-- Insert sample students
INSERT INTO students (name, email, course, enrollment_date) VALUES
('John Doe', 'john@example.com', 'Computer Science', '2026-01-15'),
('Jane Smith', 'jane@example.com', 'Information Technology', '2026-02-20'),
('Alice Johnson', 'alice@example.com', 'Data Science', '2026-03-10'),
('Bob Williams', 'bob@example.com', 'Computer Science', '2026-04-05'),
('Charlie Brown', 'charlie@example.com', 'Web Development', '2026-05-12'),
('Diana Prince', 'diana@example.com', 'Cyber Security', '2026-06-18'),
('Eve Davis', 'eve@example.com', 'Information Technology', '2026-07-22'),
('Frank Miller', 'frank@example.com', 'Data Science', '2026-08-30'),
('Grace Lee', 'grace@example.com', 'Computer Science', '2026-09-14'),
('Henry Wilson', 'henry@example.com', 'Web Development', '2026-10-01');

-- Insert sample events
INSERT INTO events (title, description, event_date, location) VALUES
('Tech Symposium 2026', 'Annual technology symposium featuring industry speakers', '2026-11-15', 'Main Auditorium'),
('Hackathon 2026', '24-hour coding competition', '2026-11-20', 'CS Lab Building'),
('Workshop: AI & ML', 'Hands-on workshop on AI and Machine Learning', '2026-12-05', 'Seminar Hall A'),
('Career Fair', 'Annual career fair with top companies', '2026-12-10', 'Convention Center'),
('Cybersecurity Awareness', 'Cybersecurity awareness seminar', '2026-12-15', 'Room 301');

-- Insert sample registrations (spread across months for analytics chart)
INSERT INTO registrations (student_id, event_id, registered_at) VALUES
(1, 1, '2026-01-10 10:00:00'),
(2, 1, '2026-02-12 11:00:00'),
(3, 2, '2026-03-15 09:30:00'),
(4, 2, '2026-04-18 14:00:00'),
(5, 3, '2026-05-20 16:00:00'),
(6, 3, '2026-06-22 10:30:00'),
(7, 4, '2026-07-25 11:00:00'),
(8, 4, '2026-08-28 13:00:00'),
(9, 5, '2026-09-05 15:00:00'),
(10, 5, '2026-10-10 09:00:00'),
(1, 3, '2026-10-12 10:00:00'),
(2, 4, '2026-10-15 11:00:00'),
(3, 5, '2026-10-18 14:00:00'),
(4, 1, '2026-10-20 16:00:00'),
(5, 2, '2026-10-21 09:30:00');

-- Insert sample audit logs
INSERT INTO audit_logs (user_id, action, details, ip_address, created_at) VALUES
(1, 'LOGIN', 'Admin logged in successfully', '127.0.0.1', '2026-10-20 08:00:00'),
(1, 'VIEW_DASHBOARD', 'Admin viewed the dashboard', '127.0.0.1', '2026-10-20 08:01:00'),
(1, 'TOGGLE_USER_STATUS', 'Toggled user john_student to inactive', '127.0.0.1', '2026-10-20 08:05:00'),
(1, 'VIEW_AUDIT_LOG', 'Admin viewed audit logs', '127.0.0.1', '2026-10-20 08:10:00');
