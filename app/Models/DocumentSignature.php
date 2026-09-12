<?php

namespace App\Models;

use App\Enums\DocumentSignatureAction;
use App\Enums\DocumentSignatureMethod;
use Database\Factories\DocumentSignatureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'user_id',
    'action',
    'method',
    'version',
    'payload_snapshot',
    'content_hash',
    'signature_hash',
    'ip_address',
    'user_agent',
    'signed_at',
])]
class DocumentSignature extends Model
{
    /** @use HasFactory<DocumentSignatureFactory> */
    use HasFactory;

    /** @return MorphTo<Model, $this> */
    public function signable(): MorphTo
    {
        return $this->morphTo();
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
            'action' => DocumentSignatureAction::class,
            'method' => DocumentSignatureMethod::class,
            'version' => 'integer',
            'payload_snapshot' => 'array',
            'signed_at' => 'datetime',
        ];
    }
}
