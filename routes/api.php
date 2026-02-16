<?php

use App\Http\Controllers\Api\LetterController as ApiLetterController;
use App\Http\Controllers\Api\TaskController as ApiTaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (for future mobile / external; RTL-aware responses)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('letters', [ApiLetterController::class, 'index'])->name('api.letters.index');
    Route::get('letters/{letter}', [ApiLetterController::class, 'show'])->name('api.letters.show');
    Route::get('tasks', [ApiTaskController::class, 'index'])->name('api.tasks.index');
    Route::get('tasks/{task}', [ApiTaskController::class, 'show'])->name('api.tasks.show');
});
