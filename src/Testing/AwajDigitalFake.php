<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Testing;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

final class AwajDigitalFake
{
    /**
     * Fake AwajDigital API HTTP calls with default or customized stub responses.
     *
     * @param  array<string, Response|PromiseInterface|callable|array<mixed>>  $responses
     */
    public static function setup(array $responses = []): void
    {
        Http::fake(array_merge($responses, self::defaultResponses()));
    }

    /**
     * Default response stubs aligned with endpoints.md specifications.
     *
     * @return array<string, PromiseInterface|Response|callable|array<string, mixed>>
     */
    public static function defaultResponses(): array
    {
        return [
            // Account
            '*/balance' => Http::response([
                'success' => true,
                'balance' => 1250.75,
            ], 200),

            // Broadcasts
            '*/broadcasts/otp' => Http::response([
                'success' => true,
                'broadcast' => [
                    'id' => 101,
                    'name' => 'OTP Broadcast',
                    'status' => 'broadcasting',
                    'createdAt' => '2026-09-29T10:00:00Z',
                ],
            ], 200),

            '*/broadcasts/dynamic' => Http::response([
                'success' => true,
                'message' => 'Request accepted',
                'data' => [
                    'request_id' => '01J8TEST000000000000000000',
                ],
            ], 202),

            '*/broadcasts/direct-tts/*/status' => Http::response([
                'success' => true,
                'status' => 'completed',
                'broadcast_id' => 102,
                'error' => null,
            ], 200),

            '*/broadcasts/direct-tts' => Http::response([
                'success' => true,
                'message' => 'TTS broadcast request accepted, processing started',
                'data' => [
                    'request_id' => '01J8TEST000000000000000000',
                ],
            ], 202),

            '*/broadcasts/direct' => Http::response([
                'success' => true,
                'broadcast' => [
                    'id' => 103,
                    'name' => 'Direct Broadcast',
                    'status' => 'broadcasting',
                    'createdAt' => '2026-09-29T10:00:00Z',
                ],
            ], 200),

            '*/broadcasts/*/result' => Http::response([
                'success' => true,
                'broadcast' => [
                    'id' => 101,
                    'name' => 'Test Broadcast',
                    'status' => 'completed',
                    'listenerCount' => 1,
                    'completeCount' => 1,
                    'createdAt' => '2026-09-29T10:00:00Z',
                ],
                'isComplete' => true,
                'statusDistribution' => [
                    'answered' => 1,
                    'notAnswered' => 0,
                ],
                'results' => [
                    [
                        'phoneNumber' => '01711000000',
                        'status' => 'answered',
                        'duration' => 15,
                    ],
                ],
            ], 200),

            '*/broadcasts*' => Http::response([
                'success' => true,
                'broadcasts' => [
                    [
                        'id' => 101,
                        'name' => 'Test Broadcast',
                        'status' => 'completed',
                        'createdAt' => '2026-09-29T10:00:00Z',
                    ],
                ],
                'dateRange' => [
                    'startDate' => '2026-08-30T00:00:00Z',
                    'endDate' => '2026-09-29T00:00:00Z',
                ],
            ], 200),

            // Voices & Senders
            '*/voices/upload' => Http::response([
                'id' => 2,
                'name' => 'Uploaded Voice',
                'status' => 'pending',
            ], 201),

            '*/voices' => Http::response([
                'success' => true,
                'voices' => [
                    [
                        'id' => 1,
                        'name' => 'Main Voice',
                        'status' => 'approved',
                        'createdAt' => '2026-09-29T10:00:00Z',
                    ],
                ],
            ], 200),

            '*/senders' => Http::response([
                'success' => true,
                'senders' => [
                    [
                        'id' => 1,
                        'callingNumber' => '8809612000000',
                        'status' => 'active',
                    ],
                ],
            ], 200),

            // Surveys
            '*/v1/surveys/direct-order' => Http::response([
                'success' => true,
                'survey' => [
                    'id' => 201,
                    'name' => 'Direct Survey',
                    'status' => 'surveying',
                    'totalCount' => 1,
                    'createdAt' => '2026-09-29T10:00:00Z',
                    'metadata' => null,
                ],
            ], 200),

            '*/surveys/*/result' => Http::response([
                'success' => true,
                'survey' => [
                    'id' => 201,
                    'name' => 'Survey Test',
                    'status' => 'completed',
                    'totalCount' => 1,
                    'completeCount' => 1,
                    'createdAt' => '2026-09-29T10:00:00Z',
                    'metadata' => null,
                ],
                'isComplete' => true,
                'statusDistribution' => [
                    'answered' => 1,
                    'not_answered' => 0,
                ],
                'numbers' => [
                    [
                        'number' => '01711000000',
                        'status' => 'answered',
                        'duration' => 20,
                        'pressedKeys' => ['1'],
                    ],
                ],
            ], 200),

            '*/surveys' => Http::response([
                'success' => true,
                'survey' => [
                    'id' => 202,
                    'name' => 'Template Survey',
                    'status' => 'ready',
                    'totalCount' => 1,
                    'createdAt' => '2026-09-29T10:00:00Z',
                    'metadata' => null,
                ],
            ], 200),

            // Payments
            '*/payments/create' => Http::response([
                'success' => true,
                'payment_url' => 'https://checkout.awajdigital.com/pay/inv_test_123',
                'invoice_id' => 'inv_test_123',
            ], 200),

            '*/payments/*/status' => Http::response([
                'success' => true,
                'invoice_id' => 'inv_test_123',
                'status' => 'completed',
                'amount' => 500.0,
            ], 200),

            // Call Center
            '*/cc/agents/*/calls*' => Http::response([
                'meta' => [
                    'total' => 1,
                    'per_page' => 100,
                    'current_page' => 1,
                    'last_page' => 1,
                ],
                'data' => [
                    [
                        'id' => 1,
                        'agent_id' => 10,
                        'uuid' => 'call-uuid-123',
                        'called_number' => '01711000000',
                        'caller_number' => '01811000000',
                        'call_type' => 'outbound',
                        'status' => 'answered',
                        'duration' => 45,
                        'recording' => 'https://cdn.awajdigital.com/rec.wav',
                        'created_at' => '2026-09-29T10:00:00Z',
                    ],
                ],
            ], 200),

            '*/cc/agents' => Http::response([
                'data' => [
                    [
                        'id' => 10,
                        'full_name' => 'Agent One',
                        'email' => 'agent1@example.com',
                        'extension' => '1001',
                        'presence' => 'online',
                        'status' => 'active',
                        'approval_status' => 'approved',
                        'sender' => '8809612000000',
                    ],
                ],
            ], 200),

            '*/sdk/token' => Http::response([
                'token' => 'avt_test_token_123',
                'expires_in' => 3600,
                'expires_at' => '2026-09-29T11:00:00Z',
                'session_url' => 'https://api.awajdigital.com/sdk/session',
            ], 200),

            '*/sdk/session' => Http::response([
                'revoked' => true,
            ], 200),
        ];
    }
}
