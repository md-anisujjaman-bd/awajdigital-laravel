<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Voices\DataTransferObjects;

final readonly class VoiceData
{
    public function __construct(
        public int $id,
        public string $name,
        public string $status,
        public ?string $createdAt = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            name: (string) ($data['name'] ?? ''),
            status: (string) ($data['status'] ?? 'pending'),
            createdAt: isset($data['createdAt']) ? (string) $data['createdAt'] : (isset($data['created_at']) ? (string) $data['created_at'] : null),
        );
    }
}
