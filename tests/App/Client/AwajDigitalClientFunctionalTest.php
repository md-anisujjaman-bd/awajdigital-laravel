<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Tests\App\Client;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ApiException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\AuthenticationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ConflictException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\InsufficientBalanceException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\NotFoundException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\PermissionDeniedException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\RateLimitedException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ServerErrorException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ValidationFailedException;
use MdAnisujjamanBd\AwajdigitalLaravel\Tests\TestCase;

final class AwajDigitalClientFunctionalTest extends TestCase
{
    public function test_it_sends_authorization_and_accept_headers(): void
    {
        // Arrange
        Http::fake([
            '*/balance' => Http::response(['success' => true, 'balance' => 500.0], 200),
        ]);

        $client = new AwajDigitalClient([
            'base_url' => 'https://api.awajdigital.com/api',
            'token' => 'test-bearer-token-12345',
        ]);

        // Act
        $client->request('GET', '/balance');

        // Assert
        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.awajdigital.com/api/balance'
                && $request->hasHeader('Authorization', 'Bearer test-bearer-token-12345')
                && $request->hasHeader('Accept', 'application/json');
        });
    }

    public function test_it_maps_401_to_authentication_exception(): void
    {
        // Arrange
        Http::fake([
            '*/balance' => Http::response(['success' => false, 'message' => 'Unauthorized'], 401),
        ]);

        $client = new AwajDigitalClient(['token' => 'invalid-token']);

        // Act & Assert
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Unauthorized');

        $client->request('GET', '/balance');
    }

    public function test_it_maps_402_to_insufficient_balance_exception(): void
    {
        // Arrange
        Http::fake([
            '*/broadcasts' => Http::response(['success' => false, 'message' => 'Insufficient account balance'], 402),
        ]);

        $client = new AwajDigitalClient(['token' => 'valid-token']);

        // Act & Assert
        $this->expectException(InsufficientBalanceException::class);
        $this->expectExceptionMessage('Insufficient account balance');

        $client->request('POST', '/broadcasts', ['json' => []]);
    }

    public function test_it_maps_403_to_permission_denied_exception_with_hint(): void
    {
        // Arrange
        Http::fake([
            '*/broadcasts/direct' => Http::response(['success' => false, 'message' => 'Direct broadcast permission not enabled'], 403),
        ]);

        $client = new AwajDigitalClient(['token' => 'valid-token']);

        // Act & Assert
        try {
            $client->request('POST', '/broadcasts/direct', ['json' => []]);
            $this->fail('Expected PermissionDeniedException was not thrown.');
        } catch (PermissionDeniedException $permissionDeniedException) {
            $this->assertStringContainsString('Direct broadcast permission not enabled', $permissionDeniedException->getMessage());
            $this->assertNotNull($permissionDeniedException->hint);
            $this->assertStringContainsString('permission', $permissionDeniedException->hint);
        }
    }

    public function test_it_maps_404_to_not_found_exception(): void
    {
        // Arrange
        Http::fake([
            '*/broadcasts/999/result' => Http::response(['success' => false, 'message' => 'Broadcast not found'], 404),
        ]);

        $client = new AwajDigitalClient(['token' => 'valid-token']);

        // Act & Assert
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Broadcast not found');

        $client->request('GET', '/broadcasts/999/result');
    }

    public function test_it_maps_409_to_conflict_exception(): void
    {
        // Arrange
        Http::fake([
            '*/broadcasts/otp' => Http::response(['success' => false, 'message' => 'Duplicate request_id within 15 minutes'], 409),
        ]);

        $client = new AwajDigitalClient(['token' => 'valid-token']);

        // Act & Assert
        $this->expectException(ConflictException::class);
        $this->expectExceptionMessage('Duplicate request_id within 15 minutes');

        $client->request('POST', '/broadcasts/otp', ['json' => []]);
    }

    public function test_it_maps_400_validation_failed_with_errors_and_duplicate_number(): void
    {
        // Arrange
        Http::fake([
            '*/broadcasts' => Http::response([
                'success' => false,
                'message' => 'Validation error',
                'duplicated_number' => '01711000000',
                'errors' => [
                    ['field' => 'phone_numbers', 'issue' => 'Contains duplicates'],
                ],
            ], 400),
        ]);

        $client = new AwajDigitalClient(['token' => 'valid-token']);

        // Act & Assert
        try {
            $client->request('POST', '/broadcasts', ['json' => []]);
            $this->fail('Expected ValidationFailedException was not thrown.');
        } catch (ValidationFailedException $validationFailedException) {
            $this->assertSame(400, $validationFailedException->statusCode);
            $this->assertSame('Validation error', $validationFailedException->getMessage());
            $this->assertSame('01711000000', $validationFailedException->duplicatedNumber);
            $this->assertCount(1, $validationFailedException->errors);
        }
    }

    public function test_it_maps_429_to_rate_limited_exception_with_retry_after(): void
    {
        // Arrange
        Http::fake([
            '*/broadcasts/direct-tts' => Http::response(
                ['success' => false, 'message' => 'Too many requests'],
                429,
                ['Retry-After' => '5']
            ),
        ]);

        $client = new AwajDigitalClient(['token' => 'valid-token']);

        // Act & Assert
        try {
            $client->request('POST', '/broadcasts/direct-tts', ['json' => []]);
            $this->fail('Expected RateLimitedException was not thrown.');
        } catch (RateLimitedException $rateLimitedException) {
            $this->assertSame(429, $rateLimitedException->statusCode);
            $this->assertSame(5, $rateLimitedException->retryAfterSeconds);
        }
    }

    public function test_it_maps_500_to_server_error_exception(): void
    {
        // Arrange
        Http::fake([
            '*/broadcasts/dynamic' => Http::response(['message' => 'Internal server error'], 500),
        ]);

        $client = new AwajDigitalClient(['token' => 'valid-token']);

        // Act & Assert
        $this->expectException(ServerErrorException::class);
        $this->expectExceptionMessage('Internal server error');

        $client->request('POST', '/broadcasts/dynamic', ['json' => []]);
    }

    public function test_it_maps_call_center_error_shape_correctly(): void
    {
        // Arrange
        Http::fake([
            '*/cc/agents/10/calls*' => Http::response([
                'error' => 'Agent not found',
                'code' => 'agent_not_found',
            ], 404),
        ]);

        $client = new AwajDigitalClient(['token' => 'valid-token']);

        // Act & Assert
        try {
            $client->request('GET', '/cc/agents/10/calls');
            $this->fail('Expected NotFoundException was not thrown.');
        } catch (NotFoundException $notFoundException) {
            $this->assertSame(404, $notFoundException->statusCode);
            $this->assertSame('Agent not found', $notFoundException->getMessage());
            $this->assertSame('agent_not_found', $notFoundException->errorCode);
        }
    }

    public function test_it_maps_unexpected_status_to_api_exception(): void
    {
        // Arrange
        Http::fake([
            '*/balance' => Http::response(['message' => 'Teapot'], 418),
        ]);

        $client = new AwajDigitalClient(['token' => 'valid-token']);

        // Act & Assert
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Teapot');

        $client->request('GET', '/balance');
    }

    public function test_it_never_leaks_token_or_sip_password_in_exception(): void
    {
        // Arrange
        $secretToken = 'super-secret-token-xyz-987';
        $sipPassword = 'secret-sip-pass-123';

        Http::fake([
            '*/sdk/token' => Http::response([
                'error' => 'Agent inactive',
                'code' => 'agent_inactive',
                'sip_password' => $sipPassword,
            ], 403),
        ]);

        $client = new AwajDigitalClient(['token' => $secretToken]);

        // Act & Assert
        try {
            $client->request('POST', '/sdk/token', ['json' => ['agent_id' => 10]]);
            $this->fail('Expected PermissionDeniedException was not thrown.');
        } catch (PermissionDeniedException $permissionDeniedException) {
            $this->assertStringNotContainsString($secretToken, $permissionDeniedException->getMessage());
            $this->assertStringNotContainsString($sipPassword, $permissionDeniedException->getMessage());

            foreach ($permissionDeniedException->context as $value) {
                if (is_string($value)) {
                    $this->assertStringNotContainsString($secretToken, $value);
                    $this->assertStringNotContainsString($sipPassword, $value);
                }
            }
        }
    }

    public function test_get_request_retries_on_server_error_but_post_does_not(): void
    {
        // Arrange
        $getAttempts = 0;
        $postAttempts = 0;

        Http::fake([
            '*/balance' => function () use (&$getAttempts) {
                $getAttempts++;

                return Http::response(['error' => 'Server Error'], 500);
            },
            '*/broadcasts' => function () use (&$postAttempts) {
                $postAttempts++;

                return Http::response(['error' => 'Server Error'], 500);
            },
        ]);

        $client = new AwajDigitalClient([
            'token' => 'valid-token',
            'retry' => [
                'times' => 3,
                'sleep_ms' => 10,
            ],
        ]);

        // Act & Assert GET
        try {
            $client->request('GET', '/balance');
        } catch (ServerErrorException) {
            // Expected
        }

        // 1 initial + 2 retries = 3 attempts
        $this->assertSame(3, $getAttempts);

        // Act & Assert POST
        try {
            $client->request('POST', '/broadcasts', ['json' => []]);
        } catch (ServerErrorException) {
            // Expected
        }

        // Exactly 1 attempt, POST is never auto-retried
        $this->assertSame(1, $postAttempts);
    }
}
