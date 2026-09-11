<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;

class InvalidCredentialsException extends Exception implements ShouldntReport
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'Las credenciales proporcionadas no son válidas.',
            'code' => 'CREDENCIALES_INVALIDAS',
            'errors' => (object) [],
        ], 401);
    }
}
