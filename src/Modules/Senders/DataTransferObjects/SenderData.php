<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Senders\DataTransferObjects;

final readonly class SenderData
{
    public function __construct(
        public int $id,
        public string $callingNumber,
        public string $status,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            callingNumber: (string) ($data['callingNumber'] ?? ($data['calling_number'] ?? '')),
            status: (string) ($data['status'] ?? 'active'),
        );
    }
}
