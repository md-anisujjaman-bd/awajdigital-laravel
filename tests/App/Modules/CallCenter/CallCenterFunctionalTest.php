<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Tests\App\Modules\CallCenter;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\RateLimitedException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\Actions\ListAgentCallsAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\Actions\ListAgentsAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\Actions\MintSdkTokenAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\Actions\RevokeSdkSessionAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Tests\TestCase;

final class CallCenterFunctionalTest extends TestCase
{
    private AwajDigitalClient $client;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new AwajDigitalClient(['token' => 'test-token']);
    }

    public function test_list_agents_success(): void
    {
        // Arrange
        Http::fake([
            '*/cc/agents' => Http::response([
                'data' => [
                    [
                        'id' => 10,
                        'full_name' => 'Agent Rahim',
                        'email' => 'rahim@example.com',
                        'extension' => '1001',
                        'presence' => 'online',
                        'status' => 'active',
                        'approval_status' => 'approved',
                        'sender' => '8809612000000',
                    ],
                ],
            ], 200),
        ]);

        $action = new ListAgentsAction($this->client);

        // Act
        $agents = $action->execute();

        // Assert
        $this->assertCount(1, $agents);
        $this->assertSame(10, $agents[0]->id);
        $this->assertSame('Agent Rahim', $agents[0]->fullName);
        $this->assertSame('online', $agents[0]->presence);
    }

    public function test_list_agents_handles_rate_limit_429(): void
    {
        // Arrange
        Http::fake([
            '*/cc/agents' => Http::response([
                'error' => 'Too many requests',
                'code' => 'rate_limited',
            ], 429),
        ]);

        $action = new ListAgentsAction($this->client);

        // Act & Assert
        $this->expectException(RateLimitedException::class);
        $action->execute();
    }

    public function test_list_agent_calls_success(): void
    {
        // Arrange
        Http::fake([
            '*/cc/agents/10/calls*' => Http::response([
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
                        'called_number' => '01711111111',
                        'caller_number' => '8809612000000',
                        'call_type' => 'outbound',
                        'status' => 'answered',
                        'duration' => 60,
                        'recording' => 'https://cdn.example.com/rec1.wav',
                        'created_at' => '2026-09-29T10:00:00Z',
                    ],
                ],
            ], 200),
        ]);

        $action = new ListAgentCallsAction($this->client);

        // Act
        $response = $action->execute(10, '2026-09-29', 1);

        // Assert
        $this->assertSame(1, $response['meta']['total']);
        $this->assertCount(1, $response['data']);
        $this->assertSame('call-uuid-123', $response['data'][0]->uuid);
        $this->assertSame('answered', $response['data'][0]->status);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.awajdigital.com/api/cc/agents/10/calls?date=2026-09-29&page=1';
        });
    }

    public function test_list_agent_calls_validates_date_format_client_side(): void
    {
        // Arrange
        Http::fake();
        $action = new ListAgentCallsAction($this->client);

        // Act & Assert
        try {
            $action->execute(10, '29-09-2026'); // Not YYYY-MM-DD
            $this->fail('Expected ClientValidationException was not thrown.');
        } catch (ClientValidationException $e) {
            $this->assertStringContainsString('Date must be formatted as YYYY-MM-DD', $e->getMessage());
            Http::assertNothingSent();
        }
    }

    public function test_mint_sdk_token_success(): void
    {
        // Arrange
        Http::fake([
            '*/sdk/token' => Http::response([
                'token' => 'avt_sample_token_xyz',
                'expires_in' => 3600,
                'expires_at' => '2026-09-29T11:00:00Z',
                'session_url' => 'https://api.awajdigital.com/sdk/session',
            ], 200),
        ]);

        $action = new MintSdkTokenAction($this->client);

        // Act
        $tokenData = $action->execute(10);

        // Assert
        $this->assertSame('avt_sample_token_xyz', $tokenData->token);
        $this->assertSame(3600, $tokenData->expiresIn);
        $this->assertSame('https://api.awajdigital.com/sdk/session', $tokenData->sessionUrl);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.awajdigital.com/api/sdk/token'
                && $request->method() === 'POST'
                && $request->data()['agent_id'] === 10;
        });
    }

    public function test_revoke_sdk_session_success(): void
    {
        // Arrange
        Http::fake([
            '*/sdk/session' => Http::response([
                'revoked' => true,
            ], 200),
        ]);

        $action = new RevokeSdkSessionAction($this->client);

        // Act
        $revokeData = $action->execute(10);

        // Assert
        $this->assertTrue($revokeData->revoked);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.awajdigital.com/api/sdk/session'
                && $request->method() === 'DELETE'
                && $request->data()['agent_id'] === 10;
        });
    }
}
