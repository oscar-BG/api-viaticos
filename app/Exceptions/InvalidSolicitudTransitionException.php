<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;

class InvalidSolicitudTransitionException extends Exception implements ShouldntReport
{
    public function __construct()
    {
        parent::__construct('La operación no es válida para el estado actual.');
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'code' => 'ESTADO_INVALIDO',
            'errors' => (object) [],
        ], 409);
    }
}
