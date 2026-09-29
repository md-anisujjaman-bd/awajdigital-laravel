<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects;

final readonly class SurveyNumberResultData
{
    /**
     * @param  array<int, string>  $pressedKeys
     */
    public function __construct(
        public string $number,
        public string $status,
        public ?int $duration = null,
        public array $pressedKeys = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<int, string> $keys */
        $keys = (array) ($data['pressedKeys'] ?? []);

        return new self(
            number: (string) ($data['number'] ?? ''),
            status: (string) ($data['status'] ?? 'unknown'),
            duration: isset($data['duration']) ? (int) $data['duration'] : null,
            pressedKeys: $keys,
        );
    }
}
