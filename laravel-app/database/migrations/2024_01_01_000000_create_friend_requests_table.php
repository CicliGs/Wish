<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('friend_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // кто отправил
            $table->unsignedBigInteger('receiver_id'); // кому отправил
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'receiver_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('friend_requests');
    }
}; 