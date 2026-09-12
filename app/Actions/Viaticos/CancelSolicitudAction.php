<?php

namespace App\Actions\Viaticos;

use App\Enums\SolicitudMovimientoAction;
use App\Enums\SolicitudViaticoStatus;
use App\Exceptions\InvalidSolicitudTransitionException;
use App\Models\SolicitudViatico;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

class CancelSolicitudAction
{
    public function __construct(private AuditService $auditService) {}

    public function handle(SolicitudViatico $solicitud, User $user): SolicitudViatico
    {
        return DB::transaction(function () use ($solicitud, $user): SolicitudViatico {
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
            $lockedSolicitud->status = SolicitudViaticoStatus::Cancelada;
            $lockedSolicitud->save();

            $this->auditService->recordSolicitudMovement(
                $lockedSolicitud,
                $user,
                SolicitudMovimientoAction::Cancelled,
                $fromStatus,
                SolicitudViaticoStatus::Cancelada,
            );

            return $lockedSolicitud->load('user', 'approvedBy');
        });
    }
}
