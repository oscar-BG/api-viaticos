<?php

namespace App\Enums;

enum TipoComprobanteViaticoClave: string
{
    case Factura = 'FACTURA';
    case ValeAzul = 'VALE_AZUL';
    case NoAplica = 'NO_APLICA';
}
