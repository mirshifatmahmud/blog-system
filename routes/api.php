<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\LoginRegisterController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// public route
Route::post('/register', [LoginRegisterController::class, 'register']);
Route::post('/login', [LoginRegisterController::class, 'login']);

// protected route
Route::middleware('auth:sanctum')->group(function () {
    // user route
    Route::post('/logout', [LoginRegisterController::class, 'logout']);

    // post route
    Route::apiResource('posts', PostController::class);
    Route::post('/posts/{post}/like', [LikeController::class, 'likePost']);
    Route::delete('/posts/{post}/like', [LikeController::class, 'unlikePost']);

    // comment route
    Route::apiResource('comments', CommentController::class);
    Route::post('/comments/{comment}/like', [LikeController::class, 'likeComment']);
    Route::delete('/comments/{comment}/like', [LikeController::class, 'unlikeComment']);
});
