<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel;

final class AwajDigital
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly array $config = [],
    ) {}

    /**
     * Get a configuration value or the entire configuration array.
     */
    public function config(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->config;
        }

        return $this->config[$key] ?? $default;
    }

    /**
     * Get the configured base URL.
     */
    public function baseUrl(): string
    {
        return (string) ($this->config['base_url'] ?? 'https://api.awajdigital.com/api');
    }
}
