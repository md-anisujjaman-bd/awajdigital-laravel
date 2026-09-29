<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects;

use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Shared\PhoneNumber;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Shared\RequestId;

final readonly class SendBulkBroadcastData
{
    /** @var array<int, PhoneNumber> */
    public array $phoneNumbers;

    public RequestId $requestId;

    /**
     * @param  array<int, PhoneNumber|string>  $phoneNumbers
     */
    public function __construct(
        public string $voice,
        array $phoneNumbers,
        public ?string $sender = null,
        ?RequestId $requestId = null,
    ) {
        if (trim($voice) === '') {
            throw new ClientValidationException('Voice cannot be empty.');
        }

        $count = count($phoneNumbers);
        if ($count < 1 || $count > 999) {
            throw new ClientValidationException("Bulk broadcast requires between 1 and 999 phone numbers, {$count} provided.");
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
            throw new ClientValidationException('Sender number is required. Provide it in SendBulkBroadcastData or configure default_sender.');
        }

        return [
            'request_id' => $this->requestId->toString(),
            'voice' => $this->voice,
            'sender' => $senderNumber,
            'phone_numbers' => array_map(fn (PhoneNumber $p): string => $p->toString(), $this->phoneNumbers),
        ];
    }
}
