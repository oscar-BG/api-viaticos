<?php

namespace Database\Factories;

use App\Enums\TipoComprobanteViaticoClave;
use App\Models\TipoComprobanteViatico;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipoComprobanteViatico>
 */
class TipoComprobanteViaticoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'clave' => fake()->unique()->lexify('TIPO_??????'),
            'nombre' => fake()->words(2, true),
            'is_active' => true,
        ];
    }

    public function factura(): static
    {
        return $this->state(fn (array $attributes): array => [
            'clave' => TipoComprobanteViaticoClave::Factura,
            'nombre' => 'Factura',
        ]);
    }

    public function valeAzul(): static
    {
        return $this->state(fn (array $attributes): array => [
            'clave' => TipoComprobanteViaticoClave::ValeAzul,
            'nombre' => 'Vale azul',
        ]);
    }

    public function noAplica(): static
    {
        return $this->state(fn (array $attributes): array => [
            'clave' => TipoComprobanteViaticoClave::NoAplica,
            'nombre' => 'No aplica',
        ]);
    }
}
