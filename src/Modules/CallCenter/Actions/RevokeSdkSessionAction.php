<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\Actions;

use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\DataTransferObjects\RevokeSessionData;

final readonly class RevokeSdkSessionAction
{
    public function __construct(
        private AwajDigitalClient $awajDigitalClient,
    ) {}

    public function execute(int $agentId): RevokeSessionData
    {
        if ($agentId <= 0) {
            throw new ClientValidationException('Agent ID must be a positive integer.');
        }

        $response = $this->awajDigitalClient->request('DELETE', '/sdk/session', [
            'json' => [
                'agent_id' => $agentId,
            ],
        ]);

        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        return RevokeSessionData::fromArray($json);
    }
}
