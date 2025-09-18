<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Link extends Model
{
    protected $fillable = [
        'title',
        'url',
        'description',
        'user_id'
    ];

    /**
     * 获取链接的提交用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取链接的所有评论
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * 获取链接的所有投票
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * 获取净投票数（点赞数 - 踩数）
     */
    public function getNetVotesAttribute(): int
    {
        $upvotes = $this->votes()->where('type', 'upvote')->count();
        $downvotes = $this->votes()->where('type', 'downvote')->count();
        return $upvotes - $downvotes;
    }

    /**
     * 获取点赞数
     */
    public function getUpvotesAttribute(): int
    {
        return $this->votes()->where('type', 'upvote')->count();
    }

    /**
     * 获取踩数
     */
    public function getDownvotesAttribute(): int
    {
        return $this->votes()->where('type', 'downvote')->count();
    }

    /**
     * 获取评论数
     */
    public function getCommentsCountAttribute(): int
    {
        return $this->comments()->count();
    }
}
