<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects;

final readonly class BroadcastSummaryData
{
    public function __construct(
        public int $id,
        public ?string $name = null,
        public string $status = 'broadcasting',
        public ?string $createdAt = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            name: isset($data['name']) ? (string) $data['name'] : null,
            status: (string) ($data['status'] ?? 'unknown'),
            createdAt: isset($data['createdAt']) ? (string) $data['createdAt'] : (isset($data['created_at']) ? (string) $data['created_at'] : null),
        );
    }
}
