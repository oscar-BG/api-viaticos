<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolicitudViaticoResource extends JsonResource
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
            'colaborador' => new UserResource($this->whenLoaded('user')),
            'area' => $this->area,
            'puesto' => $this->puesto,
            'lugar' => $this->lugar,
            'motivo' => $this->motivo,
            'fecha_inicio' => $this->fecha_inicio->toDateString(),
            'fecha_fin' => $this->fecha_fin->toDateString(),
            'importe_solicitado' => $this->importe_solicitado,
            'importe_autorizado' => $this->importe_autorizado,
            'status' => $this->status->value,
            'version' => $this->version,
            'motivo_rechazo' => $this->motivo_rechazo,
            'submitted_at' => $this->submitted_at?->toISOString(),
            'approved_at' => $this->approved_at?->toISOString(),
            'approved_by' => new UserResource($this->whenLoaded('approvedBy')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'movimientos' => SolicitudMovimientoResource::collection($this->whenLoaded('movimientos')),
            'firmas' => DocumentSignatureResource::collection($this->whenLoaded('firmas')),
        ];
    }
}
