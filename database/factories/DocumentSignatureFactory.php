<?php

namespace Database\Factories;

use App\Enums\DocumentSignatureAction;
use App\Enums\DocumentSignatureMethod;
use App\Models\DocumentSignature;
use App\Models\SolicitudViatico;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentSignature>
 */
class DocumentSignatureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'signable_type' => SolicitudViatico::class,
            'signable_id' => SolicitudViatico::factory(),
            'user_id' => User::factory(),
            'action' => DocumentSignatureAction::Envio,
            'method' => DocumentSignatureMethod::InternalConfirmation,
            'version' => 1,
            'payload_snapshot' => ['document' => 'test'],
            'content_hash' => hash('sha256', 'test-content'),
            'signature_hash' => hash('sha256', 'test-signature'),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'signed_at' => now(),
        ];
    }
}
