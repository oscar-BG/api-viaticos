<?php

namespace Database\Factories;

use App\Models\ConceptoViatico;
use App\Models\NivelJerarquico;
use App\Models\TipoComprobanteViatico;
use App\Models\ViaticoLimite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ViaticoLimite>
 */
class ViaticoLimiteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nivel_jerarquico_id' => NivelJerarquico::factory(),
            'concepto_id' => ConceptoViatico::factory(),
            'tipo_comprobante_id' => TipoComprobanteViatico::factory(),
            'monto_maximo' => fake()->randomFloat(2, 1, 999999),
            'is_active' => true,
        ];
    }
}
