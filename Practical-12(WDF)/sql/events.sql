-- ============================================
-- Practical 12: Event Management CRUD
-- Database: event_management
-- ============================================

CREATE DATABASE IF NOT EXISTS event_management;
USE event_management;

-- Events Table
DROP TABLE IF EXISTS `audit_log`;
DROP TABLE IF EXISTS `events`;

CREATE TABLE `events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `category` ENUM('Conference','Workshop','Seminar','Webinar','Meetup','Concert','Sports','Other') NOT NULL DEFAULT 'Other',
    `event_date` DATE NOT NULL,
    `venue` VARCHAR(255) NOT NULL,
    `status` ENUM('Open','Closed') NOT NULL DEFAULT 'Open',
    `poster_image` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Audit Log Table (Advanced Extension)
CREATE TABLE `audit_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_id` INT,
    `action` ENUM('CREATE','UPDATE','DELETE') NOT NULL,
    `old_values` JSON DEFAULT NULL,
    `new_values` JSON DEFAULT NULL,
    `changed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Sample Event Records with Optional Posters
-- ============================================

INSERT INTO `events` (`title`, `description`, `category`, `event_date`, `venue`, `status`, `poster_image`) VALUES
('Tech Innovation Summit 2026', 'Annual technology conference featuring keynote speakers from leading tech companies, hands-on workshops, and networking sessions.', 'Conference', '2026-11-15', 'Convention Center, Mumbai', 'Open', NULL),
('Web Development Bootcamp', 'Intensive 2-day workshop covering modern web development with React, Node.js, and cloud deployment.', 'Workshop', '2026-11-20', 'IT Hub, Pune', 'Open', NULL),
('AI & Machine Learning Seminar', 'Expert-led seminar on the latest trends in artificial intelligence and machine learning applications.', 'Seminar', '2026-12-05', 'University Auditorium, Delhi', 'Open', NULL),
('Music Fest 2026', 'A grand musical evening featuring indie bands and solo artists from across the country.', 'Concert', '2026-12-20', 'Open Air Theatre, Bangalore', 'Open', NULL),
('Startup Pitch Night', 'An evening where budding entrepreneurs pitch their startup ideas to a panel of investors.', 'Meetup', '2026-10-10', 'Co-Working Space, Hyderabad', 'Closed', NULL),
('Cloud Computing Webinar', 'Free online webinar covering AWS, Azure, and Google Cloud fundamentals for beginners.', 'Webinar', '2026-11-01', 'Online (Zoom)', 'Closed', NULL);
