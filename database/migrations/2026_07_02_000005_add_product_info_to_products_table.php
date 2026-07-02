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
            return;
        }

        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'categorie')) {
                $table->string('categorie', 100)->default('overig')->after('naam');
            }

            if (! Schema::hasColumn('products', 'ean_code')) {
                $table->string('ean_code', 13)->nullable()->unique()->after('categorie');
            }

            if (! Schema::hasColumn('products', 'leverancier')) {
                $table->string('leverancier', 150)->default('Kniploket Tiko')->after('voorraad');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'leverancier')) {
                $table->dropColumn('leverancier');
            }

            if (Schema::hasColumn('products', 'ean_code')) {
                $table->dropUnique('products_ean_code_unique');
                $table->dropColumn('ean_code');
            }

            if (Schema::hasColumn('products', 'categorie')) {
                $table->dropColumn('categorie');
            }
        });
    }
};
