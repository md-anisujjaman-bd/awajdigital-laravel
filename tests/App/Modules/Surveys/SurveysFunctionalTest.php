<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Tests\App\Modules\Surveys;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\Actions\CreateDirectSurveyAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\Actions\CreateSurveyAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\Actions\GetSurveyResultAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\CreateDirectSurveyData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\CreateSurveyData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\DtmfOptionData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\SurveyWebhookPayloadData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\VoiceEntry;
use MdAnisujjamanBd\AwajdigitalLaravel\Tests\TestCase;

final class SurveysFunctionalTest extends TestCase
{
    private AwajDigitalClient $client;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new AwajDigitalClient([
            'token' => 'test-token',
            'default_sender' => '8809612000000',
        ]);
    }

    public function test_create_template_survey_success(): void
    {
        // Arrange
        Http::fake([
            '*/surveys' => Http::response([
                'success' => true,
                'survey' => [
                    'id' => 201,
                    'name' => 'Template Survey',
                    'status' => 'ready',
                    'totalCount' => 1,
                    'createdAt' => '2026-09-29T10:00:00Z',
                ],
            ], 200),
        ]);

        $action = new CreateSurveyAction($this->client, '8809612000000');
        $data = new CreateSurveyData(
            templateName: 'customer_satisfaction',
            phoneNumbers: ['01711111111'],
        );

        // Act
        $survey = $action->execute($data);

        // Assert
        $this->assertSame(201, $survey->id);
        $this->assertSame('ready', $survey->status);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.awajdigital.com/api/surveys'
                && $request->method() === 'POST'
                && $request->data()['template_name'] === 'customer_satisfaction';
        });
    }

    public function test_create_direct_survey_success(): void
    {
        // Arrange
        Http::fake([
            '*/v1/surveys/direct-order' => Http::response([
                'success' => true,
                'survey' => [
                    'id' => 202,
                    'name' => 'Direct Order Survey',
                    'status' => 'surveying',
                    'totalCount' => 1,
                    'createdAt' => '2026-09-29T10:00:00Z',
                ],
            ], 200),
        ]);

        $action = new CreateDirectSurveyAction($this->client, '8809612000000');
        $data = new CreateDirectSurveyData(
            phoneNumbers: ['01711111111'],
            questionVoices: [
                VoiceEntry::library('question_voice_1'),
                VoiceEntry::url('https://cdn.example.com/q2.wav'),
            ],
            dtmfOptions: [
                new DtmfOptionData(
                    key: '1',
                    voices: [VoiceEntry::library('answer_1_ack')],
                ),
            ],
        );

        // Act
        $survey = $action->execute($data);

        // Assert
        $this->assertSame(202, $survey->id);
        $this->assertSame('surveying', $survey->status);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.awajdigital.com/api/v1/surveys/direct-order'
                && $request->method() === 'POST'
                && count($request->data()['question_voices']) === 2
                && $request->data()['dtmf_options'][0]['key'] === '1';
        });
    }

    public function test_create_direct_survey_rejects_duplicate_dtmf_keys_client_side(): void
    {
        // Arrange
        Http::fake();

        // Act & Assert
        try {
            new CreateDirectSurveyData(
                phoneNumbers: ['01711111111'],
                questionVoices: [VoiceEntry::library('q1')],
                dtmfOptions: [
                    new DtmfOptionData(key: '1'),
                    new DtmfOptionData(key: '1'), // Duplicate key
                ],
            );
            $this->fail('Expected ClientValidationException was not thrown.');
        } catch (ClientValidationException $e) {
            $this->assertStringContainsString('Duplicate DTMF option key detected', $e->getMessage());
            Http::assertNothingSent();
        }
    }

    public function test_get_survey_result_success(): void
    {
        // Arrange
        Http::fake([
            '*/surveys/201/result' => Http::response([
                'success' => true,
                'survey' => [
                    'id' => 201,
                    'name' => 'Survey Test',
                    'status' => 'completed',
                    'totalCount' => 1,
                    'completeCount' => 1,
                ],
                'isComplete' => true,
                'statusDistribution' => [
                    'answered' => 1,
                    'not_answered' => 0,
                ],
                'numbers' => [
                    [
                        'number' => '01711111111',
                        'status' => 'answered',
                        'duration' => 25,
                        'pressedKeys' => ['1'],
                    ],
                ],
            ], 200),
        ]);

        $action = new GetSurveyResultAction($this->client);

        // Act
        $result = $action->execute(201);

        // Assert
        $this->assertSame(201, $result->id);
        $this->assertTrue($result->isComplete);
        $this->assertSame('completed', $result->status);
        $this->assertCount(1, $result->numbers);
        $this->assertSame(['1'], $result->numbers[0]->pressedKeys);
    }

    public function test_survey_webhook_payload_data_can_be_parsed(): void
    {
        // Arrange
        $payload = [
            'survey_id' => 201,
            'metadata' => ['order_id' => '12345'],
            'results' => [
                [
                    'phone_number' => '01711111111',
                    'status' => 'answered',
                    'duration' => 30,
                    'response' => '1',
                ],
            ],
        ];

        // Act
        $dto = SurveyWebhookPayloadData::fromArray($payload);

        // Assert
        $this->assertSame(201, $dto->surveyId);
        $this->assertSame(['order_id' => '12345'], $dto->metadata);
        $this->assertCount(1, $dto->results);
        $this->assertSame('01711111111', $dto->results[0]['phone_number']);
        $this->assertSame('1', $dto->results[0]['response']);
    }
}
