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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 投票用户ID
            $table->foreignId('link_id')->constrained()->onDelete('cascade'); // 投票链接ID
            $table->enum('type', ['upvote', 'downvote']); // 投票类型：点赞或踩
            $table->timestamps();
            
            // 确保每个用户对每个链接只能投票一次
            $table->unique(['user_id', 'link_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
