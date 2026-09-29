<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Payments\Actions;

use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Payments\DataTransferObjects\CreatePaymentData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Payments\DataTransferObjects\PaymentUrlData;

final readonly class CreatePaymentAction
{
    public function __construct(
        private AwajDigitalClient $awajDigitalClient,
    ) {}

    public function execute(CreatePaymentData $data): PaymentUrlData
    {
        $response = $this->awajDigitalClient->request('POST', '/payments/create', [
            'json' => $data->toPayload(),
        ]);

        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        return PaymentUrlData::fromArray($json);
    }
}
