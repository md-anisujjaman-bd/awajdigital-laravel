<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Account\Actions;

use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Account\DataTransferObjects\BalanceData;

final readonly class GetBalanceAction
{
    public function __construct(
        private AwajDigitalClient $awajDigitalClient,
    ) {}

    public function execute(): BalanceData
    {
        $response = $this->awajDigitalClient->request('GET', '/balance');

        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        return BalanceData::fromArray($json);
    }
}
