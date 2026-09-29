<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Actions;

use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\BroadcastSummaryData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendOtpData;

final readonly class SendOtpAction
{
    public function __construct(
        private AwajDigitalClient $client,
        private ?string $defaultSender = null,
    ) {}

    public function execute(SendOtpData $data): BroadcastSummaryData
    {
        $payload = $data->toPayload($this->defaultSender);

        $response = $this->client->request('POST', '/broadcasts/otp', [
            'json' => $payload,
        ]);

        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        /** @var array<string, mixed> $broadcast */
        $broadcast = (array) ($json['broadcast'] ?? []);

        return BroadcastSummaryData::fromArray($broadcast);
    }
}
