<?php

namespace App\Actions\Viaticos;

use App\Enums\SolicitudMovimientoAction;
use App\Enums\SolicitudViaticoStatus;
use App\Models\SolicitudViatico;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

class CreateSolicitudAction
{
    public function __construct(private AuditService $auditService) {}

    /** @param array{lugar: string, motivo: string, fecha_inicio: string, fecha_fin: string, importe_solicitado: mixed} $data */
    public function handle(User $user, array $data): SolicitudViatico
    {
        return DB::transaction(function () use ($user, $data): SolicitudViatico {
            $solicitud = new SolicitudViatico($data);
            $solicitud->user()->associate($user);
            $solicitud->area = $user->area;
            $solicitud->puesto = $user->puesto;
            $solicitud->save();

            $this->auditService->recordSolicitudMovement(
                $solicitud,
                $user,
                SolicitudMovimientoAction::Created,
                null,
                SolicitudViaticoStatus::Borrador,
            );

            return $solicitud->load('user', 'approvedBy');
        });
    }
}
