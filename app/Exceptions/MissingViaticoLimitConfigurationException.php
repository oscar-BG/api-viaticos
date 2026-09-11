<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;

class MissingViaticoLimitConfigurationException extends Exception implements ShouldntReport
{
    public function __construct(
        public readonly string $concepto,
        public readonly string $tipoComprobante,
    ) {
        parent::__construct('Falta configurar un límite de viáticos requerido.');
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'code' => 'LIMITE_NO_CONFIGURADO',
            'errors' => [
                'concepto' => $this->concepto,
                'tipo_comprobante' => $this->tipoComprobante,
            ],
        ], 409);
    }
}
