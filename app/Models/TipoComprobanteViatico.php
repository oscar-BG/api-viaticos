<?php

namespace App\Models;

use App\Enums\TipoComprobanteViaticoClave;
use Database\Factories\TipoComprobanteViaticoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['clave', 'nombre', 'is_active'])]
class TipoComprobanteViatico extends Model
{
    /** @use HasFactory<TipoComprobanteViaticoFactory> */
    use HasFactory;

    protected $table = 'tipos_comprobante_viatico';

    /** @return HasMany<ViaticoLimite, $this> */
    public function limites(): HasMany
    {
        return $this->hasMany(ViaticoLimite::class, 'tipo_comprobante_id');
    }

    /** @param Builder<TipoComprobanteViatico> $query */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'clave' => TipoComprobanteViaticoClave::class,
            'is_active' => 'boolean',
        ];
    }
}
