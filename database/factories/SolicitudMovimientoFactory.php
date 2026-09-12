<?php

namespace Database\Factories;

use App\Enums\SolicitudMovimientoAction;
use App\Enums\SolicitudViaticoStatus;
use App\Models\SolicitudMovimiento;
use App\Models\SolicitudViatico;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SolicitudMovimiento>
 */
class SolicitudMovimientoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'solicitud_id' => SolicitudViatico::factory(),
            'user_id' => User::factory(),
            'action' => SolicitudMovimientoAction::Created,
            'from_status' => null,
            'to_status' => SolicitudViaticoStatus::Borrador,
            'comment' => null,
            'metadata' => null,
        ];
    }
}
