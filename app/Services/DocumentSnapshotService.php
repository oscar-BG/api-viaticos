<?php

namespace App\Services;

use App\Models\SolicitudViatico;

class DocumentSnapshotService
{
    /**
     * @return array{
     *     document_type: string,
     *     id: int,
     *     colaborador: array{id: int, nombre: string, employee_code: string|null},
     *     area: string|null,
     *     puesto: string|null,
     *     lugar: string,
     *     motivo: string,
     *     fecha_inicio: string,
     *     fecha_fin: string,
     *     importe_solicitado: string,
     *     importe_autorizado: string|null,
     *     version: int
     * }
     */
    public function solicitud(SolicitudViatico $solicitud): array
    {
        $solicitud->loadMissing('user');

        return [
            'document_type' => 'SOLICITUD_VIATICO',
            'id' => $solicitud->id,
            'colaborador' => [
                'id' => $solicitud->user->id,
                'nombre' => $solicitud->user->name,
                'employee_code' => $solicitud->user->employee_code,
            ],
            'area' => $solicitud->area,
            'puesto' => $solicitud->puesto,
            'lugar' => $solicitud->lugar,
            'motivo' => $solicitud->motivo,
            'fecha_inicio' => $solicitud->fecha_inicio->toDateString(),
            'fecha_fin' => $solicitud->fecha_fin->toDateString(),
            'importe_solicitado' => $solicitud->importe_solicitado,
            'importe_autorizado' => $solicitud->importe_autorizado,
            'version' => $solicitud->version,
        ];
    }
}
