<?php

namespace App\Helper;

use App\Enums\AuditLogAction;
use App\Enums\AuditLogResult;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;
use Throwable;

final class AuditLogger
{
    public static function log(
        AuditLogAction $action,
        AuditLogResult $result,
        ?int $userId = null,
        ?int $electionId = null,
        ?string $ipAddress = null,
        array $meta = [],
    ): void {
        try {
            AuditLog::create([
                'id_user'     => $userId,
                'id_election' => $electionId,
                'action'      => $action,
                'result'      => $result,
                'ip_address'  => $ipAddress ?? Request::ip(),
                'meta'        => empty($meta) ? null : $meta,
            ]);
        } catch (Throwable) {
            
        }
    }
}
