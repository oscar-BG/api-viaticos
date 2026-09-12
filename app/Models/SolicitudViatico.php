<?php

namespace App\Models;

use App\Enums\SolicitudViaticoStatus;
use App\Enums\UserRole;
use Database\Factories\SolicitudViaticoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable(['area', 'puesto', 'lugar', 'motivo', 'fecha_inicio', 'fecha_fin', 'importe_solicitado'])]
class SolicitudViatico extends Model
{
    /** @use HasFactory<SolicitudViaticoFactory> */
    use HasFactory;

    protected $table = 'solicitudes_viaticos';

    protected $attributes = [
        'status' => SolicitudViaticoStatus::Borrador->value,
        'version' => 1,
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return HasMany<SolicitudMovimiento, $this> */
    public function movimientos(): HasMany
    {
        return $this->hasMany(SolicitudMovimiento::class, 'solicitud_id');
    }

    /** @return MorphMany<DocumentSignature, $this> */
    public function firmas(): MorphMany
    {
        return $this->morphMany(DocumentSignature::class, 'signable');
    }

    /**
     * Hide another collaborator's request during implicit route binding.
     *
     * @param  mixed  $query
     * @param  mixed  $value
     * @param  string|null  $field
     * @return mixed
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        $query = parent::resolveRouteBindingQuery($query, $value, $field);
        $authenticatedUser = auth()->user();

        if ($authenticatedUser instanceof User && $authenticatedUser->role === UserRole::Colaborador) {
            return $query->whereBelongsTo($authenticatedUser);
        }

        return $query;
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date:Y-m-d',
            'fecha_fin' => 'date:Y-m-d',
            'importe_solicitado' => 'decimal:2',
            'importe_autorizado' => 'decimal:2',
            'status' => SolicitudViaticoStatus::class,
            'version' => 'integer',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }
}
