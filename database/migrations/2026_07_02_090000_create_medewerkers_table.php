<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medewerkers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('gebruiker_id')->nullable();
            $table->string('voornaam')->nullable();
            $table->string('achternaam')->nullable();
            $table->string('email')->unique();
            $table->string('role')->default('medewerker');
            $table->string('phone')->nullable();
            $table->string('telefoonnummer', 20)->nullable();
            $table->string('functie')->default('Medewerker');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_actief')->default(true);
            $table->timestamp('datum_aangemaakt')->nullable();
            $table->timestamp('datum_gewijzigd')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medewerkers');
    }
};
