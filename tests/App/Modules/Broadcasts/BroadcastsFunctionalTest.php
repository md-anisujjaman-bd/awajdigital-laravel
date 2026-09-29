<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Tests\App\Modules\Broadcasts;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use MdAnisujjamanBd\AwajdigitalLaravel\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ConflictException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\PermissionDeniedException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\RateLimitedException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ServerErrorException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ValidationFailedException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Actions\GetBroadcastResultAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Actions\GetDirectTtsStatusAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Actions\ListBroadcastsAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Actions\SendBulkBroadcastAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Actions\SendDirectBroadcastAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Actions\SendDirectTtsBroadcastAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Actions\SendDynamicBroadcastAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Actions\SendOtpAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\BroadcastSummaryData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\DynamicRecipientData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendBulkBroadcastData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendDirectBroadcastData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendDirectTtsBroadcastData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendDynamicBroadcastData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendOtpData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Shared\RequestId;
use MdAnisujjamanBd\AwajdigitalLaravel\Tests\TestCase;

final class BroadcastsFunctionalTest extends TestCase
{
    private AwajDigitalClient $awajDigitalClient;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->awajDigitalClient = new AwajDigitalClient([
            'token' => 'test-token',
            'default_sender' => '8809612000000',
        ]);
    }

    public function test_send_otp_success(): void
    {
        // Arrange
        Http::fake([
            '*/broadcasts/otp' => Http::response([
                'success' => true,
                'broadcast' => [
                    'id' => 101,
                    'name' => 'OTP Broadcast',
                    'status' => 'broadcasting',
                    'createdAt' => '2026-09-29T10:00:00Z',
                ],
            ], 200),
        ]);

        $action = new SendOtpAction($this->awajDigitalClient, '8809612000000');
        $requestId = RequestId::from('01J8TEST000000000000000000');

        $data = new SendOtpData(
            voice: 'otp_voice',
            phoneNumber: '01712345678',
            otpCode: '1234',
            requestId: $requestId,
        );

        // Act
        $broadcastSummaryData = $action->execute($data);

        // Assert
        $this->assertSame(101, $broadcastSummaryData->id);
        $this->assertSame('OTP Broadcast', $broadcastSummaryData->name);
        $this->assertSame('broadcasting', $broadcastSummaryData->status);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.awajdigital.com/api/broadcasts/otp'
                && $request->method() === 'POST'
                && $request->data()['phone_number'] === '01712345678'
                && $request->data()['otp_code'] === '1234'
                && $request->data()['sender'] === '8809612000000';
        });
    }

    public function test_send_otp_fails_client_validation_for_invalid_otp_code(): void
    {
        // Arrange
        Http::fake();

        // Act & Assert
        try {
            new SendOtpData(
                voice: 'otp_voice',
                phoneNumber: '01712345678',
                otpCode: '12', // Less than 4 digits
            );
            $this->fail('Expected ClientValidationException was not thrown.');
        } catch (ClientValidationException $clientValidationException) {
            $this->assertStringContainsString('OTP code must be between 4 and 6 digits', $clientValidationException->getMessage());
            Http::assertNothingSent();
        }
    }

    public function test_send_otp_maps_server_400_and_403_and_409(): void
    {
        // Arrange
        Http::fakeSequence()
            ->push(['success' => false, 'message' => 'Voice lacks digit mode'], 400)
            ->push(['success' => false, 'message' => 'Sender not approved'], 403)
            ->push(['success' => false, 'message' => 'Duplicate request_id'], 409);

        $action = new SendOtpAction($this->awajDigitalClient, '8809612000000');
        $data = new SendOtpData(
            voice: 'otp_voice',
            phoneNumber: '01712345678',
            otpCode: '1234',
        );

        // Act & Assert 400
        $this->assertThrows(fn (): BroadcastSummaryData => $action->execute($data), ValidationFailedException::class);

        // Act & Assert 403
        $this->assertThrows(fn (): BroadcastSummaryData => $action->execute($data), PermissionDeniedException::class);

        // Act & Assert 409 (Idempotency conflict)
        $this->assertThrows(fn (): BroadcastSummaryData => $action->execute($data), ConflictException::class);
    }

    public function test_send_bulk_broadcast_success(): void
    {
        // Arrange
        Http::fake([
            '*/broadcasts' => Http::response([
                'success' => true,
                'broadcast' => [
                    'id' => 102,
                    'name' => 'Bulk Voice',
                    'status' => 'broadcasting',
                ],
            ], 200),
        ]);

        $action = new SendBulkBroadcastAction($this->awajDigitalClient, '8809612000000');
        $data = new SendBulkBroadcastData(
            voice: 'main_voice',
            phoneNumbers: ['01711111111', '01822222222'],
        );

        // Act
        $broadcastSummaryData = $action->execute($data);

        // Assert
        $this->assertSame(102, $broadcastSummaryData->id);
        $this->assertSame('Bulk Voice', $broadcastSummaryData->name);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.awajdigital.com/api/broadcasts'
                && $request->method() === 'POST'
                && count($request->data()['phone_numbers']) === 2;
        });
    }

    public function test_send_bulk_broadcast_client_validation_rejects_duplicate_numbers(): void
    {
        // Arrange
        Http::fake();

        // Act & Assert
        try {
            new SendBulkBroadcastData(
                voice: 'main_voice',
                phoneNumbers: ['01711111111', '01711111111'],
            );
            $this->fail('Expected ClientValidationException was not thrown.');
        } catch (ClientValidationException $clientValidationException) {
            $this->assertStringContainsString('Duplicate recipient phone number detected', $clientValidationException->getMessage());
            Http::assertNothingSent();
        }
    }

    public function test_send_dynamic_broadcast_success_and_idempotency_semantics(): void
    {
        // Arrange
        Http::fakeSequence()
            ->push(['success' => true, 'message' => 'Request accepted', 'data' => ['request_id' => '01J8TEST000000000000000000']], 202)
            ->push(['success' => true, 'message' => 'Request already accepted', 'data' => ['request_id' => '01J8TEST000000000000000000']], 202)
            ->push(['message' => 'Request failed: voice missing dynamic part'], 500);

        $action = new SendDynamicBroadcastAction($this->awajDigitalClient, '8809612000000');
        $data = new SendDynamicBroadcastData(
            voice: 'dyn_voice',
            recipients: [
                new DynamicRecipientData('01711111111', ['name' => 'Rahim']),
            ],
            requestId: RequestId::from('01J8TEST000000000000000000'),
        );

        // Act 1: Initial dispatch (202)
        $result1 = $action->execute($data);

        // Assert 1
        $this->assertSame('01J8TEST000000000000000000', $result1['request_id']);
        $this->assertSame('Request accepted', $result1['message']);

        // Act 2: Idempotent replay within 15 min (202)
        $result2 = $action->execute($data);

        // Assert 2
        $this->assertSame('Request already accepted', $result2['message']);

        // Act 3: Retry of a failed request yields 500 per endpoints.md
        $this->assertThrows(fn (): array => $action->execute($data), ServerErrorException::class);
    }

    public function test_send_direct_broadcast_success(): void
    {
        // Arrange
        Http::fake([
            '*/broadcasts/direct' => Http::response([
                'success' => true,
                'broadcast' => [
                    'id' => 103,
                    'name' => 'Direct Audio',
                    'status' => 'broadcasting',
                ],
            ], 200),
        ]);

        $action = new SendDirectBroadcastAction($this->awajDigitalClient, '8809612000000');
        $data = new SendDirectBroadcastData(
            phoneNumbers: ['01711111111'],
            voices: ['https://cdn.example.com/audio1.wav'],
        );

        // Act
        $broadcastSummaryData = $action->execute($data);

        // Assert
        $this->assertSame(103, $broadcastSummaryData->id);
    }

    public function test_send_direct_tts_broadcast_and_rate_limit(): void
    {
        // Arrange
        Http::fakeSequence()
            ->push(['success' => true, 'message' => 'TTS broadcast request accepted, processing started', 'data' => ['request_id' => '01J8TEST000000000000000000']], 202)
            ->push(['success' => false, 'message' => 'Rate limit exceeded: 1 req/sec'], 429, ['Retry-After' => '1']);

        $action = new SendDirectTtsBroadcastAction($this->awajDigitalClient, '8809612000000');
        $data = new SendDirectTtsBroadcastData(
            phoneNumbers: ['01711111111'],
            texts: ['আপনার ওটিপি কোড হলো ১ ২ ৩ ৪'],
        );

        // Act 1: Success 202
        $result = $action->execute($data);

        // Assert 1
        $this->assertSame('01J8TEST000000000000000000', $result['request_id']);

        // Act 2: 429 Rate limit
        try {
            $action->execute($data);
            $this->fail('Expected RateLimitedException was not thrown.');
        } catch (RateLimitedException $rateLimitedException) {
            $this->assertSame(1, $rateLimitedException->retryAfterSeconds);
        }
    }

    public function test_get_direct_tts_status(): void
    {
        // Arrange
        Http::fake([
            '*/broadcasts/direct-tts/01J8TEST000000000000000000/status' => Http::response([
                'success' => true,
                'status' => 'completed',
                'broadcast_id' => 105,
            ], 200),
        ]);

        $action = new GetDirectTtsStatusAction($this->awajDigitalClient);

        // Act
        $directTtsStatusData = $action->execute('01J8TEST000000000000000000');

        // Assert
        $this->assertSame('completed', $directTtsStatusData->status);
        $this->assertSame(105, $directTtsStatusData->broadcastId);
        $this->assertNull($directTtsStatusData->error);
    }

    public function test_list_broadcasts_validates_90_days_limit_client_side(): void
    {
        // Arrange
        Http::fake();
        $action = new ListBroadcastsAction($this->awajDigitalClient);

        // Act & Assert
        try {
            $action->execute(
                startDate: '2026-01-01T00:00:00Z',
                endDate: '2026-05-01T00:00:00Z', // 120 days > 90 days
            );
            $this->fail('Expected ClientValidationException was not thrown.');
        } catch (ClientValidationException $clientValidationException) {
            $this->assertStringContainsString('Date range cannot exceed 90 days', $clientValidationException->getMessage());
            Http::assertNothingSent();
        }
    }

    public function test_get_broadcast_result_success(): void
    {
        // Arrange
        Http::fake([
            '*/broadcasts/101/result' => Http::response([
                'success' => true,
                'broadcast' => [
                    'id' => 101,
                    'name' => 'Broadcast Test',
                    'status' => 'completed',
                    'listenerCount' => 1,
                    'completeCount' => 1,
                ],
                'isComplete' => true,
                'statusDistribution' => [
                    'answered' => 1,
                    'notAnswered' => 0,
                ],
                'results' => [
                    [
                        'phoneNumber' => '01711111111',
                        'status' => 'answered',
                        'duration' => 20,
                    ],
                ],
            ], 200),
        ]);

        $action = new GetBroadcastResultAction($this->awajDigitalClient);

        // Act
        $broadcastResultData = $action->execute(101);

        // Assert
        $this->assertSame(101, $broadcastResultData->id);
        $this->assertTrue($broadcastResultData->isComplete);
        $this->assertSame(1, $broadcastResultData->completeCount);
        $this->assertCount(1, (array) $broadcastResultData->results);
    }

    public function test_list_broadcasts_with_request_id_filter(): void
    {
        // Arrange
        Http::fake([
            '*/broadcasts*' => Http::response([
                'success' => true,
                'broadcasts' => [
                    [
                        'id' => 101,
                        'name' => 'Dynamic Broadcast 1',
                        'status' => 'completed',
                        'createdAt' => '2026-09-29T10:00:00Z',
                    ],
                ],
                'dateRange' => null,
            ], 200),
        ]);

        $action = new ListBroadcastsAction($this->awajDigitalClient);

        // Act
        $result = $action->execute(requestId: '01J8TEST000000000000000000');

        // Assert
        $this->assertCount(1, $result['broadcasts']);

        Http::assertSent(function (Request $request): bool {
            return str_contains($request->url(), '/broadcasts')
                && ($request['request_id'] ?? null) === '01J8TEST000000000000000000';
        });
    }

    public function test_get_broadcast_result_in_progress_parses_message(): void
    {
        // Arrange
        Http::fake([
            '*/broadcasts/102/result' => Http::response([
                'success' => true,
                'broadcast' => [
                    'id' => 102,
                    'name' => 'Running Broadcast',
                    'status' => 'broadcasting',
                    'listenerCount' => 3,
                    'completeCount' => 1,
                ],
                'isComplete' => false,
                'message' => 'Broadcast is still in progress',
            ], 200),
        ]);

        $action = new GetBroadcastResultAction($this->awajDigitalClient);

        // Act
        $broadcastResultData = $action->execute(102);

        // Assert
        $this->assertSame(102, $broadcastResultData->id);
        $this->assertFalse($broadcastResultData->isComplete);
        $this->assertSame('Broadcast is still in progress', $broadcastResultData->message);
    }

    public function test_poll_direct_tts_status_polls_until_completed(): void
    {
        // Arrange
        Http::fakeSequence()
            ->push(['success' => true, 'status' => 'pending'], 200)
            ->push(['success' => true, 'status' => 'processing'], 200)
            ->push(['success' => true, 'status' => 'completed', 'broadcast_id' => 777], 200);

        $manager = new AwajDigital(['token' => 'test-token']);

        $trackedAttempts = [];

        // Act
        $directTtsStatusData = $manager->pollDirectTtsStatus(
            requestId: '01J8TEST000000000000000000',
            maxAttempts: 5,
            intervalSeconds: 0,
            onProgress: function ($currentStatus, $attempt) use (&$trackedAttempts): void {
                $trackedAttempts[] = [$attempt, $currentStatus->status];
            },
        );

        // Assert
        $this->assertSame('completed', $directTtsStatusData->status);
        $this->assertSame(777, $directTtsStatusData->broadcastId);
        $this->assertCount(3, $trackedAttempts);
        $this->assertSame([1, 'pending'], $trackedAttempts[0]);
        $this->assertSame([2, 'processing'], $trackedAttempts[1]);
        $this->assertSame([3, 'completed'], $trackedAttempts[2]);
    }
}
