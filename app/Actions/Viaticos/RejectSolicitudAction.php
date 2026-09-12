<?php

namespace App\Actions\Viaticos;

use App\Enums\SolicitudMovimientoAction;
use App\Enums\SolicitudViaticoStatus;
use App\Events\SolicitudRejected;
use App\Exceptions\InvalidSolicitudTransitionException;
use App\Models\SolicitudViatico;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

class RejectSolicitudAction
{
    public function __construct(private AuditService $auditService) {}

    public function handle(SolicitudViatico $solicitud, User $finanzas, string $motivo): SolicitudViatico
    {
        return DB::transaction(function () use ($solicitud, $finanzas, $motivo): SolicitudViatico {
            $lockedSolicitud = SolicitudViatico::query()
                ->whereKey($solicitud->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedSolicitud->status !== SolicitudViaticoStatus::EnRevision) {
                throw new InvalidSolicitudTransitionException;
            }

            $lockedSolicitud->status = SolicitudViaticoStatus::Rechazada;
            $lockedSolicitud->motivo_rechazo = $motivo;
            $lockedSolicitud->importe_autorizado = null;
            $lockedSolicitud->approved_by = null;
            $lockedSolicitud->approved_at = null;
            $lockedSolicitud->save();

            $this->auditService->recordSolicitudMovement(
                $lockedSolicitud,
                $finanzas,
                SolicitudMovimientoAction::Rejected,
                SolicitudViaticoStatus::EnRevision,
                SolicitudViaticoStatus::Rechazada,
                comment: $motivo,
            );

            SolicitudRejected::dispatch($lockedSolicitud);

            return $lockedSolicitud->load('user', 'approvedBy');
        });
    }
}
