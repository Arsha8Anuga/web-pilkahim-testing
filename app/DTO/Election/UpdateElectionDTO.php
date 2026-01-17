<?php

namespace App\DTO\Election;

use App\Enums\ElectionStatus;
use Illuminate\Validation\Rules\Enum;

class UpdateElectionDTO {
    public function __construct(
        public ?string $name,
        public ?string $description,
        public ?string $voting_start,
        public ?string $voting_end,
        public ?ElectionStatus $status,
    ) {}

    public static function rules(): array {
        return [
            'name' => 'nullable|string|max:150',
            'description' => 'nullable|string',
            'voting_start' => 'nullable|date',
            'voting_end' => 'nullable|date|after:voting_start',
            'status' => ['nullable', new Enum(ElectionStatus::class)],
        ];
    }

    public function toArray(): array {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'voting_start' => $this->voting_start,
            'voting_end' => $this->voting_end,
            'status' => $this->status,
        ], fn ($v) => $v !== null);
    }

    public static function from(array $data): self {
        return new self(
            $data['name'] ?? null,
            $data['description'] ?? null,
            $data['voting_start'] ?? null,
            $data['voting_end'] ?? null,
            $data['status'] ?? null,
        );
    }
}
