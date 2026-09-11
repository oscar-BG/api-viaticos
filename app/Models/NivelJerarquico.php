<?php

namespace App\Models;

use Database\Factories\NivelJerarquicoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'orden', 'is_active'])]
class NivelJerarquico extends Model
{
    /** @use HasFactory<NivelJerarquicoFactory> */
    use HasFactory;

    protected $table = 'niveles_jerarquicos';

    /** @return HasMany<User, $this> */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** @return HasMany<ViaticoLimite, $this> */
    public function limites(): HasMany
    {
        return $this->hasMany(ViaticoLimite::class);
    }

    /** @param Builder<NivelJerarquico> $query */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'orden' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
