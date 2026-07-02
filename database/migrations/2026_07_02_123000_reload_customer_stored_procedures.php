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

        $sql = file_get_contents(database_path('stored-procedures/customers_procedures.sql'));

        if ($sql === false) {
            return;
        }

        [$beforeDelimiter, $afterDelimiter] = explode('DELIMITER //', $sql, 2);
        [$procedures] = explode('DELIMITER ;', $afterDelimiter, 2);

        foreach (array_filter(array_map('trim', explode(';', $beforeDelimiter))) as $statement) {
            DB::unprepared($statement);
        }

        foreach (array_filter(array_map('trim', preg_split('/\s*\/\/\s*/', $procedures))) as $statement) {
            DB::unprepared($statement);
        }
    }

    public function down(): void
    {
    }
};
