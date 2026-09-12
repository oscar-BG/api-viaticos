<?php

namespace App\Actions\Viaticos;

use App\Enums\SolicitudMovimientoAction;
use App\Enums\SolicitudViaticoStatus;
use App\Exceptions\InvalidSolicitudTransitionException;
use App\Models\SolicitudViatico;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

class UpdateSolicitudAction
{
    public function __construct(private AuditService $auditService) {}

    /** @param array{lugar?: string, motivo?: string, fecha_inicio?: string, fecha_fin?: string, importe_solicitado?: mixed} $data */
    public function handle(SolicitudViatico $solicitud, User $user, array $data): SolicitudViatico
    {
        return DB::transaction(function () use ($solicitud, $user, $data): SolicitudViatico {
            $lockedSolicitud = SolicitudViatico::query()
                ->whereKey($solicitud->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($lockedSolicitud->status, [
                SolicitudViaticoStatus::Borrador,
                SolicitudViaticoStatus::Rechazada,
            ], true)) {
                throw new InvalidSolicitudTransitionException;
            }

            $fromStatus = $lockedSolicitud->status;
            $lockedSolicitud->fill($data);

            if ($fromStatus === SolicitudViaticoStatus::Rechazada) {
                $lockedSolicitud->status = SolicitudViaticoStatus::Borrador;
                $lockedSolicitud->motivo_rechazo = null;
                $lockedSolicitud->submitted_at = null;
            }

            $lockedSolicitud->save();

            $this->auditService->recordSolicitudMovement(
                $lockedSolicitud,
                $user,
                SolicitudMovimientoAction::Updated,
                $fromStatus,
                $lockedSolicitud->status,
            );

            return $lockedSolicitud->load('user', 'approvedBy');
        });
    }
}
