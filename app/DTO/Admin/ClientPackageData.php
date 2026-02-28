<?php

namespace App\DTO\Admin;

use App\Enums\PackageStatus;
use Carbon\CarbonImmutable;

readonly class ClientPackageData
{
    public function __construct(
        public int $clientId,
        public ?int $packageTemplateId,
        public string $name,
        public int $totalProcedures,
        public int $remainingProcedures,
        public string $purchasedAmount,
        public ?CarbonImmutable $expiresAtUtc,
        public PackageStatus $status,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            clientId: (int) $data['client_id'],
            packageTemplateId: isset($data['package_template_id']) ? (int) $data['package_template_id'] : null,
            name: (string) $data['name'],
            totalProcedures: (int) $data['total_procedures'],
            remainingProcedures: (int) $data['remaining_procedures'],
            purchasedAmount: number_format((float) $data['purchased_amount'], 2, '.', ''),
            expiresAtUtc: isset($data['expires_at']) && $data['expires_at'] !== null
                ? CarbonImmutable::parse((string) $data['expires_at'])->utc()
                : null,
            status: PackageStatus::from((string) $data['status']),
        );
    }
}
