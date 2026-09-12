<?php

use App\Enums\DocumentSignatureAction;
use App\Enums\SolicitudMovimientoAction;
use App\Enums\SolicitudViaticoStatus;
use App\Models\DocumentSignature;
use App\Models\SolicitudMovimiento;
use App\Models\SolicitudViatico;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    config()->set('viaticos.signature_secret', 'test-signature-secret');
});

function validSolicitudPayload(array $overrides = []): array
{
    return array_merge([
        'lugar' => 'Monterrey, Nuevo León',
        'motivo' => 'Visita operativa a la sucursal.',
        'fecha_inicio' => '2026-10-15',
        'fecha_fin' => '2026-10-17',
        'importe_solicitado' => '1250.50',
    ], $overrides);
}

it('creates a draft request using server-side collaborator snapshots', function () {
    $colaborador = User::factory()->colaborador()->create([
        'area' => 'Operaciones',
        'puesto' => 'Supervisor',
    ]);
    $otherUser = User::factory()->create();
    Sanctum::actingAs($colaborador);

    $response = $this->postJson('/api/v1/solicitudes', validSolicitudPayload([
        'user_id' => $otherUser->id,
        'area' => 'Área manipulada',
        'status' => SolicitudViaticoStatus::Aprobada->value,
        'importe_autorizado' => '9999.99',
    ]));

    $response
        ->assertCreated()
        ->assertJsonPath('data.colaborador.id', $colaborador->id)
        ->assertJsonPath('data.area', 'Operaciones')
        ->assertJsonPath('data.puesto', 'Supervisor')
        ->assertJsonPath('data.status', SolicitudViaticoStatus::Borrador->value)
        ->assertJsonPath('data.importe_autorizado', null)
        ->assertJsonPath('message', 'Solicitud creada correctamente.');
    $this->assertDatabaseHas('solicitudes_viaticos', [
        'user_id' => $colaborador->id,
        'area' => 'Operaciones',
        'puesto' => 'Supervisor',
        'status' => SolicitudViaticoStatus::Borrador->value,
        'importe_autorizado' => null,
    ]);
    $this->assertDatabaseHas('solicitud_movimientos', [
        'user_id' => $colaborador->id,
        'action' => SolicitudMovimientoAction::Created->value,
        'to_status' => SolicitudViaticoStatus::Borrador->value,
    ]);
});

it('returns 422 when a request is incomplete', function () {
    Sanctum::actingAs(User::factory()->colaborador()->create());

    $this->postJson('/api/v1/solicitudes')
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'lugar' => 'El lugar es obligatorio.',
            'motivo' => 'El motivo es obligatorio.',
            'fecha_inicio' => 'La fecha de inicio es obligatoria.',
            'fecha_fin' => 'La fecha de fin es obligatoria.',
            'importe_solicitado' => 'El importe solicitado es obligatorio.',
        ]);
    $this->assertDatabaseCount('solicitudes_viaticos', 0);
});

it('returns 422 when the end date precedes the start date or the amount is not positive', function () {
    Sanctum::actingAs(User::factory()->colaborador()->create());

    $this->postJson('/api/v1/solicitudes', validSolicitudPayload([
        'fecha_fin' => '2026-10-14',
        'importe_solicitado' => '0.00',
    ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'fecha_fin' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'importe_solicitado' => 'El importe solicitado debe ser mayor que cero.',
        ]);
    $this->assertDatabaseCount('solicitudes_viaticos', 0);
});

it('returns 403 when finance attempts to create a collaborator request', function () {
    Sanctum::actingAs(User::factory()->finanzas()->create());

    $this->postJson('/api/v1/solicitudes', validSolicitudPayload())->assertForbidden();
    $this->assertDatabaseCount('solicitudes_viaticos', 0);
});

it('lists only owned requests and returns 404 for another collaborator request', function () {
    $colaborador = User::factory()->colaborador()->create();
    $ownSolicitud = SolicitudViatico::factory()->for($colaborador)->create();
    $otherSolicitud = SolicitudViatico::factory()->create();
    Sanctum::actingAs($colaborador);

    $this->getJson('/api/v1/solicitudes')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $ownSolicitud->id);
    $this->getJson("/api/v1/solicitudes/{$otherSolicitud->id}")->assertNotFound();
});

it('allows finance to list and inspect requests from every collaborator', function () {
    $first = SolicitudViatico::factory()->create();
    $second = SolicitudViatico::factory()->create();
    Sanctum::actingAs(User::factory()->finanzas()->create());

    $this->getJson('/api/v1/finanzas/solicitudes')
        ->assertOk()
        ->assertJsonCount(2, 'data');
    $this->getJson("/api/v1/finanzas/solicitudes/{$second->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $second->id);
});

it('updates a draft without accepting protected attributes', function () {
    $colaborador = User::factory()->colaborador()->create();
    $solicitud = SolicitudViatico::factory()->for($colaborador)->create();
    Sanctum::actingAs($colaborador);

    $this->patchJson("/api/v1/solicitudes/{$solicitud->id}", [
        'lugar' => 'Guadalajara, Jalisco',
        'status' => SolicitudViaticoStatus::Aprobada->value,
        'importe_autorizado' => '8000.00',
    ])
        ->assertOk()
        ->assertJsonPath('data.lugar', 'Guadalajara, Jalisco')
        ->assertJsonPath('data.status', SolicitudViaticoStatus::Borrador->value)
        ->assertJsonPath('data.importe_autorizado', null);
    $this->assertDatabaseHas('solicitudes_viaticos', [
        'id' => $solicitud->id,
        'lugar' => 'Guadalajara, Jalisco',
        'status' => SolicitudViaticoStatus::Borrador->value,
    ]);
});

it('returns 403 when a collaborator attempts to edit a request under review', function () {
    $colaborador = User::factory()->colaborador()->create();
    $solicitud = SolicitudViatico::factory()->for($colaborador)->enRevision()->create();
    Sanctum::actingAs($colaborador);

    $this->patchJson("/api/v1/solicitudes/{$solicitud->id}", ['lugar' => 'Otro destino'])
        ->assertForbidden();
    $this->assertDatabaseMissing('solicitudes_viaticos', [
        'id' => $solicitud->id,
        'lugar' => 'Otro destino',
    ]);
});

it('signs and submits a draft in one auditable transaction', function () {
    $colaborador = User::factory()->colaborador()->create(['password' => 'secret-password']);
    $solicitud = SolicitudViatico::factory()->for($colaborador)->create();
    Sanctum::actingAs($colaborador);

    $response = $this->withHeader('User-Agent', 'Pest API Client')
        ->postJson("/api/v1/solicitudes/{$solicitud->id}/enviar", [
            'password' => 'secret-password',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.status', SolicitudViaticoStatus::EnRevision->value)
        ->assertJsonPath('data.version', 1)
        ->assertJsonPath('message', 'Solicitud enviada a revisión correctamente.');
    $this->assertDatabaseHas('solicitudes_viaticos', [
        'id' => $solicitud->id,
        'status' => SolicitudViaticoStatus::EnRevision->value,
        'version' => 1,
    ]);
    $this->assertDatabaseHas('document_signatures', [
        'signable_type' => SolicitudViatico::class,
        'signable_id' => $solicitud->id,
        'user_id' => $colaborador->id,
        'action' => DocumentSignatureAction::Envio->value,
        'version' => 1,
        'user_agent' => 'Pest API Client',
    ]);
    $signature = DocumentSignature::query()->firstOrFail();
    expect($signature->content_hash)->toHaveLength(64)
        ->and($signature->signature_hash)->toHaveLength(64)
        ->and($signature->payload_snapshot)->not->toHaveKey('password')
        ->and($signature->payload_snapshot['importe_autorizado'])->toBeNull();
    $this->assertDatabaseHas('solicitud_movimientos', [
        'solicitud_id' => $solicitud->id,
        'action' => SolicitudMovimientoAction::Submitted->value,
        'from_status' => SolicitudViaticoStatus::Borrador->value,
        'to_status' => SolicitudViaticoStatus::EnRevision->value,
    ]);
});

it('returns 422 and creates no signature when submission password is incorrect', function () {
    $colaborador = User::factory()->colaborador()->create(['password' => 'correct-password']);
    $solicitud = SolicitudViatico::factory()->for($colaborador)->create();
    Sanctum::actingAs($colaborador);

    $this->postJson("/api/v1/solicitudes/{$solicitud->id}/enviar", [
        'password' => 'incorrect-password',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'PASSWORD_CONFIRMACION_INVALIDO')
        ->assertJsonValidationErrors([
            'password' => 'La contraseña de confirmación no es válida.',
        ]);
    $this->assertDatabaseCount('document_signatures', 0);
    $this->assertDatabaseCount('solicitud_movimientos', 0);
    $this->assertDatabaseHas('solicitudes_viaticos', [
        'id' => $solicitud->id,
        'status' => SolicitudViaticoStatus::Borrador->value,
        'version' => 1,
    ]);
});

it('returns 500 and preserves the draft when the signature secret is missing', function () {
    config()->set('viaticos.signature_secret');
    $colaborador = User::factory()->colaborador()->create(['password' => 'secret-password']);
    $solicitud = SolicitudViatico::factory()->for($colaborador)->create();
    Sanctum::actingAs($colaborador);

    $this->postJson("/api/v1/solicitudes/{$solicitud->id}/enviar", [
        'password' => 'secret-password',
    ])
        ->assertInternalServerError()
        ->assertJsonPath('code', 'FIRMA_NO_CONFIGURADA');
    $this->assertDatabaseCount('document_signatures', 0);
    $this->assertDatabaseHas('solicitudes_viaticos', [
        'id' => $solicitud->id,
        'status' => SolicitudViaticoStatus::Borrador->value,
    ]);
});

it('returns 403 when finance attempts to approve a draft', function () {
    $solicitud = SolicitudViatico::factory()->create();
    $finanzas = User::factory()->finanzas()->create(['password' => 'finance-password']);
    Sanctum::actingAs($finanzas);

    $this->postJson("/api/v1/finanzas/solicitudes/{$solicitud->id}/aprobar", [
        'importe_autorizado' => '1000.00',
        'password' => 'finance-password',
    ])->assertForbidden();
    $this->assertDatabaseCount('document_signatures', 0);
});

it('returns 403 when a collaborator attempts to approve a request', function () {
    $colaborador = User::factory()->colaborador()->create(['password' => 'secret-password']);
    $solicitud = SolicitudViatico::factory()->for($colaborador)->enRevision()->create();
    Sanctum::actingAs($colaborador);

    $this->postJson("/api/v1/finanzas/solicitudes/{$solicitud->id}/aprobar", [
        'importe_autorizado' => '1000.00',
        'password' => 'secret-password',
    ])->assertForbidden();
    $this->assertDatabaseCount('document_signatures', 0);
});

it('approves and signs a request under review with the finance identity', function () {
    $solicitud = SolicitudViatico::factory()->enRevision()->create();
    $finanzas = User::factory()->finanzas()->create(['password' => 'finance-password']);
    Sanctum::actingAs($finanzas);

    $this->postJson("/api/v1/finanzas/solicitudes/{$solicitud->id}/aprobar", [
        'importe_autorizado' => '1100.25',
        'password' => 'finance-password',
    ])
        ->assertOk()
        ->assertJsonPath('data.status', SolicitudViaticoStatus::Aprobada->value)
        ->assertJsonPath('data.importe_autorizado', '1100.25')
        ->assertJsonPath('data.approved_by.id', $finanzas->id);
    $this->assertDatabaseHas('solicitudes_viaticos', [
        'id' => $solicitud->id,
        'status' => SolicitudViaticoStatus::Aprobada->value,
        'importe_autorizado' => 1100.25,
        'approved_by' => $finanzas->id,
    ]);
    $this->assertDatabaseHas('document_signatures', [
        'signable_id' => $solicitud->id,
        'user_id' => $finanzas->id,
        'action' => DocumentSignatureAction::Aprobacion->value,
        'version' => 1,
    ]);
    $signature = DocumentSignature::query()->firstOrFail();
    expect($signature->payload_snapshot['importe_autorizado'])->toBe('1100.25');
    $this->assertDatabaseHas('solicitud_movimientos', [
        'solicitud_id' => $solicitud->id,
        'action' => SolicitudMovimientoAction::Approved->value,
        'to_status' => SolicitudViaticoStatus::Aprobada->value,
    ]);
});

it('returns 422 and leaves a request under review when finance password is incorrect', function () {
    $solicitud = SolicitudViatico::factory()->enRevision()->create();
    $finanzas = User::factory()->finanzas()->create(['password' => 'correct-password']);
    Sanctum::actingAs($finanzas);

    $this->postJson("/api/v1/finanzas/solicitudes/{$solicitud->id}/aprobar", [
        'importe_autorizado' => '1000.00',
        'password' => 'incorrect-password',
    ])->assertUnprocessable();

    $this->assertDatabaseCount('document_signatures', 0);
    $this->assertDatabaseCount('solicitud_movimientos', 0);
    $this->assertDatabaseHas('solicitudes_viaticos', [
        'id' => $solicitud->id,
        'status' => SolicitudViaticoStatus::EnRevision->value,
        'importe_autorizado' => null,
        'approved_by' => null,
    ]);
});

it('returns 422 when finance rejects without a reason', function () {
    $solicitud = SolicitudViatico::factory()->enRevision()->create();
    Sanctum::actingAs(User::factory()->finanzas()->create());

    $this->postJson("/api/v1/finanzas/solicitudes/{$solicitud->id}/rechazar")
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'motivo' => 'El motivo de rechazo es obligatorio.',
        ]);
    $this->assertDatabaseHas('solicitudes_viaticos', [
        'id' => $solicitud->id,
        'status' => SolicitudViaticoStatus::EnRevision->value,
    ]);
});

it('rejects a request under review and records the reason', function () {
    $solicitud = SolicitudViatico::factory()->enRevision()->create();
    $finanzas = User::factory()->finanzas()->create();
    Sanctum::actingAs($finanzas);

    $this->postJson("/api/v1/finanzas/solicitudes/{$solicitud->id}/rechazar", [
        'motivo' => 'Falta justificar el destino del viaje.',
    ])
        ->assertOk()
        ->assertJsonPath('data.status', SolicitudViaticoStatus::Rechazada->value)
        ->assertJsonPath('data.motivo_rechazo', 'Falta justificar el destino del viaje.');
    $this->assertDatabaseHas('solicitud_movimientos', [
        'solicitud_id' => $solicitud->id,
        'user_id' => $finanzas->id,
        'action' => SolicitudMovimientoAction::Rejected->value,
        'from_status' => SolicitudViaticoStatus::EnRevision->value,
        'to_status' => SolicitudViaticoStatus::Rechazada->value,
        'comment' => 'Falta justificar el destino del viaje.',
    ]);
});

it('corrects and resubmits a rejected request as a new signed version', function () {
    $colaborador = User::factory()->colaborador()->create(['password' => 'secret-password']);
    $solicitud = SolicitudViatico::factory()->for($colaborador)->rechazada()->create();
    $solicitud->firmas()->save(DocumentSignature::factory()->make([
        'user_id' => $colaborador->id,
        'action' => DocumentSignatureAction::Envio,
        'version' => 1,
    ]));
    Sanctum::actingAs($colaborador);

    $this->patchJson("/api/v1/solicitudes/{$solicitud->id}", [
        'motivo' => 'Motivo corregido con mayor detalle.',
    ])
        ->assertOk()
        ->assertJsonPath('data.status', SolicitudViaticoStatus::Borrador->value)
        ->assertJsonPath('data.version', 1)
        ->assertJsonPath('data.motivo_rechazo', null);
    $this->postJson("/api/v1/solicitudes/{$solicitud->id}/enviar", [
        'password' => 'secret-password',
    ])
        ->assertOk()
        ->assertJsonPath('data.status', SolicitudViaticoStatus::EnRevision->value)
        ->assertJsonPath('data.version', 2);

    expect($solicitud->firmas()->where('action', DocumentSignatureAction::Envio->value)
        ->orderBy('version')->pluck('version')->all())->toBe([1, 2]);
    $this->assertDatabaseCount('document_signatures', 2);
});

it('cancels a draft but refuses cancellation while it is under review', function () {
    $colaborador = User::factory()->colaborador()->create();
    $draft = SolicitudViatico::factory()->for($colaborador)->create();
    $underReview = SolicitudViatico::factory()->for($colaborador)->enRevision()->create();
    Sanctum::actingAs($colaborador);

    $this->postJson("/api/v1/solicitudes/{$draft->id}/cancelar")
        ->assertOk()
        ->assertJsonPath('data.status', SolicitudViaticoStatus::Cancelada->value);
    $this->postJson("/api/v1/solicitudes/{$underReview->id}/cancelar")->assertForbidden();
    $this->assertDatabaseHas('solicitud_movimientos', [
        'solicitud_id' => $draft->id,
        'action' => SolicitudMovimientoAction::Cancelled->value,
        'to_status' => SolicitudViaticoStatus::Cancelada->value,
    ]);
});

it('exposes chronological movements and signatures to the owner and finance', function () {
    $colaborador = User::factory()->colaborador()->create();
    $solicitud = SolicitudViatico::factory()->for($colaborador)->create();
    SolicitudMovimiento::factory()->for($solicitud, 'solicitud')->for($colaborador)->create();
    $solicitud->firmas()->save(DocumentSignature::factory()->make([
        'user_id' => $colaborador->id,
    ]));
    Sanctum::actingAs($colaborador);

    $this->getJson("/api/v1/solicitudes/{$solicitud->id}/movimientos")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.action', SolicitudMovimientoAction::Created->value);
    $this->getJson("/api/v1/solicitudes/{$solicitud->id}/firmas")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.action', DocumentSignatureAction::Envio->value);

    Sanctum::actingAs(User::factory()->finanzas()->create());
    $this->getJson("/api/v1/solicitudes/{$solicitud->id}/movimientos")->assertOk();
    $this->getJson("/api/v1/solicitudes/{$solicitud->id}/firmas")->assertOk();
});
