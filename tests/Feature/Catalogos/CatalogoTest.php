<?php

use App\Enums\ConceptoViaticoClave;
use App\Enums\TipoComprobanteViaticoClave;
use App\Enums\UserRole;
use App\Models\ConceptoViatico;
use App\Models\User;
use Database\Seeders\ConceptoViaticoSeeder;
use Database\Seeders\TipoComprobanteViaticoSeeder;
use Laravel\Sanctum\Sanctum;

it('returns 401 when catalogs are requested without a token', function () {
    $this->getJson('/api/v1/catalogos/conceptos')->assertUnauthorized();
});

it('returns the seeded active concepts to either role', function (UserRole $role) {
    $this->seed(ConceptoViaticoSeeder::class);
    Sanctum::actingAs(User::factory()->create(['role' => $role]));

    $response = $this->getJson('/api/v1/catalogos/conceptos');

    $response
        ->assertOk()
        ->assertJsonCount(6, 'data')
        ->assertJsonPath('data.0.clave', ConceptoViaticoClave::Hospedaje->value)
        ->assertJsonPath('data.4.clave', ConceptoViaticoClave::Transporte->value)
        ->assertJsonPath('data.4.tiene_limite', false)
        ->assertJsonPath('data.4.diferencia_tipo_comprobante', false);
})->with(UserRole::cases());

it('does not return inactive concepts', function () {
    ConceptoViatico::factory()->transporte()->create(['is_active' => false]);
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/catalogos/conceptos')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('returns the three seeded receipt types', function () {
    $this->seed(TipoComprobanteViaticoSeeder::class);
    Sanctum::actingAs(User::factory()->create());

    $response = $this->getJson('/api/v1/catalogos/tipos-comprobante');

    $response
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.clave', TipoComprobanteViaticoClave::Factura->value)
        ->assertJsonPath('data.1.clave', TipoComprobanteViaticoClave::ValeAzul->value)
        ->assertJsonPath('data.2.clave', TipoComprobanteViaticoClave::NoAplica->value);
});
