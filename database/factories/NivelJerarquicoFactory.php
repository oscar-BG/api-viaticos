<?php

namespace Database\Factories;

use App\Models\NivelJerarquico;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NivelJerarquico>
 */
class NivelJerarquicoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->jobTitle(),
            'orden' => fake()->unique()->numberBetween(10, 65000),
            'is_active' => true,
        ];
    }
}
