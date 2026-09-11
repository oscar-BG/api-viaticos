<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConceptoViaticoResource;
use App\Http\Resources\TipoComprobanteViaticoResource;
use App\Models\ConceptoViatico;
use App\Models\TipoComprobanteViatico;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CatalogoController extends Controller
{
    public function conceptos(): AnonymousResourceCollection
    {
        return ConceptoViaticoResource::collection(
            ConceptoViatico::query()->active()->oldest('id')->get(),
        )->additional(['message' => 'Conceptos obtenidos correctamente.']);
    }

    public function tiposComprobante(): AnonymousResourceCollection
    {
        return TipoComprobanteViaticoResource::collection(
            TipoComprobanteViatico::query()->active()->oldest('id')->get(),
        )->additional(['message' => 'Tipos de comprobante obtenidos correctamente.']);
    }
}
