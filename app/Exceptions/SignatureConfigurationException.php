<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class SignatureConfigurationException extends Exception
{
    public function __construct()
    {
        parent::__construct('El secreto de firma interna no está configurado.');
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'No fue posible generar la firma del documento.',
            'code' => 'FIRMA_NO_CONFIGURADA',
            'errors' => (object) [],
        ], 500);
    }
}
