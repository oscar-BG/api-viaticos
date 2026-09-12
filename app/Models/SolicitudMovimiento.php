<?php

namespace App\Models;

use App\Enums\SolicitudMovimientoAction;
use App\Enums\SolicitudViaticoStatus;
use Database\Factories\SolicitudMovimientoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['solicitud_id', 'user_id', 'action', 'from_status', 'to_status', 'comment', 'metadata'])]
class SolicitudMovimiento extends Model
{
    /** @use HasFactory<SolicitudMovimientoFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $table = 'solicitud_movimientos';

    /** @return BelongsTo<SolicitudViatico, $this> */
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudViatico::class, 'solicitud_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'action' => SolicitudMovimientoAction::class,
            'from_status' => SolicitudViaticoStatus::class,
            'to_status' => SolicitudViaticoStatus::class,
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
