<?php

namespace App\Service\Auth;

use App\Enums\AuditLogAction;
use App\Enums\AuditLogResult;
use App\Helper\AuditLogger;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function login(string $nim, string $password): bool
    {
        if (!Auth::attempt(compact('nim', 'password'))) {

            AuditLogger::system(
                AuditLogAction::LOGIN,
                AuditLogResult::REJECTED,
            );

            return false;
        }

        session()->regenerate();

        AuditLogger::system(
            AuditLogAction::LOGIN,
            AuditLogResult::SUCCESS,
        );

        return true;
    }
}
