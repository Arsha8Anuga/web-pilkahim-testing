<?php

namespace App\DTO\User;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Validation\Rules\Enum;

class UpdateUserDTO
{
     public function __construct(
        public ?string $nim,
        public ?string $name,
        public ?string $password,
        public ?int $id_class,
        public ?UserRole $role,
        public ?UserStatus $status,
        public ?bool $can_vote
    ){}

    public static function rules() : array{
        return [
            'nim' => 'nullable|string|regex:/^[0-9]{9}$/',
            'name' => 'nullable|string',
            'password' => 'nullable|string',
            'id_class' => 'nullable|numeric|exists:classes,id',
            'role' => ['nullable', new Enum(UserRole::class)],
            'status' => ['nullable', new Enum(UserStatus::class)],
            'can_vote' => 'nullable|boolean',
        ];
    }

    public static function from(array $data): self{
        return new self(
            $data['nim'] ?? null,
            $data['name'] ?? null,
            $data['password'] ?? null,
            (int)$data['id_class'] ?? null,
            $data['role'] ?? null,
            $data['status'] ?? null,
            $data['can_vote'] ?? null,
        );
    }
}
