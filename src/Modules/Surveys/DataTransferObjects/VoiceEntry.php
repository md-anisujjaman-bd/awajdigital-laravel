<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects;

use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;

final readonly class VoiceEntry
{
    private function __construct(
        public ?string $name,
        public ?string $url,
        public bool $isLibrary,
    ) {}

    public static function library(string $name): self
    {
        $trimmed = trim($name);
        if ($trimmed === '') {
            throw new ClientValidationException('Library voice name cannot be empty.');
        }

        return new self(
            name: $trimmed,
            url: null,
            isLibrary: true,
        );
    }

    public static function url(string $url): self
    {
        $trimmed = trim($url);
        if ($trimmed === '' || ! filter_var($trimmed, FILTER_VALIDATE_URL)) {
            throw new ClientValidationException("Invalid voice URL: [{$url}].");
        }

        return new self(
            name: null,
            url: $trimmed,
            isLibrary: false,
        );
    }

    /**
     * @return array{type: 'voice', name: string}|string
     */
    public function toPayload(): array|string
    {
        if ($this->isLibrary) {
            return [
                'type' => 'voice',
                'name' => (string) $this->name,
            ];
        }

        return (string) $this->url;
    }
}
