<?php

namespace App\DTO\UserClass;

use Illuminate\Validation\Rule;

final class UpdateUserClassDTO
{
    public function __construct(
        public readonly string $name
    ) {}

    public static function rules(int $classId): array {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('classes', 'name')->ignore($classId),
            ],
        ];
    }

    public static function from(array $data): self {
        return new self(
            $data['name']
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
