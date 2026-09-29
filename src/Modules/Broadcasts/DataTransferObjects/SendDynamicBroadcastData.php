<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects;

use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Shared\RequestId;

final readonly class SendDynamicBroadcastData
{
    public RequestId $requestId;

    /**
     * @param  array<int, DynamicRecipientData>  $recipients
     */
    public function __construct(
        public string $voice,
        public array $recipients,
        public ?string $sender = null,
        ?RequestId $requestId = null,
    ) {
        $trimmedVoice = trim($voice);
        $voiceLen = mb_strlen($trimmedVoice);
        if ($voiceLen < 1 || $voiceLen > 255) {
            throw new ClientValidationException("Voice name must be between 1 and 255 characters, {$voiceLen} given.");
        }

        $count = count($recipients);
        if ($count < 1 || $count > 10) {
            throw new ClientValidationException("Dynamic broadcast requires between 1 and 10 recipients, {$count} provided.");
        }

        $seen = [];
        foreach ($recipients as $recipient) {
            $val = $recipient->phoneNumber->toString();
            if (isset($seen[$val])) {
                throw new ClientValidationException("Duplicate recipient phone number detected: {$val}.");
            }
            $seen[$val] = true;
        }

        $this->requestId = $requestId ?? RequestId::generate();
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(?string $fallbackSender = null): array
    {
        $senderNumber = $this->sender ?? $fallbackSender;

        if ($senderNumber === null || trim($senderNumber) === '') {
            throw new ClientValidationException('Sender number is required. Provide it in SendDynamicBroadcastData or configure default_sender.');
        }

        return [
            'request_id' => $this->requestId->toString(),
            'voice' => $this->voice,
            'sender' => $senderNumber,
            'recipients' => array_map(fn (DynamicRecipientData $r) => $r->toArray(), $this->recipients),
        ];
    }
}
