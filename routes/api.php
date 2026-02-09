<?php

use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\InternController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// public routes (no token needed)
Route::post('/login', [AuthController::class, 'login']); # checked thru postman returned status code 200
Route::post('/register', [AuthController::class, 'register']);

// protected routes (token required via Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    
    // auth actions
    Route::get('/user', [AuthController::class, 'user']); # checked thru postman returned status code 200
    Route::post('/logout', [AuthController::class, 'logout']); # checked thru postman

    // admin specific routes
    Route::prefix('admin')->group(function () {
        Route::get('/interns', [AdminController::class, 'getInterns']); # checked thru postman returned status code 200
        Route::post('/interns', [AdminController::class, 'createIntern']); # checked thru postman returned status code 201
        Route::put('/interns/{id}', [AdminController::class, 'updateIntern']); # checked thru postman returned status code 200
        Route::delete('/interns/{id}', [AdminController::class, 'deleteIntern']); # checked thru postman returned status code 200
        Route::get('/interns/details/{id}', [AdminController::class, 'getInternDetails']); #
        Route::get('/interns/attendance', [AdminController::class, 'getAttendanceRecords']); # checked thru postman returned status code 200
        Route::get('/interns/attendance/overview', [AdminController::class, 'getAttendanceOverview']); # checked thru postman returned status code 200
    });

    // intern specific routes
    Route::prefix('intern')->group(function () {
        Route::get('/documents', [InternController::class, 'getMyDocuments']); # checked status 200
        Route::post('/documents/submit', [InternController::class, 'submitDocument']); #checked status 201
        Route::get('/profile', [InternController::class, 'getMyProfile']); # checked status 200
        Route::post('/attendance/timeIn', [InternController::class, 'attendanceTimeIn']); # checked status 200
        Route::put('/attendance/timeOut', [InternController::class, 'attendanceTimeOut']); # checked status 200
        Route::get('/attendance/summary', [InternController::class, 'getMyAttendanceOverallSummary']); # checked status 200
        Route::get('/attendance/history', [InternController::class, 'getMyAttendanceHistory']); # checked status 200
    });
});