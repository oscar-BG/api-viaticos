<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SolicitudViaticoResource;
use App\Models\SolicitudViatico;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class FinanzasSolicitudController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAnyForFinance', SolicitudViatico::class);

        return SolicitudViaticoResource::collection(
            SolicitudViatico::query()
                ->with('user', 'approvedBy')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get(),
        )->additional(['message' => 'Solicitudes para revisión obtenidas correctamente.']);
    }

    public function show(SolicitudViatico $solicitud): SolicitudViaticoResource
    {
        Gate::authorize('view', $solicitud);

        return (new SolicitudViaticoResource(
            $solicitud->load('user', 'approvedBy', 'movimientos.user', 'firmas.user'),
        ))->additional(['message' => 'Solicitud obtenida correctamente.']);
    }
}
