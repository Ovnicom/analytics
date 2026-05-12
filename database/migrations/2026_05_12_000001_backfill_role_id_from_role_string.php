<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            UPDATE users u
            JOIN roles r ON r.slug = u.role
            SET u.role_id = r.id
            WHERE u.role_id IS NULL
              AND u.role IS NOT NULL
        ');
    }

    public function down(): void
    {
        // Irreversible intencionalmente: no se puede deducir el role_id original
    }
};
