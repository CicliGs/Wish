<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Очищаем дублирующие записи дружбы для PostgreSQL
        DB::statement("
            DELETE FROM friend_requests 
            WHERE id IN (
                SELECT id FROM (
                    SELECT id,
                           ROW_NUMBER() OVER (
                               PARTITION BY user_id, receiver_id, status 
                               ORDER BY id
                           ) as rn
                    FROM friend_requests
                ) t
                WHERE t.rn > 1
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Нет необходимости откатывать очистку дубликатов
    }
}; 