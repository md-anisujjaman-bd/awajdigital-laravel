<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\DataTransferObjects;

final readonly class RevokeSessionData
{
    public function __construct(
        public bool $revoked,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            revoked: (bool) ($data['revoked'] ?? false),
        );
    }
}
