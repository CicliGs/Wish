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
        Schema::table('friend_requests', function (Blueprint $table) {
            // Добавляем уникальный индекс для предотвращения дублирующих записей
            $table->unique(['user_id', 'receiver_id'], 'friend_requests_user_receiver_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('friend_requests', function (Blueprint $table) {
            $table->dropUnique('friend_requests_user_receiver_unique');
        });
    }
}; 