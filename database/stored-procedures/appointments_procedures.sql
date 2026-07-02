-- Stored procedures voor de afsprakenmodule van Kniploket Tiko.
-- Voer dit bestand uit na database/kniploket.sql.

DROP PROCEDURE IF EXISTS sp_seed_appointment_basisdata;
DROP PROCEDURE IF EXISTS sp_ensure_customer_for_user;
DROP PROCEDURE IF EXISTS sp_get_customer_appointments;
DROP PROCEDURE IF EXISTS sp_create_appointment;
DROP PROCEDURE IF EXISTS sp_update_appointment;
DROP PROCEDURE IF EXISTS sp_cancel_appointment;
DROP PROCEDURE IF EXISTS sp_delete_appointment;

DELIMITER //

CREATE PROCEDURE sp_seed_appointment_basisdata()
BEGIN
    INSERT INTO rollen (naam, omschrijving)
    SELECT 'Klant', 'Geregistreerde klant'
    WHERE NOT EXISTS (SELECT 1 FROM rollen WHERE naam = 'Klant');

    INSERT INTO rollen (naam, omschrijving)
    SELECT 'Medewerker', 'Kapper of stylist'
    WHERE NOT EXISTS (SELECT 1 FROM rollen WHERE naam = 'Medewerker');

    INSERT INTO behandelingen (naam, categorie, duur, prijs, omschrijving)
    SELECT 'Knippen', 'Haar', 45, 25.00, 'Knippen en modelleren'
    WHERE NOT EXISTS (SELECT 1 FROM behandelingen WHERE naam = 'Knippen');

    INSERT INTO behandelingen (naam, categorie, duur, prijs, omschrijving)
    SELECT 'Kleuren', 'Haar', 90, 55.00, 'Kleurbehandeling'
    WHERE NOT EXISTS (SELECT 1 FROM behandelingen WHERE naam = 'Kleuren');

    INSERT INTO behandelingen (naam, categorie, duur, prijs, omschrijving)
    SELECT 'Stylen', 'Haar', 30, 20.00, 'Styling en finish'
    WHERE NOT EXISTS (SELECT 1 FROM behandelingen WHERE naam = 'Stylen');

    INSERT INTO behandelingen (naam, categorie, duur, prijs, omschrijving)
    SELECT 'Extensions', 'Haar', 120, 120.00, 'Extensions plaatsen'
    WHERE NOT EXISTS (SELECT 1 FROM behandelingen WHERE naam = 'Extensions');

    INSERT INTO specialisaties (naam, omschrijving)
    SELECT 'Knippen', 'Knippen en modelleren'
    WHERE NOT EXISTS (SELECT 1 FROM specialisaties WHERE naam = 'Knippen');

    INSERT INTO specialisaties (naam, omschrijving)
    SELECT 'Kleuren', 'Kleurbehandelingen uitvoeren'
    WHERE NOT EXISTS (SELECT 1 FROM specialisaties WHERE naam = 'Kleuren');

    INSERT INTO specialisaties (naam, omschrijving)
    SELECT 'Stylen', 'Haar stylen en finishen'
    WHERE NOT EXISTS (SELECT 1 FROM specialisaties WHERE naam = 'Stylen');

    INSERT INTO specialisaties (naam, omschrijving)
    SELECT 'Extensions', 'Extensions plaatsen en verzorgen'
    WHERE NOT EXISTS (SELECT 1 FROM specialisaties WHERE naam = 'Extensions');

    INSERT INTO medewerkers (voornaam, achternaam, email, functie)
    SELECT 'Yassin', 'Attiah', 'yassin.attiah@kniplokettiko.nl', 'Kapper'
    WHERE NOT EXISTS (SELECT 1 FROM medewerkers WHERE email = 'yassin.attiah@kniplokettiko.nl');

    INSERT INTO medewerkers (voornaam, achternaam, email, functie)
    SELECT 'Mohammad', 'Abdullah', 'mohammad.abdullah@kniplokettiko.nl', 'Stylist'
    WHERE NOT EXISTS (SELECT 1 FROM medewerkers WHERE email = 'mohammad.abdullah@kniplokettiko.nl');

    INSERT INTO medewerkers (voornaam, achternaam, email, functie)
    SELECT 'Amina', 'El Idrissi', 'amina.elidrissi@kniplokettiko.nl', 'Extensions specialist'
    WHERE NOT EXISTS (SELECT 1 FROM medewerkers WHERE email = 'amina.elidrissi@kniplokettiko.nl');

    INSERT INTO medewerkers (voornaam, achternaam, email, functie)
    SELECT 'Sara', 'Bakker', 'sara.bakker@kniplokettiko.nl', 'Colorist'
    WHERE NOT EXISTS (SELECT 1 FROM medewerkers WHERE email = 'sara.bakker@kniplokettiko.nl');

    INSERT INTO medewerkers (voornaam, achternaam, email, functie)
    SELECT 'Omar', 'Hassan', 'omar.hassan@kniplokettiko.nl', 'Kapper'
    WHERE NOT EXISTS (SELECT 1 FROM medewerkers WHERE email = 'omar.hassan@kniplokettiko.nl');

    INSERT INTO medewerkers (voornaam, achternaam, email, functie)
    SELECT 'Noor', 'Smit', 'noor.smit@kniplokettiko.nl', 'Stylist'
    WHERE NOT EXISTS (SELECT 1 FROM medewerkers WHERE email = 'noor.smit@kniplokettiko.nl');

    INSERT IGNORE INTO medewerkers_specialisaties (medewerker_id, specialisatie_id)
    SELECT medewerkers.id, specialisaties.id
    FROM medewerkers
    INNER JOIN specialisaties ON specialisaties.naam IN ('Knippen', 'Kleuren')
    WHERE medewerkers.email = 'yassin.attiah@kniplokettiko.nl';

    INSERT IGNORE INTO medewerkers_specialisaties (medewerker_id, specialisatie_id)
    SELECT medewerkers.id, specialisaties.id
    FROM medewerkers
    INNER JOIN specialisaties ON specialisaties.naam = 'Stylen'
    WHERE medewerkers.email = 'mohammad.abdullah@kniplokettiko.nl';

    INSERT IGNORE INTO medewerkers_specialisaties (medewerker_id, specialisatie_id)
    SELECT medewerkers.id, specialisaties.id
    FROM medewerkers
    INNER JOIN specialisaties ON specialisaties.naam = 'Extensions'
    WHERE medewerkers.email = 'amina.elidrissi@kniplokettiko.nl';

    INSERT IGNORE INTO medewerkers_specialisaties (medewerker_id, specialisatie_id)
    SELECT medewerkers.id, specialisaties.id
    FROM medewerkers
    INNER JOIN specialisaties ON specialisaties.naam IN ('Kleuren', 'Stylen')
    WHERE medewerkers.email = 'sara.bakker@kniplokettiko.nl';

    INSERT IGNORE INTO medewerkers_specialisaties (medewerker_id, specialisatie_id)
    SELECT medewerkers.id, specialisaties.id
    FROM medewerkers
    INNER JOIN specialisaties ON specialisaties.naam = 'Knippen'
    WHERE medewerkers.email = 'omar.hassan@kniplokettiko.nl';

    INSERT IGNORE INTO medewerkers_specialisaties (medewerker_id, specialisatie_id)
    SELECT medewerkers.id, specialisaties.id
    FROM medewerkers
    INNER JOIN specialisaties ON specialisaties.naam IN ('Extensions', 'Kleuren')
    WHERE medewerkers.email = 'noor.smit@kniplokettiko.nl';
END //

CREATE PROCEDURE sp_ensure_customer_for_user(IN p_user_id BIGINT UNSIGNED)
BEGIN
    DECLARE v_customer_id BIGINT UNSIGNED;
    DECLARE v_legacy_user_id BIGINT UNSIGNED;
    DECLARE v_customer_role_id BIGINT UNSIGNED;
    DECLARE v_name VARCHAR(255);
    DECLARE v_email VARCHAR(255);
    DECLARE v_password VARCHAR(255);
    DECLARE v_first_name VARCHAR(100);
    DECLARE v_last_name VARCHAR(100);

    SELECT name, email, password INTO v_name, v_email, v_password
    FROM users
    WHERE id = p_user_id
    LIMIT 1;

    SELECT id INTO v_legacy_user_id
    FROM gebruikers
    WHERE email = v_email
    LIMIT 1;

    IF v_legacy_user_id IS NULL THEN
        INSERT INTO rollen (naam, omschrijving)
        SELECT 'Klant', 'Geregistreerde klant'
        WHERE NOT EXISTS (SELECT 1 FROM rollen WHERE naam = 'Klant');

        SELECT id INTO v_customer_role_id
        FROM rollen
        WHERE naam = 'Klant'
        LIMIT 1;

        INSERT INTO gebruikers (rol_id, gebruikersnaam, email, wachtwoord)
        VALUES (v_customer_role_id, v_email, v_email, v_password);

        SET v_legacy_user_id = LAST_INSERT_ID();
    END IF;

    SELECT id INTO v_customer_id
    FROM klanten
    WHERE gebruiker_id = v_legacy_user_id
        OR email = v_email
    LIMIT 1;

    IF v_customer_id IS NULL THEN
        SET v_first_name = SUBSTRING_INDEX(v_name, ' ', 1);
        SET v_last_name = NULLIF(TRIM(SUBSTRING(v_name, CHAR_LENGTH(v_first_name) + 1)), '');

        INSERT INTO klanten (gebruiker_id, voornaam, achternaam, email)
        VALUES (
            v_legacy_user_id,
            v_first_name,
            COALESCE(v_last_name, '-'),
            v_email
        );

        SET v_customer_id = LAST_INSERT_ID();
    ELSE
        UPDATE klanten
        SET gebruiker_id = v_legacy_user_id
        WHERE id = v_customer_id
            AND gebruiker_id IS NULL;
    END IF;

    SELECT v_customer_id AS customer_id;
END //

CREATE PROCEDURE sp_get_customer_appointments(IN p_customer_id BIGINT UNSIGNED)
BEGIN
    SELECT
        afspraken.id,
        CONCAT(klanten.voornaam, ' ', klanten.achternaam) AS customer_name,
        TIME_FORMAT(afspraken.starttijd, '%H:%i') AS start_time,
        TIME_FORMAT(afspraken.eindtijd, '%H:%i') AS end_time,
        afspraken.datum AS date,
        CONCAT(medewerkers.voornaam, ' ', medewerkers.achternaam) AS employee_name,
        behandelingen.naam AS treatment_name,
        afspraken.status
    FROM afspraken
    INNER JOIN klanten
        ON klanten.id = afspraken.klant_id
    INNER JOIN medewerkers
        ON medewerkers.id = afspraken.medewerker_id
    INNER JOIN afspraak_behandeling
        ON afspraak_behandeling.afspraak_id = afspraken.id
    INNER JOIN behandelingen
        ON behandelingen.id = afspraak_behandeling.behandeling_id
    WHERE afspraken.klant_id = p_customer_id
        AND afspraken.status = 'Gepland'
        AND afspraken.is_actief = 1
        AND CAST(CONCAT(afspraken.datum, ' ', afspraken.starttijd) AS DATETIME) >= NOW()
    ORDER BY afspraken.datum, afspraken.starttijd;
END //

CREATE PROCEDURE sp_create_appointment(
    IN p_customer_id BIGINT UNSIGNED,
    IN p_employee_id BIGINT UNSIGNED,
    IN p_treatment_id BIGINT UNSIGNED,
    IN p_date DATE,
    IN p_start_time TIME
)
BEGIN
    DECLARE v_treatment_name VARCHAR(100);
    DECLARE v_duration SMALLINT UNSIGNED;
    DECLARE v_price DECIMAL(7,2);
    DECLARE v_end_time TIME;
    DECLARE v_overlap_count INT DEFAULT 0;
    DECLARE v_appointment_id BIGINT UNSIGNED;

    SELECT naam, duur, prijs INTO v_treatment_name, v_duration, v_price
    FROM behandelingen
    WHERE id = p_treatment_id
        AND is_actief = 1
    LIMIT 1;

    IF v_duration IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Deze behandeling is niet beschikbaar';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM medewerkers WHERE id = p_employee_id AND is_actief = 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Deze medewerker is niet beschikbaar';
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM medewerkers_specialisaties
        INNER JOIN specialisaties
            ON specialisaties.id = medewerkers_specialisaties.specialisatie_id
        WHERE medewerkers_specialisaties.medewerker_id = p_employee_id
            AND specialisaties.naam = v_treatment_name
            AND specialisaties.is_actief = 1
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Deze medewerker kan deze behandeling niet uitvoeren';
    END IF;

    IF CAST(CONCAT(p_date, ' ', p_start_time) AS DATETIME) <= NOW() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Dit tijdstip is niet beschikbaar';
    END IF;

    IF SECOND(p_start_time) <> 0 OR MINUTE(p_start_time) NOT IN (0, 15, 30, 45) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Kies een starttijd in stappen van 15 minuten';
    END IF;

    SET v_end_time = ADDTIME(p_start_time, SEC_TO_TIME(v_duration * 60));

    IF p_start_time < '09:00:00' OR v_end_time > '19:00:00' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Afspraken kunnen alleen tussen 09:00 en 19:00 worden gepland';
    END IF;

    SELECT COUNT(*) INTO v_overlap_count
    FROM afspraken
    WHERE medewerker_id = p_employee_id
        AND datum = p_date
        AND status = 'Gepland'
        AND is_actief = 1
        AND p_start_time < eindtijd
        AND v_end_time > starttijd;

    IF v_overlap_count > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Deze medewerker is op dit tijdstip niet beschikbaar';
    END IF;

    INSERT INTO afspraken (klant_id, medewerker_id, datum, starttijd, eindtijd, status)
    VALUES (p_customer_id, p_employee_id, p_date, p_start_time, v_end_time, 'Gepland');

    SET v_appointment_id = LAST_INSERT_ID();

    INSERT INTO afspraak_behandeling (afspraak_id, behandeling_id, prijs_op_moment, duur_op_moment)
    VALUES (v_appointment_id, p_treatment_id, v_price, v_duration);

    SELECT v_appointment_id AS appointment_id;
END //

CREATE PROCEDURE sp_update_appointment(
    IN p_appointment_id BIGINT UNSIGNED,
    IN p_customer_id BIGINT UNSIGNED,
    IN p_employee_id BIGINT UNSIGNED,
    IN p_treatment_id BIGINT UNSIGNED,
    IN p_date DATE,
    IN p_start_time TIME
)
BEGIN
    DECLARE v_treatment_name VARCHAR(100);
    DECLARE v_duration SMALLINT UNSIGNED;
    DECLARE v_price DECIMAL(7,2);
    DECLARE v_end_time TIME;
    DECLARE v_overlap_count INT DEFAULT 0;

    IF NOT EXISTS (
        SELECT 1
        FROM afspraken
        WHERE id = p_appointment_id
            AND klant_id = p_customer_id
            AND status = 'Gepland'
            AND is_actief = 1
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Deze afspraak kan niet gewijzigd worden';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM afspraken
        WHERE id = p_appointment_id
            AND klant_id = p_customer_id
            AND datum <= CURDATE()
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Deze afspraak kan op de dag zelf niet meer gewijzigd worden';
    END IF;

    SELECT naam, duur, prijs INTO v_treatment_name, v_duration, v_price
    FROM behandelingen
    WHERE id = p_treatment_id
        AND is_actief = 1
    LIMIT 1;

    IF v_duration IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Deze behandeling is niet beschikbaar';
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM medewerkers
        WHERE id = p_employee_id
            AND is_actief = 1
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Deze medewerker is niet beschikbaar';
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM medewerkers_specialisaties
        INNER JOIN specialisaties
            ON specialisaties.id = medewerkers_specialisaties.specialisatie_id
        WHERE medewerkers_specialisaties.medewerker_id = p_employee_id
            AND specialisaties.naam = v_treatment_name
            AND specialisaties.is_actief = 1
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Deze medewerker kan deze behandeling niet uitvoeren';
    END IF;

    IF CAST(CONCAT(p_date, ' ', p_start_time) AS DATETIME) <= NOW() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Dit tijdstip is niet beschikbaar';
    END IF;

    IF SECOND(p_start_time) <> 0 OR MINUTE(p_start_time) NOT IN (0, 15, 30, 45) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Kies een starttijd in stappen van 15 minuten';
    END IF;

    SET v_end_time = ADDTIME(p_start_time, SEC_TO_TIME(v_duration * 60));

    IF p_start_time < '09:00:00' OR v_end_time > '19:00:00' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Afspraken kunnen alleen tussen 09:00 en 19:00 worden gepland';
    END IF;

    SELECT COUNT(*) INTO v_overlap_count
    FROM afspraken
    WHERE id <> p_appointment_id
        AND medewerker_id = p_employee_id
        AND datum = p_date
        AND status = 'Gepland'
        AND is_actief = 1
        AND p_start_time < eindtijd
        AND v_end_time > starttijd;

    IF v_overlap_count > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Dit tijdstip is niet beschikbaar';
    END IF;

    UPDATE afspraken
    SET medewerker_id = p_employee_id,
        datum = p_date,
        starttijd = p_start_time,
        eindtijd = v_end_time,
        datum_gewijzigd = CURRENT_TIMESTAMP(6)
    WHERE id = p_appointment_id;

    DELETE FROM afspraak_behandeling
    WHERE afspraak_id = p_appointment_id;

    INSERT INTO afspraak_behandeling (afspraak_id, behandeling_id, prijs_op_moment, duur_op_moment)
    VALUES (p_appointment_id, p_treatment_id, v_price, v_duration);

    SELECT p_appointment_id AS appointment_id;
END //

CREATE PROCEDURE sp_cancel_appointment(
    IN p_appointment_id BIGINT UNSIGNED,
    IN p_customer_id BIGINT UNSIGNED
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM afspraken
        WHERE id = p_appointment_id
            AND klant_id = p_customer_id
            AND status = 'Gepland'
            AND is_actief = 1
            AND datum > CURDATE()
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Deze afspraak kan niet meer geannuleerd worden';
    END IF;

    UPDATE afspraken
    SET status = 'Geannuleerd',
        is_actief = 0,
        datum_gewijzigd = CURRENT_TIMESTAMP(6)
    WHERE id = p_appointment_id
        AND klant_id = p_customer_id;

    SELECT p_appointment_id AS appointment_id;
END //

CREATE PROCEDURE sp_delete_appointment(
    IN p_appointment_id BIGINT UNSIGNED,
    IN p_customer_id BIGINT UNSIGNED
)
BEGIN
    CALL sp_cancel_appointment(p_appointment_id, p_customer_id);
END //

DELIMITER ;

CALL sp_seed_appointment_basisdata();
