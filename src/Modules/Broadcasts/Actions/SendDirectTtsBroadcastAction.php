<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Actions;

use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendDirectTtsBroadcastData;

final readonly class SendDirectTtsBroadcastAction
{
    public function __construct(
        private AwajDigitalClient $client,
        private ?string $defaultSender = null,
    ) {}

    /**
     * @return array{request_id: string, message: string}
     */
    public function execute(SendDirectTtsBroadcastData $data): array
    {
        $payload = $data->toPayload($this->defaultSender);

        $response = $this->client->request('POST', '/broadcasts/direct-tts', [
            'json' => $payload,
        ]);

        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        /** @var array<string, mixed> $responseData */
        $responseData = (array) ($json['data'] ?? []);

        return [
            'request_id' => (string) ($responseData['request_id'] ?? $data->requestId->toString()),
            'message' => (string) ($json['message'] ?? 'TTS broadcast request accepted, processing started'),
        ];
    }
}
