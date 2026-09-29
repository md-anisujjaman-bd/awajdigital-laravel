> Canonical: https://awajdigital.com/api-docs

# AwajDigital API Documentation

## Overview

Voice Broadcasting API Documentation for Bangladesh.

- **Version:** 1.0
- **Base URL:** `https://api.awajdigital.com/api`

The AwajDigital REST API lets you send Voice OTPs, broadcast voice messages to multiple numbers, run automated voice surveys, upload voices, check your balance, list call-center agents and their calls, and embed a click-to-call widget in your own site — all programmatically.

## Postman Collection

Download the [Postman collection](/amarvoice-api.postman_collection.json) and import it into Postman to test the API interactively.

## Endpoints

- [Authentication](/api-docs/authentication) — Bearer token setup and required headers
- [Check Balance](/api-docs/balance) — retrieve your current account balance
- [Voice OTP Broadcast](/api-docs/broadcasts/otp) — send an OTP voice call to a single number
- [Broadcast to Multiple Numbers](/api-docs/broadcasts/multiple) — bulk voice messaging
- [Dynamic Broadcast](/api-docs/broadcasts/dynamic) — personalized broadcast with per-recipient data
- [Direct Broadcast](/api-docs/broadcasts/direct) — CDN audio URLs, no pre-approved voice
- [Direct TTS Broadcast](/api-docs/broadcasts/direct-tts) — plain text converted to speech with AI TTS
- [Get Direct TTS Broadcast Status](/api-docs/broadcasts/direct-tts-status) — poll a direct TTS request's processing status
- [List Broadcasts](/api-docs/broadcasts/list) — query broadcasts by date range
- [Get Broadcast Result](/api-docs/broadcasts/result) — per-number call outcomes
- [List Voices](/api-docs/voices/list) — your voices and their approval status
- [Upload Voice](/api-docs/voices/upload) — upload a new audio file
- [List Senders](/api-docs/senders) — your caller sender numbers
- [Create Survey](/api-docs/surveys/create) — start an IVR voice survey campaign
- [Direct Survey](/api-docs/surveys/direct) — start a survey without a template, using library voices or audio URLs
- [Get Survey Result](/api-docs/surveys/result) — survey responses and pressed keys
- [Survey Webhooks](/api-docs/surveys/webhooks) — completion webhook payload
- [Create Payment](/api-docs/payments/create) — create a balance-recharge payment
- [Get Payment Status](/api-docs/payments/status) — verify a payment server-to-server
- [List Agents](/api-docs/call-center/agents) — call-center agents on your account
- [List Agent Calls](/api-docs/call-center/calls) — one agent's outgoing calls for a Dhaka day
- [Embed a Call Widget](/api-docs/sdk) — put calling on your own website
- [Mint SDK Token](/api-docs/sdk/token) — your server gets a token for one agent
- [Exchange Session](/api-docs/sdk/session) — the widget uses that token to connect
- [Revoke Session](/api-docs/sdk/revoke) — end an agent's session when they log out
- [Browser Widget](/api-docs/sdk/widget) — open the phone, dial, mute, hang up
- [Error Responses](/api-docs/errors) — HTTP status codes and error payloads

---

## Authentication

All API requests require authentication using a Bearer token. You can obtain your API token from your dashboard account settings.

The [browser widget](/api-docs/sdk) is the exception: it sends a short-lived token from your backend to [Exchange Session](/api-docs/sdk/session). Do not put your Bearer token on the embed page.

:::caution[Important]
Keep your API token secure and never share it publicly.
:::

## Example Request Headers

```
Authorization: Bearer your_api_token_here
Accept: application/json
```

**Required Headers:**

- `Authorization` - Bearer token for authentication
- `Accept` - Must be set to `application/json` to receive JSON responses
- `Content-Type` - Required for POST requests, must be `application/json`

## Postman Collection

Download the [Postman collection](/amarvoice-api.postman_collection.json) and import it into Postman to test the API.

---

## Check Balance

`GET /api/balance`

Retrieve the current account balance for the authenticated user. This endpoint returns the available balance that can be used for broadcasts and surveys.

## Request Example

```php
<?php
// PHP with cURL
$url = 'https://api.awajdigital.com/api/balance';
$token = 'your_api_token_here';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

## Success Response Example

```json
{
  "success": true,
  "balance": 1250.75
}
```

:::note[Balance]
The balance is returned as a number representing the amount in your account currency (BDT). Use this endpoint to check your balance before creating broadcasts or surveys.
:::

---

## Voice OTP Broadcast

`POST /api/broadcasts/otp`

Send OTP (One-Time Password) voice messages to a single phone number. The voice must contain at least one dynamic part with digit mode to read the OTP code.

## Request Parameters

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| request_id | string | Yes | Unique request identifier (16-64 chars) to prevent duplicate requests. Use UUID or random string. |
| voice | string | Yes | Name of your approved voice with digit mode |
| sender | string | Yes | Your active caller sender number |
| phone_number | string | Yes | Single Bangladeshi phone number (01XXXXXXXXX format) |
| otp_code | string | Yes | OTP code (4-6 digits) |

## Request Example

```php
<?php
// PHP with cURL
$url = 'https://api.awajdigital.com/api/broadcasts/otp';
$token = 'your_api_token_here';

$data = [
    'request_id' => 'unique_request_id_123',
    'voice' => 'your_voice_name',
    'sender' => '8801234567890',
    'phone_number' => '019XXXXXXXX',
    'otp_code' => '1234'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

## Success Response Example

```json
{
  "success": true,
  "broadcast": {
    "id": 123,
    "name": "otp_api_1_your_voice_019XXXXXXXX",
    "status": "broadcasting",
    "createdAt": "2025-12-29T10:30:00.000Z"
  }
}
```

:::note
Use a unique `request_id` for each request to prevent duplicate processing. Requests with the same `request_id` within 15 minutes will be rejected.
:::

---

## Broadcast to Multiple Numbers

`POST /api/broadcasts`

Send voice messages to multiple phone numbers in a single API call. Perfect for bulk announcements, notifications, or marketing campaigns.

## Request Parameters

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| request_id | string | Yes | Unique request identifier (16-64 chars) to prevent duplicate requests. Use UUID or random string. |
| voice | string | Yes | Name of your approved voice |
| sender | string | Yes | Your active caller sender number |
| phone_numbers | array | Yes | Array of Bangladeshi phone numbers (01XXXXXXXXX format, max 999 numbers) |

## Request Example

```php
<?php
// PHP with cURL
$url = 'https://api.awajdigital.com/api/broadcasts';
$token = 'your_api_token_here';

$data = [
    'request_id' => 'unique_request_id_456',
    'voice' => 'your_voice_name',
    'sender' => '8801234567890',
    'phone_numbers' => ['019XXXXXXXX', '018XXXXXXXX', '017XXXXXXXX']
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

## Success Response Example

```json
{
  "success": true,
  "broadcast": {
    "id": 123,
    "name": "api_1_your_voice_3",
    "status": "broadcasting",
    "createdAt": "2025-12-29T10:30:00.000Z"
  }
}
```

---

## Dynamic Broadcast

`POST /api/broadcasts/dynamic`

Send a personalized broadcast using a **dynamic voice** — an approved voice with variable parts (name, amount, etc.) filled in per recipient. Processing is asynchronous: the API accepts the request, then creates one broadcast per recipient in the background.

:::note[Dashboard setup required]
Before using this endpoint, create a dynamic voice in the dashboard and wait until it is approved. Then use that approved voice's name in the `voice` field.
:::

:::caution[Permission required]
If any required dynamic part of the voice uses `tts`, your account needs the AI TTS permission. Contact support to enable it. Requests without it receive `403`. `audio_url` dynamic parts do not need this permission.
:::

## Request Parameters

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| request_id | string | Yes | Unique request identifier (16-64 chars) to prevent duplicate requests. Use UUID or random string. |
| voice | string | Yes | Name of an approved voice owned by your account (1-255 chars). The voice must have dynamic parts with configured dynamic keys. |
| sender | string | Yes | Your active caller sender number (1-20 chars) |
| recipients | array | Yes | 1-10 recipient objects (see below). No duplicate phone numbers. |

### Recipient object

| Field | Type | Required | Description |
| --- | --- | --- | --- |
| phone_number | string | Yes | Bangladeshi phone number, `01XXXXXXXXX` format. |
| data | object | Yes | Key/value pairs (string values, max 5000 chars each by default). Every dynamic key required by the voice must be present and non-empty. |

## Dynamic parts

Dynamic voices support parts in these modes: `tts` (text spoken by AI TTS), `number`, `character`, `digit`, and `audio_url` (a CDN audio URL played in place). `audio_url` values are downloaded and validated as G.711-compatible WAV (8000 Hz, mono, `pcm_s16le`) during processing; an invalid file marks the request as failed.

## Request Example

```php
<?php
// PHP with cURL
$url = 'https://api.awajdigital.com/api/broadcasts/dynamic';
$token = 'your_api_token_here';

$data = [
    'request_id' => 'unique_dynamic_broadcast_001',
    'voice' => 'due_reminder_dynamic',
    'sender' => '8801234567890',
    'recipients' => [
        [
            'phone_number' => '019XXXXXXXX',
            'data' => ['customer_name' => 'রহিম উদ্দিন', 'due_amount' => '1250']
        ],
        [
            'phone_number' => '018XXXXXXXX',
            'data' => ['customer_name' => 'করিম শেখ', 'due_amount' => '3400']
        ]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

## Success Response Example

```json
{
  "success": true,
  "message": "Request accepted",
  "data": {
    "request_id": "unique_dynamic_broadcast_001"
  }
}
```

The `202` response means the request was accepted, not that calls have started. Processing creates one broadcast per recipient, all sharing the same `request_id`. There is no dedicated status endpoint — poll [List Broadcasts](/api-docs/broadcasts/list) with `?request_id=<request_id>` until the broadcasts appear, then use [Get Broadcast Result](/api-docs/broadcasts/result) for each ID. If processing fails, the List Broadcasts response includes the failure in `data.error`.

## Error Responses

| Status | Description |
| --- | --- |
| 400 | Duplicate recipient phone numbers, voice has no dynamic parts / configured keys, or missing dynamic data for a recipient |
| 402 | Insufficient account balance |
| 403 | Voice not found / not approved, sender not found / not active, or missing AI TTS permission for `tts` dynamic parts |
| 422 | Invalid request parameters (`request_id` length, phone format, recipients array size, data value length) |
| 500 | Request acceptance failure, or a retry of a previously failed `request_id` |

:::note
Re-sending the same `request_id` for an already accepted request returns `202` with message `Request already accepted` (idempotent). If the stored request failed, retrying the same `request_id` returns `500` with message `Request failed` and the original error — use a new `request_id` instead.

Call minutes are billed per your pulse rate, same as any broadcast.
:::

---

## Direct Broadcast

`POST /api/broadcasts/direct`

Send a broadcast without a pre-approved voice by providing CDN audio URLs. Files are downloaded and validated, then played in sequence on each call. Same idea as a direct survey, for announcements.

:::caution[Permission required]
This endpoint requires extra permission on your account. Contact support to enable it. Requests without it receive `403`.
:::

## Request Parameters

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| request_id | string | Yes | Unique request identifier (16-64 chars) to prevent duplicate requests. Use UUID or random string. |
| sender | string | Yes | Your active caller sender number |
| phone_numbers | array | Yes | Array of Bangladeshi phone numbers (01XXXXXXXXX format, 1-999 numbers). No duplicates allowed. |
| voices | array | Yes | CDN audio URLs (1-10), played in sequence. A single file is a one-item array. |
| metadata | object | No | Custom data to associate with the broadcast. |

## Audio format

WAV, 8000 Hz, mono, `pcm_s16le` (G.711-compatible). Every URL is downloaded and validated before the broadcast is queued. Invalid files are rejected with `400`.

## Request Example

```php
<?php
// PHP with cURL
$url = 'https://api.awajdigital.com/api/broadcasts/direct';
$token = 'your_api_token_here';

$data = [
    'request_id' => 'unique_direct_broadcast_123',
    'sender' => '8801234567890',
    'phone_numbers' => ['019XXXXXXXX', '018XXXXXXXX'],
    'voices' => ['https://cdn.example.com/audio/announcement.wav'],
    'metadata' => ['campaign_id' => 'direct_broadcast_2026']
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

## Success Response Example

```json
{
  "success": true,
  "broadcast": {
    "id": 123,
    "name": "direct_1_1705312800000",
    "status": "broadcasting",
    "createdAt": "2026-08-31T10:30:00.000Z"
  }
}
```

Poll [Get Broadcast Result](/api-docs/broadcasts/result) for per-number call outcomes.

## Error Responses

| Status | Description |
| --- | --- |
| 400 | Duplicate phone numbers, or audio files not in G.711-compatible format |
| 402 | Insufficient account balance |
| 403 | Extra permission not enabled, or sender not found / not active |
| 409 | `request_id` already used within 15 minutes |
| 422 | Invalid request parameters (`request_id` length, phone format, URL, array size) |

:::note
Use a unique `request_id` for each request. The same `request_id` within 15 minutes is rejected with `409`. This endpoint does not create a Voice row — host the WAV files on a CDN you control.
:::

---

## Direct TTS Broadcast

`POST /api/broadcasts/direct-tts`

Send a broadcast from plain text — the text is converted to speech (AI TTS) and played to each recipient. Processing is asynchronous: the API accepts the request, generates the audio in the background, then queues the calls.

:::caution[Permission required]
This endpoint requires both the direct-broadcast and AI TTS permissions on your account. Contact support to enable them. Requests without them receive `403`.
:::

## Request Parameters

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| request_id | string | Yes | Unique request identifier (16-64 chars) to prevent duplicate requests. Use UUID or random string. |
| sender | string | Yes | Your active caller sender number (1-20 chars) |
| phone_numbers | array | Yes | Array of Bangladeshi phone numbers (01XXXXXXXXX format, 1-999 numbers). No duplicates allowed. |
| texts | array | Yes | Text strings (1-10, max 5000 chars each by default), converted to speech and played in order. |
| voice | string | No | `male` or `female` (default: provider default voice) |
| language_code | string | No | Language code, e.g. `bn-BD` (max 10 chars; default: account/provider default) |
| metadata | object | No | Custom data to associate with the broadcast. |

## Request Example

```php
<?php
// PHP with cURL
$url = 'https://api.awajdigital.com/api/broadcasts/direct-tts';
$token = 'your_api_token_here';

$data = [
    'request_id' => 'unique_direct_tts_001',
    'sender' => '8801234567890',
    'phone_numbers' => ['019XXXXXXXX', '018XXXXXXXX'],
    'texts' => ['আসসালামু আলাইকুম। আগামীকাল আমাদের অফিস বন্ধ থাকবে।'],
    'voice' => 'female',
    'language_code' => 'bn-BD',
    'metadata' => ['campaign_id' => 'tts_broadcast_2026']
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

## Success Response Example

```json
{
  "success": true,
  "message": "TTS broadcast request accepted, processing started",
  "data": {
    "request_id": "unique_direct_tts_001"
  }
}
```

The `202` response means the request was accepted, not that calls have started. Poll [Get Direct TTS Broadcast Status](/api-docs/broadcasts/direct-tts-status) with the same `request_id` until it is `completed`, then poll [Get Broadcast Result](/api-docs/broadcasts/result) for per-number call outcomes.

## Error Responses

| Status | Description |
| --- | --- |
| 400 | Duplicate phone numbers in request |
| 402 | Insufficient account balance |
| 403 | Required permissions not enabled, or sender not found / not active |
| 422 | Invalid request parameters (`request_id` length, phone format, array size, text length) |
| 429 | Rate limited — max 1 request per second per account |

:::note
Re-sending the same `request_id` returns `202` with the original result (idempotent). If the stored request failed, the retry returns `500` — use a new `request_id` instead.

Call minutes are billed per your pulse rate, same as any broadcast. TTS generation cost is tracked internally and is not charged separately.
:::

---

## Get Direct TTS Broadcast Status

`GET /api/broadcasts/direct-tts/{requestId}/status`

Check the processing status of a [Direct TTS Broadcast](/api-docs/broadcasts/direct-tts) request. Only requests owned by your account can be queried.

## Request Parameters

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| requestId | string | Yes | The `request_id` returned when the direct TTS broadcast was accepted. |

## Request Example

```php
<?php
// PHP with cURL
$url = 'https://api.awajdigital.com/api/broadcasts/direct-tts/unique_direct_tts_001/status';
$token = 'your_api_token_here';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

## Success Response Example

```json
{
  "success": true,
  "status": "completed",
  "broadcast_id": 123
}
```

A failed request includes the processing error:

```json
{
  "success": true,
  "status": "failed",
  "error": "TTS provider request failed"
}
```

| status | Meaning |
| --- | --- |
| pending | Request accepted, waiting to be processed |
| processing | Audio is being generated |
| completed | Broadcast created and calls queued — `broadcast_id` is included (may be `null`) |
| failed | Processing failed — an `error` message is included |

:::note
When `completed`, poll [Get Broadcast Result](/api-docs/broadcasts/result) with the `broadcast_id` for per-number call outcomes.
:::

## Error Responses

| Status | Description |
| --- | --- |
| 401 | Missing or invalid access token |
| 404 | Request not found or not owned by your account |

---

## List Broadcasts

`GET /api/broadcasts`

Retrieve a list of all broadcasts created by the authenticated user. By default, returns broadcasts from the last 30 days. You can specify a custom date range (maximum 90 days).

## Query Parameters

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| start_date | string | No | Start date in ISO 8601 format (e.g., 2025-01-01). Defaults to 30 days ago. |
| end_date | string | No | End date in ISO 8601 format (e.g., 2025-01-31). Defaults to today. |

:::caution[Important]
The date range cannot exceed 90 days. If you need data for a longer period, make multiple requests with different date ranges.
:::

## Request Examples

```php
<?php
// PHP with cURL
$url = 'https://api.awajdigital.com/api/broadcasts';
$token = 'your_api_token_here';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

## Success Response Example

```json
{
  "success": true,
  "broadcasts": [
    {
      "id": 123,
      "name": "api_1_voice_100",
      "status": "completed",
      "createdAt": "2025-01-15T10:30:00.000+06:00"
    },
    {
      "id": 124,
      "name": "api_1_voice_50",
      "status": "broadcasting",
      "createdAt": "2025-01-14T14:20:00.000+06:00"
    }
  ],
  "dateRange": {
    "startDate": "2025-01-01",
    "endDate": "2025-01-31"
  }
}
```

## 400 Bad Request - Date Range Too Large

Date range exceeds 90 days:

```json
{
  "success": false,
  "message": "Date range cannot exceed 90 days"
}
```

---

## Get Broadcast Result

`GET /api/broadcasts/:id/result`

Retrieve the result of a broadcast. If the broadcast is still in progress, only status information is returned. If the broadcast is complete, detailed results for each phone number are included.

## Path Parameters

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| id | integer | Yes | The broadcast ID returned from broadcast creation |

## Request Example

```php
<?php
// PHP with cURL
$broadcastId = 123;
$url = "https://api.awajdigital.com/api/broadcasts/{$broadcastId}/result";
$token = 'your_api_token_here';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

## Running Broadcast Response

```json
{
  "success": true,
  "broadcast": {
    "id": 123,
    "name": "api_1_voice_3",
    "status": "broadcasting",
    "listenerCount": 3,
    "completeCount": 1,
    "createdAt": "2025-12-30T10:00:00.000Z"
  },
  "isComplete": false,
  "message": "Broadcast is still in progress"
}
```

## Completed Broadcast Response

```json
{
  "success": true,
  "broadcast": {
    "id": 123,
    "name": "api_1_voice_3",
    "status": "completed",
    "listenerCount": 3,
    "completeCount": 3,
    "createdAt": "2025-12-30T10:00:00.000Z"
  },
  "isComplete": true,
  "statusDistribution": {
    "pending": 0,
    "answered": 2,
    "notAnswered": 1,
    "rejected": 0,
    "busy": 0,
    "failed": 0,
    "unknown": 0
  },
  "results": [
    {
      "phoneNumber": "019XXXXXXXX",
      "status": "answered",
      "duration": 45
    },
    {
      "phoneNumber": "018XXXXXXXX",
      "status": "not_answered",
      "duration": null
    },
    {
      "phoneNumber": "017XXXXXXXX",
      "status": "answered",
      "duration": 30
    }
  ]
}
```

:::note
The `isComplete` field indicates whether the broadcast has finished. Only when `isComplete` is `true` will the `results` array and `statusDistribution` be included in the response.
:::

---

## List Voices

`GET /api/voices`

Retrieve a list of all voices created by the authenticated user along with their approval status.

## Request Example

```php
<?php
// PHP with cURL
$url = 'https://api.awajdigital.com/api/voices';
$token = 'your_api_token_here';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

## Success Response Example

```json
{
  "success": true,
  "voices": [
    {
      "id": 1,
      "name": "welcome_message",
      "status": "approved",
      "createdAt": "2025-12-01T10:00:00.000Z"
    },
    {
      "id": 2,
      "name": "otp_voice",
      "status": "pending",
      "createdAt": "2025-12-15T14:30:00.000Z"
    },
    {
      "id": 3,
      "name": "promo_announcement",
      "status": "rejected",
      "createdAt": "2025-12-20T09:15:00.000Z"
    }
  ]
}
```

:::note[Voice Status]
Voices can have one of the following statuses: `pending` (under review), `approved` (ready to use), or `rejected` (not approved).
:::

---

## Upload Voice

`POST /api/voices/upload`

Upload a new voice audio file via the API. The voice will be created with `pending` status and must be approved by an admin before it can be used in broadcasts. This endpoint uses `multipart/form-data` encoding for file upload.

## Request Parameters

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| name | string | Yes | Unique name for the voice (1-255 chars). Names are case-insensitive and must be unique per account. |
| audio | file | Yes | Audio file (max 10MB). Supported formats: mp3, wav, ogg, m4a, aac, webm, flac. |

:::caution[Important]
This endpoint requires `Content-Type: multipart/form-data`. Do **not** set the Content-Type header manually when using browser Fetch or axios — the library will set it automatically with the correct boundary.
:::

## Request Example

```php
<?php
// PHP with cURL
$url = 'https://api.awajdigital.com/api/voices/upload';
$token = 'your_api_token_here';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'name' => 'my_voice_name',
    'audio' => new CURLFile('/path/to/your/audio.mp3', 'audio/mpeg', 'audio.mp3')
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

## Success Response (201 Created)

```json
{
  "id": 4,
  "name": "my_voice_name",
  "status": "pending"
}
```

:::note[Voice Approval]
Uploaded voices are created with `pending` status. An admin will review and approve or reject the voice. Only approved voices can be used in broadcasts. Use the [List Voices](/api-docs/voices/list) endpoint to check the approval status of your voices.
:::

## 409 Conflict - Duplicate Voice Name

A voice with the same name already exists in your account:

```json
{
  "message": "A voice with this name already exists"
}
```

## 413 Payload Too Large

Audio file exceeds the 10MB size limit:

```json
{
  "message": "File size too large. Maximum allowed size is 10MB"
}
```

## 415 Unsupported Media Type

Audio file format not supported:

```json
{
  "message": "Invalid file extension. Allowed extensions: mp3, wav, ogg, m4a, aac, webm, flac"
}
```

---

## List Senders

`GET /api/senders`

Retrieve a list of all caller sender numbers associated with the authenticated user's account along with their status.

## Request Example

```php
<?php
// PHP with cURL
$url = 'https://api.awajdigital.com/api/senders';
$token = 'your_api_token_here';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

## Success Response Example

```json
{
  "success": true,
  "senders": [
    {
      "id": 1,
      "callingNumber": "8801234567890",
      "status": "active"
    },
    {
      "id": 2,
      "callingNumber": "8809876543210",
      "status": "active"
    },
    {
      "id": 3,
      "callingNumber": "8805555555555",
      "status": "inactive"
    }
  ]
}
```

:::note[Sender Status]
Senders can have one of the following statuses: `active` (ready to use) or `inactive` (temporarily disabled). Only active senders can be used for broadcasts.
:::

---

## Create Survey

`POST /api/surveys`

Create and start a voice survey campaign to collect responses from multiple phone numbers. The survey uses a published template with interactive questions that recipients can respond to using their phone keypad.

## Request Parameters

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| request_id | string | Yes | Unique request identifier (16-64 chars) to prevent duplicate requests. Use UUID or random string. |
| template_name | string | Yes | Name of your published survey template. Template must be in "published" status. |
| sender | string | Yes | Your active caller sender number (must be assigned to your account and active). |
| phone_numbers | array | Yes | Array of Bangladeshi phone numbers (01XXXXXXXXX format, max 999 numbers). No duplicates allowed. |
| metadata | object | No | Custom data to associate with the survey. Will be returned in results and webhook payload. |
| webhook_url | string | No | URL to receive webhook notification when survey completes. |

## Request Example

```php
<?php
// PHP with cURL
$url = 'https://api.awajdigital.com/api/surveys';
$token = 'your_api_token_here';

$data = [
    'request_id' => 'unique_survey_request_123',
    'template_name' => 'customer_satisfaction_survey',
    'sender' => '8801234567890',
    'phone_numbers' => ['019XXXXXXXX', '018XXXXXXXX', '017XXXXXXXX'],
    'metadata' => ['campaign_id' => 'summer2025', 'customer_segment' => 'premium'],
    'webhook_url' => 'https://your-domain.com/webhook'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

## Success Response Example

```json
{
  "success": true,
  "survey": {
    "id": 456,
    "name": "api_survey_1_customer_satisfaction_survey_3",
    "status": "ready",
    "totalCount": 3,
    "createdAt": "2025-12-30T10:00:00.000Z",
    "metadata": { "campaign_id": "summer2025", "customer_segment": "premium" }
  }
}
```

:::note
The survey will be created with status "ready" and will automatically start broadcasting to the provided phone numbers. Use the `request_id` to prevent duplicate survey creation within 15 minutes.
:::

:::tip[Prerequisites]
Before creating a survey, ensure you have: (1) A published survey template created in your dashboard, (2) An active sender number assigned to your account, and (3) Sufficient balance to cover the survey calls.
:::

---

## Direct Survey

`POST /api/v1/surveys/direct-order`

Start a voice survey without a pre-built template. You send the survey tree in the request: optional start voices, the question, what each keypad option does (play voices or transfer to an agent), and optional end voices. Every voice slot takes either an approved voice from your [voice library](/api-docs/voices/list) or a CDN audio URL.

:::caution[Permission required for audio URLs]
Approved voice-library references (`{"type": "voice", "name": "..."}`) work on every account with survey access. Plain audio URLs skip voice approval, so they need extra permission on your account — contact support to enable it. A request that contains any URL entry from an account without it is rejected with `403`, listing each URL field.
:::

## Request Parameters

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| request_id | string | Yes | Unique request identifier (16-64 chars) to prevent duplicate requests. Use UUID or random string. |
| sender | string | Yes | Your active caller sender number |
| phone_numbers | array | Yes | Array of Bangladeshi phone numbers (01XXXXXXXXX format, 1-999 numbers). No duplicates allowed. |
| start_voices | array | No | Voice entries (0-10) played in sequence before the question |
| question_voices | array | Yes | Voice entries (1-10) for the question, played in sequence |
| invalid_voice | voice entry | No | Played when the caller presses a key that matches no option, before the question repeats. If omitted, the question repeats after silence. |
| end_voices | array | No | Voice entries (0-10) played at the end of the call |
| dtmf_options | array | Yes | Keypad options (1-9 items) |
| dtmf_options[].key | string | Yes | Keypad key, `1`-`9` (`0`, `*`, `#` not allowed). Keys must be unique. |
| dtmf_options[].option_type | string | No | `voice` (default) or `transfer` |
| dtmf_options[].voices | array | No | Voice entries (0-10) played after the caller picks this option |
| dtmf_options[].transfer_numbers | array | For `transfer` | Numbers to transfer the caller to |
| dtmf_options[].ringback_voice | voice entry | No | Transfer only: played while connecting |
| dtmf_options[].all_busy_voice | voice entry | No | Transfer only: played when every transfer number is busy |
| metadata | object | No | Custom data to associate with the survey. Returned in results and the webhook payload. |
| webhook_url | string | No | URL to receive a [webhook](/api-docs/surveys/webhooks) when the survey completes |
| config.retry_count | number | No | Retry attempts for failed calls, `0`-`3` (default `0`) |

## Voice entries

Every voice field accepts entries in either form, and you can mix them in the same array:

```jsonc
{ "type": "voice", "name": "welcome-intro" }  // approved voice from your voice library
"https://cdn.example.com/audio/part1.wav"     // CDN audio URL (needs extra permission)
```

**Voice-library references**

- `name` is the voice name shown in your dashboard voice library ([List Voices](/api-docs/voices/list) returns them). Matching ignores case.
- The voice must be approved, not archived, and fully static — voices with dynamic parts are rejected.
- A multi-part voice expands to all of its parts, played in order. The 1-10 limit on array fields counts your request entries, before expansion.
- In single-file fields (`invalid_voice`, `ringback_voice`, `all_busy_voice`) the voice must have exactly one part.

**Audio URLs**

- Must be WAV, 8000 Hz, mono, `pcm_s16le` (G.711-compatible). Every URL is downloaded and validated before the survey starts; invalid files are rejected with `400`.
- Require extra permission on your account (see above).

Any other entry type (for example `{"type": "tts"}`) is reserved and currently fails with `422`.

## Request Example

```php
<?php
// PHP with cURL
$url = 'https://api.awajdigital.com/api/v1/surveys/direct-order';
$token = 'your_api_token_here';

$data = [
    'request_id' => 'unique_direct_survey_123',
    'sender' => '8801234567890',
    'phone_numbers' => ['019XXXXXXXX', '018XXXXXXXX'],
    'start_voices' => [['type' => 'voice', 'name' => 'welcome-intro']],
    'question_voices' => [['type' => 'voice', 'name' => 'delivery-question']],
    'invalid_voice' => ['type' => 'voice', 'name' => 'invalid-key'],
    'end_voices' => [['type' => 'voice', 'name' => 'thank-you']],
    'dtmf_options' => [
        ['key' => '1', 'voices' => [['type' => 'voice', 'name' => 'order-confirmed']]],
        [
            'key' => '2',
            'option_type' => 'transfer',
            'transfer_numbers' => ['017XXXXXXXX'],
            'ringback_voice' => ['type' => 'voice', 'name' => 'please-wait'],
            'all_busy_voice' => ['type' => 'voice', 'name' => 'agents-busy']
        ]
    ],
    'metadata' => ['campaign_id' => 'delivery_confirm_2026'],
    'webhook_url' => 'https://your-domain.com/webhook',
    'config' => ['retry_count' => 1]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

With the URL permission enabled, any entry can be a CDN URL instead, mixed freely with library voices:

```json
"question_voices": [
  "https://cdn.example.com/audio/question_part1.wav",
  { "type": "voice", "name": "question-part-2" }
]
```

## Success Response Example

```json
{
  "success": true,
  "survey": {
    "id": 789,
    "name": "direct_order_v1_1_1705312800000",
    "status": "surveying",
    "totalCount": 2,
    "createdAt": "2026-09-25T14:30:00.000+06:00",
    "metadata": { "campaign_id": "delivery_confirm_2026" }
  }
}
```

Poll [Get Survey Result](/api-docs/surveys/result) for per-number responses, or set `webhook_url` to be notified on completion.

## Error Responses

| Status | Description |
| --- | --- |
| 400 | Duplicate phone numbers or DTMF keys; audio URL not in G.711-compatible format; voice not found / not approved / archived; voice has dynamic parts; multi-part voice in a single-file field |
| 402 | Insufficient account balance |
| 403 | Audio URL entries sent without the URL permission, or sender not found / not active |
| 409 | `request_id` already used within 15 minutes |
| 422 | Invalid request parameters (including `tts` or unknown voice entry types) |

Voice and audio problems are reported together in a single `400`, one item per field in `errors`:

```json
{
  "success": false,
  "message": "Audio file validation failed. Files must be in G.711-compatible format (WAV, 8000Hz, Mono, pcm_s16le)",
  "errors": [
    { "field": "question_voices[1]", "issue": "voice \"greeting-v2\" not found or not approved" },
    { "field": "dtmf_options[0].voices[0]", "issue": "voice \"promo\" has dynamic parts; only fully static voices are allowed" }
  ]
}
```

URL entries without the permission return `403` in the same shape:

```json
{
  "success": false,
  "message": "Direct audio URLs are not enabled for your account. Use approved voices ({\"type\": \"voice\", \"name\": \"...\"}) or contact support.",
  "errors": [
    { "field": "question_voices[0]", "issue": "direct audio URLs require the send-direct-survey permission" }
  ]
}
```

:::note
Use a unique `request_id` for each request. The same `request_id` within 15 minutes is rejected with `409`. A request rejected with `403` for URL entries is not recorded, so you can retry it with the same `request_id`.
:::

---

## Get Survey Result

`GET /api/surveys/:id/result`

Retrieve the results of a voice survey campaign. The response includes survey metadata, call status distribution, and detailed results for each phone number including the keys pressed by recipients during the survey.

## Path Parameters

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| id | integer | Yes | The survey ID returned from survey creation. |

## Request Example

```php
<?php
// PHP with cURL
$surveyId = 456;
$url = "https://api.awajdigital.com/api/surveys/{$surveyId}/result";
$token = 'your_api_token_here';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

## In-Progress Survey Response

```json
{
  "success": true,
  "survey": {
    "id": 456,
    "name": "api_survey_1_customer_satisfaction_survey_3",
    "status": "broadcasting",
    "totalCount": 3,
    "completeCount": 1,
    "createdAt": "2025-12-30T10:00:00.000Z",
    "metadata": { "campaign_id": "summer2025", "customer_segment": "premium" }
  },
  "isComplete": false,
  "statusDistribution": {
    "pending": 2,
    "answered": 1,
    "not_answered": 0,
    "failed": 0
  },
  "numbers": [
    {
      "number": "019XXXXXXXX",
      "status": "pending",
      "duration": null,
      "pressedKeys": []
    },
    {
      "number": "018XXXXXXXX",
      "status": "answered",
      "duration": 45,
      "pressedKeys": ["1", "5"]
    },
    {
      "number": "017XXXXXXXX",
      "status": "pending",
      "duration": null,
      "pressedKeys": []
    }
  ]
}
```

## Completed Survey Response

```json
{
  "success": true,
  "survey": {
    "id": 456,
    "name": "api_survey_1_customer_satisfaction_survey_3",
    "status": "completed",
    "totalCount": 3,
    "completeCount": 3,
    "createdAt": "2025-12-30T10:00:00.000Z",
    "metadata": { "campaign_id": "summer2025", "customer_segment": "premium" }
  },
  "isComplete": true,
  "statusDistribution": {
    "pending": 0,
    "answered": 2,
    "not_answered": 1,
    "failed": 0
  },
  "numbers": [
    {
      "number": "019XXXXXXXX",
      "status": "answered",
      "duration": 52,
      "pressedKeys": ["2", "4", "1"]
    },
    {
      "number": "018XXXXXXXX",
      "status": "answered",
      "duration": 45,
      "pressedKeys": ["1", "5"]
    },
    {
      "number": "017XXXXXXXX",
      "status": "not_answered",
      "duration": null,
      "pressedKeys": []
    }
  ]
}
```

:::note
The `isComplete` field indicates whether the survey has finished (status is "completed" or "cancelled"). The `pressedKeys` array contains the digits pressed by the recipient in response to survey questions. The `statusDistribution` shows the count of calls in each status: pending, answered, not_answered, or failed.
:::

:::note[Survey Response Data]
Each number in the results includes the call status, duration (in seconds), and the keys pressed by the recipient. Use this data to analyze survey responses and calculate metrics like response rates, average call duration, and answer distributions.
:::

---

## Survey Webhooks

When a survey completes, a POST request is sent to your webhook URL with the survey results.

## Webhook Payload

```json
{
  "survey_id": 456,
  "metadata": { "campaign_id": "summer2025", "customer_segment": "premium" },
  "results": [
    {
      "phone_number": "019XXXXXXXX",
      "status": "answered",
      "duration": 45,
      "response": "1",
      "responses": ["1", "5"]
    },
    {
      "phone_number": "018XXXXXXXX",
      "status": "answered",
      "duration": 62,
      "response": "2",
      "responses": ["2", "4", "1"]
    },
    {
      "phone_number": "017XXXXXXXX",
      "status": "not_answered",
      "duration": 0
    }
  ]
}
```

### Payload Fields

| Field | Type | Description |
| --- | --- | --- |
| survey_id | integer | The unique identifier of the survey |
| metadata | object | Custom metadata passed when creating the survey (optional) |
| results | array | Array of result objects for each phone number |

### Result Object Fields

| Field | Type | Description |
| --- | --- | --- |
| phone_number | string | The phone number of the respondent |
| status | string | Call status: `pending`, `answered`, `not_answered`, or `failed` |
| duration | integer | Call duration in seconds (0 if not answered) |
| response | string | The first key pressed by the respondent (only if status is `answered`) |
| responses | array | All keys pressed during the survey (only if status is `answered`) |

:::note
The webhook is sent once when the survey status becomes "completed". The `response` and `responses` fields are only present when the call status is `answered`.
:::

---

## Create Payment

`POST /api/payments/create`

Create a balance-recharge payment for your account. You receive a hosted payment URL — send your payer there, and after payment the payer is redirected to **your** custom success page.

Requirement: the hostname of your `success_url` (and `cancel_url`, if used) must be **registered on our side** before use (contact support to allowlist your domain, e.g. `shop.example.com`). Requests with unregistered hosts are rejected with `422`.

## Request Parameters

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| amount | number | Yes | Amount in BDT. Minimum 20. |
| success_url | string | Yes | HTTPS URL on an allowlisted host. Payer is sent here after payment. |
| cancel_url | string | No | HTTPS URL on an allowlisted host. Payer is sent here if they cancel. |

## Request Example

```php
<?php
// PHP with cURL
$url = 'https://api.awajdigital.com/api/payments/create';
$token = 'your_api_token_here';

$data = [
    'amount' => 500,
    'success_url' => 'https://shop.example.com/payment/success',
    'cancel_url' => 'https://shop.example.com/payment/cancel'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

## Success Response Example

```json
{
  "success": true,
  "payment_url": "https://pay.example.com/checkout/9f8b7c6d-...",
  "invoice_id": "9f8b7c6d-..."
}
```

Redirect your payer to `payment_url`. After payment, the flow is:

1. The payment gateway returns the payer to **our** server first (this guarantees the balance is credited even if the webhook is missed).
2. We immediately redirect the payer to your `success_url` with two query parameters appended:
   - `invoice_id` — the payment reference
   - `status` — `completed` or `pending`

:::caution[Confirm before delivering]
The `success_url` redirect alone is **not** proof of payment — anyone can type that URL. Always confirm with the [Get Payment Status](/api-docs/payments/status) endpoint before delivering your service.
:::

## Error Responses

| Status | Description |
| --- | --- |
| 401 | Missing or invalid access token |
| 403 | Payment API not enabled for your account |
| 422 | Validation failed (amount < 20, non-https URL, non-allowlisted host) |
| 502 | Gateway error — payment could not be created |

---

## Get Payment Status

`GET /api/payments/{invoice_id}/status`

Verify a payment server-to-server and get its real status. This endpoint also triggers fulfillment if the payment completed but has not been credited yet (both the webhook and the success redirect missed it).

## Request Example

```php
<?php
// PHP with cURL
$url = 'https://api.awajdigital.com/api/payments/9f8b7c6d-.../status';
$token = 'your_api_token_here';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

## Success Response Example

```json
{
  "success": true,
  "invoice_id": "9f8b7c6d-...",
  "status": "completed",
  "amount": 500
}
```

| status | Meaning |
| --- | --- |
| completed | Payment confirmed and balance credited |
| anything else | Payment not completed (pending/failed) |

:::note
Always call this endpoint to confirm a payment before delivering your service — the `success_url` redirect alone is not proof of payment.
:::

## Error Responses

| Status | Description |
| --- | --- |
| 401 | Missing or invalid access token |
| 403 | Payment API not enabled for your account |
| 404 | Payment not found or not owned by your account |
| 502 | Gateway error — payment could not be verified |

---

## List Agents

`GET /api/cc/agents`

Return every call-center agent on the authenticated account, including inactive and unapproved ones. Use `id` as `agent_id` when minting an SDK token (`POST /api/sdk/token`). Token minting still rejects inactive or unapproved agents.

Requires a Bearer API token (from [Authentication](/api-docs/authentication) / dashboard **API Tokens**) and the **call-center** permission.

Rate limit: 60 requests per minute per account.

## Request Example

```php
<?php
// PHP with cURL
$url = 'https://api.awajdigital.com/api/cc/agents';
$token = 'your_api_token_here';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

## Success Response Example

```json
{
  "data": [
    {
      "id": 42,
      "full_name": "Nadia Rahman",
      "email": "nadia@example.com",
      "extension": "200",
      "presence": "online",
      "status": "active",
      "approval_status": "approved",
      "sender": "01712345678"
    }
  ]
}
```

| Field | Type | Description |
| --- | --- | --- |
| data[] | array | Agents on this account, ordered by `id` ascending. Empty array if none. |
| id | integer | `cc_agents.id` — pass this as `agent_id` to [Mint SDK Token](/api-docs/sdk/token) and [List Agent Calls](/api-docs/call-center/calls). |
| full_name | string\|null | Agent display name. |
| email | string | Agent login email. |
| extension | string | SIP extension. |
| presence | string | `offline`, `online`, `on_call`, `on_break`, or `wrap_up`. May be briefly stale; do not poll for live presence. |
| status | string | `active`, `inactive`, or `banned`. |
| approval_status | string | `pending`, `approved`, or `rejected`. |
| sender | string\|null | Caller ID from the agent's live sender assignment; `null` if none or the number was released. |

## Error Responses

| Status | Code | Description |
| --- | --- | --- |
| 401 | — | Missing or invalid access token |
| 403 | — | Account does not have the call-center permission |
| 429 | — | Rate limit exceeded (60 requests/minute) |

---

## List Agent Calls

`GET /api/cc/agents/:agent_id/calls`

Outgoing calls for one of your agents on a single calendar day. Re-fetch the same `agent_id` + `date` to pick up status, duration, and recording after hangup billing (typically 1–10 minutes). Poll **today** and **yesterday** for a few hours after midnight; older days do not change.

Requires a Bearer API token (from [Authentication](/api-docs/authentication) / dashboard **API Tokens**) and the **call-center** permission.

Rate limit: 60 requests per hour per account. Page size is fixed at 100.

This endpoint covers outbound and internal calls. Inbound queue calls are not included.

## Path Parameters

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| agent_id | integer | Yes | Agent `id` from [List Agents](/api-docs/call-center/agents). Must belong to your account. |

## Query Parameters

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| date | string | Yes | Calendar day `YYYY-MM-DD` in `Asia/Dhaka`. The day is call **start** (`created_at`), not hangup time. |
| page | number | No | Page number (default 1). Size is always 100. |

## Request Example

```php
<?php
// PHP with cURL
$agentId = 42;
$date = '2026-09-07';
$url = 'https://api.awajdigital.com/api/cc/agents/' . $agentId . '/calls?date=' . urlencode($date) . '&page=1';
$token = 'your_api_token_here';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

## Success Response Example

```json
{
  "meta": {
    "total": 2,
    "per_page": 100,
    "current_page": 1,
    "last_page": 1
  },
  "data": [
    {
      "id": 101,
      "agent_id": 42,
      "uuid": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
      "called_number": "01712345678",
      "caller_number": "200",
      "call_type": "outbound",
      "status": "initiated",
      "duration": null,
      "recording": null,
      "created_at": "2026-09-07T00:30:00.000+06:00"
    },
    {
      "id": 102,
      "agent_id": 42,
      "uuid": "b2c3d4e5-f6a7-8901-bcde-f12345678901",
      "called_number": "01812345678",
      "caller_number": "200",
      "call_type": "outbound",
      "status": "answered",
      "duration": 63,
      "recording": "https://cdn.example.com/recordings/call.wav",
      "created_at": "2026-09-07T10:12:00.000+06:00"
    }
  ]
}
```

| Field | Type | Description |
| --- | --- | --- |
| meta.total | integer | Calls that started on this Dhaka day. |
| meta.per_page | integer | Always 100. |
| meta.current_page | integer | Current page. |
| meta.last_page | integer | Last page. |
| data[] | array | Calls ordered by `created_at` ascending. Empty array if none. |
| id | integer | Call id. Upsert on this when re-fetching the day. |
| agent_id | integer | Agent id. |
| uuid | string | Call UUID. |
| called_number | string | Number the agent dialed. |
| caller_number | string | Caller ID / extension used. |
| call_type | string | `internal` or `outbound`. |
| status | string | `initiated`, `answered`, `failed`, `busy`, `no_answer`, or `cancelled`. |
| duration | integer\|null | Seconds of talk time (`null` until billing finalizes). |
| recording | string\|null | Recording URL once attached (`null` until then). |
| created_at | string | Call start, ISO 8601 with `Asia/Dhaka` offset. |

A call that starts at 23:50 and hangs up after midnight still belongs to yesterday. `initiated` rows are included on purpose so a later fetch of the same day can fill in duration, status, and recording.

## Error Responses

| Status | Code | Description |
| --- | --- | --- |
| 401 | — | Missing or invalid access token |
| 403 | — | Account does not have the call-center permission |
| 404 | `agent_not_found` | Agent does not exist or belongs to another account |
| 422 | — | Missing or invalid `date` (`YYYY-MM-DD`) or `page` |
| 429 | — | Rate limit exceeded (60 requests/hour) |

```json
{
  "error": "agent not found",
  "code": "agent_not_found"
}
```

---

## Embed a Call Widget

This guide shows how to add AwajDigital calling to your own website.

Your agents stay logged in on your site. When they click Call, a phone popup opens so they can dial, answer, mute, and hang up. You keep your AwajDigital API token on your server. The browser only talks to your site; your API token never goes to the page.

You need:

1. An API token from the dashboard (**Profile → API Tokens**)
2. Call center enabled on your account
3. At least one agent — store that agent's numeric **id** on your user ([List Agents](/api-docs/call-center/agents) returns those ids)
4. Every production hostname added under **Profile → Call SDK**. Pages must be **HTTPS**. `shop.example.com` matches that host only; `*.shop.example.com` matches its subdomains (not the apex). Up to 25 domains. An empty list rejects every origin.

Give each of your users one agent. One agent can be used in only one browser at a time.

For local `http://localhost` development, turn on **localhost test mode** on the same Call SDK page (1 or 3 days). While it is on, any origin can connect, including http://localhost. It turns itself off when the window expires. localhost is not a valid allowlist entry.

## 1. Add a token route on your server

When the phone needs to connect, it POSTs to **your** site (cookies / login still apply). Your route calls us with the API token, then returns the JSON unchanged.

`POST https://api.awajdigital.com/api/sdk/token`

Send `{ "agent_id": 42 }` with `Authorization: Bearer your_api_token`.

You get `{ "token": "avt_…", "session_url": "https://api.awajdigital.com/api/sdk/session", … }`.

### Laravel

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::post('/amarvoice-token', function (Request $request) {
    $response = Http::withToken(config('services.awajdigital.token'))
        ->acceptJson()
        ->post('https://api.awajdigital.com/api/sdk/token', [
            'agent_id' => $request->user()->awaj_agent_id,
        ]);

    return response()->json($response->json(), $response->status());
})->middleware('auth');
```

Put this in `routes/api.php` so the browser calls `/api/amarvoice-token`. (`auth` can be `auth:sanctum` if that is what you use.)

In `.env`:

```
AWAJDIGITAL_API_TOKEN=your_api_token_here
```

In `config/services.php`:

```php
'awajdigital' => [
    'token' => env('AWAJDIGITAL_API_TOKEN'),
],
```

`awaj_agent_id` is your column. Map each logged-in user to one AwajDigital agent id.

### PHP (cURL)

```php
$ch = curl_init('https://api.awajdigital.com/api/sdk/token');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . getenv('AWAJDIGITAL_API_TOKEN'),
        'Accept: application/json',
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'agent_id' => $currentUser->awaj_agent_id,
    ]),
]);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

http_response_code($code);
header('Content-Type: application/json');
echo $body;
```

If this fails: `401` bad API token, `403` call center off or agent not active, `404` that `agent_id` is not yours.

## 2. Put the widget on the page

There are two ways to start a call from your page. Most sites use the first. Both are covered in [Browser Widget](/api-docs/sdk/widget).

**Let the SDK listen to your buttons.**

```html
<button type="button" data-call="01712345678">Call customer</button>
<button type="button" id="open-phone">Open phone</button>

<script src="https://dashboard.awajdigital.com/sdk/amarvoice-call.js"></script>
<script>
  const phone = new AmarVoiceCall({
    tokenUrl: '/api/amarvoice-token',
  });

  phone.mount('[data-call], #open-phone');
</script>
```

Against that HTML:

- `[data-call]` is the "Call customer" button. `data-call` is special: its value is the number. Clicking it opens the phone and starts a call to `01712345678`.
- `#open-phone` is the "Open phone" button. Any other element you pass to `mount` only opens the keypad, so the agent can type a number.
- Incoming calls open the popup too.
- The browser will ask for the microphone. That is required.

**Call from your own JavaScript** when the number is in your code, not on the button.

```html
<button type="button" id="call-selected">Call selected customer</button>

<script src="https://dashboard.awajdigital.com/sdk/amarvoice-call.js"></script>
<script>
  const phone = new AmarVoiceCall({
    tokenUrl: '/api/amarvoice-token',
  });

  phone.mount();

  // selectedCustomer.phone is your data — not a data-call attribute
  document.getElementById('call-selected').addEventListener('click', () => {
    phone.call(selectedCustomer.phone);
  });
</script>
```

`mount()` with no selector still puts the popup on the page, but does not wire any buttons. Your click handler reads the number from your own data; `phone.call` opens the popup and starts that call. Use the same `phone.call(number)` from a table row, a search result, or any other function. See [Browser Widget](/api-docs/sdk/widget) for opening the keypad only, mixing both styles, and other patterns.

## 3. Stop the session when they log out

`DELETE https://api.awajdigital.com/api/sdk/session` with `{ "agent_id": 42 }` and your API token.

### Laravel

```php
Http::withToken(config('services.awajdigital.token'))
    ->acceptJson()
    ->delete('https://api.awajdigital.com/api/sdk/session', [
        'agent_id' => $request->user()->awaj_agent_id,
    ]);
```

Call this in your logout action.

### PHP (cURL)

```php
$ch = curl_init('https://api.awajdigital.com/api/sdk/session');
curl_setopt_array($ch, [
    CURLOPT_CUSTOMREQUEST => 'DELETE',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . getenv('AWAJDIGITAL_API_TOKEN'),
        'Accept: application/json',
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'agent_id' => $currentUser->awaj_agent_id,
    ]),
]);
curl_exec($ch);
curl_close($ch);
```

A call that is already connected is not cut off. The next page load starts from step 2 again.

---

## Mint SDK Token

`POST /api/sdk/token`

Create a single-use token for one of your call-center agents. Call this from **your backend** (see the [embed guide](/api-docs/sdk)). The browser talks only to your site; your server talks to us.

Requires a Bearer API token (from [Authentication](/api-docs/authentication) / dashboard **API Tokens**) and the **call-center** permission.

Return this JSON to the browser unchanged. The [widget](/api-docs/sdk/widget) uses it to connect. Do not store or reuse the token.

## Request Parameters

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| agent_id | integer | Yes | Numeric id of a call-center agent that belongs to your account. The agent must be active and approved. Get ids from [List Agents](/api-docs/call-center/agents). |

## Request Example

```php
<?php
// PHP with cURL
$url = 'https://api.awajdigital.com/api/sdk/token';
$token = 'your_api_token_here';

$data = [
    'agent_id' => 42
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

## Success Response Example

```json
{
  "token": "avt_…",
  "expires_in": 300,
  "expires_at": "2026-09-06T12:05:00.000+06:00",
  "session_url": "https://api.awajdigital.com/api/sdk/session"
}
```

| Field | Type | Description |
| --- | --- | --- |
| token | string | Single-use token. Your page should not request this itself — proxy this endpoint and let the SDK call your proxy via `tokenUrl`. |
| expires_in | integer | Lifetime in seconds. |
| expires_at | string | ISO 8601 expiry. |
| session_url | string | URL the widget uses to start the call session. Forward this JSON as-is. |

## Error Responses

| Status | Code | Description |
| --- | --- | --- |
| 401 | — | Missing or invalid access token |
| 403 | — | Account does not have the call-center permission |
| 403 | `agent_inactive` | Agent exists but is not active |
| 403 | `agent_not_approved` | Agent exists but is not approved |
| 404 | `agent_not_found` | Agent does not exist or belongs to another account |
| 422 | — | Validation failed (`agent_id` missing or not a positive integer) |
| 429 | — | Too many requests |

```json
{
  "error": "agent not found",
  "code": "agent_not_found"
}
```

---

## Exchange Session

`POST /api/sdk/session`

The [browser widget](/api-docs/sdk/widget) calls this from the page with the token your backend returned from [Mint SDK Token](/api-docs/sdk/token). Do not send your API Bearer token here.

You do not need to call this endpoint or handle the response. The widget uses the result automatically to connect and place calls.

Your page must be served over HTTPS on a hostname you have added under **Profile → Call SDK** (see the [embed guide](/api-docs/sdk)). For `http://localhost`, turn on test mode there instead.

## Request Parameters

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| token | string | Yes | The token from your backend. 16–128 characters. |

## Request Example

The examples below are for debugging. Production traffic is the widget in the browser.

```php
<?php
// PHP with cURL — for testing only.
$url = 'https://api.awajdigital.com/api/sdk/session';

$data = [
    'token' => 'avt_…'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
    'Origin: https://shop.example.com'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

## Success Response Example

The widget uses this payload automatically. You can ignore these fields unless you are building your own client.

```json
{
  "wss_url": "wss://pbxfs1wss.awajdigital.com",
  "domain": "agent.example.awajdigital.com",
  "extension": "1001",
  "sip_username": "1001",
  "sip_password": "a1b2c3d4e5f6…",
  "expires_in": 43200,
  "expires_at": "2026-09-07T00:00:00.000+06:00",
  "ice_servers": [
    {
      "urls": ["stun:stun.l.google.com:19302"]
    }
  ]
}
```

| Field | Type | Description |
| --- | --- | --- |
| wss_url | string | WebSocket URL the phone registers against. |
| domain | string | Domain for this agent. |
| extension | string | Extension number. |
| sip_username | string | Username for this session. |
| sip_password | string | Password for this session. Do not persist in your app. |
| expires_in | integer | Advisory lifetime in seconds. Ends sooner if you connect again or revoke. |
| expires_at | string | ISO 8601 advisory expiry. |
| ice_servers | array | Connection servers (`urls`, optional `username` / `credential`). |

## Error Responses

| Status | Code | Description |
| --- | --- | --- |
| 401 | `token_invalid` | Token missing, already used, expired, or unknown |
| 403 | `agent_inactive` | Agent was deactivated or unapproved after the token was created |
| 403 | `origin_not_allowed` | This page is not on HTTPS, or its hostname is not on your Call SDK allowed-domain list (and test mode is off) |
| 422 | — | Validation failed (`token` missing or wrong length) |
| 429 | — | Too many requests |

```json
{
  "error": "invalid or expired token",
  "code": "token_invalid"
}
```

---

## Revoke Session

`DELETE /api/sdk/session`

End an agent's embedded phone session. Call this from **your backend** when one of your users logs out or you need to cut off access.

Requires a Bearer API token and the **call-center** permission (same as [Mint SDK Token](/api-docs/sdk/token)).

After this, that agent cannot keep using the previous browser session. Unused tokens for that agent also stop working. A call that is already connected is not hung up. You can still create a new token for the same agent later.

## Request Parameters

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| agent_id | integer | Yes | Numeric id of a call-center agent that belongs to your account. Get ids from [List Agents](/api-docs/call-center/agents). |

## Request Example

```php
<?php
// PHP with cURL
$url = 'https://api.awajdigital.com/api/sdk/session';
$token = 'your_api_token_here';

$data = [
    'agent_id' => 42
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

## Success Response Example

```json
{
  "revoked": true
}
```

A later [Mint SDK Token](/api-docs/sdk/token) for the same agent still works — this ends the current session; it does not disable the agent.

## Error Responses

| Status | Code | Description |
| --- | --- | --- |
| 401 | — | Missing or invalid access token |
| 403 | — | Account does not have the call-center permission |
| 404 | `agent_not_found` | Agent does not exist or belongs to another account |
| 422 | — | Validation failed (`agent_id` missing or not a positive integer) |
| 429 | — | Too many requests |

```json
{
  "error": "agent not found",
  "code": "agent_not_found"
}
```

---

## Browser Widget

Load the script from:

```
https://dashboard.awajdigital.com/sdk/amarvoice-call.js
```

That makes `AmarVoiceCall` available on the page. The phone popup sits in the bottom-right corner and uses its own styles, so your page CSS will not restyle it. It still opens from `open()`, `call()`, or an incoming call if you never pass a button.

HTTPS is required. Add production hostnames under **Profile → Call SDK**. For `http://localhost`, turn on localhost test mode on that page (1 or 3 days).

## Init

```javascript
const phone = new AmarVoiceCall({
  tokenUrl: '/api/amarvoice-token',
  callbacks: {
    onRegistrationState: (state) => {},
    onIncomingCall: ({ remoteParty, remoteName }) => {},
    onCallAnswered: () => {},
    onCallEnded: () => {},
    onError: (message) => {},
    onMuteChanged: (muted) => {},
  },
});

phone.mount('[data-call]');
```

`mount` puts the phone popup on the page and starts connecting in the background. How a call starts is below.

| Option | Type | Required | Description |
| --- | --- | --- | --- |
| tokenUrl | string | Yes* | Your login-protected route that proxies [Mint SDK Token](/api-docs/sdk/token). |
| sessionUrl | string | No | Override for `POST /api/sdk/session`. Taken from the token response when omitted. |
| token | string | No | Already-created token. Use `tokenUrl` instead unless you create it yourself. |
| callbacks | object | No | Optional hooks; the widget still updates itself without them. |

\* Pass `tokenUrl`, or pass both `token` and `sessionUrl`.

The SDK POSTs `tokenUrl` when the user connects (not on page load). After `disconnect()`, create a new `AmarVoiceCall` so it can request a token again.

## Two ways to start a call

Both use the same `AmarVoiceCall` object. `mount` always adds the popup. The difference is who handles the click.

### Let the SDK listen to your buttons

Pass a selector for the elements that should open the phone.

```html
<button type="button" data-call="01712345678">Call customer</button>
<button type="button" id="open-phone">Open phone</button>
```

```javascript
phone.mount('[data-call], #open-phone');
```

Against that HTML:

- Click **Call customer** — the element has `data-call="01712345678"`, so the popup opens and a call starts to that number.
- Click **Open phone** — no `data-call`, so the keypad opens empty and the agent types the number.

Any element you pass to `mount` opens the phone. `data-call="01…"` is the special part: that value is the destination, and the call starts. Without it, the agent uses the keypad.

You can pass a selector, a DOM element, or a list of elements. A second `mount` replaces the first.

```javascript
phone.mount('#open-phone');
phone.mount(document.getElementById('open-phone'));
phone.mount(document.querySelectorAll('[data-call]'));
```

### Call from your own JavaScript

Use this when the number is already in your JavaScript — a selected row, a search result, or a handler you already have. Do not put `data-call` on those elements. Call `mount()` with no selector so the popup is on the page, then `phone.call(number)` from your code.

**From a function**

```javascript
const phone = new AmarVoiceCall({
  tokenUrl: '/api/amarvoice-token',
});

phone.mount();

function callCustomer(number) {
  phone.call(number);
}

callCustomer('01712345678');
```

`mount()` adds the popup without wiring buttons. `phone.call` opens it and starts the call to that number — same result as clicking a `data-call` button.

**From your own click handler**

```html
<tr data-phone="01712345678">
  <td>Ayesha</td>
  <td><button type="button" class="call-btn">Call</button></td>
</tr>
```

```javascript
phone.mount();

document.querySelectorAll('[data-phone]').forEach((row) => {
  row.querySelector('.call-btn').addEventListener('click', () => {
    phone.call(row.getAttribute('data-phone'));
  });
});
```

The number lives on the row (or in your app state), not in `data-call`. Your handler decides which number to dial.

**Open the keypad without starting a call**

```javascript
phone.mount();
phone.open();
```

Use this for a toolbar button or shortcut that should only show the dialer. The agent types the number.

**Mix both**

```javascript
phone.mount('[data-call]');

function callFromSearch(number) {
  phone.call(number);
}
```

The SDK still listens to `data-call` buttons. Other parts of your page call `phone.call` with a number they already have. Incoming calls open the popup in all of these.

## Methods

| Method | Description |
| --- | --- |
| `mount(target?)` | Attach the popup to buttons (or to the page with no trigger). Returns `this`. |
| `unmount()` | Remove the popup and unbind those buttons. |
| `open()` / `close()` / `isOpen()` | Show or hide the panel. |
| `connect()` | Start the session. `mount()` already calls this. |
| `call(number)` | Place an outbound call. Also available as `placeCall(number)`. Opens the popup. Does nothing on an empty string; errors if a call is already in progress. |
| `answer()` / `reject()` | Answer or decline an incoming call. |
| `hangup()` | End the current call (outbound or inbound). |
| `mute()` / `unmute()` / `setMuted(boolean)` | Mute the local microphone on an **established** call. Does not put the other party on hold. |
| `isMuted()` / `isOnCall()` | Current mute / call flags. |
| `setAudioElement(el)` | Where remote audio plays. If omitted, `connect()` creates a hidden `<audio>` element. |
| `disconnect()` | Sign out of the phone and return the popup to idle. Create a new `AmarVoiceCall` to connect again. |

## Callbacks

| Callback | When |
| --- | --- |
| `onRegistrationState(state)` | `state` is `registering`, `registered`, `failed`, or `disconnected`. |
| `onIncomingCall({ remoteParty, remoteName })` | An incoming call. The popup opens itself. |
| `onCallAnswered()` | Call established (outbound or inbound). |
| `onCallEnded()` | Call terminated (local hangup, remote hangup, or failure after ringing). |
| `onError(message)` | Connect failure, mute with no call, overlapping `call()`, or a call error. |
| `onMuteChanged(muted)` | Mute applied or cleared, including on hangup. |

## In the popup

- **Idle** — number field, 12-key pad, Call.
- **Ringing out** — destination + Hang up.
- **Ringing in** — remote party, Answer, Decline.
- **Active** — duration, Mute / Unmute, Hang up.
- **Ended** — returns to idle after a short pause.

The widget requests the microphone when a call starts. The user must allow it.

## Cleanup

```javascript
phone.unmount();
await phone.disconnect();
```

Call these when your user leaves the page or logs out, and revoke the agent session from your backend with [Revoke Session](/api-docs/sdk/revoke).

---

## Error Responses

The API uses standard HTTP status codes and returns error details in JSON format.

## 401 Unauthorized

Invalid or missing API token:

```json
{
  "success": false,
  "message": "Unauthorized"
}
```

## 400 Bad Request - OTP Validation

Voice must have at least one dynamic part with digit mode:

```json
{
  "success": false,
  "message": "Voice must have at least one dynamic part with digit mode for OTP broadcast"
}
```

## 400 Bad Request - Duplicate Numbers

Duplicate phone numbers found in the request:

```json
{
  "success": false,
  "message": "Duplicate phone number found",
  "duplicated_number": "019XXXXXXXX"
}
```

## 400 Bad Request - Audio Validation Failed

Direct broadcast audio must be G.711-compatible (WAV, 8000 Hz, mono, `pcm_s16le`). Each failed URL is listed:

```json
{
  "success": false,
  "message": "Audio file validation failed. Files must be in G.711-compatible format (WAV, 8000Hz, Mono, pcm_s16le)",
  "errors": [
    {
      "field": "voices[0]",
      "url": "https://cdn.example.com/audio/announcement.wav",
      "issue": "Invalid sample rate. Expected: 8000Hz, Found: 44100Hz"
    }
  ]
}
```

## 402 Payment Required

Insufficient balance to process the broadcast:

```json
{
  "success": false,
  "message": "Insufficient balance"
}
```

## 403 Forbidden

Voice not found, not approved, or sender not active:

```json
{
  "success": false,
  "message": "Voice not found or not approved"
}
```

## 409 Conflict

Duplicate request - request_id already used within 15 minutes:

```json
{
  "success": false,
  "message": "Request already processed"
}
```

## 404 Not Found

Broadcast or survey not found or access denied:

```json
{
  "success": false,
  "message": "Broadcast not found"
}
```

## 500 Internal Server Error

Server error occurred while processing the request:

```json
{
  "success": false,
  "message": "Failed to create OTP broadcast"
}
```

## Call Center Errors

Call-center SDK routes and `GET /api/cc/agents/:agent_id/calls` return `{ "error", "code" }` rather than `{ "success": false, "message" }`.

### 401 Unauthorized - Invalid SDK Token

`POST /api/sdk/session` when the token is missing, already used, or expired:

```json
{
  "error": "invalid or expired token",
  "code": "token_invalid"
}
```

### 403 Forbidden - Agent Inactive

Agent is not active (when creating a token) or was deactivated after the token was created (when connecting):

```json
{
  "error": "agent not active",
  "code": "agent_inactive"
}
```

### 403 Forbidden - Origin Not Allowed

`POST /api/sdk/session` when the page is not on HTTPS, or its hostname is not on your Call SDK allowed-domain list (and test mode is off):

```json
{
  "error": "origin not allowed for this account",
  "code": "origin_not_allowed"
}
```

### 404 Not Found - Agent Not Found

`POST /api/sdk/token`, `DELETE /api/sdk/session`, and `GET /api/cc/agents/:agent_id/calls` when `agent_id` does not belong to your account:

```json
{
  "error": "agent not found",
  "code": "agent_not_found"
}
```

## Survey-Specific Errors

### 403 Forbidden - Template Not Found

Survey template not found or not in published status:

```json
{
  "success": false,
  "message": "Template not found or not published"
}
```

### 403 Forbidden - Sender Not Found

Sender number not found or not active:

```json
{
  "success": false,
  "message": "Sender not found or not active"
}
```
