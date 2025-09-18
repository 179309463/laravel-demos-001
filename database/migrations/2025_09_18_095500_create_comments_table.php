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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->text('content'); // 评论内容
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 评论用户ID
            $table->foreignId('link_id')->constrained()->onDelete('cascade'); // 所属链接ID
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade'); // 父评论ID（用于回复）
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
