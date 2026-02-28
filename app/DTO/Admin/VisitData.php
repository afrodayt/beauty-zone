<?php

namespace App\DTO\Admin;

use App\Enums\PaymentMethod;
use App\Enums\VisitStatus;
use Carbon\CarbonImmutable;

readonly class VisitData
{
    public function __construct(
        public int $clientId,
        public int $serviceId,
        public ?int $deviceId,
        public ?int $masterId,
        public ?int $clientPackageId,
        public string $zone,
        public CarbonImmutable $startsAtUtc,
        public string $price,
        public PaymentMethod $paymentMethod,
        public VisitStatus $visitStatus,
        public ?string $masterComment,
        public bool $deductFromPackage,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            clientId: (int) $data['client_id'],
            serviceId: (int) $data['service_id'],
            deviceId: isset($data['device_id']) ? (int) $data['device_id'] : null,
            masterId: isset($data['master_id']) ? (int) $data['master_id'] : null,
            clientPackageId: isset($data['client_package_id']) ? (int) $data['client_package_id'] : null,
            zone: (string) $data['zone'],
            startsAtUtc: CarbonImmutable::parse((string) $data['starts_at'])->utc(),
            price: number_format((float) $data['price'], 2, '.', ''),
            paymentMethod: PaymentMethod::from((string) $data['payment_method']),
            visitStatus: VisitStatus::from((string) $data['visit_status']),
            masterComment: isset($data['master_comment']) ? (string) $data['master_comment'] : null,
            deductFromPackage: (bool) ($data['deduct_from_package'] ?? false),
        );
    }
}
