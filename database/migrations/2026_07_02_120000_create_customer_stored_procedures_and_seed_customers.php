<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->createStoredProcedures();
        $this->seedCustomers();
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_customers');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_create_customer');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_update_customer');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_delete_customer');
    }

    private function createStoredProcedures(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_customers');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_create_customer');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_update_customer');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_delete_customer');

        DB::unprepared(<<<'SQL'
CREATE PROCEDURE sp_get_customers(IN p_search VARCHAR(200))
BEGIN
    DECLARE v_search VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

    SET v_search = CONVERT(p_search USING utf8mb4) COLLATE utf8mb4_unicode_ci;

    SELECT
        klanten.id,
        klanten.voornaam,
        klanten.achternaam,
        CONCAT(klanten.voornaam, ' ', klanten.achternaam) AS naam,
        klanten.telefoonnummer,
        klanten.email,
        gebruikers.gebruikersnaam
    FROM klanten
    LEFT JOIN gebruikers
        ON gebruikers.id = klanten.gebruiker_id
    WHERE klanten.is_actief = 1
        AND (
            v_search IS NULL
            OR v_search = ''
            OR klanten.voornaam LIKE CONCAT('%', v_search, '%')
            OR klanten.achternaam LIKE CONCAT('%', v_search, '%')
            OR CONCAT(klanten.voornaam, ' ', klanten.achternaam) LIKE CONCAT('%', v_search, '%')
        )
    ORDER BY klanten.voornaam, klanten.achternaam;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE PROCEDURE sp_create_customer(
    IN p_first_name VARCHAR(100),
    IN p_last_name VARCHAR(100),
    IN p_phone VARCHAR(20),
    IN p_email VARCHAR(150)
)
BEGIN
    IF p_first_name IS NULL OR TRIM(p_first_name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Naam is een verplicht veld en mag niet leeg zijn';
    END IF;

    IF p_email IS NULL OR p_email NOT LIKE '%@%' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vul een geldig e-mailadres in';
    END IF;

    INSERT INTO klanten (voornaam, achternaam, telefoonnummer, email, is_actief, datum_aangemaakt, datum_gewijzigd)
    VALUES (TRIM(p_first_name), COALESCE(NULLIF(TRIM(p_last_name), ''), '-'), TRIM(p_phone), TRIM(p_email), 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

    SELECT LAST_INSERT_ID() AS customer_id;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE PROCEDURE sp_update_customer(
    IN p_customer_id BIGINT UNSIGNED,
    IN p_first_name VARCHAR(100),
    IN p_last_name VARCHAR(100),
    IN p_phone VARCHAR(20),
    IN p_email VARCHAR(150)
)
BEGIN
    IF NOT EXISTS (SELECT 1 FROM klanten WHERE id = p_customer_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Klant niet gevonden';
    END IF;

    IF p_first_name IS NULL OR TRIM(p_first_name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Naam is een verplicht veld en mag niet leeg zijn';
    END IF;

    IF p_email IS NULL OR p_email NOT LIKE '%@%' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vul een geldig e-mailadres in';
    END IF;

    UPDATE klanten
    SET voornaam = TRIM(p_first_name),
        achternaam = COALESCE(NULLIF(TRIM(p_last_name), ''), '-'),
        telefoonnummer = TRIM(p_phone),
        email = TRIM(p_email),
        datum_gewijzigd = CURRENT_TIMESTAMP
    WHERE id = p_customer_id;

    SELECT p_customer_id AS customer_id;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE PROCEDURE sp_delete_customer(IN p_customer_id BIGINT UNSIGNED)
BEGIN
    IF NOT EXISTS (SELECT 1 FROM klanten WHERE id = p_customer_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Klant niet gevonden';
    END IF;

    DELETE FROM klanten
    WHERE id = p_customer_id;

    SELECT p_customer_id AS customer_id;
END
SQL);
    }

    private function seedCustomers(): void
    {
        foreach ($this->customers() as $customer) {
            DB::table('klanten')->updateOrInsert(
                ['email' => $customer['email']],
                [
                    ...$customer,
                    'is_actief' => true,
                    'datum_aangemaakt' => now(),
                    'datum_gewijzigd' => now(),
                ],
            );
        }
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function customers(): array
    {
        return [
            [
                'voornaam' => 'Lisa',
                'achternaam' => 'Tiko',
                'telefoonnummer' => '0612345678',
                'email' => 'lisa.tiko@example.com',
            ],
            [
                'voornaam' => 'Sanne',
                'achternaam' => 'Bakker',
                'telefoonnummer' => '0687654321',
                'email' => 'sanne.bakker@example.com',
            ],
            [
                'voornaam' => 'Mila',
                'achternaam' => 'Jansen',
                'telefoonnummer' => '0611223344',
                'email' => 'mila.jansen@example.com',
            ],
        ];
    }
};
