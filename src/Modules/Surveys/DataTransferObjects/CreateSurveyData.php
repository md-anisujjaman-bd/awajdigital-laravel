<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects;

use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Shared\PhoneNumber;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Shared\RequestId;

final readonly class CreateSurveyData
{
    /** @var array<int, PhoneNumber> */
    public array $phoneNumbers;

    public RequestId $requestId;

    /**
     * @param  array<int, PhoneNumber|string>  $phoneNumbers
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public string $templateName,
        array $phoneNumbers,
        public ?string $sender = null,
        public ?array $metadata = null,
        public ?string $webhookUrl = null,
        ?RequestId $requestId = null,
    ) {
        if (trim($templateName) === '') {
            throw new ClientValidationException('Template name cannot be empty.');
        }

        $count = count($phoneNumbers);
        if ($count < 1 || $count > 999) {
            throw new ClientValidationException("Template survey requires between 1 and 999 phone numbers, {$count} provided.");
        }

        $normalized = [];
        $seen = [];
        foreach ($phoneNumbers as $num) {
            $phone = $num instanceof PhoneNumber ? $num : PhoneNumber::from((string) $num);
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
            throw new ClientValidationException('Sender number is required. Provide it in CreateSurveyData or configure default_sender.');
        }

        $payload = [
            'request_id' => $this->requestId->toString(),
            'template_name' => $this->templateName,
            'sender' => $senderNumber,
            'phone_numbers' => array_map(fn (PhoneNumber $p) => $p->toString(), $this->phoneNumbers),
        ];

        if ($this->metadata !== null) {
            $payload['metadata'] = $this->metadata;
        }

        if ($this->webhookUrl !== null) {
            $payload['webhook_url'] = $this->webhookUrl;
        }

        return $payload;
    }
}
