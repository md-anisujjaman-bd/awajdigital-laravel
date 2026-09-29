<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\DataTransferObjects;

final readonly class AgentCallData
{
    public function __construct(
        public int $id,
        public int $agentId,
        public string $uuid,
        public string $calledNumber,
        public string $callerNumber,
        public string $callType,
        public string $status,
        public ?int $duration = null,
        public ?string $recording = null,
        public ?string $createdAt = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            agentId: (int) ($data['agent_id'] ?? 0),
            uuid: (string) ($data['uuid'] ?? ''),
            calledNumber: (string) ($data['called_number'] ?? ''),
            callerNumber: (string) ($data['caller_number'] ?? ''),
            callType: (string) ($data['call_type'] ?? 'outbound'),
            status: (string) ($data['status'] ?? 'initiated'),
            duration: isset($data['duration']) ? (int) $data['duration'] : null,
            recording: isset($data['recording']) ? (string) $data['recording'] : null,
            createdAt: isset($data['created_at']) ? (string) $data['created_at'] : null,
        );
    }
}
