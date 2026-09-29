<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects;

use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\Enums\DtmfOptionType;

final readonly class DtmfOptionData
{
    /**
     * @param  array<int, VoiceEntry|string|array<string, mixed>>  $voices
     * @param  array<int, string>|null  $transferNumbers
     */
    public function __construct(
        public string $key,
        public DtmfOptionType $optionType = DtmfOptionType::Voice,
        public array $voices = [],
        public ?array $transferNumbers = null,
        public VoiceEntry|string|null $ringbackVoice = null,
        public VoiceEntry|string|null $allBusyVoice = null,
    ) {
        if (! preg_match('/^[1-9]$/', $key)) {
            throw new ClientValidationException("DTMF option key must be between 1 and 9 ('{$key}' given). Keys 0, *, # are not supported.");
        }

        if ($this->optionType === DtmfOptionType::Transfer && (empty($this->transferNumbers))) {
            throw new ClientValidationException("DTMF transfer option requires at least one transfer number for key [{$key}].");
        }

        if ($this->optionType === DtmfOptionType::Voice && count($this->voices) > 10) {
            throw new ClientValidationException("DTMF voice option cannot have more than 10 voices for key [{$key}].");
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        $payload = [
            'key' => $this->key,
            'option_type' => $this->optionType->value,
        ];

        if ($this->optionType === DtmfOptionType::Voice) {
            $payload['voices'] = array_map(function ($voice) {
                if ($voice instanceof VoiceEntry) {
                    return $voice->toPayload();
                }

                return $voice;
            }, $this->voices);
        }

        if ($this->optionType === DtmfOptionType::Transfer) {
            $payload['transfer_numbers'] = $this->transferNumbers ?? [];

            if ($this->ringbackVoice !== null) {
                $payload['ringback_voice'] = $this->ringbackVoice instanceof VoiceEntry
                    ? $this->ringbackVoice->toPayload()
                    : $this->ringbackVoice;
            }

            if ($this->allBusyVoice !== null) {
                $payload['all_busy_voice'] = $this->allBusyVoice instanceof VoiceEntry
                    ? $this->allBusyVoice->toPayload()
                    : $this->allBusyVoice;
            }
        }

        return $payload;
    }
}
