<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

/*
|--------------------------------------------------------------------------
| Management API Routes
|--------------------------------------------------------------------------
|
| These routes are used for remote application management and deployment.
| All routes require authentication via management secret key.
|
*/

Route::prefix('management')->group(function (): void {
    Route::post('/deploy', [ManagementController::class, 'deploy']);
    Route::post('/stop', [ManagementController::class, 'stop']);
    Route::post('/start', [ManagementController::class, 'start']);
    Route::post('/custom-script', [ManagementController::class, 'customScript']);
    Route::get('/status', [ManagementController::class, 'status']);
});
