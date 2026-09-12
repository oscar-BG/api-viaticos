<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Viaticos\ApproveSolicitudAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ApproveSolicitudRequest;
use App\Http\Resources\SolicitudViaticoResource;
use App\Models\SolicitudViatico;
use App\Models\User;

class FinanzasSolicitudApprovalController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        ApproveSolicitudRequest $request,
        SolicitudViatico $solicitud,
        ApproveSolicitudAction $approveSolicitud,
    ): SolicitudViaticoResource {
        /** @var User $finanzas */
        $finanzas = $request->user();
        $approvedSolicitud = $approveSolicitud->handle(
            $solicitud,
            $finanzas,
            $request->string('importe_autorizado')->toString(),
            $request->string('password')->toString(),
            $request->ip(),
            $request->userAgent(),
        );

        return (new SolicitudViaticoResource($approvedSolicitud))
            ->additional(['message' => 'Solicitud aprobada correctamente.']);
    }
}
