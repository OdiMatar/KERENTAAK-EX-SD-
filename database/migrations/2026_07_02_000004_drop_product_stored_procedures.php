<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_producten_overzicht');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_product_zoeken');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_product_toevoegen');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_product_wijzigen');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_product_verwijderen');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
