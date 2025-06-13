<?php
use App\Http\Controllers\PuzzleController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // Puzzle endpoints
    Route::get('/puzzle/{length?}', [PuzzleController::class, 'generate']);
    
    // Submission endpoints
    Route::post('/puzzle/{puzzle}/submit', [SubmissionController::class, 'submit']);
    Route::post('/puzzle/{puzzle}/end', [SubmissionController::class, 'endGame']);
    
    // Leaderboard endpoints
    Route::get('/leaderboard/{puzzle}', [SubmissionController::class, 'puzzleLeaderboard']);
});