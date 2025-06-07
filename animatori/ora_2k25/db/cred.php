-- Utente con permessi di sola lettura
CREATE USER 'lettore'@'%' IDENTIFIED BY 'password_lettore';
GRANT SELECT ON ora_2k25.* TO 'lettore'@'%';

-- Utente con permessi completi (lettura, scrittura, modifica)
CREATE USER 'editor'@'%' IDENTIFIED BY 'password_editor';
GRANT ALL PRIVILEGES ON ora_2k25.* TO 'editor'@'%';
