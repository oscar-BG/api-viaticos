<?php

use App\Enums\ConceptoViaticoClave;
use App\Enums\TipoComprobanteViaticoClave;

it('seeds the foundational catalogs without creating default users or fictional limits', function () {
    $this->seed();

    $this->assertDatabaseCount('niveles_jerarquicos', 3);
    $this->assertDatabaseHas('niveles_jerarquicos', [
        'nombre' => '1er Nivel',
        'orden' => 1,
        'is_active' => true,
    ]);
    $this->assertDatabaseCount('conceptos_viaticos', 6);
    $this->assertDatabaseHas('conceptos_viaticos', [
        'clave' => ConceptoViaticoClave::Transporte->value,
        'tiene_limite' => false,
        'diferencia_tipo_comprobante' => false,
    ]);
    $this->assertDatabaseHas('conceptos_viaticos', [
        'clave' => ConceptoViaticoClave::Extraordinario->value,
        'tiene_limite' => false,
        'diferencia_tipo_comprobante' => false,
    ]);
    $this->assertDatabaseCount('tipos_comprobante_viatico', 3);
    $this->assertDatabaseHas('tipos_comprobante_viatico', [
        'clave' => TipoComprobanteViaticoClave::NoAplica->value,
    ]);
    $this->assertDatabaseCount('users', 0);
    $this->assertDatabaseCount('viaticos_limites', 0);
});
