<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\ManagerAgentController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\AIChatController;
use App\Http\Controllers\DeveloperToolsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LeadershipInsightsController;

// Public Health Check
Route::get('/health', HealthCheckController::class);

// Guest Routes (Login / Register)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'loginPost'])->name('login.post');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'registerPost'])->name('register.post');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [ManagerAgentController::class, 'dashboard']);
    
    Route::get('/employees/search', [TeamController::class, 'searchEmployees']);
    Route::get('/teams', [TeamController::class, 'index']);
    Route::post('/teams', [TeamController::class, 'store']);
    Route::get('/teams/{id}', [TeamController::class, 'show']);
    Route::put('/teams/{id}', [TeamController::class, 'update']);
    Route::delete('/teams/{id}', [TeamController::class, 'destroy']);
    Route::get('/teams/{id}/members', [TeamController::class, 'members']);

    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);
    Route::put('/projects/{id}', [ProjectController::class, 'update']);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
    
    Route::get('/employees', [ManagerAgentController::class, 'employees']);
    Route::post('/employees', [ManagerAgentController::class, 'storeEmployee']);
    Route::put('/employees/{id}', [ManagerAgentController::class, 'updateEmployee']);
    Route::delete('/employees/{id}', [ManagerAgentController::class, 'deleteEmployee']);

    Route::post('/ai-chat/send', [AIChatController::class, 'send']);
    Route::get('/ai-chat/history', [AIChatController::class, 'history']);
    Route::post('/ai-chat/clear', [AIChatController::class, 'clear']);

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
    Route::post('/commits/{id}/insight', [ManagerAgentController::class, 'generateCommitInsight']);
    
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

    Route::get('/leadership-insights', [LeadershipInsightsController::class, 'index']);

    Route::get('/developer-tools', [DeveloperToolsController::class, 'index']);
    Route::post('/developer-tools/tokens', [DeveloperToolsController::class, 'store']);
    Route::post('/developer-tools/tokens/{id}/toggle', [DeveloperToolsController::class, 'toggle']);
    Route::delete('/developer-tools/tokens/{id}', [DeveloperToolsController::class, 'destroy']);

    Route::get('/developer-tools/gitlab/test', [DeveloperToolsController::class, 'testGitLabConnection']);
    Route::get('/developer-tools/gitlab/projects', [DeveloperToolsController::class, 'getGitLabProjects']);
    Route::post('/developer-tools/gitlab/sync', [DeveloperToolsController::class, 'syncGitLabCommits']);
});
