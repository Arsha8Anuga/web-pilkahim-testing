<?php

namespace App\DTO\UserClass;

final class DeleteUserClassDTO
{
    public function __construct(
        public readonly int $id
    ) {}

    public static function rules(): array {
        return [
            'id' => [
                'required',
                'integer',
                'exists:classes,id',
            ],
        ];
    }
}
