<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\Actions;

use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\SurveyResultData;

final readonly class GetSurveyResultAction
{
    public function __construct(
        private AwajDigitalClient $client,
    ) {}

    public function execute(int $id): SurveyResultData
    {
        if ($id <= 0) {
            throw new ClientValidationException('Survey ID must be a positive integer.');
        }

        $response = $this->client->request('GET', "/surveys/{$id}/result");

        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        return SurveyResultData::fromArray($json);
    }
}
