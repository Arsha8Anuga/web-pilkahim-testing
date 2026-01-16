<?php

namespace App\Helper;

use App\Enums\AuditLogAction;
use App\Enums\AuditLogResult;
use App\Models\AuditLog;
use Throwable;

final class AuditLogger
{
    public static function user(
        AuditLogAction $action,
        AuditLogResult $result,
        int $userId,
        array $meta = []
    ){
        self::write(
            action: $action,
            result: $result,
            userId: $userId,
            meta: $meta
        );
    }

    public static function system(
        AuditLogAction $action,
        AuditLogResult $result,
        array $meta = []
    ){
        self::write(
            action: $action,
            result: $result,
            userId: null,
            meta: $meta
        );
    }

    private static function write(
        AuditLogAction $action,
        AuditLogResult $result,
        ?int $userId,
        array $meta
    ){
        try {
            AuditLog::create([
                'id_user'   => $userId,
                'action'    => $action,
                'result'    => $result,
                'ip'        => request()->ip(),
                'meta'      => $meta,
            ]);
        } catch (Throwable) {
           
        }
    }
}
