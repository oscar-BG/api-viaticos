<?php

namespace Database\Seeders;

use App\Enums\TipoComprobanteViaticoClave;
use App\Models\TipoComprobanteViatico;
use Illuminate\Database\Seeder;

class TipoComprobanteViaticoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            TipoComprobanteViaticoClave::Factura->value => 'Factura',
            TipoComprobanteViaticoClave::ValeAzul->value => 'Vale azul',
            TipoComprobanteViaticoClave::NoAplica->value => 'No aplica',
        ];

        foreach ($tipos as $clave => $nombre) {
            TipoComprobanteViatico::query()->updateOrCreate(
                ['clave' => $clave],
                ['nombre' => $nombre, 'is_active' => true],
            );
        }
    }
}
