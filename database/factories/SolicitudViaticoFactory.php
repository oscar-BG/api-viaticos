<?php

namespace Database\Factories;

use App\Enums\SolicitudViaticoStatus;
use App\Models\SolicitudViatico;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SolicitudViatico>
 */
class SolicitudViaticoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->colaborador(),
            'area' => fake()->word(),
            'puesto' => fake()->jobTitle(),
            'lugar' => fake()->city(),
            'motivo' => fake()->sentence(),
            'fecha_inicio' => now()->addWeek()->toDateString(),
            'fecha_fin' => now()->addWeek()->addDays(2)->toDateString(),
            'importe_solicitado' => '5000.00',
            'importe_autorizado' => null,
            'status' => SolicitudViaticoStatus::Borrador,
            'version' => 1,
            'motivo_rechazo' => null,
            'submitted_at' => null,
            'approved_at' => null,
            'approved_by' => null,
        ];
    }

    public function enRevision(): static
    {
        return $this->state(fn (): array => [
            'status' => SolicitudViaticoStatus::EnRevision,
            'submitted_at' => now(),
        ]);
    }

    public function rechazada(): static
    {
        return $this->state(fn (): array => [
            'status' => SolicitudViaticoStatus::Rechazada,
            'motivo_rechazo' => 'Información insuficiente.',
            'submitted_at' => now(),
        ]);
    }

    public function aprobada(?User $finanzas = null): static
    {
        return $this->state(fn (): array => [
            'status' => SolicitudViaticoStatus::Aprobada,
            'importe_autorizado' => '4500.00',
            'submitted_at' => now()->subHour(),
            'approved_at' => now(),
            'approved_by' => $finanzas?->id ?? User::factory()->finanzas(),
        ]);
    }

    public function cancelada(): static
    {
        return $this->state(fn (): array => [
            'status' => SolicitudViaticoStatus::Cancelada,
        ]);
    }
}
