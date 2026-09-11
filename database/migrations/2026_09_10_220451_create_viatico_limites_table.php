<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('viaticos_limites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nivel_jerarquico_id')
                ->constrained('niveles_jerarquicos')
                ->restrictOnDelete();
            $table->foreignId('concepto_id')
                ->constrained('conceptos_viaticos')
                ->restrictOnDelete();
            $table->foreignId('tipo_comprobante_id')
                ->constrained('tipos_comprobante_viatico')
                ->restrictOnDelete();
            $table->decimal('monto_maximo', 12, 2)->unsigned();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(
                ['nivel_jerarquico_id', 'concepto_id', 'tipo_comprobante_id'],
                'viaticos_limites_configuracion_unique',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('viaticos_limites');
    }
};
