CREATE DATABASE ora_2k25 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE ora_2k25;

-- Tabella Laboratori
CREATE TABLE Laboratori (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Nome VARCHAR(100) NOT NULL,
    Descrizione TEXT
);

-- Inserisce il laboratorio di default con ID 0
INSERT INTO Laboratori (ID, Nome, Descrizione) VALUES (0, 'Altro', 'Laboratorio di default');

-- Tabella Responsabili
CREATE TABLE Responsabili (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Nome VARCHAR(100) NOT NULL,
    Descrizione TEXT
);

-- Tabella Animatori (molti a uno verso Laboratori)
CREATE TABLE Animatori (
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

-- Tabella di collegamento Animatori ↔ Responsabili (molti a molti)
CREATE TABLE Animatori_Responsabili (
    AnimatoreID INT,
    ResponsabileID INT,
    PRIMARY KEY (AnimatoreID, ResponsabileID),
    FOREIGN KEY (AnimatoreID) REFERENCES Animatori(ID),
    FOREIGN KEY (ResponsabileID) REFERENCES Responsabili(ID)
);
