<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\Actions;

use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\DataTransferObjects\MintSdkTokenData;

final readonly class MintSdkTokenAction
{
    public function __construct(
        private AwajDigitalClient $client,
    ) {}

    public function execute(int $agentId): MintSdkTokenData
    {
        if ($agentId <= 0) {
            throw new ClientValidationException('Agent ID must be a positive integer.');
        }

        $response = $this->client->request('POST', '/sdk/token', [
            'json' => [
                'agent_id' => $agentId,
            ],
        ]);

        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        return MintSdkTokenData::fromArray($json);
    }
}
