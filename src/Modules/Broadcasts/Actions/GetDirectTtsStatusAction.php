<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Actions;

use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\DirectTtsStatusData;

final readonly class GetDirectTtsStatusAction
{
    public function __construct(
        private AwajDigitalClient $client,
    ) {}

    public function execute(string $requestId): DirectTtsStatusData
    {
        if (trim($requestId) === '') {
            throw new ClientValidationException('Request ID cannot be empty.');
        }

        $response = $this->client->request('GET', "/broadcasts/direct-tts/{$requestId}/status");

        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        return DirectTtsStatusData::fromArray($json);
    }
}
