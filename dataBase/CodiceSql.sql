CREATE DATABASE IF NOT EXISTS CampusHelp;
USE CampusHelp;

CREATE TABLE IF NOT EXISTS utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    ruolo ENUM('user', 'admin') DEFAULT 'user',
    punti_karma INT DEFAULT 50,
    -- DROP COLUMN iban VARCHAR(34) DEFAULT NULL,
    codice_conferma VARCHAR(6) DEFAULT NULL,
    stato_account ENUM('non_confermato', 'attivo', 'bannato') DEFAULT 'non_confermato',
    creato_il TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS categorie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS punti_incontro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_luogo VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bacheca (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titolo VARCHAR(100) NOT NULL,
    descrizione TEXT NOT NULL,
    tipo ENUM('Offerta Cibo', 'Richiesta Oggetto') NOT NULL,
    stato ENUM('attivo', 'assegnato', 'completato') DEFAULT 'attivo',
    id_categoria INT,
    id_punto_incontro INT,
    id_utente_creatore INT,
    creato_il TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorie(id) ON DELETE CASCADE,
    FOREIGN KEY (id_punto_incontro) REFERENCES punti_incontro(id) ON DELETE CASCADE,
    FOREIGN KEY (id_utente_creatore) REFERENCES utenti(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS transazioni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_post INT NOT NULL,
    id_utente_aiutante INT NOT NULL,
    durata_ore_max INT NOT NULL,
    penalita_ora_karma INT NOT NULL,
    -- DROP COLUMN penale_denaro_min DECIMAL(10,2) NOT NULL,
    valore_oggetti_karma INT NOT NULL,
    foto_oggetto VARCHAR(255) DEFAULT NULL, -- Qui l'utente salverà un URL testuale per non superare i 5MB!
    minuti_arrivo_proprietario INT DEFAULT NULL,
    codice_restituzione VARCHAR(4) DEFAULT NULL,
    data_inizio_effettiva TIMESTAMP NULL DEFAULT NULL,
    data_restituzione_effettiva TIMESTAMP NULL DEFAULT NULL,
    stato_transazione ENUM('proposta','in_corso','ritardo_morbido','riscatto_totale','completato') DEFAULT 'proposta',
    FOREIGN KEY (id_post) REFERENCES bacheca(id) ON DELETE CASCADE,
    FOREIGN KEY (id_utente_aiutante) REFERENCES utenti(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
