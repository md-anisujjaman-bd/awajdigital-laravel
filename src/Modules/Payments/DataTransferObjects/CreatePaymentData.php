<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Payments\DataTransferObjects;

use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;

final readonly class CreatePaymentData
{
    public function __construct(
        public float $amount,
        public string $successUrl,
        public ?string $cancelUrl = null,
    ) {
        if ($amount < 20.0) {
            throw new ClientValidationException("Payment amount must be at least 20 BDT, {$amount} provided.");
        }

        if (! str_starts_with(strtolower($successUrl), 'https://')) {
            throw new ClientValidationException('Success URL must use HTTPS protocol.');
        }

        if ($cancelUrl !== null && ! str_starts_with(strtolower($cancelUrl), 'https://')) {
            throw new ClientValidationException('Cancel URL must use HTTPS protocol.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        $payload = [
            'amount' => $this->amount,
            'success_url' => $this->successUrl,
        ];

        if ($this->cancelUrl !== null) {
            $payload['cancel_url'] = $this->cancelUrl;
        }

        return $payload;
    }
}
