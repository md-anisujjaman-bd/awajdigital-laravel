<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Account\DataTransferObjects;

final readonly class BalanceData
{
    public function __construct(
        public float $amount,
        public string $currency = 'BDT',
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            amount: (float) ($data['balance'] ?? 0.0),
            currency: 'BDT',
        );
    }
}
