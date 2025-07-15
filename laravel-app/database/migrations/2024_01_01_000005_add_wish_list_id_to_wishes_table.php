<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wishes', function (Blueprint $table) {
            if (!Schema::hasColumn('wishes', 'wish_list_id')) {
                $table->foreignId('wish_list_id')->after('id')->constrained('wish_lists')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('wishes', function (Blueprint $table) {
            if (Schema::hasColumn('wishes', 'wish_list_id')) {
                $table->dropForeign(['wish_list_id']);
                $table->dropColumn('wish_list_id');
            }
        });
    }
}; 