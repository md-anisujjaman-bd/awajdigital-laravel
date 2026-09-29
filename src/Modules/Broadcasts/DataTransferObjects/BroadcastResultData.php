<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects;

final readonly class BroadcastResultData
{
    /**
     * @param  array<string, int>|null  $statusDistribution
     * @param  array<int, array{phoneNumber: string, status: string, duration?: int}>|null  $results
     */
    public function __construct(
        public int $id,
        public ?string $name = null,
        public string $status = 'completed',
        public ?int $listenerCount = null,
        public ?int $completeCount = null,
        public ?string $createdAt = null,
        public bool $isComplete = false,
        public ?array $statusDistribution = null,
        public ?array $results = null,
        public ?string $message = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed> $broadcast */
        $broadcast = (array) ($data['broadcast'] ?? []);

        /** @var array<string, int>|null $statusDist */
        $statusDist = isset($data['statusDistribution']) && is_array($data['statusDistribution'])
            ? $data['statusDistribution']
            : null;

        /** @var array<int, array{phoneNumber: string, status: string, duration?: int}>|null $results */
        $results = isset($data['results']) && is_array($data['results'])
            ? $data['results']
            : null;

        return new self(
            id: (int) ($broadcast['id'] ?? 0),
            name: isset($broadcast['name']) ? (string) $broadcast['name'] : null,
            status: (string) ($broadcast['status'] ?? 'unknown'),
            listenerCount: isset($broadcast['listenerCount']) ? (int) $broadcast['listenerCount'] : null,
            completeCount: isset($broadcast['completeCount']) ? (int) $broadcast['completeCount'] : null,
            createdAt: isset($broadcast['createdAt']) ? (string) $broadcast['createdAt'] : null,
            isComplete: (bool) ($data['isComplete'] ?? false),
            statusDistribution: $statusDist,
            results: $results,
            message: isset($data['message']) ? (string) $data['message'] : null,
        );
    }
}
