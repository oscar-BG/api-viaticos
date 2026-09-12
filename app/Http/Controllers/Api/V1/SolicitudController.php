<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Viaticos\CreateSolicitudAction;
use App\Actions\Viaticos\UpdateSolicitudAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSolicitudRequest;
use App\Http\Requests\Api\V1\UpdateSolicitudRequest;
use App\Http\Resources\SolicitudViaticoResource;
use App\Models\SolicitudViatico;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class SolicitudController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', SolicitudViatico::class);

        /** @var User $user */
        $user = $request->user();

        return SolicitudViaticoResource::collection(
            SolicitudViatico::query()
                ->whereBelongsTo($user)
                ->with('user', 'approvedBy')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get(),
        )->additional(['message' => 'Solicitudes obtenidas correctamente.']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSolicitudRequest $request, CreateSolicitudAction $createSolicitud): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $solicitud = $createSolicitud->handle($user, $request->validated());

        return (new SolicitudViaticoResource($solicitud))
            ->additional(['message' => 'Solicitud creada correctamente.'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(SolicitudViatico $solicitud): SolicitudViaticoResource
    {
        Gate::authorize('view', $solicitud);

        return (new SolicitudViaticoResource($solicitud->load('user', 'approvedBy')))
            ->additional(['message' => 'Solicitud obtenida correctamente.']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateSolicitudRequest $request,
        SolicitudViatico $solicitud,
        UpdateSolicitudAction $updateSolicitud,
    ): SolicitudViaticoResource {
        /** @var User $user */
        $user = $request->user();

        return (new SolicitudViaticoResource(
            $updateSolicitud->handle($solicitud, $user, $request->validated()),
        ))->additional(['message' => 'Solicitud actualizada correctamente.']);
    }
}
