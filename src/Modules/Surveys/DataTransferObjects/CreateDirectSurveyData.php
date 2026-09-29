<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects;

use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Shared\PhoneNumber;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Shared\RequestId;

final readonly class CreateDirectSurveyData
{
    /** @var array<int, PhoneNumber> */
    public array $phoneNumbers;

    public RequestId $requestId;

    /**
     * @param  array<int, PhoneNumber|string>  $phoneNumbers
     * @param  array<int, VoiceEntry|string|array<string, mixed>>  $questionVoices
     * @param  array<int, DtmfOptionData>  $dtmfOptions
     * @param  array<int, VoiceEntry|string|array<string, mixed>>  $startVoices
     * @param  VoiceEntry|string|array<string, mixed>|null  $invalidVoice
     * @param  array<int, VoiceEntry|string|array<string, mixed>>  $endVoices
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        array $phoneNumbers,
        public array $questionVoices,
        public array $dtmfOptions,
        public array $startVoices = [],
        public VoiceEntry|string|array|null $invalidVoice = null,
        public array $endVoices = [],
        public ?string $sender = null,
        public ?array $metadata = null,
        public ?string $webhookUrl = null,
        public int $retryCount = 0,
        ?RequestId $requestId = null,
    ) {
        $count = count($phoneNumbers);
        if ($count < 1 || $count > 999) {
            throw new ClientValidationException("Direct survey requires between 1 and 999 phone numbers, {$count} provided.");
        }

        $qCount = count($questionVoices);
        if ($qCount < 1 || $qCount > 10) {
            throw new ClientValidationException("Direct survey requires between 1 and 10 question voices, {$qCount} provided.");
        }

        $dtmfCount = count($dtmfOptions);
        if ($dtmfCount < 1 || $dtmfCount > 9) {
            throw new ClientValidationException("Direct survey requires between 1 and 9 DTMF options, {$dtmfCount} provided.");
        }

        $seenKeys = [];
        foreach ($dtmfOptions as $dtmfOption) {
            if (isset($seenKeys[$dtmfOption->key])) {
                throw new ClientValidationException("Duplicate DTMF option key detected: [{$dtmfOption->key}].");
            }

            $seenKeys[$dtmfOption->key] = true;
        }

        if (count($startVoices) > 10) {
            throw new ClientValidationException('Direct survey allows maximum 10 start voices.');
        }

        if (count($endVoices) > 10) {
            throw new ClientValidationException('Direct survey allows maximum 10 end voices.');
        }

        if ($retryCount < 0 || $retryCount > 3) {
            throw new ClientValidationException("Retry count must be between 0 and 3, {$retryCount} provided.");
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
            throw new ClientValidationException('Sender number is required. Provide it in CreateDirectSurveyData or configure default_sender.');
        }

        $mapVoices = fn (array $voices): array => array_map(function ($v) {
            if ($v instanceof VoiceEntry) {
                return $v->toPayload();
            }

            return $v;
        }, $voices);

        $payload = [
            'request_id' => $this->requestId->toString(),
            'sender' => $senderNumber,
            'phone_numbers' => array_map(fn (PhoneNumber $p): string => $p->toString(), $this->phoneNumbers),
            'question_voices' => $mapVoices($this->questionVoices),
            'dtmf_options' => array_map(fn (DtmfOptionData $opt): array => $opt->toPayload(), $this->dtmfOptions),
            'config' => [
                'retry_count' => $this->retryCount,
            ],
        ];

        if ($this->startVoices !== []) {
            $payload['start_voices'] = $mapVoices($this->startVoices);
        }

        if ($this->invalidVoice !== null) {
            $payload['invalid_voice'] = $this->invalidVoice instanceof VoiceEntry
                ? $this->invalidVoice->toPayload()
                : $this->invalidVoice;
        }

        if ($this->endVoices !== []) {
            $payload['end_voices'] = $mapVoices($this->endVoices);
        }

        if ($this->metadata !== null) {
            $payload['metadata'] = $this->metadata;
        }

        if ($this->webhookUrl !== null) {
            $payload['webhook_url'] = $this->webhookUrl;
        }

        return $payload;
    }
}
