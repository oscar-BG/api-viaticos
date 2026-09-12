<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Viaticos\SubmitSolicitudAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SubmitSolicitudRequest;
use App\Http\Resources\SolicitudViaticoResource;
use App\Models\SolicitudViatico;
use App\Models\User;

class SolicitudSubmissionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        SubmitSolicitudRequest $request,
        SolicitudViatico $solicitud,
        SubmitSolicitudAction $submitSolicitud,
    ): SolicitudViaticoResource {
        /** @var User $user */
        $user = $request->user();
        $submittedSolicitud = $submitSolicitud->handle(
            $solicitud,
            $user,
            $request->string('password')->toString(),
            $request->ip(),
            $request->userAgent(),
        );

        return (new SolicitudViaticoResource($submittedSolicitud))
            ->additional(['message' => 'Solicitud enviada a revisión correctamente.']);
    }
}
