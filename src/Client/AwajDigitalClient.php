<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Client;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ApiException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\AuthenticationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\AwajDigitalException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ConflictException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\InsufficientBalanceException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\NotFoundException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\PermissionDeniedException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\RateLimitedException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ServerErrorException;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ValidationFailedException;
use Throwable;

final class AwajDigitalClient
{
    private string $baseUrl;

    private ?string $token;

    private int $timeout;

    /** @var array{times: int, sleep_ms: int} */
    private array $retry;

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(array $config = [])
    {
        $this->baseUrl = rtrim((string) ($config['base_url'] ?? 'https://api.awajdigital.com/api'), '/');
        $this->token = isset($config['token']) && (string) $config['token'] !== '' ? (string) $config['token'] : null;
        $this->timeout = (int) ($config['timeout'] ?? 30);

        /** @var array<string, mixed> $retryConfig */
        $retryConfig = isset($config['retry']) && is_array($config['retry']) ? $config['retry'] : [];
        $this->retry = [
            'times' => (int) ($retryConfig['times'] ?? 2),
            'sleep_ms' => (int) ($retryConfig['sleep_ms'] ?? 200),
        ];
    }

    /**
     * Send an HTTP request to the AwajDigital API.
     *
     * Note: Automatic retries are applied strictly to GET requests on connection
     * or 5xx server failures. POST and DELETE requests are never auto-retried
     * to prevent accidental duplicate actions. Rate limits (429) are surfaced
     * directly as RateLimitedException so callers can handle throttling backoff.
     *
     * @param  array<string, mixed>  $options  Supported keys: 'query', 'json', 'data', 'attach'
     *
     * @throws AuthenticationException
     * @throws PermissionDeniedException
     * @throws InsufficientBalanceException
     * @throws NotFoundException
     * @throws ConflictException
     * @throws ValidationFailedException
     * @throws RateLimitedException
     * @throws ServerErrorException
     * @throws ApiException
     */
    public function request(string $method, string $path, array $options = []): Response
    {
        $url = $this->buildUrl($path);
        $pendingRequest = $this->createPendingRequest($method, $options);

        $upperMethod = strtoupper($method);

        try {
            $response = match ($upperMethod) {
                'GET' => $pendingRequest->get($url, (array) ($options['query'] ?? [])),
                'POST' => ! empty($options['attach'])
                    ? $pendingRequest->post($url, (array) ($options['data'] ?? []))
                    : $pendingRequest->post($url, (array) ($options['json'] ?? [])),
                'DELETE' => $pendingRequest->delete($url, (array) ($options['json'] ?? [])),
                default => throw new ApiException("Unsupported HTTP method [{$method}]."),
            };
        } catch (Throwable $throwable) {
            if ($throwable instanceof AwajDigitalException) {
                throw $throwable;
            }

            throw new ServerErrorException('Network error connecting to AwajDigital: '.$throwable->getMessage(), 0, null, $throwable);
        }

        if ($response->successful()) {
            return $response;
        }

        $this->handleResponseError($response);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function createPendingRequest(string $method, array $options): PendingRequest
    {
        $pendingRequest = Http::timeout($this->timeout)->acceptJson();

        if ($this->token !== null && $this->token !== '') {
            $pendingRequest->withToken($this->token);
        }

        /** @var array<int, array{name: string, contents: resource|string, filename?: string}> $attach */
        $attach = $options['attach'] ?? [];

        if (! empty($attach)) {
            foreach ($attach as $file) {
                $pendingRequest->attach($file['name'], $file['contents'], $file['filename'] ?? null);
            }
        } else {
            $pendingRequest->asJson();
        }

        if (strtoupper($method) === 'GET' && $this->retry['times'] > 0) {
            $pendingRequest->retry(
                times: $this->retry['times'],
                sleepMilliseconds: $this->retry['sleep_ms'],
                when: fn (Throwable $exception): bool => $this->shouldRetry($exception),
                throw: false
            );
        }

        return $pendingRequest;
    }

    private function shouldRetry(Throwable $exception): bool
    {
        if ($exception instanceof ConnectionException) {
            return true;
        }

        if ($exception instanceof RequestException && $exception->response !== null) {
            return $exception->response->status() >= 500;
        }

        return false;
    }

    private function handleResponseError(Response $response): never
    {
        $status = $response->status();
        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        $message = '';
        $errorCode = null;
        /** @var array<int|string, mixed> $errors */
        $errors = [];
        $duplicatedNumber = null;

        if (isset($json['error'])) {
            // Shape B: {"error": "...", "code": "..."}
            $message = (string) $json['error'];
            $errorCode = isset($json['code']) ? (string) $json['code'] : null;
        } elseif (isset($json['message'])) {
            // Shape A: {"success": false, "message": "...", "errors": [...], "duplicated_number": "..."}
            $message = (string) $json['message'];
        } else {
            $message = $response->body() ?: "HTTP error {$status}";
        }

        if (isset($json['errors']) && is_array($json['errors'])) {
            $errors = $json['errors'];
        }

        if (isset($json['duplicated_number'])) {
            $duplicatedNumber = (string) $json['duplicated_number'];
        }

        $hint = null;
        if ($status === 403) {
            $hint = 'Check that your account has enabled the required API permission and active sender.';
        }

        match ($status) {
            401 => throw new AuthenticationException($message, $errorCode),
            402 => throw new InsufficientBalanceException($message, $errorCode),
            403 => throw new PermissionDeniedException($message, $hint, $errorCode),
            404 => throw new NotFoundException($message, $errorCode),
            409 => throw new ConflictException($message, null, $errorCode),
            400, 413, 415, 422 => throw new ValidationFailedException(
                message: $message,
                statusCode: $status,
                errors: $errors,
                duplicatedNumber: $duplicatedNumber,
                errorCode: $errorCode,
            ),
            429 => throw new RateLimitedException(
                message: $message,
                retryAfterSeconds: $response->hasHeader('Retry-After') ? (int) $response->header('Retry-After') : null,
                errorCode: $errorCode,
            ),
            500, 502 => throw new ServerErrorException($message, $status, $errorCode),
            default => throw new ApiException($message, $status, $errorCode, $json),
        };
    }

    private function buildUrl(string $path): string
    {
        $normalized = '/'.ltrim($path, '/');

        if (str_starts_with($normalized, '/api/')) {
            $normalized = substr($normalized, 4);
        }

        return $this->baseUrl.$normalized;
    }
}
