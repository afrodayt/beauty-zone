<?php

namespace App\DTO\Admin;

use App\Enums\ClientStatus;

readonly class ClientData
{
    public function __construct(
        public string $fullName,
        public string $phone,
        public ?string $birthDate,
        public ClientStatus $status,
        public ?string $notes,
        public bool $contraPregnancy,
        public bool $contraAllergy,
        public bool $contraSkinDamage,
        public bool $contraVaricose,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            fullName: (string) $data['full_name'],
            phone: (string) $data['phone'],
            birthDate: isset($data['birth_date']) ? (string) $data['birth_date'] : null,
            status: ClientStatus::from((string) $data['status']),
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
            contraPregnancy: (bool) ($data['contra_pregnancy'] ?? false),
            contraAllergy: (bool) ($data['contra_allergy'] ?? false),
            contraSkinDamage: (bool) ($data['contra_skin_damage'] ?? false),
            contraVaricose: (bool) ($data['contra_varicose'] ?? false),
        );
    }
}
