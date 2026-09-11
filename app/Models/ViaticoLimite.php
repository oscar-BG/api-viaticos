<?php

namespace App\Models;

use Database\Factories\ViaticoLimiteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['nivel_jerarquico_id', 'concepto_id', 'tipo_comprobante_id', 'monto_maximo', 'is_active'])]
class ViaticoLimite extends Model
{
    /** @use HasFactory<ViaticoLimiteFactory> */
    use HasFactory;

    protected $table = 'viaticos_limites';

    /** @return BelongsTo<NivelJerarquico, $this> */
    public function nivelJerarquico(): BelongsTo
    {
        return $this->belongsTo(NivelJerarquico::class);
    }

    /** @return BelongsTo<ConceptoViatico, $this> */
    public function concepto(): BelongsTo
    {
        return $this->belongsTo(ConceptoViatico::class, 'concepto_id');
    }

    /** @return BelongsTo<TipoComprobanteViatico, $this> */
    public function tipoComprobante(): BelongsTo
    {
        return $this->belongsTo(TipoComprobanteViatico::class, 'tipo_comprobante_id');
    }

    /** @param Builder<ViaticoLimite> $query */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'monto_maximo' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
