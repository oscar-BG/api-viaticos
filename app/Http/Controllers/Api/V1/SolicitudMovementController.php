<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SolicitudMovimientoResource;
use App\Models\SolicitudViatico;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class SolicitudMovementController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(SolicitudViatico $solicitud): AnonymousResourceCollection
    {
        Gate::authorize('viewAudit', $solicitud);

        return SolicitudMovimientoResource::collection(
            $solicitud->movimientos()->with('user')->oldest('id')->get(),
        )->additional(['message' => 'Movimientos obtenidos correctamente.']);
    }
}
