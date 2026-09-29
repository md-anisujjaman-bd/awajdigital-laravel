<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Actions;

use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\BroadcastSummaryData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendBulkBroadcastData;

final readonly class SendBulkBroadcastAction
{
    public function __construct(
        private AwajDigitalClient $client,
        private ?string $defaultSender = null,
    ) {}

    public function execute(SendBulkBroadcastData $data): BroadcastSummaryData
    {
        $payload = $data->toPayload($this->defaultSender);

        $response = $this->client->request('POST', '/broadcasts', [
            'json' => $payload,
        ]);

        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        /** @var array<string, mixed> $broadcast */
        $broadcast = (array) ($json['broadcast'] ?? []);

        return BroadcastSummaryData::fromArray($broadcast);
    }
}
