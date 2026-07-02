<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rollen', function (Blueprint $table): void {
            $table->id();
            $table->string('naam', 50)->unique();
            $table->string('omschrijving')->nullable();
            $table->boolean('is_actief')->default(true);
            $table->timestamp('datum_aangemaakt')->nullable();
            $table->timestamp('datum_gewijzigd')->nullable();
        });

        Schema::create('gebruikers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rol_id')->constrained('rollen')->restrictOnDelete();
            $table->string('gebruikersnaam', 100)->unique();
            $table->string('email', 150)->unique();
            $table->string('wachtwoord');
            $table->boolean('is_actief')->default(true);
            $table->timestamp('datum_aangemaakt')->nullable();
            $table->timestamp('datum_gewijzigd')->nullable();
        });

        Schema::create('klanten', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('gebruiker_id')->nullable()->constrained('gebruikers')->nullOnDelete();
            $table->string('voornaam', 100);
            $table->string('achternaam', 100);
            $table->string('telefoonnummer', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->boolean('is_actief')->default(true);
            $table->timestamp('datum_aangemaakt')->nullable();
            $table->timestamp('datum_gewijzigd')->nullable();
        });

        Schema::create('specialisaties', function (Blueprint $table): void {
            $table->id();
            $table->string('naam', 100)->unique();
            $table->string('omschrijving')->nullable();
            $table->boolean('is_actief')->default(true);
            $table->timestamp('datum_aangemaakt')->nullable();
            $table->timestamp('datum_gewijzigd')->nullable();
        });

        Schema::create('medewerkers_specialisaties', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('medewerker_id')->constrained('medewerkers')->cascadeOnDelete();
            $table->foreignId('specialisatie_id')->constrained('specialisaties')->restrictOnDelete();
            $table->timestamp('datum_aangemaakt')->nullable();
            $table->unique(['medewerker_id', 'specialisatie_id'], 'uq_medewerker_specialisatie');
        });

        Schema::create('behandelingen', function (Blueprint $table): void {
            $table->id();
            $table->string('naam', 100)->unique();
            $table->string('categorie', 50);
            $table->unsignedSmallInteger('duur');
            $table->decimal('prijs', 7, 2);
            $table->string('omschrijving')->nullable();
            $table->boolean('is_actief')->default(true);
            $table->timestamp('datum_aangemaakt')->nullable();
            $table->timestamp('datum_gewijzigd')->nullable();
        });

        Schema::create('afspraken', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('klant_id')->constrained('klanten')->restrictOnDelete();
            $table->foreignId('medewerker_id')->constrained('medewerkers')->restrictOnDelete();
            $table->date('datum');
            $table->time('starttijd');
            $table->time('eindtijd');
            $table->string('status', 20)->default('Gepland');
            $table->boolean('is_actief')->default(true);
            $table->string('opmerking')->nullable();
            $table->timestamp('datum_aangemaakt')->nullable();
            $table->timestamp('datum_gewijzigd')->nullable();
        });

        Schema::create('afspraak_behandeling', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('afspraak_id')->constrained('afspraken')->cascadeOnDelete();
            $table->foreignId('behandeling_id')->constrained('behandelingen')->restrictOnDelete();
            $table->decimal('prijs_op_moment', 7, 2);
            $table->unsignedSmallInteger('duur_op_moment');
            $table->unique(['afspraak_id', 'behandeling_id'], 'uq_afspraak_behandeling');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('afspraak_behandeling');
        Schema::dropIfExists('afspraken');
        Schema::dropIfExists('behandelingen');
        Schema::dropIfExists('medewerkers_specialisaties');
        Schema::dropIfExists('specialisaties');
        Schema::dropIfExists('klanten');
        Schema::dropIfExists('gebruikers');
        Schema::dropIfExists('rollen');
    }
};
