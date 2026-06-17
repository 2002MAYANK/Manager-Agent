<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Middleware\TokenAuthMiddleware;

Route::middleware([TokenAuthMiddleware::class])->group(function () {
    Route::get('/employees', [ApiController::class, 'getEmployees']);
    Route::post('/employees', [ApiController::class, 'storeEmployee']);
    Route::put('/employees/{id}', [ApiController::class, 'updateEmployee']);
    Route::delete('/employees/{id}', [ApiController::class, 'deleteEmployee']);

    Route::get('/export', [ApiController::class, 'exportData']);
    Route::post('/import', [ApiController::class, 'importData']);

    Route::get('/tasks', [ApiController::class, 'getTasks']);
    Route::post('/tasks', [ApiController::class, 'storeTask']);

    Route::get('/attendences', [ApiController::class, 'getAttendences']);
    Route::post('/attendences', [ApiController::class, 'storeAttendence']);

    Route::get('/commits', [ApiController::class, 'getCommits']);
    Route::post('/commits', [ApiController::class, 'storeCommit']);

    Route::get('/meetings', [ApiController::class, 'getMeetings']);
    Route::post('/meetings', [ApiController::class, 'storeMeeting']);

    Route::post('/reports/generate', [ApiController::class, 'generateReport']);
    
    Route::get('/health', [ApiController::class, 'healthCheck']);
});
