<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;

class InvalidSignatureCredentialsException extends Exception implements ShouldntReport
{
    public function __construct()
    {
        parent::__construct('La contraseña de confirmación no es válida.');
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'Los datos proporcionados no son válidos.',
            'code' => 'PASSWORD_CONFIRMACION_INVALIDO',
            'errors' => [
                'password' => [$this->getMessage()],
            ],
        ], 422);
    }
}
