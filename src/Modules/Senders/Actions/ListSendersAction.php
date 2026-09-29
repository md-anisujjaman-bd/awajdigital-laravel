<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Senders\Actions;

use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Senders\DataTransferObjects\SenderData;

final readonly class ListSendersAction
{
    public function __construct(
        private AwajDigitalClient $client,
    ) {}

    /**
     * @return array<int, SenderData>
     */
    public function execute(): array
    {
        $response = $this->client->request('GET', '/senders');

        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        /** @var array<int, array<string, mixed>> $rawSenders */
        $rawSenders = (array) ($json['senders'] ?? []);

        return array_map(
            fn (array $s) => SenderData::fromArray($s),
            $rawSenders
        );
    }
}
