<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects;

final readonly class SurveyResultData
{
    /**
     * @param  array<string, mixed>|null  $metadata
     * @param  array<string, int>|null  $statusDistribution
     * @param  array<int, SurveyNumberResultData>  $numbers
     */
    public function __construct(
        public int $id,
        public ?string $name = null,
        public string $status = 'ready',
        public ?int $totalCount = null,
        public ?int $completeCount = null,
        public ?string $createdAt = null,
        public ?array $metadata = null,
        public bool $isComplete = false,
        public ?array $statusDistribution = null,
        public array $numbers = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed> $survey */
        $survey = (array) ($data['survey'] ?? []);

        /** @var array<string, int>|null $statusDist */
        $statusDist = isset($data['statusDistribution']) && is_array($data['statusDistribution'])
            ? $data['statusDistribution']
            : null;

        /** @var array<int, array<string, mixed>> $rawNumbers */
        $rawNumbers = (array) ($data['numbers'] ?? []);

        $numbers = array_map(
            fn (array $n): SurveyNumberResultData => SurveyNumberResultData::fromArray($n),
            $rawNumbers
        );

        return new self(
            id: (int) ($survey['id'] ?? 0),
            name: isset($survey['name']) ? (string) $survey['name'] : null,
            status: (string) ($survey['status'] ?? 'unknown'),
            totalCount: isset($survey['totalCount']) ? (int) $survey['totalCount'] : null,
            completeCount: isset($survey['completeCount']) ? (int) $survey['completeCount'] : null,
            createdAt: isset($survey['createdAt']) ? (string) $survey['createdAt'] : null,
            metadata: isset($survey['metadata']) && is_array($survey['metadata']) ? $survey['metadata'] : null,
            isComplete: (bool) ($data['isComplete'] ?? false),
            statusDistribution: $statusDist,
            numbers: $numbers,
        );
    }
}
