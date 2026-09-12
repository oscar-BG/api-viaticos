<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Viaticos\CancelSolicitudAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\SolicitudViaticoResource;
use App\Models\SolicitudViatico;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SolicitudCancellationController extends Controller
{
    public function __invoke(
        Request $request,
        SolicitudViatico $solicitud,
        CancelSolicitudAction $cancelSolicitud,
    ): SolicitudViaticoResource {
        Gate::authorize('cancel', $solicitud);

        /** @var User $user */
        $user = $request->user();

        return (new SolicitudViaticoResource($cancelSolicitud->handle($solicitud, $user)))
            ->additional(['message' => 'Solicitud cancelada correctamente.']);
    }
}
