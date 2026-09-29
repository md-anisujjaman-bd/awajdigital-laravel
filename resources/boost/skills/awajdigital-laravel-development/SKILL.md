---
name: awajdigital-laravel-development
description: >
  Configure and apply the Awajdigital Laravel package in Laravel applications.
license: MIT
metadata:
  author: md-anisujjaman-bd
---

# Awajdigital Laravel

Use this skill when integrating the `md-anisujjaman-bd/awajdigital-laravel` package into a Laravel application.

## Configuration

Set environment variables in `.env`:

```env
AWAJDIGITAL_TOKEN=your_api_bearer_token
AWAJDIGITAL_BASE_URL=https://api.awajdigital.com/api
AWAJDIGITAL_SENDER=8801XXXXXXXXX
AWAJDIGITAL_TIMEOUT=30
```

Publish configuration if customization is needed:
```bash
php artisan vendor:publish --tag=awajdigital-config
```

## Usage via Facade

Import `MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital`.

### 1. Check Account Balance
```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;

$balance = AwajDigital::checkBalance();
// returns BalanceData: $balance->amount (float in BDT)
```

### 2. Send Voice OTP
```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendOtpData;

$result = AwajDigital::sendOtp(new SendOtpData(
    voice: 'otp_voice_v1',
    phoneNumber: '01712345678',
    otpCode: '4829',
    sender: '8801712345678', // optional, defaults to config
));
// returns BroadcastResultData or id: $result->broadcastId
```

### 3. Send Bulk Broadcast
```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendBulkBroadcastData;

$broadcast = AwajDigital::sendBulkBroadcast(new SendBulkBroadcastData(
    voice: 'campaign_announcement',
    phoneNumbers: ['01712345678', '01812345678'],
));
```

### 4. Create Template Survey
```php
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\DataTransferObjects\CreateSurveyData;

$survey = AwajDigital::createSurvey(new CreateSurveyData(
    templateName: 'customer_satisfaction_v1',
    phoneNumbers: ['01712345678'],
    webhookUrl: 'https://example.com/api/webhooks/survey',
));
```

## Testing in Applications

Use `AwajDigital::fake()` in PHPUnit/Pest tests to mock responses without making network calls:
```php
AwajDigital::fake();
```
