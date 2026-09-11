<?php

use App\Enums\ConceptoViaticoClave;
use App\Enums\TipoComprobanteViaticoClave;
use App\Models\ConceptoViatico;
use App\Models\NivelJerarquico;
use App\Models\TipoComprobanteViatico;
use App\Models\User;
use App\Models\ViaticoLimite;
use Laravel\Sanctum\Sanctum;

it('uses the authenticated user hierarchical level and ignores a client supplied level', function () {
    $userLevel = NivelJerarquico::factory()->create();
    $otherLevel = NivelJerarquico::factory()->create();
    $concepto = ConceptoViatico::factory()->hospedaje()->create();
    $tipo = TipoComprobanteViatico::factory()->noAplica()->create();
    $user = User::factory()->for($userLevel, 'nivelJerarquico')->create();
    ViaticoLimite::factory()->for($userLevel, 'nivelJerarquico')->for($concepto, 'concepto')
        ->for($tipo, 'tipoComprobante')->create(['monto_maximo' => '450.00']);
    ViaticoLimite::factory()->for($otherLevel, 'nivelJerarquico')->for($concepto, 'concepto')
        ->for($tipo, 'tipoComprobante')->create(['monto_maximo' => '999.00']);
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/catalogos/mis-limites?nivel_jerarquico_id='.$otherLevel->id);

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.concepto.clave', ConceptoViaticoClave::Hospedaje->value)
        ->assertJsonPath('data.0.tipo_comprobante.clave', TipoComprobanteViaticoClave::NoAplica->value)
        ->assertJsonPath('data.0.limite_aplicado', '450.00')
        ->assertJsonPath('data.0.excedente', '0.00');
});

it('returns null limit and zero excess for an unlimited concept', function () {
    $level = NivelJerarquico::factory()->create();
    ConceptoViatico::factory()->transporte()->create();
    Sanctum::actingAs(User::factory()->for($level, 'nivelJerarquico')->create());

    $response = $this->getJson('/api/v1/catalogos/mis-limites');

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.concepto.clave', ConceptoViaticoClave::Transporte->value)
        ->assertJsonPath('data.0.tipo_comprobante', null)
        ->assertJsonPath('data.0.limite_aplicado', null)
        ->assertJsonPath('data.0.excedente', '0.00');
    $this->assertDatabaseCount('viaticos_limites', 0);
});

it('returns 409 when a limited concept has no configuration', function () {
    $level = NivelJerarquico::factory()->create();
    ConceptoViatico::factory()->hospedaje()->create();
    TipoComprobanteViatico::factory()->noAplica()->create();
    Sanctum::actingAs(User::factory()->for($level, 'nivelJerarquico')->create());

    $this->getJson('/api/v1/catalogos/mis-limites')
        ->assertConflict()
        ->assertExactJson([
            'message' => 'Falta configurar un límite de viáticos requerido.',
            'code' => 'LIMITE_NO_CONFIGURADO',
            'errors' => [
                'concepto' => ConceptoViaticoClave::Hospedaje->value,
                'tipo_comprobante' => TipoComprobanteViaticoClave::NoAplica->value,
            ],
        ]);
});

it('resolves separate limits for invoice and blue voucher', function () {
    $level = NivelJerarquico::factory()->create();
    $concepto = ConceptoViatico::factory()->comida()->create();
    $factura = TipoComprobanteViatico::factory()->factura()->create();
    $valeAzul = TipoComprobanteViatico::factory()->valeAzul()->create();
    $user = User::factory()->for($level, 'nivelJerarquico')->create();
    ViaticoLimite::factory()->for($level, 'nivelJerarquico')->for($concepto, 'concepto')
        ->for($factura, 'tipoComprobante')->create(['monto_maximo' => '300.00']);
    ViaticoLimite::factory()->for($level, 'nivelJerarquico')->for($concepto, 'concepto')
        ->for($valeAzul, 'tipoComprobante')->create(['monto_maximo' => '200.00']);
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/catalogos/mis-limites');

    $response
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.tipo_comprobante.clave', TipoComprobanteViaticoClave::Factura->value)
        ->assertJsonPath('data.0.limite_aplicado', '300.00')
        ->assertJsonPath('data.1.tipo_comprobante.clave', TipoComprobanteViaticoClave::ValeAzul->value)
        ->assertJsonPath('data.1.limite_aplicado', '200.00');
});

it('returns 409 when the authenticated user has no hierarchical level', function () {
    Sanctum::actingAs(User::factory()->create(['nivel_jerarquico_id' => null]));

    $this->getJson('/api/v1/catalogos/mis-limites')
        ->assertConflict()
        ->assertJsonPath('code', 'NIVEL_JERARQUICO_NO_CONFIGURADO');
});
