<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogoController;
use App\Http\Controllers\Api\V1\FinanzasSolicitudApprovalController;
use App\Http\Controllers\Api\V1\FinanzasSolicitudController;
use App\Http\Controllers\Api\V1\FinanzasSolicitudRejectionController;
use App\Http\Controllers\Api\V1\MisLimitesController;
use App\Http\Controllers\Api\V1\SolicitudCancellationController;
use App\Http\Controllers\Api\V1\SolicitudController;
use App\Http\Controllers\Api\V1\SolicitudMovementController;
use App\Http\Controllers\Api\V1\SolicitudSignatureController;
use App\Http\Controllers\Api\V1\SolicitudSubmissionController;
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

        Route::prefix('solicitudes')->name('solicitudes.')->group(function (): void {
            Route::get('/', [SolicitudController::class, 'index'])->name('index');
            Route::post('/', [SolicitudController::class, 'store'])->name('store');
            Route::get('{solicitud}', [SolicitudController::class, 'show'])->name('show');
            Route::patch('{solicitud}', [SolicitudController::class, 'update'])->name('update');
            Route::post('{solicitud}/enviar', SolicitudSubmissionController::class)
                ->middleware('throttle:signature')
                ->name('enviar');
            Route::post('{solicitud}/cancelar', SolicitudCancellationController::class)->name('cancelar');
            Route::get('{solicitud}/movimientos', SolicitudMovementController::class)->name('movimientos');
            Route::get('{solicitud}/firmas', SolicitudSignatureController::class)->name('firmas');
        });

        Route::prefix('finanzas/solicitudes')->name('finanzas.solicitudes.')->group(function (): void {
            Route::get('/', [FinanzasSolicitudController::class, 'index'])->name('index');
            Route::get('{solicitud}', [FinanzasSolicitudController::class, 'show'])->name('show');
            Route::post('{solicitud}/aprobar', FinanzasSolicitudApprovalController::class)
                ->middleware('throttle:signature')
                ->name('aprobar');
            Route::post('{solicitud}/rechazar', FinanzasSolicitudRejectionController::class)->name('rechazar');
        });
    });
});
