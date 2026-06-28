CREATE DATABASE kolekcja_gier;
USE kolekcja_gier;

-- 1. Tabela użytkowników
CREATE TABLE uzytkownicy (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    haslo VARCHAR(255) NOT NULL,
    rola VARCHAR(20) DEFAULT 'user'
);

-- 2. Tabela kategorii (potrzebna do filtrowania/linków)
CREATE TABLE kategorie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nazwa VARCHAR(50) NOT NULL
);

-- 3. Tabela główna obiektów (Gry) - zawiera CRUD i obsługę zdjęć
CREATE TABLE gry (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tytul VARCHAR(100) NOT NULL,
    opis TEXT,
    zdjecie VARCHAR(255) DEFAULT 'default.jpg',
    kategoria_id INT,
    uzytkownik_id INT,
    FOREIGN KEY (kategoria_id) REFERENCES kategorie(id),
    FOREIGN KEY (uzytkownik_id) REFERENCES uzytkownicy(id) ON DELETE CASCADE
);

-- 4. Tabela powiązana (Recenzje/Komentarze)
CREATE TABLE recenzje (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gra_id INT,
    uzytkownik_id INT,
    komentarz TEXT,
    ocena INT CHECK (ocena BETWEEN 1 AND 5),
    FOREIGN KEY (gra_id) REFERENCES gry(id) ON DELETE CASCADE,
    FOREIGN KEY (uzytkownik_id) REFERENCES uzytkownicy(id)
);
CREATE TABLE IF NOT EXISTS ulubione (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uzytkownik_id INT NOT NULL,
    gra_id INT NOT NULL,
    data_dodania TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uzytkownik_id) REFERENCES uzytkownicy(id) ON DELETE CASCADE,
    FOREIGN KEY (gra_id) REFERENCES gry(id) ON DELETE CASCADE,
    UNIQUE KEY unikalne_ulubione (uzytkownik_id, gra_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS recenzje (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gra_id INT NOT NULL,
    uzytkownik_id INT NOT NULL,
    ocena INT NOT NULL CHECK (ocena BETWEEN 1 AND 5),
    tresc TEXT NOT NULL,
    data_dodania TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gra_id) REFERENCES gry(id) ON DELETE CASCADE,
    FOREIGN KEY (uzytkownik_id) REFERENCES uzytkownicy(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dane początkowe dla kategorii
INSERT INTO kategorie (nazwa) VALUES ('Strategiczne'), ('Towarzyskie'), ('Ekonomiczne'), ('Karciane');

ALTER TABLE recenzje ADD COLUMN tresc TEXT NOT NULL;
ALTER TABLE recenzje ADD COLUMN data_dodania TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE gry ADD kategoria VARCHAR(100) NULL AFTER tytul;