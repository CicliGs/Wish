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
            $table->dropForeign(['user_id']);
            $table->dropUnique('friend_requests_user_id_receiver_id_unique');
            
            $table->renameColumn('user_id', 'sender_id');
        });
        
        Schema::table('friend_requests', function (Blueprint $table) {
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['sender_id', 'receiver_id'], 'friend_requests_sender_receiver_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('friend_requests', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->dropUnique('friend_requests_sender_receiver_unique');
            
            $table->renameColumn('sender_id', 'user_id');
        });
        
        Schema::table('friend_requests', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'receiver_id'], 'friend_requests_user_id_receiver_id_unique');
        });
    }
};
