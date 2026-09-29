<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\Actions;

use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\CreateSurveyData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\SurveyResultData;

final readonly class CreateSurveyAction
{
    public function __construct(
        private AwajDigitalClient $client,
        private ?string $defaultSender = null,
    ) {}

    public function execute(CreateSurveyData $data): SurveyResultData
    {
        $payload = $data->toPayload($this->defaultSender);

        $response = $this->client->request('POST', '/surveys', [
            'json' => $payload,
        ]);

        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        return SurveyResultData::fromArray($json);
    }
}
