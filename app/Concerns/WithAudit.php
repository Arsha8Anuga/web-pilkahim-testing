<?php

namespace App\Concerns;

use App\Enums\AuditLogAction;
use App\Enums\AuditLogResult;
use App\Helper\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Throwable;

trait WithAudit
{
    protected function withAudit(
        AuditLogAction $action,
        callable $callback,
        array $meta = []
    ) {
        try {
            $result = $callback();

            AuditLogger::user(
                $action,
                AuditLogResult::SUCCESS,
                auth()->id(),
                $meta
            );

            return $result;

        } catch (ValidationException | AuthorizationException $e) {

            AuditLogger::user(
                $action,
                AuditLogResult::REJECTED,
                auth()->id(),
                array_merge($meta, [
                    'reason' => class_basename($e),
                ])
            );

            throw $e;

        } catch (ModelNotFoundException $e) {

            AuditLogger::system(
                $action,
                AuditLogResult::FAILED,
                ['reason' => 'model_not_found']
            );

            throw $e;

        } catch (QueryException | Throwable $e) {

            AuditLogger::system(
                $action,
                AuditLogResult::ERROR,
                ['exception' => $e->getMessage()]
            );

            throw $e;
        }
    }
}
