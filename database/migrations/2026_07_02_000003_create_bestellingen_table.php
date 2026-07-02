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
        if (! Schema::hasTable('bestellingen')) {
            Schema::create('bestellingen', function (Blueprint $table): void {
                $table->id();
                $table->string('klant_naam', 150);
                $table->date('orderdatum');
                $table->date('verwachte_leverdatum');
                $table->string('status', 50)->default('Nieuw');
                $table->decimal('totaalprijs', 10, 2)->default(0);
                $table->string('opmerking')->nullable();
                $table->boolean('is_actief')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bestellingen');
    }
};
