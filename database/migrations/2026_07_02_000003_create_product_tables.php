<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table): void {
                $table->id();
                $table->string('naam', 50)->unique();
                $table->string('omschrijving')->nullable();
                $table->boolean('is_actief')->default(true);
                $table->string('opmerking')->nullable();
                $table->dateTime('datum_aangemaakt', 6)->useCurrent();
                $table->dateTime('datum_gewijzigd', 6)->nullable()->useCurrentOnUpdate();
            });
        }

        if (! Schema::hasTable('leveranciers')) {
            Schema::create('leveranciers', function (Blueprint $table): void {
                $table->id();
                $table->string('naam', 100);
                $table->string('contactpersoon', 100)->nullable();
                $table->string('telefoonnummer', 20)->nullable();
                $table->string('email', 150)->nullable();
                $table->string('adres')->nullable();
                $table->boolean('is_actief')->default(true);
                $table->string('opmerking')->nullable();
                $table->dateTime('datum_aangemaakt', 6)->useCurrent();
                $table->dateTime('datum_gewijzigd', 6)->nullable()->useCurrentOnUpdate();
            });
        }

        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('categorie_id')->constrained('categories')->restrictOnDelete()->cascadeOnUpdate();
                $table->foreignId('leverancier_id')->constrained('leveranciers')->restrictOnDelete()->cascadeOnUpdate();
                $table->string('naam', 150);
                $table->string('barcode', 20)->unique();
                $table->decimal('prijs', 10, 2);
                $table->integer('voorraad')->default(0);
                $table->date('houdbaarheidsdatum')->nullable();
                $table->string('omschrijving')->nullable();
                $table->string('status', 50)->default('Beschikbaar');
                $table->boolean('is_actief')->default(true);
                $table->string('opmerking')->nullable();
                $table->dateTime('datum_aangemaakt', 6)->useCurrent();
                $table->dateTime('datum_gewijzigd', 6)->nullable()->useCurrentOnUpdate();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('leveranciers');
        Schema::dropIfExists('categories');
    }
};
