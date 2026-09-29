<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\Actions;

use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\DataTransferObjects\AgentCallData;

final readonly class ListAgentCallsAction
{
    public function __construct(
        private AwajDigitalClient $awajDigitalClient,
    ) {}

    /**
     * @return array{meta: array<string, int>, data: array<int, AgentCallData>}
     */
    public function execute(int $agentId, string $date, int $page = 1): array
    {
        if ($agentId <= 0) {
            throw new ClientValidationException('Agent ID must be a positive integer.');
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new ClientValidationException("Date must be formatted as YYYY-MM-DD ('{$date}' given).");
        }

        if ($page < 1) {
            throw new ClientValidationException("Page must be at least 1, {$page} given.");
        }

        $response = $this->awajDigitalClient->request('GET', "/cc/agents/{$agentId}/calls", [
            'query' => [
                'date' => $date,
                'page' => $page,
            ],
        ]);

        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        /** @var array<string, int> $meta */
        $meta = (array) ($json['meta'] ?? []);

        /** @var array<int, array<string, mixed>> $rawData */
        $rawData = (array) ($json['data'] ?? []);

        $calls = array_map(
            fn (array $c): \MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\DataTransferObjects\AgentCallData => AgentCallData::fromArray($c),
            $rawData
        );

        return [
            'meta' => $meta,
            'data' => $calls,
        ];
    }
}
