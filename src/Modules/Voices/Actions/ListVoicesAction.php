<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Voices\Actions;

use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Voices\DataTransferObjects\VoiceData;

final readonly class ListVoicesAction
{
    public function __construct(
        private AwajDigitalClient $awajDigitalClient,
    ) {}

    /**
     * @return array<int, VoiceData>
     */
    public function execute(): array
    {
        $response = $this->awajDigitalClient->request('GET', '/voices');

        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        /** @var array<int, array<string, mixed>> $rawVoices */
        $rawVoices = (array) ($json['voices'] ?? []);

        return array_map(
            fn (array $v): VoiceData => VoiceData::fromArray($v),
            $rawVoices
        );
    }
}
