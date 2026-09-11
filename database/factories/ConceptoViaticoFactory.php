<?php

namespace Database\Factories;

use App\Enums\ConceptoViaticoClave;
use App\Enums\UnidadViatico;
use App\Models\ConceptoViatico;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConceptoViatico>
 */
class ConceptoViaticoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'clave' => fake()->unique()->lexify('CONCEPTO_??????'),
            'nombre' => fake()->words(2, true),
            'unidad' => UnidadViatico::Evento,
            'tiene_limite' => false,
            'diferencia_tipo_comprobante' => false,
            'is_active' => true,
        ];
    }

    public function hospedaje(): static
    {
        return $this->state(fn (array $attributes): array => [
            'clave' => ConceptoViaticoClave::Hospedaje,
            'nombre' => 'Hospedaje',
            'unidad' => UnidadViatico::Noche,
            'tiene_limite' => true,
            'diferencia_tipo_comprobante' => false,
        ]);
    }

    public function comida(): static
    {
        return $this->state(fn (array $attributes): array => [
            'clave' => ConceptoViaticoClave::Comida,
            'nombre' => 'Comida',
            'unidad' => UnidadViatico::Dia,
            'tiene_limite' => true,
            'diferencia_tipo_comprobante' => true,
        ]);
    }

    public function transporte(): static
    {
        return $this->state(fn (array $attributes): array => [
            'clave' => ConceptoViaticoClave::Transporte,
            'nombre' => 'Transporte',
            'unidad' => UnidadViatico::Evento,
            'tiene_limite' => false,
            'diferencia_tipo_comprobante' => false,
        ]);
    }
}
