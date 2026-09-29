<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\DataTransferObjects;

final readonly class MintSdkTokenData
{
    /**
     * @param  string  $token  Sensitive browser widget authentication token. Do not log or expose.
     */
    public function __construct(
        #[\SensitiveParameter]
        public string $token,
        public int $expiresIn,
        public string $expiresAt,
        public string $sessionUrl,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            token: (string) ($data['token'] ?? ''),
            expiresIn: (int) ($data['expires_in'] ?? 0),
            expiresAt: (string) ($data['expires_at'] ?? ''),
            sessionUrl: (string) ($data['session_url'] ?? ''),
        );
    }
}
