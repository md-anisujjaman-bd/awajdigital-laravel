<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\DataTransferObjects;

final readonly class AgentData
{
    public function __construct(
        public int $id,
        public string $fullName,
        public string $email,
        public string $extension,
        public string $presence,
        public string $status,
        public string $approvalStatus,
        public ?string $sender = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            fullName: (string) ($data['full_name'] ?? ($data['fullName'] ?? '')),
            email: (string) ($data['email'] ?? ''),
            extension: (string) ($data['extension'] ?? ''),
            presence: (string) ($data['presence'] ?? 'offline'),
            status: (string) ($data['status'] ?? 'inactive'),
            approvalStatus: (string) ($data['approval_status'] ?? ($data['approvalStatus'] ?? 'pending')),
            sender: isset($data['sender']) ? (string) $data['sender'] : null,
        );
    }
}
