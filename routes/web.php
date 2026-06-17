<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\ManagerAgentController;

Route::get('/health', HealthCheckController::class);
Route::get('/', [ManagerAgentController::class, 'dashboard']);
Route::get('/employees', [ManagerAgentController::class, 'employees']);
Route::post('/employees', [ManagerAgentController::class, 'storeEmployee']);
Route::put('/employees/{id}', [ManagerAgentController::class, 'updateEmployee']);
Route::delete('/employees/{id}', [ManagerAgentController::class, 'deleteEmployee']);

Route::get('/export-data', [ManagerAgentController::class, 'exportData']);
Route::post('/import-data', [ManagerAgentController::class, 'importData']);
Route::get('/tasks', [ManagerAgentController::class, 'tasks']);
Route::post('/tasks', [ManagerAgentController::class, 'storeTask']);
Route::put('/tasks/{id}', [ManagerAgentController::class, 'updateTask']);
Route::delete('/tasks/{id}', [ManagerAgentController::class, 'deleteTask']);
Route::get('/attendence', [ManagerAgentController::class, 'attendence']);
Route::post('/attendence', [ManagerAgentController::class, 'storeAttendence']);
Route::put('/attendence/{id}', [ManagerAgentController::class, 'updateAttendence']);
Route::delete('/attendence/{id}', [ManagerAgentController::class, 'deleteAttendence']);
Route::get('/commits', [ManagerAgentController::class, 'commits']);
Route::post('/commits', [ManagerAgentController::class, 'storeCommit']);
Route::put('/commits/{id}', [ManagerAgentController::class, 'updateCommit']);
Route::delete('/commits/{id}', [ManagerAgentController::class, 'deleteCommit']);
Route::get('/meetings', [ManagerAgentController::class, 'meetings']);
Route::post('/meetings', [ManagerAgentController::class, 'storeMeeting']);
Route::get('/meetings/{id}', [ManagerAgentController::class, 'showMeeting']);
Route::put('/meetings/{id}', [ManagerAgentController::class, 'updateMeeting']);
Route::delete('/meetings/{id}', [ManagerAgentController::class, 'deleteMeeting']);

Route::post('/reports/preview', [ManagerAgentController::class, 'previewReport']);
Route::post('/generate-report', [ManagerAgentController::class, 'generate']);
Route::get('/reports', [ManagerAgentController::class, 'reports']);
Route::get('/reports/{id}', [ManagerAgentController::class, 'showReport']);
Route::get('/reports/{id}/export', [ManagerAgentController::class, 'exportReport']);

use App\Http\Controllers\DeveloperToolsController;
Route::get('/developer-tools', [DeveloperToolsController::class, 'index']);
Route::post('/developer-tools/tokens', [DeveloperToolsController::class, 'store']);
Route::post('/developer-tools/tokens/{id}/toggle', [DeveloperToolsController::class, 'toggle']);
Route::delete('/developer-tools/tokens/{id}', [DeveloperToolsController::class, 'destroy']);
