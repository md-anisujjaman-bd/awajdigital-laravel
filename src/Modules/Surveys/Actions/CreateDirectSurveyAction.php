<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\Actions;

use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\CreateDirectSurveyData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\SurveyResultData;

final readonly class CreateDirectSurveyAction
{
    public function __construct(
        private AwajDigitalClient $awajDigitalClient,
        private ?string $defaultSender = null,
    ) {}

    public function execute(CreateDirectSurveyData $data): SurveyResultData
    {
        $payload = $data->toPayload($this->defaultSender);

        // Note: The direct survey endpoint lives under /v1/surveys/direct-order per API documentation
        $response = $this->awajDigitalClient->request('POST', '/v1/surveys/direct-order', [
            'json' => $payload,
        ]);

        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        return SurveyResultData::fromArray($json);
    }
}
