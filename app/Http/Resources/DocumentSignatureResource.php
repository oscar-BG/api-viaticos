<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentSignatureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'action' => $this->action->value,
            'method' => $this->method->value,
            'version' => $this->version,
            'payload_snapshot' => $this->payload_snapshot,
            'content_hash' => $this->content_hash,
            'signature_hash' => $this->signature_hash,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'signed_at' => $this->signed_at->toISOString(),
        ];
    }
}
