<?php

namespace App\Enums;

enum SolicitudMovimientoAction: string
{
    case Created = 'CREATED';
    case Updated = 'UPDATED';
    case Submitted = 'SUBMITTED';
    case Approved = 'APPROVED';
    case Rejected = 'REJECTED';
    case Cancelled = 'CANCELLED';
    case Signed = 'SIGNED';
}
