<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;

class MissingUserHierarchicalLevelException extends Exception implements ShouldntReport
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'El usuario no tiene un nivel jerárquico configurado.',
            'code' => 'NIVEL_JERARQUICO_NO_CONFIGURADO',
            'errors' => (object) [],
        ], 409);
    }
}
