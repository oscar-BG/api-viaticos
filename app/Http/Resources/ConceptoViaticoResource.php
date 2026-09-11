<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConceptoViaticoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'clave' => $this->clave->value,
            'nombre' => $this->nombre,
            'unidad' => $this->unidad->value,
            'tiene_limite' => $this->tiene_limite,
            'diferencia_tipo_comprobante' => $this->diferencia_tipo_comprobante,
        ];
    }
}
