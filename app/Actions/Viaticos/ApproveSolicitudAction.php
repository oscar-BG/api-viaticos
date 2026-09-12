<?php

namespace App\Actions\Viaticos;

use App\Enums\DocumentSignatureAction;
use App\Enums\SolicitudMovimientoAction;
use App\Enums\SolicitudViaticoStatus;
use App\Events\DocumentSigned;
use App\Events\SolicitudApproved;
use App\Exceptions\InvalidSolicitudTransitionException;
use App\Models\SolicitudViatico;
use App\Models\User;
use App\Services\AuditService;
use App\Services\DocumentSignatureService;
use App\Services\DocumentSnapshotService;
use Illuminate\Support\Facades\DB;

class ApproveSolicitudAction
{
    public function __construct(
        private DocumentSnapshotService $snapshotService,
        private DocumentSignatureService $signatureService,
        private AuditService $auditService,
    ) {}

    public function handle(
        SolicitudViatico $solicitud,
        User $finanzas,
        string $importeAutorizado,
        string $password,
        ?string $ipAddress,
        ?string $userAgent,
    ): SolicitudViatico {
        return DB::transaction(function () use (
            $solicitud,
            $finanzas,
            $importeAutorizado,
            $password,
            $ipAddress,
            $userAgent,
        ): SolicitudViatico {
            $lockedSolicitud = SolicitudViatico::query()
                ->with('user')
                ->whereKey($solicitud->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedSolicitud->status !== SolicitudViaticoStatus::EnRevision) {
                throw new InvalidSolicitudTransitionException;
            }

            $lockedSolicitud->importe_autorizado = $importeAutorizado;
            $signature = $this->signatureService->sign(
                $lockedSolicitud,
                $finanzas,
                DocumentSignatureAction::Aprobacion,
                $this->snapshotService->solicitud($lockedSolicitud),
                $password,
                $ipAddress,
                $userAgent,
            );

            $lockedSolicitud->status = SolicitudViaticoStatus::Aprobada;
            $lockedSolicitud->approved_by = $finanzas->id;
            $lockedSolicitud->approved_at = now();
            $lockedSolicitud->motivo_rechazo = null;
            $lockedSolicitud->save();

            $this->auditService->recordSolicitudMovement(
                $lockedSolicitud,
                $finanzas,
                SolicitudMovimientoAction::Signed,
                SolicitudViaticoStatus::EnRevision,
                SolicitudViaticoStatus::EnRevision,
                metadata: ['signature_id' => $signature->id],
            );
            $this->auditService->recordSolicitudMovement(
                $lockedSolicitud,
                $finanzas,
                SolicitudMovimientoAction::Approved,
                SolicitudViaticoStatus::EnRevision,
                SolicitudViaticoStatus::Aprobada,
                metadata: ['importe_autorizado' => $importeAutorizado],
            );

            DocumentSigned::dispatch($signature);
            SolicitudApproved::dispatch($lockedSolicitud);

            return $lockedSolicitud->load('user', 'approvedBy');
        });
    }
}
