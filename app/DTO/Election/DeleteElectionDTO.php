<?php

namespace App\DTO\Election;

class DeleteElectionDTO
{
    public function __construct(
        public int $id
    ) {}

    public static function rules(): array
    {
        return [
            'id' => 'required|integer',
        ];
    }
}
