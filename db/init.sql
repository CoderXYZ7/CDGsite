CREATE DATABASE tag_system;
USE tag_system;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    tag VARCHAR(50) DEFAULT 'user'
);

CREATE TABLE pages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    page_name VARCHAR(50) NOT NULL,
    path VARCHAR(255) NOT NULL,
    allowed_tags VARCHAR(255) NOT NULL
);

INSERT INTO pages (page_name, path, allowed_tags) VALUES
('Hub', 'adminHub.php', 'admin,user'),
('Foglietto', 'adminFog.php', 'admin,user'),
('Eventi', 'adminEve.php', 'admin,user'),
('Admin Panel', 'admin.php', 'admin');

CREATE USER 'tag_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON tag_system.* TO 'tag_user'@'localhost';