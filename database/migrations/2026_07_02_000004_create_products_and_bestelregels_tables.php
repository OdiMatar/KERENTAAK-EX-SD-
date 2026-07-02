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
        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table): void {
                $table->id();
                $table->string('naam', 150)->unique();
                $table->string('categorie', 100);
                $table->string('ean_code', 13)->unique();
                $table->decimal('prijs', 10, 2);
                $table->integer('voorraad')->default(0);
                $table->string('leverancier', 150);
                $table->boolean('is_actief')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('bestelregels')) {
            Schema::create('bestelregels', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('bestelling_id')->constrained('bestellingen')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
                $table->integer('aantal');
                $table->decimal('prijs_per_stuk', 10, 2);
                $table->decimal('subtotaal', 10, 2);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bestelregels');
        Schema::dropIfExists('products');
    }
};
