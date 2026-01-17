<?php

namespace App\DTO\Election;

class RestoreElectionDTO
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
