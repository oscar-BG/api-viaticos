<?php

namespace App\Services;

use App\Enums\SolicitudMovimientoAction;
use App\Enums\SolicitudViaticoStatus;
use App\Models\SolicitudMovimiento;
use App\Models\SolicitudViatico;
use App\Models\User;

class AuditService
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function recordSolicitudMovement(
        SolicitudViatico $solicitud,
        User $user,
        SolicitudMovimientoAction $action,
        ?SolicitudViaticoStatus $fromStatus,
        ?SolicitudViaticoStatus $toStatus,
        ?string $comment = null,
        ?array $metadata = null,
    ): SolicitudMovimiento {
        return $solicitud->movimientos()->create([
            'user_id' => $user->id,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'comment' => $comment,
            'metadata' => $metadata,
        ]);
    }
}
