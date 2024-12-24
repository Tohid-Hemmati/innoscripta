<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/reset-password/{token}', function ($token) {
    return view('auth.reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::get('/articles', [ArticleController::class, 'getArticles'])->middleware('auth:sanctum');
Route::get('/article/{id}', [ArticleController::class, 'getArticle'])->middleware('auth:sanctum');
Route::post('/preferences', [ArticleController::class, 'setPreferredNews'])->middleware('auth:sanctum');
Route::get('/preferences', [ArticleController::class, 'getPreferredNews'])->middleware('auth:sanctum');
Route::get('/news-feed', [ArticleController::class, 'fetchNewsFeed'])->middleware('auth:sanctum');

