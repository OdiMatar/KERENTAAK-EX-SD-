-- Stored procedures voor de klantenmodule van Kniploket Tiko.

DROP PROCEDURE IF EXISTS sp_get_customers;
DROP PROCEDURE IF EXISTS sp_create_customer;
DROP PROCEDURE IF EXISTS sp_update_customer;
DROP PROCEDURE IF EXISTS sp_delete_customer;
DROP PROCEDURE IF EXISTS sp_get_customer_history;

DELIMITER //

CREATE PROCEDURE sp_get_customers(IN p_search VARCHAR(200))
BEGIN
    DECLARE v_search VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

    SET v_search = CONVERT(p_search USING utf8mb4) COLLATE utf8mb4_unicode_ci;

    SELECT
        klanten.id,
        klanten.voornaam,
        klanten.achternaam,
        CONCAT(klanten.voornaam, ' ', klanten.achternaam) AS naam,
        klanten.adres,
        klanten.telefoonnummer,
        klanten.email,
        klanten.is_actief,
        gebruikers.gebruikersnaam
    FROM klanten
    LEFT JOIN gebruikers
        ON gebruikers.id = klanten.gebruiker_id
    WHERE (
            v_search IS NULL
            OR v_search = ''
            OR klanten.voornaam LIKE CONCAT('%', v_search, '%')
            OR klanten.achternaam LIKE CONCAT('%', v_search, '%')
            OR CONCAT(klanten.voornaam, ' ', klanten.achternaam) LIKE CONCAT('%', v_search, '%')
        )
    ORDER BY klanten.achternaam, klanten.voornaam;
END //

CREATE PROCEDURE sp_create_customer(
    IN p_first_name VARCHAR(100),
    IN p_last_name VARCHAR(100),
    IN p_address VARCHAR(255),
    IN p_phone VARCHAR(20),
    IN p_email VARCHAR(150),
    IN p_is_active TINYINT(1),
    IN p_wishes VARCHAR(255),
    IN p_allergies VARCHAR(255)
)
BEGIN
    DECLARE v_customer_id BIGINT UNSIGNED;
    DECLARE v_wish_id BIGINT UNSIGNED;
    DECLARE v_allergy_id BIGINT UNSIGNED;
    DECLARE v_address VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_email VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

    SET v_address = CONVERT(TRIM(p_address) USING utf8mb4) COLLATE utf8mb4_unicode_ci;
    SET v_email = CONVERT(TRIM(p_email) USING utf8mb4) COLLATE utf8mb4_unicode_ci;

    IF p_first_name IS NULL OR TRIM(p_first_name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Naam is een verplicht veld en mag niet leeg zijn';
    END IF;

    IF p_address IS NULL OR TRIM(p_address) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Adres is een verplicht veld en mag niet leeg zijn';
    END IF;

    IF p_email IS NULL OR p_email NOT LIKE '%@%' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vul een geldig e-mailadres in';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM klanten
        WHERE is_actief = 1
            AND LOWER(email) = LOWER(v_email)
            AND LOWER(adres) = LOWER(v_address)
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Er bestaat al een klant met dit adres en e-mailadres';
    END IF;

    INSERT INTO klanten (voornaam, achternaam, adres, telefoonnummer, email, is_actief, datum_aangemaakt, datum_gewijzigd)
    VALUES (TRIM(p_first_name), COALESCE(NULLIF(TRIM(p_last_name), ''), '-'), v_address, TRIM(p_phone), v_email, p_is_active, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

    SET v_customer_id = LAST_INSERT_ID();

    IF p_wishes IS NOT NULL AND TRIM(p_wishes) <> '' THEN
        INSERT INTO wens_allergies (type, beschrijving, datum_aangemaakt, datum_gewijzigd)
        VALUES ('wens', TRIM(p_wishes), CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

        SET v_wish_id = LAST_INSERT_ID();

        INSERT INTO klant_wensen (klant_id, wens_allergie_id, datum_aangemaakt)
        VALUES (v_customer_id, v_wish_id, CURRENT_TIMESTAMP);
    END IF;

    IF p_allergies IS NOT NULL AND TRIM(p_allergies) <> '' THEN
        INSERT INTO wens_allergies (type, beschrijving, datum_aangemaakt, datum_gewijzigd)
        VALUES ('allergie', TRIM(p_allergies), CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

        SET v_allergy_id = LAST_INSERT_ID();

        INSERT INTO klant_wensen (klant_id, wens_allergie_id, datum_aangemaakt)
        VALUES (v_customer_id, v_allergy_id, CURRENT_TIMESTAMP);
    END IF;

    SELECT v_customer_id AS customer_id;
END //

CREATE PROCEDURE sp_update_customer(
    IN p_customer_id BIGINT UNSIGNED,
    IN p_first_name VARCHAR(100),
    IN p_last_name VARCHAR(100),
    IN p_address VARCHAR(255),
    IN p_phone VARCHAR(20),
    IN p_email VARCHAR(150),
    IN p_is_active TINYINT(1),
    IN p_wishes VARCHAR(255),
    IN p_allergies VARCHAR(255)
)
BEGIN
    DECLARE v_wish_id BIGINT UNSIGNED;
    DECLARE v_allergy_id BIGINT UNSIGNED;
    DECLARE v_address VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_email VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

    SET v_address = CONVERT(TRIM(p_address) USING utf8mb4) COLLATE utf8mb4_unicode_ci;
    SET v_email = CONVERT(TRIM(p_email) USING utf8mb4) COLLATE utf8mb4_unicode_ci;

    IF NOT EXISTS (SELECT 1 FROM klanten WHERE id = p_customer_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Klant niet gevonden';
    END IF;

    IF p_first_name IS NULL OR TRIM(p_first_name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Naam is een verplicht veld en mag niet leeg zijn';
    END IF;

    IF p_address IS NULL OR TRIM(p_address) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Adres is een verplicht veld en mag niet leeg zijn';
    END IF;

    IF p_email IS NULL OR p_email NOT LIKE '%@%' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vul een geldig e-mailadres in';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM klanten
        WHERE id <> p_customer_id
            AND is_actief = 1
            AND LOWER(email) = LOWER(v_email)
            AND LOWER(adres) = LOWER(v_address)
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Er bestaat al een klant met dit adres en e-mailadres';
    END IF;

    UPDATE klanten
    SET voornaam = TRIM(p_first_name),
        achternaam = COALESCE(NULLIF(TRIM(p_last_name), ''), '-'),
        adres = v_address,
        telefoonnummer = TRIM(p_phone),
        email = v_email,
        is_actief = p_is_active,
        datum_gewijzigd = CURRENT_TIMESTAMP
    WHERE id = p_customer_id;

    DELETE klant_wensen
    FROM klant_wensen
    INNER JOIN wens_allergies
        ON wens_allergies.id = klant_wensen.wens_allergie_id
    WHERE klant_wensen.klant_id = p_customer_id
        AND wens_allergies.type IN ('wens', 'allergie');

    IF p_wishes IS NOT NULL AND TRIM(p_wishes) <> '' THEN
        INSERT INTO wens_allergies (type, beschrijving, datum_aangemaakt, datum_gewijzigd)
        VALUES ('wens', TRIM(p_wishes), CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

        SET v_wish_id = LAST_INSERT_ID();

        INSERT INTO klant_wensen (klant_id, wens_allergie_id, datum_aangemaakt)
        VALUES (p_customer_id, v_wish_id, CURRENT_TIMESTAMP);
    END IF;

    IF p_allergies IS NOT NULL AND TRIM(p_allergies) <> '' THEN
        INSERT INTO wens_allergies (type, beschrijving, datum_aangemaakt, datum_gewijzigd)
        VALUES ('allergie', TRIM(p_allergies), CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

        SET v_allergy_id = LAST_INSERT_ID();

        INSERT INTO klant_wensen (klant_id, wens_allergie_id, datum_aangemaakt)
        VALUES (p_customer_id, v_allergy_id, CURRENT_TIMESTAMP);
    END IF;

    SELECT p_customer_id AS customer_id;
END //

CREATE PROCEDURE sp_delete_customer(IN p_customer_id BIGINT UNSIGNED)
BEGIN
    IF NOT EXISTS (SELECT 1 FROM klanten WHERE id = p_customer_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Klant niet gevonden';
    END IF;

    IF EXISTS (SELECT 1 FROM klanten WHERE id = p_customer_id AND is_actief = 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Deze klant is nog actief. Zet de klant eerst op inactief voordat je deze verwijdert.';
    END IF;

    IF EXISTS (SELECT 1 FROM afspraken WHERE klant_id = p_customer_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Deze klant kan niet worden verwijderd omdat er afspraken aan gekoppeld zijn';
    END IF;

    DELETE FROM klanten
    WHERE id = p_customer_id;

    SELECT p_customer_id AS customer_id;
END //

CREATE PROCEDURE sp_get_customer_history(IN p_customer_id BIGINT UNSIGNED)
BEGIN
    SELECT
        'behandeling' AS type,
        behandelingen.naam AS titel,
        afspraken.datum AS datum,
        afspraken.status AS status,
        CONCAT(medewerkers.voornaam, ' ', medewerkers.achternaam) AS extra
    FROM afspraken
    INNER JOIN afspraak_behandeling
        ON afspraak_behandeling.afspraak_id = afspraken.id
    INNER JOIN behandelingen
        ON behandelingen.id = afspraak_behandeling.behandeling_id
    INNER JOIN medewerkers
        ON medewerkers.id = afspraken.medewerker_id
    WHERE afspraken.klant_id = p_customer_id

    UNION ALL

    SELECT
        'product' AS type,
        products.naam AS titel,
        bestellingen.besteldatum AS datum,
        bestellingen.status AS status,
        CONCAT(bestelregels.aantal, ' x ', categories.naam) AS extra
    FROM klanten
    INNER JOIN bestellingen
        ON bestellingen.klant_id = klanten.id
    INNER JOIN bestelregels
        ON bestelregels.bestelling_id = bestellingen.id
    INNER JOIN products
        ON products.id = bestelregels.product_id
    INNER JOIN categories
        ON categories.id = products.categorie_id
    WHERE klanten.id = p_customer_id

    ORDER BY datum DESC, titel ASC;
END //

DELIMITER ;
