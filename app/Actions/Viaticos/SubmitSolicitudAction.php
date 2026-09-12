<?php

namespace App\Actions\Viaticos;

use App\Enums\DocumentSignatureAction;
use App\Enums\SolicitudMovimientoAction;
use App\Enums\SolicitudViaticoStatus;
use App\Events\DocumentSigned;
use App\Events\SolicitudSubmitted;
use App\Exceptions\InvalidSolicitudTransitionException;
use App\Models\SolicitudViatico;
use App\Models\User;
use App\Services\AuditService;
use App\Services\DocumentSignatureService;
use App\Services\DocumentSnapshotService;
use Illuminate\Support\Facades\DB;

class SubmitSolicitudAction
{
    public function __construct(
        private DocumentSnapshotService $snapshotService,
        private DocumentSignatureService $signatureService,
        private AuditService $auditService,
    ) {}

    public function handle(
        SolicitudViatico $solicitud,
        User $user,
        string $password,
        ?string $ipAddress,
        ?string $userAgent,
    ): SolicitudViatico {
        return DB::transaction(function () use ($solicitud, $user, $password, $ipAddress, $userAgent): SolicitudViatico {
            $lockedSolicitud = SolicitudViatico::query()
                ->with('user')
                ->whereKey($solicitud->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedSolicitud->status !== SolicitudViaticoStatus::Borrador) {
                throw new InvalidSolicitudTransitionException;
            }

            $hasPreviousSubmission = $lockedSolicitud->firmas()
                ->where('action', DocumentSignatureAction::Envio->value)
                ->exists();

            if ($hasPreviousSubmission) {
                $lockedSolicitud->version++;
            }

            $signature = $this->signatureService->sign(
                $lockedSolicitud,
                $user,
                DocumentSignatureAction::Envio,
                $this->snapshotService->solicitud($lockedSolicitud),
                $password,
                $ipAddress,
                $userAgent,
            );

            $lockedSolicitud->status = SolicitudViaticoStatus::EnRevision;
            $lockedSolicitud->submitted_at = now();
            $lockedSolicitud->motivo_rechazo = null;
            $lockedSolicitud->save();

            $this->auditService->recordSolicitudMovement(
                $lockedSolicitud,
                $user,
                SolicitudMovimientoAction::Signed,
                SolicitudViaticoStatus::Borrador,
                SolicitudViaticoStatus::Borrador,
                metadata: ['signature_id' => $signature->id],
            );
            $this->auditService->recordSolicitudMovement(
                $lockedSolicitud,
                $user,
                SolicitudMovimientoAction::Submitted,
                SolicitudViaticoStatus::Borrador,
                SolicitudViaticoStatus::EnRevision,
            );

            DocumentSigned::dispatch($signature);
            SolicitudSubmitted::dispatch($lockedSolicitud);

            return $lockedSolicitud->load('user', 'approvedBy');
        });
    }
}
