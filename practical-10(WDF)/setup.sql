-- ============================================================
-- Practical 10: Secure Login/Logout with Sessions
-- Run this in phpMyAdmin or: mysql -u root auth_demo < setup.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS auth_demo
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE auth_demo;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(60)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('admin','student') NOT NULL DEFAULT 'student',
    last_login    DATETIME     DEFAULT NULL,
    created_at    DATETIME     NOT NULL DEFAULT NOW()
) ENGINE=InnoDB;

-- Remember-me tokens (selector + hashed validator)
CREATE TABLE IF NOT EXISTS remember_tokens (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    selector   CHAR(24)     NOT NULL UNIQUE,
    token_hash CHAR(64)     NOT NULL,
    expires_at DATETIME     NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
