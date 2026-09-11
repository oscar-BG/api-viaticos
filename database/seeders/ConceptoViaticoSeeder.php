<?php

namespace Database\Seeders;

use App\Enums\ConceptoViaticoClave;
use App\Enums\UnidadViatico;
use App\Models\ConceptoViatico;
use Illuminate\Database\Seeder;

class ConceptoViaticoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conceptos = [
            [ConceptoViaticoClave::Hospedaje, 'Hospedaje', UnidadViatico::Noche, true, false],
            [ConceptoViaticoClave::Desayuno, 'Desayuno', UnidadViatico::Dia, true, true],
            [ConceptoViaticoClave::Comida, 'Comida', UnidadViatico::Dia, true, true],
            [ConceptoViaticoClave::Cena, 'Cena', UnidadViatico::Dia, true, true],
            [ConceptoViaticoClave::Transporte, 'Transporte', UnidadViatico::Evento, false, false],
            [ConceptoViaticoClave::Extraordinario, 'Extraordinario', UnidadViatico::Evento, false, false],
        ];

        foreach ($conceptos as [$clave, $nombre, $unidad, $tieneLimite, $diferenciaTipoComprobante]) {
            ConceptoViatico::query()->updateOrCreate(
                ['clave' => $clave],
                [
                    'nombre' => $nombre,
                    'unidad' => $unidad,
                    'tiene_limite' => $tieneLimite,
                    'diferencia_tipo_comprobante' => $diferenciaTipoComprobante,
                    'is_active' => true,
                ],
            );
        }
    }
}
