<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\NivelJerarquico;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::Colaborador,
            'employee_code' => fake()->optional()->bothify('EMP-####'),
            'area' => fake()->optional()->word(),
            'puesto' => fake()->optional()->jobTitle(),
            'nivel_jerarquico_id' => null,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function colaborador(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Colaborador,
        ]);
    }

    public function finanzas(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Finanzas,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function withNivelJerarquico(?NivelJerarquico $nivelJerarquico = null): static
    {
        return $this->for($nivelJerarquico ?? NivelJerarquico::factory(), 'nivelJerarquico');
    }
}
