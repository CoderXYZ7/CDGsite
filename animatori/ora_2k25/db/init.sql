-- Create the database with proper character set
CREATE DATABASE IF NOT EXISTS ora_2k25 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ora_2k25;

-- Table for Laboratories
CREATE TABLE IF NOT EXISTS Laboratori (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Nome VARCHAR(100) NOT NULL,
    Descrizione TEXT
);

-- Table for Responsibles
CREATE TABLE IF NOT EXISTS Responsabili (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Nome VARCHAR(100) NOT NULL,
    Descrizione TEXT
);

-- Table for Animators (many-to-one with Laboratories)
CREATE TABLE IF NOT EXISTS Animatori (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Nome VARCHAR(100) NOT NULL,
    Cognome VARCHAR(100) NOT NULL,
    Laboratorio INT NOT NULL DEFAULT 0,
    Fascia ENUM('A', 'D') NOT NULL,
    Colore ENUM('B', 'R', 'G', 'A', 'X') NOT NULL DEFAULT 'X',
    M ENUM('M', 'X') NOT NULL DEFAULT 'X',
    J ENUM('J', 'X') NOT NULL DEFAULT 'X',
    S ENUM('S', 'X') NOT NULL DEFAULT 'X',
    FOREIGN KEY (Laboratorio) REFERENCES Laboratori(ID)
);

-- Junction table for Animators ↔ Responsibles (many-to-many)
CREATE TABLE IF NOT EXISTS Animatori_Responsabili (
    AnimatoreID INT,
    ResponsabileID INT,
    PRIMARY KEY (AnimatoreID, ResponsabileID),
    FOREIGN KEY (AnimatoreID) REFERENCES Animatori(ID),
    FOREIGN KEY (ResponsabileID) REFERENCES Responsabili(ID)
);

-- Insert Laboratories
INSERT INTO Laboratori (ID, Nome, Descrizione) VALUES
(13, 'Altro', 'Laboratorio di default'),
(1, '50  Sfumatore di bellezza', ''),
(2, 'Giochi da Tavolo', ''),
(3, 'Mosaico', ''),
(4, 'Sportiamo', ''),
(5, 'Stoffe delle Meraviglie', ''),
(6, 'Brico 1', ''),
(7, 'Brico 2', ''),
(8, 'Pittura su... con fantasia', ''),
(9, 'String art', ''),
(10, 'The Space Music', ''),
(11, 'Giornalismo', ''),
(12, 'Brico generale', '')
ON DUPLICATE KEY UPDATE Nome=VALUES(Nome), Descrizione=VALUES(Descrizione);

-- Insert Responsibles
INSERT INTO Responsabili (ID, Nome, Descrizione) VALUES
(1, 'Fascia D', ''),
(2, 'Animatoriadi', ''),
(3, 'Giochi', ''),
(4, 'Serate', ''),
(5, 'Oratori in Festa', '')
ON DUPLICATE KEY UPDATE Nome=VALUES(Nome), Descrizione=VALUES(Descrizione);

-- Insert Animators
INSERT INTO Animatori (ID, Nome, Cognome, Laboratorio, Fascia, Colore, M, J, S) VALUES
(1, 'Cloe', 'Montagna', 1, 'D', 'X', 'X', 'X', 'X'),
(2, 'Sofia', 'Todesco', 1, 'D', 'B', 'M', 'X', 'X'),
(3, 'Chiara', 'Grop', 1, 'A', 'X', 'X', 'X', 'X'),
(4, 'Laura', 'Stocco', 1, 'A', 'B', 'X', 'J', 'X'),
(5, 'Nicola', 'Miolo', 2, 'D', 'X', 'X', 'X', 'X'),
(6, 'Armando', 'Franceschinis', 2, 'D', 'X', 'X', 'X', 'X'),
(7, 'Sarah', 'Gregoricchi', 2, 'A', 'G', 'X', 'J', 'X'),
(8, 'Chiara', 'Pitaccolo', 2, 'A', 'G', 'X', 'J', 'X'),
(9, 'Noemy', 'Lizzano', 3, 'D', 'X', 'X', 'X', 'X'),
(10, 'Giorgia', 'Bogaro', 3, 'D', 'X', 'X', 'X', 'X'),
(11, 'Brian', 'Squazzin', 4, 'D', 'X', 'X', 'X', 'X'),
(12, 'Gabriele', 'Zemolin', 4, 'A', 'B', 'M', 'X', 'X'),
(13, 'Riccardo', 'Indri', 4, 'A', 'B', 'M', 'X', 'X'),
(14, 'Matteo', 'Nali', 4, 'A', 'B', 'X', 'J', 'X'),
(15, 'Riccardo', 'Toniolo', 4, 'A', 'X', 'X', 'X', 'X'),
(16, 'Giorgia', 'Fabris', 5, 'D', 'B', 'M', 'X', 'X'),
(17, 'Maria', 'Vicenzin', 5, 'D', 'X', 'X', 'X', 'X'),
(18, 'Miriam', 'Savorgnan', 5, 'A', 'A', 'M', 'X', 'X'),
(19, 'Dalila', 'Franceschinis', 8, 'A', 'B', 'M', 'X', 'X'),
(20, 'Alice', 'Pozzar', 8, 'A', 'B', 'M', 'X', 'X'),
(21, 'Christian', 'Mantovani', 8, 'A', 'R', 'X', 'X', 'S'),
(22, 'Martina', 'Pantanali', 9, 'A', 'B', 'X', 'J', 'X'),
(23, 'Alice', 'Ravidà', 9, 'A', 'G', 'X', 'J', 'X'),
(24, 'Luca', 'Fabris', 10, 'A', 'X', 'X', 'X', 'X'),
(25, 'Matteo', 'Lazzarini', 10, 'A', 'X', 'X', 'X', 'X'),
(26, 'Lucia', 'Codarin', 10, 'A', 'X', 'X', 'X', 'X'),
(27, 'Veronica', 'Carrara', 11, 'D', 'X', 'X', 'X', 'X'),
(28, 'Elisa', 'Candotti', 11, 'D', 'X', 'X', 'X', 'X'),
(29, 'Gioia', 'Biasutti', 11, 'D', 'X', 'X', 'X', 'X'),
(30, 'Emma', 'Zanon', 11, 'D', 'X', 'X', 'X', 'X'),
(31, 'Veronica', 'Targato', 11, 'D', 'X', 'X', 'X', 'X'),
(32, 'Daniele', 'Toniolo', 11, 'A', 'A', 'X', 'X', 'S'),
(33, 'Narcis', 'Nedelcu', 12, 'A', 'X', 'X', 'X', 'X'),
(34, 'Aurora', 'Cocetta', 12, 'A', 'R', 'M', 'X', 'X'),
(35, 'Fabio', 'Milan', 12, 'A', 'R', 'X', 'J', 'X'),
(36, 'Vincenzo', 'Nocereto', 13, 'A', 'X', 'X', 'X', 'X'),
(37, 'Andrea', 'Vicenzino', 13, 'A', 'G', 'M', 'X', 'X'),
(38, 'Marco', 'Mason', 13, 'A', 'X', 'X', 'X', 'X'),
(39, 'Fabio', 'Breda', 13, 'A', 'B', 'X', 'X', 'S'),
(40, 'Andres', 'Fattorutto', 13, 'A', 'G', 'M', 'X', 'X'),
(41, 'Riccardo', 'Pantanali', 13, 'A', 'G', 'M', 'X', 'X'),
(42, 'Sergei', 'X', 13, 'D', 'G', 'M', 'X', 'X'),
(43, 'Nicholas', 'Sinigaglia', 13, 'A', 'A', 'X', 'J', 'X')
ON DUPLICATE KEY UPDATE 
    Nome=VALUES(Nome), 
    Cognome=VALUES(Cognome), 
    Laboratorio=VALUES(Laboratorio), 
    Fascia=VALUES(Fascia), 
    Colore=VALUES(Colore), 
    M=VALUES(M), 
    J=VALUES(J), 
    S=VALUES(S);

-- Insert Animator-Responsible relationships
INSERT INTO Animatori_Responsabili (AnimatoreID, ResponsabileID) VALUES
(4, 2),
(7, 2),
(32, 1),
(32, 2),
(36, 4),
(37, 3),
(38, 3)
ON DUPLICATE KEY UPDATE AnimatoreID=VALUES(AnimatoreID), ResponsabileID=VALUES(ResponsabileID);

-- Create read-only user
CREATE USER IF NOT EXISTS 'lettore'@'%' IDENTIFIED BY 'password_lettore';
GRANT SELECT ON ora_2k25.* TO 'lettore'@'%';



FLUSH PRIVILEGES;