<?php

use App\Http\Controllers\Api\FeedController;
use Illuminate\Support\Facades\Route;

Route::get('feed', [FeedController::class, 'index']);
Route::get('posts/featured', [FeedController::class, 'featured']);
Route::get('posts/{post}', [FeedController::class, 'show']);
Route::post('posts/{post}/view', [FeedController::class, 'view']);
