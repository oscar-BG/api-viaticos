<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role->value,
            'employee_code' => $this->employee_code,
            'area' => $this->area,
            'puesto' => $this->puesto,
            'nivel_jerarquico' => $this->whenLoaded('nivelJerarquico', fn (): ?array => $this->nivelJerarquico ? [
                'id' => $this->nivelJerarquico->id,
                'nombre' => $this->nivelJerarquico->nombre,
                'orden' => $this->nivelJerarquico->orden,
            ] : null),
            'is_active' => $this->is_active,
        ];
    }
}
