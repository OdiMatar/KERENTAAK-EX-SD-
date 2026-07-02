-- Kniploket Tiko - MySQL database voor dit Laravel-project
-- Tabellen uit ERD: rollen, gebruikers, klanten, medewerkers, specialisaties,
-- medewerkers_specialisaties, roosters, wens_allergies, klant_wensen,
-- afspraken, behandelingen, afspraak_behandeling, categories, leveranciers,
-- products, behandelingen_product, bestellingen, bestelregels
-- Laravel-tabellen: users, password_reset_tokens, sessions, cache, cache_locks,
-- jobs, job_batches, failed_jobs, technical_logs, migrations

DROP DATABASE IF EXISTS kerentaak_ex;
CREATE DATABASE kerentaak_ex
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE kerentaak_ex;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS technical_logs;
DROP TABLE IF EXISTS failed_jobs;
DROP TABLE IF EXISTS job_batches;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS cache_locks;
DROP TABLE IF EXISTS cache;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS password_reset_tokens;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS migrations;
DROP TABLE IF EXISTS bestelregels;
DROP TABLE IF EXISTS bestellingen;
DROP TABLE IF EXISTS behandelingen_product;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS leveranciers;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS afspraak_behandeling;
DROP TABLE IF EXISTS behandelingen;
DROP TABLE IF EXISTS afspraken;
DROP TABLE IF EXISTS klant_wensen;
DROP TABLE IF EXISTS wens_allergies;
DROP TABLE IF EXISTS roosters;
DROP TABLE IF EXISTS medewerkers_specialisaties;
DROP TABLE IF EXISTS specialisaties;
DROP TABLE IF EXISTS medewerkers;
DROP TABLE IF EXISTS klanten;
DROP TABLE IF EXISTS gebruikers;
DROP TABLE IF EXISTS rollen;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================
-- Laravel framework tabellen
-- =========================
CREATE TABLE migrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(30) NOT NULL DEFAULT 'klant',
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    INDEX users_role_index (role)
) ENGINE=InnoDB;

CREATE TABLE password_reset_tokens (
    email VARCHAR(255) NOT NULL PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE sessions (
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX sessions_user_id_index (user_id),
    INDEX sessions_last_activity_index (last_activity)
) ENGINE=InnoDB;

CREATE TABLE cache (
    `key` VARCHAR(255) NOT NULL PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration BIGINT NOT NULL,
    INDEX cache_expiration_index (expiration)
) ENGINE=InnoDB;

CREATE TABLE cache_locks (
    `key` VARCHAR(255) NOT NULL PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration BIGINT NOT NULL,
    INDEX cache_locks_expiration_index (expiration)
) ENGINE=InnoDB;

CREATE TABLE jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts SMALLINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    INDEX jobs_queue_index (queue)
) ENGINE=InnoDB;

CREATE TABLE job_batches (
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids LONGTEXT NOT NULL,
    options MEDIUMTEXT NULL,
    cancelled_at INT NULL,
    created_at INT NOT NULL,
    finished_at INT NULL
) ENGINE=InnoDB;

CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection VARCHAR(255) NOT NULL,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX failed_jobs_connection_queue_failed_at_index (connection, queue, failed_at)
) ENGINE=InnoDB;

CREATE TABLE technical_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(80) NOT NULL,
    message VARCHAR(255) NOT NULL,
    context JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    INDEX technical_logs_action_created_at_index (action, created_at),
    INDEX technical_logs_user_id_index (user_id),
    CONSTRAINT technical_logs_user_id_foreign
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================
-- Basis / accounts
-- =========================
CREATE TABLE rollen (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    naam VARCHAR(50) NOT NULL UNIQUE,
    omschrijving VARCHAR(255) NULL,
    is_actief TINYINT(1) NOT NULL DEFAULT 1,
    opmerking VARCHAR(255) NULL,
    datum_aangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    datum_gewijzigd DATETIME(6) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6)
) ENGINE=InnoDB;

CREATE TABLE gebruikers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rol_id BIGINT UNSIGNED NOT NULL,
    gebruikersnaam VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    wachtwoord VARCHAR(255) NOT NULL,
    is_actief TINYINT(1) NOT NULL DEFAULT 1,
    opmerking VARCHAR(255) NULL,
    datum_aangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    datum_gewijzigd DATETIME(6) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
    CONSTRAINT fk_gebruikers_rollen
        FOREIGN KEY (rol_id) REFERENCES rollen(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================
-- Klanten / wensen
-- =========================
CREATE TABLE klanten (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    gebruiker_id BIGINT UNSIGNED NULL,
    voornaam VARCHAR(100) NOT NULL,
    achternaam VARCHAR(100) NOT NULL,
    telefoonnummer VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    adres VARCHAR(255) NULL,
    is_actief TINYINT(1) NOT NULL DEFAULT 1,
    opmerking VARCHAR(255) NULL,
    datum_aangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    datum_gewijzigd DATETIME(6) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
    CONSTRAINT fk_klanten_gebruikers
        FOREIGN KEY (gebruiker_id) REFERENCES gebruikers(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE wens_allergies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(20) NOT NULL,
    beschrijving VARCHAR(255) NOT NULL,
    is_actief TINYINT(1) NOT NULL DEFAULT 1,
    datum_aangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    datum_gewijzigd DATETIME(6) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6)
) ENGINE=InnoDB;

CREATE TABLE klant_wensen (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    klant_id BIGINT UNSIGNED NOT NULL,
    wens_allergie_id BIGINT UNSIGNED NOT NULL,
    opmerking VARCHAR(255) NULL,
    datum_aangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    CONSTRAINT fk_klant_wensen_klanten
        FOREIGN KEY (klant_id) REFERENCES klanten(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_klant_wensen_wens_allergies
        FOREIGN KEY (wens_allergie_id) REFERENCES wens_allergies(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT uq_klant_wens UNIQUE (klant_id, wens_allergie_id)
) ENGINE=InnoDB;

-- =========================
-- Medewerkers / roosters / specialisaties
-- =========================
CREATE TABLE medewerkers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    gebruiker_id BIGINT UNSIGNED NULL,
    voornaam VARCHAR(100) NOT NULL,
    achternaam VARCHAR(100) NOT NULL,
    telefoonnummer VARCHAR(20) NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    functie VARCHAR(50) NOT NULL DEFAULT 'Medewerker',
    is_actief TINYINT(1) NOT NULL DEFAULT 1,
    datum_aangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    datum_gewijzigd DATETIME(6) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
    CONSTRAINT fk_medewerkers_gebruikers
        FOREIGN KEY (gebruiker_id) REFERENCES gebruikers(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE specialisaties (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    naam VARCHAR(100) NOT NULL UNIQUE,
    omschrijving VARCHAR(255) NULL,
    is_actief TINYINT(1) NOT NULL DEFAULT 1,
    datum_aangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    datum_gewijzigd DATETIME(6) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6)
) ENGINE=InnoDB;

CREATE TABLE medewerkers_specialisaties (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    medewerker_id BIGINT UNSIGNED NOT NULL,
    specialisatie_id BIGINT UNSIGNED NOT NULL,
    datum_aangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    CONSTRAINT fk_medewerkers_specialisaties_medewerkers
        FOREIGN KEY (medewerker_id) REFERENCES medewerkers(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_medewerkers_specialisaties_specialisaties
        FOREIGN KEY (specialisatie_id) REFERENCES specialisaties(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT uq_medewerker_specialisatie UNIQUE (medewerker_id, specialisatie_id)
) ENGINE=InnoDB;

CREATE TABLE roosters (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    medewerker_id BIGINT UNSIGNED NOT NULL,
    dag VARCHAR(20) NOT NULL,
    starttijd TIME NOT NULL,
    eindtijd TIME NOT NULL,
    is_actief TINYINT(1) NOT NULL DEFAULT 1,
    datum_aangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    datum_gewijzigd DATETIME(6) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
    CONSTRAINT fk_roosters_medewerkers
        FOREIGN KEY (medewerker_id) REFERENCES medewerkers(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================
-- Afspraken / behandelingen
-- =========================
CREATE TABLE behandelingen (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    naam VARCHAR(100) NOT NULL UNIQUE,
    categorie VARCHAR(50) NOT NULL,
    duur SMALLINT UNSIGNED NOT NULL COMMENT 'Duur in minuten',
    prijs DECIMAL(7,2) NOT NULL,
    omschrijving VARCHAR(255) NULL,
    is_actief TINYINT(1) NOT NULL DEFAULT 1,
    datum_aangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    datum_gewijzigd DATETIME(6) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6)
) ENGINE=InnoDB;

CREATE TABLE afspraken (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    klant_id BIGINT UNSIGNED NOT NULL,
    medewerker_id BIGINT UNSIGNED NOT NULL,
    datum DATE NOT NULL,
    starttijd TIME NOT NULL,
    eindtijd TIME NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Gepland',
    is_actief TINYINT(1) NOT NULL DEFAULT 1,
    opmerking VARCHAR(255) NULL,
    datum_aangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    datum_gewijzigd DATETIME(6) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
    CONSTRAINT fk_afspraken_klanten
        FOREIGN KEY (klant_id) REFERENCES klanten(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_afspraken_medewerkers
        FOREIGN KEY (medewerker_id) REFERENCES medewerkers(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE afspraak_behandeling (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    afspraak_id BIGINT UNSIGNED NOT NULL,
    behandeling_id BIGINT UNSIGNED NOT NULL,
    prijs_op_moment DECIMAL(7,2) NOT NULL,
    duur_op_moment SMALLINT UNSIGNED NOT NULL,
    CONSTRAINT fk_afspraak_behandeling_afspraken
        FOREIGN KEY (afspraak_id) REFERENCES afspraken(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_afspraak_behandeling_behandelingen
        FOREIGN KEY (behandeling_id) REFERENCES behandelingen(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT uq_afspraak_behandeling UNIQUE (afspraak_id, behandeling_id)
) ENGINE=InnoDB;

-- =========================
-- Producten / leveranciers / categorieën
-- =========================
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    naam VARCHAR(50) NOT NULL UNIQUE,
    omschrijving VARCHAR(255) NULL,
    is_actief TINYINT(1) NOT NULL DEFAULT 1,
    opmerking VARCHAR(255) NULL,
    datum_aangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    datum_gewijzigd DATETIME(6) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6)
) ENGINE=InnoDB;

CREATE TABLE leveranciers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    naam VARCHAR(100) NOT NULL,
    contactpersoon VARCHAR(100) NULL,
    telefoonnummer VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    adres VARCHAR(255) NULL,
    is_actief TINYINT(1) NOT NULL DEFAULT 1,
    opmerking VARCHAR(255) NULL,
    datum_aangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    datum_gewijzigd DATETIME(6) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6)
) ENGINE=InnoDB;

CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categorie_id BIGINT UNSIGNED NOT NULL,
    leverancier_id BIGINT UNSIGNED NOT NULL,
    naam VARCHAR(150) NOT NULL,
    barcode VARCHAR(20) NOT NULL UNIQUE,
    prijs DECIMAL(10,2) NOT NULL,
    voorraad INT NOT NULL DEFAULT 0,
    houdbaarheidsdatum DATE NULL,
    omschrijving VARCHAR(255) NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Beschikbaar',
    is_actief TINYINT(1) NOT NULL DEFAULT 1,
    opmerking VARCHAR(255) NULL,
    datum_aangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    datum_gewijzigd DATETIME(6) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
    CONSTRAINT fk_products_categories
        FOREIGN KEY (categorie_id) REFERENCES categories(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_products_leveranciers
        FOREIGN KEY (leverancier_id) REFERENCES leveranciers(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE behandelingen_product (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    behandeling_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    aantal INT NOT NULL DEFAULT 1,
    opmerking VARCHAR(255) NULL,
    datum_aangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    CONSTRAINT fk_behandelingen_product_behandelingen
        FOREIGN KEY (behandeling_id) REFERENCES behandelingen(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_behandelingen_product_products
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT uq_behandeling_product UNIQUE (behandeling_id, product_id)
) ENGINE=InnoDB;

-- =========================
-- Bestellingen / bestelregels
-- =========================
CREATE TABLE bestellingen (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    klant_id BIGINT UNSIGNED NOT NULL,
    besteldatum DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    status VARCHAR(50) NOT NULL DEFAULT 'Nieuw',
    totaalprijs DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    is_actief TINYINT(1) NOT NULL DEFAULT 1,
    opmerking VARCHAR(255) NULL,
    datum_aangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    datum_gewijzigd DATETIME(6) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
    CONSTRAINT fk_bestellingen_klanten
        FOREIGN KEY (klant_id) REFERENCES klanten(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE bestelregels (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bestelling_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    aantal INT NOT NULL,
    prijs_per_stuk DECIMAL(10,2) NOT NULL,
    subtotaal DECIMAL(10,2) NOT NULL,
    is_actief TINYINT(1) NOT NULL DEFAULT 1,
    opmerking VARCHAR(255) NULL,
    datum_aangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    datum_gewijzigd DATETIME(6) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
    CONSTRAINT fk_bestelregels_bestellingen
        FOREIGN KEY (bestelling_id) REFERENCES bestellingen(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_bestelregels_products
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================
-- Eigenaar-account
-- Wachtwoord: Welkom123!
-- =========================
INSERT INTO rollen (id, naam, omschrijving) VALUES
(1, 'Eigenaar', 'Volledige toegang tot het systeem');

INSERT INTO gebruikers (id, rol_id, gebruikersnaam, email, wachtwoord) VALUES
(1, 1, 'admin', 'admin@kniplokettiko.nl', '$2y$12$QekOUmgWtMrpEMAkR/APeuSl.8bU6O59WgYTJnOLKRbZEA94hsqUG');

INSERT INTO users (id, name, email, email_verified_at, password, role, remember_token, created_at, updated_at) VALUES
(1, 'Admin', 'admin@kniplokettiko.nl', NULL, '$2y$12$QekOUmgWtMrpEMAkR/APeuSl.8bU6O59WgYTJnOLKRbZEA94hsqUG', 'eigenaar', NULL, NOW(), NOW());

INSERT INTO migrations (migration, batch) VALUES
('0001_01_01_000000_create_users_table', 1),
('0001_01_01_000001_create_cache_table', 1),
('0001_01_01_000002_create_jobs_table', 1),
('2026_06_30_000000_create_technical_logs_table', 1),
('2026_07_01_000000_add_role_to_users_table', 1);

-- =========================
-- Stored procedures voor auth/rapportage
-- =========================
DROP PROCEDURE IF EXISTS sp_register_user;
DROP PROCEDURE IF EXISTS sp_get_user_login_summary;

DELIMITER //

CREATE PROCEDURE sp_register_user(
    IN p_name VARCHAR(255),
    IN p_email VARCHAR(255),
    IN p_password VARCHAR(255)
)
BEGIN
    INSERT INTO users (name, email, password, role, created_at, updated_at)
    VALUES (p_name, p_email, p_password, 'klant', NOW(), NOW());

    SELECT LAST_INSERT_ID() AS user_id;
END //

CREATE PROCEDURE sp_get_user_login_summary()
BEGIN
    SELECT
        users.id,
        users.name,
        users.email,
        MAX(technical_logs.created_at) AS last_login_at,
        COUNT(technical_logs.id) AS login_count
    FROM users
    LEFT JOIN technical_logs
        ON technical_logs.user_id = users.id
        AND technical_logs.action = 'login'
    GROUP BY users.id, users.name, users.email
    ORDER BY last_login_at DESC;
END //

DELIMITER ;
