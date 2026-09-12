<?php

namespace App\Services;

use App\Enums\DocumentSignatureAction;
use App\Enums\DocumentSignatureMethod;
use App\Exceptions\InvalidSignatureCredentialsException;
use App\Exceptions\SignatureConfigurationException;
use App\Models\DocumentSignature;
use App\Models\SolicitudViatico;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use JsonException;

class DocumentSignatureService
{
    /**
     * @param  array<string, mixed>  $snapshot
     *
     * @throws JsonException
     */
    public function sign(
        SolicitudViatico $solicitud,
        User $user,
        DocumentSignatureAction $action,
        array $snapshot,
        string $password,
        ?string $ipAddress,
        ?string $userAgent,
    ): DocumentSignature {
        if (! Hash::check($password, $user->password)) {
            throw new InvalidSignatureCredentialsException;
        }

        $signatureSecret = (string) config('viaticos.signature_secret');

        if ($signatureSecret === '') {
            throw new SignatureConfigurationException;
        }

        $canonicalPayload = json_encode(
            $snapshot,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION,
        );
        $contentHash = hash('sha256', $canonicalPayload);
        $signaturePayload = implode('|', [
            $solicitud->getMorphClass(),
            (string) $solicitud->getKey(),
            $action->value,
            (string) $solicitud->version,
            $contentHash,
        ]);

        return $solicitud->firmas()->create([
            'user_id' => $user->id,
            'action' => $action,
            'method' => DocumentSignatureMethod::InternalConfirmation,
            'version' => $solicitud->version,
            'payload_snapshot' => $snapshot,
            'content_hash' => $contentHash,
            'signature_hash' => hash_hmac('sha256', $signaturePayload, $signatureSecret),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'signed_at' => now(),
        ]);
    }
}
