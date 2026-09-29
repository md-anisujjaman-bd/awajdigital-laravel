<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects;

final readonly class DirectTtsStatusData
{
    public function __construct(
        public string $status,
        public ?int $broadcastId = null,
        public ?string $error = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            status: (string) ($data['status'] ?? 'unknown'),
            broadcastId: isset($data['broadcast_id']) ? (int) $data['broadcast_id'] : null,
            error: isset($data['error']) ? (string) $data['error'] : null,
        );
    }
}
