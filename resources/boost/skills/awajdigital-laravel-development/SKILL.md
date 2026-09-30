---
name: awajdigital-laravel-development
description: >
  Integrate the AwajDigital Voice Broadcasting, IVR Surveys, Audio Management, Payments, and Call Center SDK in Laravel applications.
license: MIT
metadata:
  author: md-anisujjaman-bd
---

# Awajdigital Laravel Development

## Primary Goal

Provide idiomatic, strongly-typed guidance for Laravel applications adopting the `md-anisujjaman-bd/awajdigital-laravel` package to dispatch voice broadcasts, send OTPs, conduct IVR surveys, manage audio, process balance recharge payments, and issue call center browser SDK tokens.

## Workflow

1. **Install & Configure**:
   - `composer require md-anisujjaman-bd/awajdigital-laravel`
   - Publish config: `php artisan vendor:publish --tag="awajdigital-config"`
   - Add `.env` credentials (`AWAJDIGITAL_TOKEN`, `AWAJDIGITAL_BASE_URL`, `AWAJDIGITAL_SENDER`, `AWAJDIGITAL_TIMEOUT`).
2. **Interact via Facade**:
   - Use `MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital` as the primary entry point.
3. **Use Strongly-Typed DTOs**:
   - Provision action inputs via final readonly DTOs under `MdAnisujjamanBd\AwajdigitalLaravel\Modules\<Domain>\DataTransferObjects`.
4. **Handle Webhooks & Async Status**:
   - For Surveys: parse incoming webhooks using `SurveyWebhookPayloadData::fromArray($request->all())`.
   - For TTS/Dynamic: check status with `getDirectTtsStatus()` or `pollDirectTtsStatus()`.
5. **Test With Fakes**:
   - Always call `AwajDigital::fake()` in PHPUnit/Pest test setups. No live HTTP calls in tests.

## References

| Key | Default | Description |
|---|---|---|
| `AWAJDIGITAL_BASE_URL` | `https://api.awajdigital.com/api` | AwajDigital API Base URL |
| `AWAJDIGITAL_TOKEN` | `null` | API Bearer Token |
| `AWAJDIGITAL_SENDER` | `null` | Default approved caller ID (e.g. `8809612000000`) |
| `AWAJDIGITAL_TIMEOUT` | `30` | Request timeout in seconds |

### Core Facade Methods
- **Account**: `AwajDigital::checkBalance(): BalanceData`
- **Broadcasts**:
  - `sendOtp(SendOtpData): BroadcastSummaryData`
  - `sendBulkBroadcast(SendBulkBroadcastData): BroadcastSummaryData`
  - `sendDynamicBroadcast(SendDynamicBroadcastData): array{request_id: string, message: string}`
  - `sendDirectBroadcast(SendDirectBroadcastData): BroadcastSummaryData`
  - `sendDirectTtsBroadcast(SendDirectTtsBroadcastData): array{request_id: string, message: string}`
  - `getDirectTtsStatus(string $requestId): DirectTtsStatusData`
  - `pollDirectTtsStatus(string $requestId, int $maxAttempts = 10, int $interval = 2): DirectTtsStatusData`
  - `listBroadcasts(?string $startDate, ?string $endDate, ?string $requestId): array`
  - `getBroadcastResult(int $id): BroadcastResultData`
- **Surveys**:
  - `createSurvey(CreateSurveyData): SurveyResultData`
  - `createDirectSurvey(CreateDirectSurveyData): SurveyResultData`
  - `getSurveyResult(int $id): SurveyResultData`
- **Voices & Senders**:
  - `listVoices(): array<int, VoiceData>`
  - `uploadVoice(UploadVoiceData): VoiceData`
  - `listSenders(): array<int, SenderData>`
- **Payments**:
  - `createPayment(CreatePaymentData): PaymentUrlData`
  - `getPaymentStatus(string $invoiceId): PaymentStatusData`
- **Call Center**:
  - `listAgents(): array<int, AgentData>`
  - `listAgentCalls(int $agentId, string $date, int $page = 1): array`
  - `mintSdkToken(int $agentId): MintSdkTokenData`
  - `revokeSdkSession(int $agentId): RevokeSessionData`
- **Testing**:
  - `AwajDigital::fake(array $responses = []): void`
  - `AwajDigital::assertSent(callable $callback): void`

## Examples

### 1. Check Account Balance
```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;

$balance = AwajDigital::checkBalance();
// $balance->amount (float), $balance->currency (string, default 'BDT')
```

### 2. Send Voice OTP
```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendOtpData;

$broadcast = AwajDigital::sendOtp(new SendOtpData(
    voice: 'otp_prompt_v1',
    phoneNumber: '01712345678',
    otpCode: '4829',
    sender: '8809612000000', // optional, defaults to AWAJDIGITAL_SENDER
));
// $broadcast->id (int), $broadcast->status (string)
```

### 3. Bulk & Direct Audio Broadcasts
```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendBulkBroadcastData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendDirectBroadcastData;

// Approved library voice to 1-999 recipients
$bulk = AwajDigital::sendBulkBroadcast(new SendBulkBroadcastData(
    voice: 'campaign_announcement',
    phoneNumbers: ['01711111111', '01822222222'],
));

// Direct hosted audio URLs (WAV/8000Hz/mono)
$direct = AwajDigital::sendDirectBroadcast(new SendDirectBroadcastData(
    phoneNumbers: ['01711111111'],
    voices: ['https://cdn.example.com/audio/announcement.wav'],
));
```

### 4. Dynamic & Direct TTS Broadcasts
```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\DynamicRecipientData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendDirectTtsBroadcastData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendDynamicBroadcastData;

// Dynamic broadcast (1-10 recipients with custom variables)
$dynamic = AwajDigital::sendDynamicBroadcast(new SendDynamicBroadcastData(
    voice: 'bill_reminder',
    recipients: [
        new DynamicRecipientData('01711111111', ['name' => 'Rahim', 'amount' => '1500']),
    ],
));

// AI Text-to-Speech broadcast and automatic polling
$tts = AwajDigital::sendDirectTtsBroadcast(new SendDirectTtsBroadcastData(
    phoneNumbers: ['01711111111'],
    texts: ['আপনার অর্ডারটি কনফার্ম হয়েছে।'],
    voice: 'female',
    languageCode: 'bn-BD',
));

$status = AwajDigital::pollDirectTtsStatus($tts['request_id']);
// $status->status ('completed'|'failed'), $status->broadcastId
```

### 5. IVR Surveys & Webhook Handling
```php
use Illuminate\Http\Request;
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\CreateDirectSurveyData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\CreateSurveyData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\DtmfOptionData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\SurveyWebhookPayloadData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\VoiceEntry;

// Template survey
$survey = AwajDigital::createSurvey(new CreateSurveyData(
    templateName: 'nps_feedback',
    phoneNumbers: ['01711111111'],
    webhookUrl: 'https://example.com/api/webhooks/survey',
));

// Direct interactive IVR survey tree
$directSurvey = AwajDigital::createDirectSurvey(new CreateDirectSurveyData(
    phoneNumbers: ['01711111111'],
    questionVoices: [VoiceEntry::library('satisfaction_question')],
    dtmfOptions: [
        new DtmfOptionData(key: '1', voices: [VoiceEntry::library('ack_yes')]),
        new DtmfOptionData(key: '2', voices: [VoiceEntry::library('ack_no')]),
    ],
    webhookUrl: 'https://example.com/api/webhooks/survey',
));

// In Webhook Controller
Route::post('/api/webhooks/survey', function (Request $request) {
    $payload = SurveyWebhookPayloadData::fromArray($request->all());
    foreach ($payload->results as $result) {
        // $result['phone_number'], $result['status'], $result['response']
    }
    return response()->json(['status' => 'acknowledged']);
});
```

### 6. Voices & Senders
```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Voices\DataTransferObjects\UploadVoiceData;

$voices = AwajDigital::listVoices();
$senders = AwajDigital::listSenders();

// Upload audio from file path or UploadedFile
$uploaded = AwajDigital::uploadVoice(UploadVoiceData::fromPath(
    filePath: storage_path('app/audio/welcome.wav'),
    name: 'Welcome Greeting',
));
```

### 7. Payments (Account Balance Top-Up)
```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Payments\DataTransferObjects\CreatePaymentData;

$payment = AwajDigital::createPayment(new CreatePaymentData(
    amount: 500.0, // Minimum 20 BDT
    successUrl: 'https://example.com/payment/success',
    cancelUrl: 'https://example.com/payment/cancelled',
));

// Redirect user: redirect($payment->paymentUrl)
// Verify: AwajDigital::getPaymentStatus($payment->invoiceId)
```

### 8. Call Center Browser SDK Tokens
```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;

// Issue widget session token for agent
$token = AwajDigital::mintSdkToken(agentId: 10);
// $token->token, $token->expiresIn, $token->sessionUrl

// Revoke active browser session
AwajDigital::revokeSdkSession(agentId: 10);
```

### 9. Application Testing
```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;

AwajDigital::fake();

// Execute application code...

AwajDigital::assertSent(function ($request) {
    return str_contains($request->url(), '/broadcasts/otp');
});
```

## Anti-Patterns

- **Exposing secrets**: Never pass `AWAJDIGITAL_TOKEN` or call-center credentials to frontend clients. Only mint temporary browser session tokens via `mintSdkToken()`.
- **Skipping typed DTOs**: Never construct raw payload arrays manually. Always use the provided DTOs (`SendOtpData`, `SendBulkBroadcastData`, etc.) to benefit from client-side validation.
- **Ignoring client validation**: Do not send duplicate recipient numbers, audio files > 10MB, or non-HTTPS URLs—these are rejected client-side before touching the network.
- **Making live API calls in tests**: Never omit `AwajDigital::fake()` during automated test execution.
- **Missing sender configuration**: Do not forget to configure `AWAJDIGITAL_SENDER` in `.env` if omitting the explicit `sender:` parameter in broadcast DTOs.
