<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects;

use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Shared\PhoneNumber;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Shared\RequestId;

final readonly class SendDirectBroadcastData
{
    /** @var array<int, PhoneNumber> */
    public array $phoneNumbers;

    public RequestId $requestId;

    /**
     * @param  array<int, PhoneNumber|string>  $phoneNumbers
     * @param  array<int, string>  $voices
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        array $phoneNumbers,
        public array $voices,
        public ?string $sender = null,
        public ?array $metadata = null,
        ?RequestId $requestId = null,
    ) {
        $count = count($phoneNumbers);
        if ($count < 1 || $count > 999) {
            throw new ClientValidationException("Direct broadcast requires between 1 and 999 phone numbers, {$count} provided.");
        }

        $voiceCount = count($voices);
        if ($voiceCount < 1 || $voiceCount > 10) {
            throw new ClientValidationException("Direct broadcast requires between 1 and 10 voice URLs, {$voiceCount} provided.");
        }

        $normalized = [];
        $seen = [];
        foreach ($phoneNumbers as $phoneNumber) {
            $phone = $phoneNumber instanceof PhoneNumber ? $phoneNumber : PhoneNumber::from((string) $phoneNumber);
            $val = $phone->toString();
            if (isset($seen[$val])) {
                throw new ClientValidationException("Duplicate recipient phone number detected: {$val}.");
            }

            $seen[$val] = true;
            $normalized[] = $phone;
        }

        $this->phoneNumbers = $normalized;
        $this->requestId = $requestId ?? RequestId::generate();
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(?string $fallbackSender = null): array
    {
        $senderNumber = $this->sender ?? $fallbackSender;

        if ($senderNumber === null || trim($senderNumber) === '') {
            throw new ClientValidationException('Sender number is required. Provide it in SendDirectBroadcastData or configure default_sender.');
        }

        $payload = [
            'request_id' => $this->requestId->toString(),
            'sender' => $senderNumber,
            'phone_numbers' => array_map(fn (PhoneNumber $p): string => $p->toString(), $this->phoneNumbers),
            'voices' => $this->voices,
        ];

        if ($this->metadata !== null) {
            $payload['metadata'] = $this->metadata;
        }

        return $payload;
    }
}
