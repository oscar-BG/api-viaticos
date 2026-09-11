<?php

namespace Database\Seeders;

use App\Models\NivelJerarquico;
use Illuminate\Database\Seeder;

class NivelJerarquicoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['1er Nivel', '2do Nivel', '3er Nivel'] as $index => $nombre) {
            NivelJerarquico::query()->updateOrCreate(
                ['orden' => $index + 1],
                ['nombre' => $nombre, 'is_active' => true],
            );
        }
    }
}
