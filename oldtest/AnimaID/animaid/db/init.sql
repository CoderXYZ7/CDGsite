-- Create database structure from your previous design
CREATE DATABASE IF NOT EXISTS animaid CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE animaid;

-- Users table
CREATE TABLE Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(50) UNIQUE NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    PasswordHash VARCHAR(255) NOT NULL,
    CreationDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    LastLogin DATETIME NULL,
    Status ENUM('active', 'suspended', 'banned') NOT NULL DEFAULT 'active',
    RankID INT NOT NULL
);

-- ActivationCodes table
CREATE TABLE ActivationCodes (
    CodeID INT AUTO_INCREMENT PRIMARY KEY,
    Code VARCHAR(32) UNIQUE NOT NULL,
    CreatedBy INT NOT NULL,
    CreationDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ExpirationDate DATETIME NOT NULL,
    UsedBy INT NULL,
    UsedDate DATETIME NULL
);

-- Tags table
CREATE TABLE Tags (
    TagID INT AUTO_INCREMENT PRIMARY KEY,
    TagName VARCHAR(50) UNIQUE NOT NULL,
    TagDescription TEXT NULL
);

-- UserTags junction table
CREATE TABLE UserTags (
    UserID INT NOT NULL,
    TagID INT NOT NULL,
    PRIMARY KEY (UserID, TagID)
);

-- Ranks table
CREATE TABLE Ranks (
    RankID INT AUTO_INCREMENT PRIMARY KEY,
    RankName VARCHAR(50) UNIQUE NOT NULL,
    RankLevel INT UNIQUE NOT NULL,
    RankDescription TEXT NULL,
    Permissions JSON NOT NULL
);

-- Sessions table
CREATE TABLE Sessions (
    SessionID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    Token VARCHAR(255) UNIQUE NOT NULL,
    CreationDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ExpirationDate DATETIME NOT NULL,
    LastActivity DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    IP VARCHAR(45) NOT NULL,
    UserAgent TEXT NULL
);

-- AuthConnections table
CREATE TABLE AuthConnections (
    ConnectionID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    SystemID VARCHAR(50) NOT NULL,
    ExternalID VARCHAR(255) NOT NULL,
    ConnectionDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    LastUsed DATETIME NULL
);

-- Add foreign keys
ALTER TABLE Users ADD FOREIGN KEY (RankID) REFERENCES Ranks(RankID);
ALTER TABLE ActivationCodes ADD FOREIGN KEY (CreatedBy) REFERENCES Users(UserID);
ALTER TABLE ActivationCodes ADD FOREIGN KEY (UsedBy) REFERENCES Users(UserID);
ALTER TABLE UserTags ADD FOREIGN KEY (UserID) REFERENCES Users(UserID);
ALTER TABLE UserTags ADD FOREIGN KEY (TagID) REFERENCES Tags(TagID);
ALTER TABLE Sessions ADD FOREIGN KEY (UserID) REFERENCES Users(UserID);
ALTER TABLE AuthConnections ADD FOREIGN KEY (UserID) REFERENCES Users(UserID);
ALTER TABLE AuthConnections ADD UNIQUE KEY (UserID, SystemID);

-- Insert default ranks
INSERT INTO Ranks (RankName, RankLevel, RankDescription, Permissions) VALUES
('User', 1, 'Regular user', '{"self_edit": true}'),
('Moderator', 2, 'Content moderator', '{"moderate_content": true, "manage_tags": true}'),
('Administrator', 3, 'System administrator', '{"manage_users": true, "manage_codes": true, "manage_ranks": true, "manage_tags": true}');

-- Create initial admin user (password: Admin123!)
INSERT INTO Users (Username, Email, PasswordHash, RankID) VALUES
('admin', 'admin@animaid.example', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3);