<?php

namespace App\DTO\User;

class RestoreUserDTO
{

    public function __construct(
        public int $id
    ){}

    public static function rules(): array{
        return [
            'id' => 'required|integer'
        ];
    }


    public static function from(array $data): self{
        return new self(
            $data['id']
        );
    }

}
