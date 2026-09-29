<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Actions;

use DateTimeImmutable;
use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\BroadcastSummaryData;

final readonly class ListBroadcastsAction
{
    public function __construct(
        private AwajDigitalClient $awajDigitalClient,
    ) {}

    /**
     * @return array{broadcasts: array<int, BroadcastSummaryData>, dateRange: array<string, string>|null}
     */
    public function execute(?string $startDate = null, ?string $endDate = null, ?string $requestId = null): array
    {
        $query = [];

        if ($startDate !== null) {
            $query['start_date'] = $startDate;
        }

        if ($endDate !== null) {
            $query['end_date'] = $endDate;
        }

        if ($requestId !== null && trim($requestId) !== '') {
            $query['request_id'] = trim($requestId);
        }

        if ($startDate !== null && $endDate !== null) {
            $start = new DateTimeImmutable($startDate);
            $end = new DateTimeImmutable($endDate);
            $diffDays = (int) $start->diff($end)->format('%r%a');

            if (abs($diffDays) > 90) {
                throw new ClientValidationException('Date range cannot exceed 90 days.');
            }
        }

        $response = $this->awajDigitalClient->request('GET', '/broadcasts', [
            'query' => $query,
        ]);

        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        /** @var array<int, array<string, mixed>> $rawBroadcasts */
        $rawBroadcasts = (array) ($json['broadcasts'] ?? []);

        $broadcasts = array_map(
            fn (array $item): BroadcastSummaryData => BroadcastSummaryData::fromArray($item),
            $rawBroadcasts
        );

        /** @var array<string, string>|null $dateRange */
        $dateRange = isset($json['dateRange']) && is_array($json['dateRange'])
            ? $json['dateRange']
            : null;

        return [
            'broadcasts' => $broadcasts,
            'dateRange' => $dateRange,
        ];
    }
}
