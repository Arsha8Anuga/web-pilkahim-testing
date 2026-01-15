<?php

namespace App\Enums;

enum AuditLogResult: string
{
    case SUCCESS = 'SUCCESS';
    case FAILED = 'FAILED';
    case REJECTED = 'REJECTED';
    case ERROR = 'ERROR';
}
