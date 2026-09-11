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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('COLABORADOR')->after('password');
            $table->string('employee_code', 50)->nullable()->after('role');
            $table->string('area', 150)->nullable()->after('employee_code');
            $table->string('puesto', 150)->nullable()->after('area');
            $table->foreignId('nivel_jerarquico_id')
                ->nullable()
                ->after('puesto')
                ->constrained('niveles_jerarquicos')
                ->restrictOnDelete();
            $table->boolean('is_active')->default(true)->after('nivel_jerarquico_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('nivel_jerarquico_id');
            $table->dropColumn([
                'role',
                'employee_code',
                'area',
                'puesto',
                'is_active',
            ]);
        });
    }
};
