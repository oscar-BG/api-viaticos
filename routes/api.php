<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogoController;
use App\Http\Controllers\Api\V1\MisLimitesController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('auth.login');

    Route::middleware(['auth:sanctum', 'active-user'])->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');

        Route::prefix('catalogos')->name('catalogos.')->group(function (): void {
            Route::get('conceptos', [CatalogoController::class, 'conceptos'])->name('conceptos');
            Route::get('tipos-comprobante', [CatalogoController::class, 'tiposComprobante'])
                ->name('tipos-comprobante');
            Route::get('mis-limites', MisLimitesController::class)->name('mis-limites');
        });
    });
});
