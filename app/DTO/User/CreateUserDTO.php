<?php

namespace App\DTO\User;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Validation\Rules\Enum;

class CreateUserDTO
{

    public function __construct(
        public string $nim,
        public string $name,
        public string $password,
        public int $id_class,
        public UserRole $role,
        public UserStatus $status,
        public bool $can_vote
    ){}

    public static function rules() : array{
        return [
            'nim' => 'required|string|regex:/^[0-9]{9}$/',
            'name' => 'required|string',
            'password' => 'required|string',
            'id_class' => 'required|integer|exists:classes,id',
            'role' => ['required', new Enum(UserRole::class)],
            'status' => ['required', new Enum(UserStatus::class)],
            'can_vote' => 'required|boolean',
        ];
    }

    public static function from(array $data): self{
        return new self(
            $data['nim'],
            $data['name'],
            $data['password'],
            (int)$data['id_class'],
            $data['role'],
            $data['status'],
            $data['can_vote'],
        );
    }

}
