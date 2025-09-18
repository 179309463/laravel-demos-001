<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    protected $fillable = [
        'user_id',
        'link_id',
        'type'
    ];

    /**
     * 获取投票的用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取投票的链接
     */
    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }
}
