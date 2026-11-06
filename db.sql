CREATE DATABASE IF NOT EXISTS art_gallery;
USE art_gallery;

CREATE TABLE IF NOT EXISTS paintings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_path VARCHAR(255) NOT NULL,
    original_price DECIMAL(10, 2) NOT NULL,
    promo_price DECIMAL(10, 2) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS paintings_translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    painting_id INT NOT NULL,
    language ENUM('ar', 'en', 'fr') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    FOREIGN KEY (painting_id) REFERENCES paintings(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);
