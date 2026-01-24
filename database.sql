-- Database: soulutionhbz

CREATE DATABASE IF NOT EXISTS soulutionhbz;
USE soulutionhbz;

-- Table Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(50),
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    adresse VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table Messages
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT, -- Can be NULL for admin replies if not keyed to a specific admin user, or 0
    to_user_id INT, -- For private messages/replies
    user_name VARCHAR(100),
    message TEXT NOT NULL,
    type ENUM('user_msg', 'admin_reply') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert Admin (Password: 2004) - Using plain text as per implied requirement, but should be hashed in production
-- Using a dummy entry for admin user actions if needed, or handle in code
