<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel;

use Illuminate\Support\Facades\Http;
use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Account\Actions\GetBalanceAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Account\DataTransferObjects\BalanceData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Actions\GetBroadcastResultAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Actions\GetDirectTtsStatusAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Actions\ListBroadcastsAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Actions\SendBulkBroadcastAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Actions\SendDirectBroadcastAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Actions\SendDirectTtsBroadcastAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Actions\SendDynamicBroadcastAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Actions\SendOtpAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\BroadcastResultData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\BroadcastSummaryData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\DirectTtsStatusData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendBulkBroadcastData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendDirectBroadcastData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendDirectTtsBroadcastData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendDynamicBroadcastData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendOtpData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\Actions\ListAgentCallsAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\Actions\ListAgentsAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\Actions\MintSdkTokenAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\Actions\RevokeSdkSessionAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\DataTransferObjects\AgentCallData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\DataTransferObjects\AgentData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\DataTransferObjects\MintSdkTokenData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\DataTransferObjects\RevokeSessionData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Payments\Actions\CreatePaymentAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Payments\Actions\GetPaymentStatusAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Payments\DataTransferObjects\CreatePaymentData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Payments\DataTransferObjects\PaymentStatusData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Payments\DataTransferObjects\PaymentUrlData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Senders\Actions\ListSendersAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Senders\DataTransferObjects\SenderData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\Actions\CreateDirectSurveyAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\Actions\CreateSurveyAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\Actions\GetSurveyResultAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\CreateDirectSurveyData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\CreateSurveyData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\SurveyResultData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Voices\Actions\ListVoicesAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Voices\Actions\UploadVoiceAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Voices\DataTransferObjects\UploadVoiceData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Voices\DataTransferObjects\VoiceData;
use MdAnisujjamanBd\AwajdigitalLaravel\Testing\AwajDigitalFake;

final class AwajDigital
{
    private ?AwajDigitalClient $awajDigitalClient = null;

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly array $config = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    public function baseUrl(): string
    {
        return (string) ($this->config['base_url'] ?? 'https://api.awajdigital.com/api');
    }

    public function client(): AwajDigitalClient
    {
        if ($this->awajDigitalClient === null) {
            $this->awajDigitalClient = new AwajDigitalClient($this->config);
        }

        return $this->awajDigitalClient;
    }

    private function defaultSender(): ?string
    {
        $sender = (string) ($this->config['default_sender'] ?? '');

        return $sender !== '' ? $sender : null;
    }

    /**
     * Fake AwajDigital API HTTP calls with sensible default stubs or customized responses.
     *
     * @param  array<string, mixed>  $responses
     */
    public static function fake(array $responses = []): void
    {
        AwajDigitalFake::setup($responses);
    }

    /**
     * Assert that a specific request was sent through the HTTP client.
     */
    public static function assertSent(callable $callback): void
    {
        Http::assertSent($callback);
    }

    // Account
    public function checkBalance(): BalanceData
    {
        return (new GetBalanceAction($this->client()))->execute();
    }

    // Broadcasts
    public function sendOtp(SendOtpData $data): BroadcastSummaryData
    {
        return (new SendOtpAction($this->client(), $this->defaultSender()))->execute($data);
    }

    public function sendBulkBroadcast(SendBulkBroadcastData $data): BroadcastSummaryData
    {
        return (new SendBulkBroadcastAction($this->client(), $this->defaultSender()))->execute($data);
    }

    /**
     * @return array{request_id: string, message: string}
     */
    public function sendDynamicBroadcast(SendDynamicBroadcastData $data): array
    {
        return (new SendDynamicBroadcastAction($this->client(), $this->defaultSender()))->execute($data);
    }

    public function sendDirectBroadcast(SendDirectBroadcastData $data): BroadcastSummaryData
    {
        return (new SendDirectBroadcastAction($this->client(), $this->defaultSender()))->execute($data);
    }

    /**
     * @return array{request_id: string, message: string}
     */
    public function sendDirectTtsBroadcast(SendDirectTtsBroadcastData $data): array
    {
        return (new SendDirectTtsBroadcastAction($this->client(), $this->defaultSender()))->execute($data);
    }

    public function getDirectTtsStatus(string $requestId): DirectTtsStatusData
    {
        return (new GetDirectTtsStatusAction($this->client()))->execute($requestId);
    }

    /**
     * Poll a Direct TTS broadcast request until completed or failed, or max attempts reached.
     *
     * @param  int  $maxAttempts  Maximum polling attempts (default 10)
     * @param  int  $intervalSeconds  Sleep interval in seconds between attempts (default 2)
     * @param  (callable(DirectTtsStatusData, int): void)|null  $onProgress  Optional progress callback
     */
    public function pollDirectTtsStatus(
        string $requestId,
        int $maxAttempts = 10,
        int $intervalSeconds = 2,
        ?callable $onProgress = null,
    ): DirectTtsStatusData {
        $status = $this->getDirectTtsStatus($requestId);

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            if ($onProgress !== null) {
                $onProgress($status, $attempt);
            }

            if ($status->status !== 'pending' && $status->status !== 'processing') {
                return $status;
            }

            if ($attempt < $maxAttempts && $intervalSeconds > 0) {
                sleep($intervalSeconds);
            }

            if ($attempt < $maxAttempts) {
                $status = $this->getDirectTtsStatus($requestId);
            }
        }

        return $status;
    }

    /**
     * @return array{broadcasts: array<int, BroadcastSummaryData>, dateRange: array<string, string>|null}
     */
    public function listBroadcasts(?string $startDate = null, ?string $endDate = null, ?string $requestId = null): array
    {
        return (new ListBroadcastsAction($this->client()))->execute($startDate, $endDate, $requestId);
    }

    public function getBroadcastResult(int $id): BroadcastResultData
    {
        return (new GetBroadcastResultAction($this->client()))->execute($id);
    }

    // Surveys
    public function createSurvey(CreateSurveyData $data): SurveyResultData
    {
        return (new CreateSurveyAction($this->client(), $this->defaultSender()))->execute($data);
    }

    public function createDirectSurvey(CreateDirectSurveyData $data): SurveyResultData
    {
        return (new CreateDirectSurveyAction($this->client(), $this->defaultSender()))->execute($data);
    }

    public function getSurveyResult(int $id): SurveyResultData
    {
        return (new GetSurveyResultAction($this->client()))->execute($id);
    }

    // Voices & Senders
    /**
     * @return array<int, VoiceData>
     */
    public function listVoices(): array
    {
        return (new ListVoicesAction($this->client()))->execute();
    }

    public function uploadVoice(UploadVoiceData $data): VoiceData
    {
        return (new UploadVoiceAction($this->client()))->execute($data);
    }

    /**
     * @return array<int, SenderData>
     */
    public function listSenders(): array
    {
        return (new ListSendersAction($this->client()))->execute();
    }

    // Payments
    public function createPayment(CreatePaymentData $data): PaymentUrlData
    {
        return (new CreatePaymentAction($this->client()))->execute($data);
    }

    public function getPaymentStatus(string $invoiceId): PaymentStatusData
    {
        return (new GetPaymentStatusAction($this->client()))->execute($invoiceId);
    }

    // Call Center
    /**
     * @return array<int, AgentData>
     */
    public function listAgents(): array
    {
        return (new ListAgentsAction($this->client()))->execute();
    }

    /**
     * @return array{meta: array<string, int>, data: array<int, AgentCallData>}
     */
    public function listAgentCalls(int $agentId, string $date, int $page = 1): array
    {
        return (new ListAgentCallsAction($this->client()))->execute($agentId, $date, $page);
    }

    public function mintSdkToken(int $agentId): MintSdkTokenData
    {
        return (new MintSdkTokenAction($this->client()))->execute($agentId);
    }

    public function revokeSdkSession(int $agentId): RevokeSessionData
    {
        return (new RevokeSdkSessionAction($this->client()))->execute($agentId);
    }
}
