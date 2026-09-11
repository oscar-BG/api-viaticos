<?php

namespace App\Models;

use App\Enums\ConceptoViaticoClave;
use App\Enums\UnidadViatico;
use Database\Factories\ConceptoViaticoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['clave', 'nombre', 'unidad', 'tiene_limite', 'diferencia_tipo_comprobante', 'is_active'])]
class ConceptoViatico extends Model
{
    /** @use HasFactory<ConceptoViaticoFactory> */
    use HasFactory;

    protected $table = 'conceptos_viaticos';

    /** @return HasMany<ViaticoLimite, $this> */
    public function limites(): HasMany
    {
        return $this->hasMany(ViaticoLimite::class, 'concepto_id');
    }

    /** @param Builder<ConceptoViatico> $query */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'clave' => ConceptoViaticoClave::class,
            'unidad' => UnidadViatico::class,
            'tiene_limite' => 'boolean',
            'diferencia_tipo_comprobante' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
