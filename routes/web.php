<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

// 首页重定向到链接列表
Route::get('/', [LinkController::class, 'index'])->name('home');

// 认证路由
Auth::routes();

// 重定向 /home 到链接列表
Route::get('/home', [HomeController::class, 'index'])->name('dashboard');

// 链接相关路由
Route::resource('links', LinkController::class);

// 需要认证的路由
Route::middleware('auth')->group(function () {
    // 评论路由
    Route::post('/links/{link}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    
    // 投票路由
    Route::post('/links/{link}/vote', [VoteController::class, 'vote'])->name('links.vote');
});
