<?php

namespace App\Http\Resources;

use App\Models\ConceptoViatico;
use App\Models\TipoComprobanteViatico;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LimiteViaticoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ConceptoViatico $concepto */
        $concepto = $this->resource['concepto'];
        /** @var TipoComprobanteViatico|null $tipoComprobante */
        $tipoComprobante = $this->resource['tipo_comprobante'];

        return [
            'concepto' => new ConceptoViaticoResource($concepto),
            'tipo_comprobante' => $tipoComprobante
                ? new TipoComprobanteViaticoResource($tipoComprobante)
                : null,
            'limite_aplicado' => $this->resource['limite_aplicado'],
            'excedente' => $this->resource['excedente'],
        ];
    }
}
