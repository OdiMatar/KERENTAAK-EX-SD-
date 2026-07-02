<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        DB::table('categories')->updateOrInsert(
            ['naam' => 'Algemeen'],
            [
                'omschrijving' => 'Standaardcategorie voor producten',
                'is_actief' => true,
                'datum_gewijzigd' => now(),
            ],
        );

        DB::table('leveranciers')->updateOrInsert(
            ['naam' => 'Kniploket Tiko'],
            [
                'contactpersoon' => 'Lisa Jansen',
                'email' => 'info@kniplokettiko.nl',
                'is_actief' => true,
                'datum_gewijzigd' => now(),
            ],
        );

        $categoryId = DB::table('categories')->where('naam', 'Algemeen')->value('id');
        $supplierId = DB::table('leveranciers')->where('naam', 'Kniploket Tiko')->value('id');

        DB::table('products')->updateOrInsert(
            ['barcode' => '871000000001'],
            [
                'categorie_id' => $categoryId,
                'leverancier_id' => $supplierId,
                'naam' => 'Repair Shampoo 250ml',
                'prijs' => 12.95,
                'voorraad' => 25,
                'omschrijving' => 'Shampoo voor beschadigd haar',
                'status' => 'Beschikbaar',
                'is_actief' => true,
                'datum_gewijzigd' => now(),
            ],
        );

        DB::table('products')->updateOrInsert(
            ['barcode' => '871000000002'],
            [
                'categorie_id' => $categoryId,
                'leverancier_id' => $supplierId,
                'naam' => 'Matte Wax 100ml',
                'prijs' => 9.95,
                'voorraad' => 40,
                'omschrijving' => 'Wax met matte finish',
                'status' => 'Beschikbaar',
                'is_actief' => true,
                'datum_gewijzigd' => now(),
            ],
        );

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
