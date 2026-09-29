<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Actions;

use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\BroadcastResultData;

final readonly class GetBroadcastResultAction
{
    public function __construct(
        private AwajDigitalClient $awajDigitalClient,
    ) {}

    public function execute(int $id): BroadcastResultData
    {
        if ($id <= 0) {
            throw new ClientValidationException('Broadcast ID must be a positive integer.');
        }

        $response = $this->awajDigitalClient->request('GET', "/broadcasts/{$id}/result");

        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        return BroadcastResultData::fromArray($json);
    }
}
