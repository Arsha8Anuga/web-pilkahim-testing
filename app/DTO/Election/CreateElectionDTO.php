<?php

namespace App\DTO\Election;

use App\Enums\ElectionStatus;
use Illuminate\Validation\Rules\Enum;

class CreateElectionDTO
{
    public function __construct(
        public string $name,
        public ?string $description,
        public string $voting_start,
        public string $voting_end,
        public ElectionStatus $status,
    ) {}

    public static function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'voting_start' => 'required|date',
            'voting_end' => 'required|date|after:voting_start',
            'status' => ['required', new Enum(ElectionStatus::class)],
        ];
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'voting_start' => $this->voting_start,
            'voting_end' => $this->voting_end,
            'status' => $this->status,
        ];
    }

    public static function from(array $data): self
    {
        return new self(
            $data['name'],
            $data['description'] ?? null,
            $data['voting_start'],
            $data['voting_end'],
            $data['status'],
        );
    }
}
