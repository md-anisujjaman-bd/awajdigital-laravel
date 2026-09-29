<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\Actions;

use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\DataTransferObjects\AgentData;

final readonly class ListAgentsAction
{
    public function __construct(
        private AwajDigitalClient $awajDigitalClient,
    ) {}

    /**
     * @return array<int, AgentData>
     */
    public function execute(): array
    {
        $response = $this->awajDigitalClient->request('GET', '/cc/agents');

        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        /** @var array<int, array<string, mixed>> $rawAgents */
        $rawAgents = (array) ($json['data'] ?? []);

        return array_map(
            fn (array $a): AgentData => AgentData::fromArray($a),
            $rawAgents
        );
    }
}
