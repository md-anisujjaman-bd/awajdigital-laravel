# AwajDigital Laravel

> **Notice:** This is an unofficial package, not affiliated with or endorsed by AwajDigital.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/md-anisujjaman-bd/awajdigital-laravel.svg?style=flat-square)](https://packagist.org/packages/md-anisujjaman-bd/awajdigital-laravel)
[![Tests](https://img.shields.io/github/actions/workflow/status/md-anisujjaman-bd/awajdigital-laravel/php-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/md-anisujjaman-bd/awajdigital-laravel/actions)
[![License](https://img.shields.io/packagist/l/md-anisujjaman-bd/awajdigital-laravel.svg?style=flat-square)](https://packagist.org/packages/md-anisujjaman-bd/awajdigital-laravel)
[![PHP Version](https://img.shields.io/packagist/php-v/md-anisujjaman-bd/awajdigital-laravel.svg?style=flat-square)](https://packagist.org/packages/md-anisujjaman-bd/awajdigital-laravel)

An idiomatic, strongly-typed Laravel client for the [AwajDigital](https://awajdigital.com) Voice Broadcasting, IVR Surveys, Audio Management, and Call Center SDK APIs.

## Requirements

- PHP `^8.3`
- Laravel `^12.0` or `^13.0`

## Installation

Install the package via Composer:

```bash
composer require md-anisujjaman-bd/awajdigital-laravel
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="awajdigital-config"
```

## Configuration

Add your AwajDigital credentials to your `.env` file:

```dotenv
AWAJDIGITAL_BASE_URL=https://api.awajdigital.com/api
AWAJDIGITAL_TOKEN=your_api_bearer_token_here
AWAJDIGITAL_SENDER=8809612000000
AWAJDIGITAL_TIMEOUT=30
```

> [!TIP]
> You can generate and manage your API Bearer token in the **AwajDigital Dashboard → API Tokens**.

| Environment Variable | Default | Description |
|---|---|---|
| `AWAJDIGITAL_BASE_URL` | `https://api.awajdigital.com/api` | API Base URL |
| `AWAJDIGITAL_TOKEN` | `null` | AwajDigital API Bearer Token |
| `AWAJDIGITAL_SENDER` | `null` | Default approved sender number (e.g. `8809612000000`) |
| `AWAJDIGITAL_TIMEOUT` | `30` | HTTP request timeout in seconds |

## Quick Start

```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendBulkBroadcastData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendOtpData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\CreateSurveyData;

// 1. Check account balance
$balance = AwajDigital::checkBalance();
echo "Current balance: {$balance->amount} {$balance->currency}";

// 2. Send Voice OTP
$otpBroadcast = AwajDigital::sendOtp(new SendOtpData(
    voice: 'otp_voice_part',
    phoneNumber: '01712345678',
    otpCode: '5842',
));

// 3. Send Bulk Audio Broadcast (up to 999 numbers)
$bulkBroadcast = AwajDigital::sendBulkBroadcast(new SendBulkBroadcastData(
    voice: 'promotional_campaign_v1',
    phoneNumbers: ['01711111111', '01822222222'],
));

// 4. Create Template-based IVR Survey
$survey = AwajDigital::createSurvey(new CreateSurveyData(
    templateName: 'nps_feedback_survey',
    phoneNumbers: ['01711111111'],
));
```

## Usage by Area

### 1. Broadcasts

#### Voice OTP (`POST /broadcasts/otp`)
Voice OTP dispatches an audio prompt containing a dynamic digit-mode part.
```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendOtpData;

$broadcast = AwajDigital::sendOtp(new SendOtpData(
    voice: 'otp_voice',
    phoneNumber: '01712345678',
    otpCode: '8491',
));
```

#### Bulk Broadcast (`POST /broadcasts`)
Broadcast an approved library voice to up to 999 unique recipients.
```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendBulkBroadcastData;

$broadcast = AwajDigital::sendBulkBroadcast(new SendBulkBroadcastData(
    voice: 'announcement_voice',
    phoneNumbers: ['01711111111', '01822222222'],
));
```

#### Dynamic Broadcast (`POST /broadcasts/dynamic`)
Asynchronous broadcast (returns HTTP 202) personalizing approved voices with dynamic keys (`tts`, `number`, `digit`, `character`, `audio_url`) for 1–10 recipients.
```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\DynamicRecipientData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendDynamicBroadcastData;

$response = AwajDigital::sendDynamicBroadcast(new SendDynamicBroadcastData(
    voice: 'due_payment_reminder',
    recipients: [
        new DynamicRecipientData('01711111111', ['customer_name' => 'Rahim', 'amount' => '1500']),
        new DynamicRecipientData('01822222222', ['customer_name' => 'Karim', 'amount' => '2200']),
    ],
));

// Returns array{request_id: string, message: string}
$requestId = $response['request_id'];
```

#### Direct Broadcast (`POST /broadcasts/direct`)
Broadcast 1–10 direct CDN audio URLs (WAV/8000Hz/mono/pcm_s16le) to up to 999 recipients.
```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendDirectBroadcastData;

$broadcast = AwajDigital::sendDirectBroadcast(new SendDirectBroadcastData(
    phoneNumbers: ['01711111111'],
    voices: ['https://cdn.example.com/audio/message.wav'],
));
```

#### Direct TTS Broadcast (`POST /broadcasts/direct-tts`)
Dispatches raw text converted on the fly via AwajDigital's AI TTS engine (rate limited to 1 req/sec). Returns HTTP 202.
```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendDirectTtsBroadcastData;

$response = AwajDigital::sendDirectTtsBroadcast(new SendDirectTtsBroadcastData(
    phoneNumbers: ['01711111111'],
    texts: ['আপনার অ্যাকাউন্ট নবায়ন সফল হয়েছে।'],
    voice: 'female',
    languageCode: 'bn-BD',
));

// Check status by request ID
$status = AwajDigital::getDirectTtsStatus($response['request_id']);
if ($status->status === 'completed') {
    $broadcastId = $status->broadcastId;
}
```

#### List Broadcasts & Results
```php
// List broadcasts from the last 30 days (date range maximum: 90 days)
$history = AwajDigital::listBroadcasts(
    startDate: '2026-09-01T00:00:00Z',
    endDate: '2026-09-29T00:00:00Z',
);

// Get detailed call results and status distribution
$result = AwajDigital::getBroadcastResult($broadcastId);
echo "Completed: {$result->completeCount}, Answered: {$result->statusDistribution['answered']}";
```

---

### 2. Surveys (IVR)

#### Template Survey (`POST /surveys`)
```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\CreateSurveyData;

$survey = AwajDigital::createSurvey(new CreateSurveyData(
    templateName: 'published_template_slug',
    phoneNumbers: ['01711111111'],
    webhookUrl: 'https://example.com/api/webhooks/survey-completed',
));
```

#### Direct Survey (`POST /v1/surveys/direct-order`)
Define a full dynamic IVR tree without a pre-published template, combining library voices or CDN URLs with DTMF branch options.
```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\CreateDirectSurveyData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\DtmfOptionData;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\VoiceEntry;

$survey = AwajDigital::createDirectSurvey(new CreateDirectSurveyData(
    phoneNumbers: ['01711111111'],
    questionVoices: [
        VoiceEntry::library('intro_question_voice'),
    ],
    dtmfOptions: [
        new DtmfOptionData(key: '1', voices: [VoiceEntry::library('ack_yes_voice')]),
        new DtmfOptionData(key: '2', voices: [VoiceEntry::library('ack_no_voice')]),
    ],
    retryCount: 1,
    webhookUrl: 'https://example.com/api/webhooks/survey-completed',
));
```

#### Handling Survey Completion Webhook
Use `SurveyWebhookPayloadData` in your webhook controller to parse the incoming JSON payload:
```php
use Illuminate\Http\Request;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\SurveyWebhookPayloadData;

Route::post('/api/webhooks/survey-completed', function (Request $request) {
    $payload = SurveyWebhookPayloadData::fromArray($request->all());

    foreach ($payload->results as $result) {
        // $result['phone_number'], $result['status'], $result['response']
    }

    return response()->json(['status' => 'acknowledged']);
});
```

---

### 3. Voices & Senders

```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Voices\DataTransferObjects\UploadVoiceData;

// List approved and pending account voices
$voices = AwajDigital::listVoices();

// Upload a new audio file (MP3, WAV, OGG, M4A, AAC, WEBM, FLAC; max 10MB)
// Option A: From local path
$upload = AwajDigital::uploadVoice(UploadVoiceData::fromPath(
    filePath: storage_path('app/audio/prompt.wav'),
    name: 'Customer Support Welcome',
));

// Option B: From controller UploadedFile
// $upload = AwajDigital::uploadVoice(UploadVoiceData::fromUploadedFile($request->file('audio')));

// List approved caller IDs / senders
$senders = AwajDigital::listSenders();
```

---

### 4. Payments

AwajDigital provides a hosted checkout portal for refilling account balances.
```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Payments\DataTransferObjects\CreatePaymentData;

// Initiate payment (minimum 20 BDT; URLs must use HTTPS)
$payment = AwajDigital::createPayment(new CreatePaymentData(
    amount: 500.0,
    successUrl: 'https://example.com/billing/payment-success',
    cancelUrl: 'https://example.com/billing/payment-cancelled',
));

// Redirect user to payment URL
return redirect($payment->paymentUrl);

// Verify status and trigger balance fulfillment
$status = AwajDigital::getPaymentStatus($payment->invoiceId);
if ($status->status === 'completed') {
    // Payment verified and account credited
}
```

---

### 5. Call Center (Agents & SDK)

Manage call center agents and issue temporary session tokens for browser widgets.
```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;

// List agents
$agents = AwajDigital::listAgents();

// Query agent call logs for a specific date (YYYY-MM-DD)
$callLogs = AwajDigital::listAgentCalls(
    agentId: 10,
    date: '2026-09-29',
    page: 1,
);

// Mint a temporary SDK token for the browser widget
$tokenData = AwajDigital::mintSdkToken(agentId: 10);
// Returns: tokenData->token, tokenData->expiresIn, tokenData->sessionUrl

// Revoke an active browser session
AwajDigital::revokeSdkSession(agentId: 10);
```

---

## Call Widget (Browser Embed)

This package handles backend token issuance and session revocation. The browser call widget (`amarvoice-call.js`) runs client-side in the browser and connects via WebRTC/SIP directly to AwajDigital.

### Example Token Route

Create a secure backend endpoint in your Laravel application:

```php
use Illuminate\Http\Request;
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;

Route::middleware('auth:sanctum')->post('/api/call-center/token', function (Request $request) {
    $tokenData = AwajDigital::mintSdkToken($request->user()->call_center_agent_id);

    return response()->json($tokenData);
});
```

The frontend widget is loaded from AwajDigital's CDN on an allowlisted HTTPS origin:

```html
<script src="https://dashboard.awajdigital.com/sdk/amarvoice-call.js"></script>
```

For complete client-side JavaScript widget options and event listeners, refer to the [AwajDigital Browser Widget Documentation](https://awajdigital.com/api-docs/sdk/widget).

---

## Exceptions

All exceptions thrown by this package extend `MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\AwajDigitalException`.

| Exception Class | HTTP Status | Trigger Condition |
|---|---|---|
| `ClientValidationException` | *None* | Thrown **locally before sending HTTP requests** (e.g. invalid phone number, file > 10MB, texts > 10, date range > 90 days). |
| `AuthenticationException` | 401 | Invalid or missing API Bearer token. |
| `InsufficientBalanceException` | 402 | Account balance is insufficient to initiate the broadcast. |
| `PermissionDeniedException` | 403 | Missing feature permission (e.g. Direct Broadcast, AI TTS) or unapproved sender. Provides `$e->hint`. |
| `NotFoundException` | 404 | Broadcast, survey, invoice, or agent ID not found. |
| `ConflictException` | 409 | Duplicate `request_id` reused within the 15-minute window on conflict endpoints. |
| `ValidationFailedException` | 400, 413, 415, 422 | Server-side validation failure. Access raw error details via `$e->errors` and `$e->duplicatedNumber`. |
| `RateLimitedException` | 429 | Rate limit exceeded (e.g. Direct TTS 1 req/sec). Retry delay in seconds via `$e->retryAfterSeconds`. |
| `ServerErrorException` | 500, 502 | Remote server error, gateway issue, or retry of a failed dynamic broadcast. |
| `ApiException` | Any | Generic fallback for unexpected HTTP responses. |

> [!NOTE]
> Client-side validation checks run before any network requests are dispatched, preventing unnecessary API calls and preserving your rate limits.

---

## Testing Your Own App

When writing tests in your Laravel application, use `AwajDigital::fake()`:

```php
use Illuminate\Support\Facades\Http;
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendOtpData;

public function test_it_dispatches_otp_broadcast(): void
{
    // Preload standard success responses for all AwajDigital endpoints
    AwajDigital::fake();

    // Call your application logic
    $this->postJson('/api/request-voice-otp', [
        'phone' => '01712345678',
    ])->assertOk();

    // Assert that the OTP request was sent
    AwajDigital::assertSent(function ($request) {
        return str_contains($request->url(), '/broadcasts/otp')
            && $request->data()['phone_number'] === '01712345678';
    });
}
```

You can also pass custom stub overrides:

```php
AwajDigital::fake([
    '*/balance' => Http::response(['success' => true, 'balance' => 9999.0], 200),
]);
```

---

## AI-Assisted Development

This repository includes tailored `.ai/` guidelines and agent skills for AI coding assistants. For host applications using [Laravel Boost](https://github.com/laravel/boost), a bundled skill is available under `resources/boost/skills/awajdigital-laravel-development/SKILL.md` to assist AI tools in writing accurate AwajDigital client code.

---

## Contributing

Please review the [Contributing Guide](.github/CONTRIBUTING.md) for details on code standards and testing.

## Security

If you discover a security vulnerability, please review the [Security Policy](.github/SECURITY.md).

## License

AwajDigital Laravel is open-sourced software licensed under the [MIT license](LICENSE.md).
