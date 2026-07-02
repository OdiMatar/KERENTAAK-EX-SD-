<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_customers');
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
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_customers');
    }
};
