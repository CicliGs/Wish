<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wish_lists', function (Blueprint $table) {
            $table->string('public_id', 36)->unique()->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('wish_lists', function (Blueprint $table) {
            $table->dropColumn('public_id');
        });
    }
}; 