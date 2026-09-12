<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Viaticos\RejectSolicitudAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RejectSolicitudRequest;
use App\Http\Resources\SolicitudViaticoResource;
use App\Models\SolicitudViatico;
use App\Models\User;

class FinanzasSolicitudRejectionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        RejectSolicitudRequest $request,
        SolicitudViatico $solicitud,
        RejectSolicitudAction $rejectSolicitud,
    ): SolicitudViaticoResource {
        /** @var User $finanzas */
        $finanzas = $request->user();
        $rejectedSolicitud = $rejectSolicitud->handle(
            $solicitud,
            $finanzas,
            $request->string('motivo')->toString(),
        );

        return (new SolicitudViaticoResource($rejectedSolicitud))
            ->additional(['message' => 'Solicitud rechazada correctamente.']);
    }
}
