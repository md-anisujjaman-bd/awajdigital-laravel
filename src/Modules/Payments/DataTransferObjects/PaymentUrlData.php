<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Payments\DataTransferObjects;

final readonly class PaymentUrlData
{
    public function __construct(
        public string $paymentUrl,
        public string $invoiceId,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            paymentUrl: (string) ($data['payment_url'] ?? ''),
            invoiceId: (string) ($data['invoice_id'] ?? ''),
        );
    }
}
