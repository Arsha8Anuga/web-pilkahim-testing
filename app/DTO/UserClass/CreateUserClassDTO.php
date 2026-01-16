<?php

namespace App\DTO\UserClass;

final class CreateUserClassDTO
{
    public function __construct(
        public readonly string $name
    ) {}

    public static function rules(): array {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:classes,name',
            ],
        ];
    }

    public static function from(array $data): self {
        return new self(
            $data['name']
        );
    }

    public function toArray(): array {
        return [
            'name' => $this->name,
        ];
    }
}
