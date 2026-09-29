<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Facades;

use Illuminate\Support\Facades\Facade;
use MdAnisujjamanBd\AwajdigitalLaravel\AwajDigital as AwajDigitalManager;
use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Account\DataTransferObjects\BalanceData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\BroadcastResultData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\BroadcastSummaryData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\DirectTtsStatusData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendBulkBroadcastData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendDirectBroadcastData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendDirectTtsBroadcastData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendDynamicBroadcastData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendOtpData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\DataTransferObjects\AgentCallData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\DataTransferObjects\AgentData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\DataTransferObjects\MintSdkTokenData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\DataTransferObjects\RevokeSessionData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Payments\DataTransferObjects\CreatePaymentData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Payments\DataTransferObjects\PaymentStatusData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Payments\DataTransferObjects\PaymentUrlData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Senders\DataTransferObjects\SenderData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\CreateDirectSurveyData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\CreateSurveyData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\SurveyResultData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Voices\DataTransferObjects\UploadVoiceData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Voices\DataTransferObjects\VoiceData;

/**
 * @method static array<string, mixed> getConfig()
 * @method static string baseUrl()
 * @method static AwajDigitalClient client()
 * @method static void fake(array<string, mixed> $responses = [])
 * @method static void assertSent(callable $callback)
 * @method static BalanceData checkBalance()
 * @method static BroadcastSummaryData sendOtp(SendOtpData $data)
 * @method static BroadcastSummaryData sendBulkBroadcast(SendBulkBroadcastData $data)
 * @method static array{request_id: string, message: string} sendDynamicBroadcast(SendDynamicBroadcastData $data)
 * @method static BroadcastSummaryData sendDirectBroadcast(SendDirectBroadcastData $data)
 * @method static array{request_id: string, message: string} sendDirectTtsBroadcast(SendDirectTtsBroadcastData $data)
 * @method static DirectTtsStatusData getDirectTtsStatus(string $requestId)
 * @method static DirectTtsStatusData pollDirectTtsStatus(string $requestId, int $maxAttempts = 10, int $intervalSeconds = 2, ?callable $onProgress = null)
 * @method static array{broadcasts: array<int, BroadcastSummaryData>, dateRange: array<string, string>|null} listBroadcasts(?string $startDate = null, ?string $endDate = null, ?string $requestId = null)
 * @method static BroadcastResultData getBroadcastResult(int $id)
 * @method static SurveyResultData createSurvey(CreateSurveyData $data)
 * @method static SurveyResultData createDirectSurvey(CreateDirectSurveyData $data)
 * @method static SurveyResultData getSurveyResult(int $id)
 * @method static array<int, VoiceData> listVoices()
 * @method static VoiceData uploadVoice(UploadVoiceData $data)
 * @method static array<int, SenderData> listSenders()
 * @method static PaymentUrlData createPayment(CreatePaymentData $data)
 * @method static PaymentStatusData getPaymentStatus(string $invoiceId)
 * @method static array<int, AgentData> listAgents()
 * @method static array{meta: array<string, int>, data: array<int, AgentCallData>} listAgentCalls(int $agentId, string $date, int $page = 1)
 * @method static MintSdkTokenData mintSdkToken(int $agentId)
 * @method static RevokeSessionData revokeSdkSession(int $agentId)
 *
 * @see AwajDigitalManager
 */
final class AwajDigital extends Facade
{
    /**
     * Get the registered name of the component.
     */
    #[\Override]
    protected static function getFacadeAccessor(): string
    {
        return AwajDigitalManager::class;
    }
}
