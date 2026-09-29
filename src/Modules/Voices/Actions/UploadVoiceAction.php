<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Voices\Actions;

use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Voices\DataTransferObjects\UploadVoiceData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Voices\DataTransferObjects\VoiceData;

final readonly class UploadVoiceAction
{
    public function __construct(
        private AwajDigitalClient $client,
    ) {}

    public function execute(UploadVoiceData $data): VoiceData
    {
        $response = $this->client->request('POST', '/voices/upload', [
            'data' => [
                'name' => $data->name,
            ],
            'attach' => [
                [
                    'name' => 'audio',
                    'contents' => $data->contents,
                    'filename' => $data->filename,
                ],
            ],
        ]);

        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        return VoiceData::fromArray($json);
    }
}
