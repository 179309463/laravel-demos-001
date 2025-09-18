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
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // 链接标题
            $table->text('url'); // 链接URL
            $table->text('description')->nullable(); // 链接描述
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 提交用户ID
            $table->integer('upvotes')->default(0); // 点赞数
            $table->integer('downvotes')->default(0); // 踩数
            $table->integer('comments_count')->default(0); // 评论数
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
