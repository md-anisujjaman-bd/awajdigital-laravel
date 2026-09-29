<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects;

/**
 * Convenience DTO for host applications handling incoming AwajDigital survey completion webhooks.
 *
 * This DTO is never sent outbound by this client; it is provided solely as a strongly-typed helper
 * for host applications receiving POST webhook requests dispatched when a survey completes.
 */
final readonly class SurveyWebhookPayloadData
{
    /**
     * @param  array<string, mixed>|null  $metadata
     * @param  array<int, array{phone_number: string, status: string, duration?: int, response?: mixed, responses?: mixed}>  $results
     */
    public function __construct(
        public int $surveyId,
        public ?array $metadata,
        public array $results,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        /** @var array<int, array{phone_number: string, status: string, duration?: int, response?: mixed, responses?: mixed}> $results */
        $results = (array) ($payload['results'] ?? []);

        return new self(
            surveyId: (int) ($payload['survey_id'] ?? 0),
            metadata: isset($payload['metadata']) && is_array($payload['metadata']) ? $payload['metadata'] : null,
            results: $results,
        );
    }
}
