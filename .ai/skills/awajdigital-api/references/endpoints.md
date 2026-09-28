# AwajDigital API — endpoint reference

Source: official Postman collection "AwajDigital API". Anything not listed here is undocumented.

- Base URL: `https://api.awajdigital.com/api`
- Auth: `Authorization: Bearer {apiToken}`; send `Accept: application/json` (and `Content-Type: application/json` for JSON bodies).
- `request_id`: unique per request. OTP endpoint documents min 16 chars; other endpoints document 16-64 chars. Dedup window 15 minutes where stated.

## Account
| Method | Path | Notes |
|---|---|---|
| GET | `/balance` | Current balance in BDT for broadcasts and surveys. |

## OTP broadcast
`POST /broadcasts/otp`
Body: `request_id`, `voice` (approved voice with a dynamic digit part), `sender`, `phone_number` (single), `otp_code`.

## Bulk broadcast
`POST /broadcasts`
Body: `request_id`, `voice`, `sender`, `phone_numbers[]` (max 999).

## Direct broadcast (extra permission)
`POST /broadcasts/direct`
Body: `request_id` (16-64), `sender`, `phone_numbers[]`, `voices[]` (1-10 CDN URLs, played in sequence; WAV, 8000 Hz, mono, pcm_s16le), `metadata` (optional object).
Requires extra permission; contact support to enable.

## Direct TTS broadcast (needs direct-broadcast AND AI TTS permissions)
`POST /broadcasts/direct-tts`
Body: `request_id`, `sender`, `phone_numbers[]`, `texts[]` (1-10, max 5000 chars each), `voice` (example: `female`), `language_code` (example: `bn-BD`), `metadata` (optional).
- Rate limit: 1 request/second.
- Same `request_id` re-sent returns 202 idempotently.
- Asynchronous processing.

`GET /broadcasts/direct-tts/{requestId}/status`
Status values: `pending`, `processing`, `completed` (includes `broadcast_id`), `failed` (includes `error`).

## Broadcast list and result
`GET /broadcasts?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD`
- ISO 8601 dates, both optional. Default: last 30 days. Max range 90 days.

`GET /broadcasts/{id}/result` — status and results of one broadcast.

## Surveys
`POST /surveys` (template survey)
Body: `request_id` (16-64), `template_name` (published template), `sender`, `phone_numbers[]`, `metadata` (optional), `webhook_url` (optional).

`POST /v1/surveys/direct-order` (full path: `/api/v1/surveys/direct-order`; note the `/v1`)
Body: `request_id` (16-64), `sender`, `phone_numbers[]`, `start_voices[]`, `question_voices[]`, `invalid_voice`, `end_voices[]`, `dtmf_options[]`, `metadata`, `webhook_url`, `config` (example: `{"retry_count": 1}`).
- Voice entry is either `{"type": "voice", "name": "..."}` (approved library voice, fully static; for `invalid_voice`/`ringback_voice`/`all_busy_voice` exactly one part) or a CDN audio URL (WAV, 8000 Hz, mono, pcm_s16le). Audio URLs need extra permission; library voices do not.
- `dtmf_options[]` item: `key` plus either `voices[]` (play) or `option_type: "transfer"` with `transfer_numbers[]`, `ringback_voice`, `all_busy_voice`.

`GET /surveys/{id}/result` — survey metadata, call status distribution, per-number results including keys pressed.

## Resources
| Method | Path | Notes |
|---|---|---|
| GET | `/voices` | Approved voices for the account. |
| GET | `/senders` | Active sender numbers. |
| POST | `/voices/upload` | multipart: `name`, `audio`. Created as `pending`; needs admin approval. Formats: mp3, wav, ogg, m4a, aac, webm, flac. Max 10 MB. |

## Call center (requires call-center permission)
| Method | Path | Notes |
|---|---|---|
| GET | `/cc/agents` | All agents incl. inactive/unapproved. `id` is used as `agent_id`. 60 req/min. |
| GET | `/cc/agents/{agentId}/calls?date=YYYY-MM-DD&page=1` | Outgoing calls for one Asia/Dhaka calendar day (by call start). Page size fixed at 100. Re-fetch later for status/duration/recording after hangup billing. 60 req/hour. |
| POST | `/sdk/token` | Body `{ "agent_id": 42 }`. Single-use token; call from backend only. |
| POST | `/sdk/session` | Browser widget only. Body `{ "token": "avt_..." }`. No Bearer token. Page must be HTTPS on a hostname registered under Profile -> Call SDK (localhost needs test mode). |
| DELETE | `/sdk/session` | Body `{ "agent_id": 42 }`. Ends the embedded phone session (call on user logout). Does not hang up an in-progress call. |

## Undocumented (do not invent)
Response bodies, error payloads, HTTP status codes other than 202 (TTS), webhook payloads, phone number format accepted by the API (examples use `019XXXXXXXX`; sender uses `8801234567890`).
