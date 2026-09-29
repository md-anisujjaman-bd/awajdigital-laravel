<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects;

use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Shared\PhoneNumber;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Shared\RequestId;

final readonly class SendOtpData
{
    public PhoneNumber $phoneNumber;

    public RequestId $requestId;

    public function __construct(
        public string $voice,
        PhoneNumber|string $phoneNumber,
        public string $otpCode,
        public ?string $sender = null,
        ?RequestId $requestId = null,
    ) {
        if (trim($voice) === '') {
            throw new ClientValidationException('Voice cannot be empty.');
        }

        if (! preg_match('/^\d{4,6}$/', $otpCode)) {
            throw new ClientValidationException('OTP code must be between 4 and 6 digits.');
        }

        $this->phoneNumber = $phoneNumber instanceof PhoneNumber ? $phoneNumber : PhoneNumber::from($phoneNumber);
        $this->requestId = $requestId ?? RequestId::generate();
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(?string $fallbackSender = null): array
    {
        $senderNumber = $this->sender ?? $fallbackSender;

        if ($senderNumber === null || trim($senderNumber) === '') {
            throw new ClientValidationException('Sender number is required. Provide it in SendOtpData or configure default_sender.');
        }

        return [
            'request_id' => $this->requestId->toString(),
            'voice' => $this->voice,
            'sender' => $senderNumber,
            'phone_number' => $this->phoneNumber->toString(),
            'otp_code' => $this->otpCode,
        ];
    }
}
