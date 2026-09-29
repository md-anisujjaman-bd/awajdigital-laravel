<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Payments\DataTransferObjects;

final readonly class PaymentStatusData
{
    public function __construct(
        public string $invoiceId,
        public string $status,
        public ?float $amount = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            invoiceId: (string) ($data['invoice_id'] ?? ''),
            status: (string) ($data['status'] ?? 'unknown'),
            amount: isset($data['amount']) ? (float) $data['amount'] : null,
        );
    }
}
