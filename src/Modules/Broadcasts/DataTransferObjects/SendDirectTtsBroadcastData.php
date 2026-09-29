<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects;

use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Shared\PhoneNumber;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Shared\RequestId;

final readonly class SendDirectTtsBroadcastData
{
    /** @var array<int, PhoneNumber> */
    public array $phoneNumbers;

    public RequestId $requestId;

    /**
     * @param  array<int, PhoneNumber|string>  $phoneNumbers
     * @param  array<int, string>  $texts
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        array $phoneNumbers,
        public array $texts,
        public ?string $voice = null,
        public ?string $languageCode = null,
        public ?string $sender = null,
        public ?array $metadata = null,
        ?RequestId $requestId = null,
    ) {
        $count = count($phoneNumbers);
        if ($count < 1 || $count > 999) {
            throw new ClientValidationException("Direct TTS broadcast requires between 1 and 999 phone numbers, {$count} provided.");
        }

        $textCount = count($texts);
        if ($textCount < 1 || $textCount > 10) {
            throw new ClientValidationException("Direct TTS broadcast requires between 1 and 10 texts, {$textCount} provided.");
        }

        foreach ($texts as $index => $text) {
            if (mb_strlen($text) > 5000) {
                throw new ClientValidationException("Direct TTS text at index {$index} exceeds 5000 characters limit.");
            }
        }

        if ($languageCode !== null && mb_strlen($languageCode) > 10) {
            throw new ClientValidationException('Language code cannot exceed 10 characters.');
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
            throw new ClientValidationException('Sender number is required. Provide it in SendDirectTtsBroadcastData or configure default_sender.');
        }

        $payload = [
            'request_id' => $this->requestId->toString(),
            'sender' => $senderNumber,
            'phone_numbers' => array_map(fn (PhoneNumber $p): string => $p->toString(), $this->phoneNumbers),
            'texts' => $this->texts,
        ];

        if ($this->voice !== null) {
            $payload['voice'] = $this->voice;
        }

        if ($this->languageCode !== null) {
            $payload['language_code'] = $this->languageCode;
        }

        if ($this->metadata !== null) {
            $payload['metadata'] = $this->metadata;
        }

        return $payload;
    }
}
