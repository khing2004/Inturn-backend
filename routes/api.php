<?php

use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\InternController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public Routes (No token needed)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected Routes (Token required via Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth Actions
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Admin Specific Routes
    Route::prefix('admin')->group(function () {
        Route::get('/interns', [AdminController::class, 'getInterns']);
        Route::post('/interns', [AdminController::class, 'createIntern']);
        Route::put('/interns/{id}', [AdminController::class, 'updateIntern']);
        Route::delete('/interns/{id}', [AdminController::class, 'deleteIntern']);
    });

    // Intern Specific Routes
    Route::prefix('intern')->group(function () {
        Route::get('/tasks', [InternController::class, 'getMyTasks']);
        Route::post('/tasks/submit', [InternController::class, 'submitTask']);
        Route::get('/profile', [InternController::class, 'getMyProfile']);
    });
});