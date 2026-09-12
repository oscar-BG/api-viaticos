<?php

namespace App\Enums;

enum SolicitudViaticoStatus: string
{
    case Borrador = 'BORRADOR';
    case EnRevision = 'EN_REVISION';
    case Aprobada = 'APROBADA';
    case Rechazada = 'RECHAZADA';
    case Cancelada = 'CANCELADA';
}
