<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Payments\Actions;

use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Payments\DataTransferObjects\PaymentStatusData;

final readonly class GetPaymentStatusAction
{
    public function __construct(
        private AwajDigitalClient $client,
    ) {}

    public function execute(string $invoiceId): PaymentStatusData
    {
        if (trim($invoiceId) === '') {
            throw new ClientValidationException('Invoice ID cannot be empty.');
        }

        $response = $this->client->request('GET', "/payments/{$invoiceId}/status");

        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        return PaymentStatusData::fromArray($json);
    }
}
