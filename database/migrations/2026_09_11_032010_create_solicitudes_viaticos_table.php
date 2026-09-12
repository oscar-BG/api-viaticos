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
        Schema::create('solicitudes_viaticos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('area', 150)->nullable();
            $table->string('puesto', 150)->nullable();
            $table->string('lugar');
            $table->text('motivo');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->decimal('importe_solicitado', 12, 2)->unsigned();
            $table->decimal('importe_autorizado', 12, 2)->unsigned()->nullable();
            $table->string('status', 30)->default('BORRADOR');
            $table->unsignedInteger('version')->default(1);
            $table->text('motivo_rechazo')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes_viaticos');
    }
};
