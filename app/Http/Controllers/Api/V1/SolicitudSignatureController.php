<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentSignatureResource;
use App\Models\SolicitudViatico;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class SolicitudSignatureController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(SolicitudViatico $solicitud): AnonymousResourceCollection
    {
        Gate::authorize('viewAudit', $solicitud);

        return DocumentSignatureResource::collection(
            $solicitud->firmas()->with('user')->oldest('id')->get(),
        )->additional(['message' => 'Firmas obtenidas correctamente.']);
    }
}
