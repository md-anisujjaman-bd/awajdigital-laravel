# AwajDigital API — endpoint reference (v2, from official docs)

Source: official documentation at awajdigital.com/api-docs (canonical), cross-checked against the Postman collection. This supersedes the collection wherever they differ.

- Base URL: `https://api.awajdigital.com/api`
- Auth: `Authorization: Bearer {apiToken}`, `Accept: application/json`; `Content-Type: application/json` for JSON bodies (except Upload Voice, which is multipart).
- `request_id`: 16-64 chars, unique per request. Where stated, the same `request_id` re-sent within 15 minutes is idempotent (returns the original 202/200) EXCEPT where noted as a straight `409` conflict.
- Standard error shape: `{"success": false, "message": "..."}`, sometimes with `"errors": [{"field", "issue"}]` or `"duplicated_number"`. Call-center/SDK routes and `GET /cc/agents/{id}/calls` use a DIFFERENT shape: `{"error": "...", "code": "..."}`.
- Bangladeshi phone numbers: `01XXXXXXXXX` format for recipients; `sender`/caller numbers use the `8801XXXXXXXXX` form (see examples).

## Account

### `GET /balance`
Response: `{"success": true, "balance": 1250.75}` (BDT, number).

## Broadcasts

### `POST /broadcasts/otp` — Voice OTP
Body: `request_id`, `voice` (must have a dynamic digit-mode part), `sender`, `phone_number` (single), `otp_code` (4-6 digits).
Success: `{"success": true, "broadcast": {"id", "name", "status", "createdAt"}}`.
Errors: `400` (voice lacks digit-mode dynamic part), `401`, `403` (voice/sender), `409` (duplicate `request_id` within 15 min).
Idempotency: duplicate `request_id` within 15 min rejected with `409`.

### `POST /broadcasts` — Bulk broadcast
Body: `request_id`, `voice`, `sender`, `phone_numbers[]` (max 999, no dupes).
Success/errors: same shape as OTP.

### `POST /broadcasts/dynamic` — Dynamic broadcast (NOT in the old Postman collection)
Personalized broadcast: one approved voice with dynamic parts (`tts`, `number`, `character`, `digit`, `audio_url`), filled per recipient. Async: 202 accepted, one broadcast created per recipient in the background, all sharing the request `request_id`.
Body: `request_id`, `voice` (1-255 chars, must have dynamic parts), `sender`, `recipients[]` (1-10, no duplicate phone numbers): each `{phone_number, data: {key: value, ...}}` (string values, max 5000 chars each; every dynamic key required by the voice must be present).
`audio_url` dynamic parts: downloaded and validated as WAV/8000Hz/mono/pcm_s16le; invalid file marks the request failed.
Permission: if any required dynamic part uses `tts`, the account needs the AI TTS permission (else `403`); `audio_url` parts do not need it.
Success (202): `{"success": true, "message": "Request accepted", "data": {"request_id": "..."}}`.
No dedicated status endpoint — poll `GET /broadcasts?request_id=<id>` until broadcasts appear (NOTE: this filter param is not documented elsewhere in List Broadcasts — treat as this endpoint's own convention), then `GET /broadcasts/{id}/result` per broadcast id. On failure, List Broadcasts includes it in `data.error`.
Errors: `400` (dup recipient numbers, voice has no dynamic parts/keys, missing recipient data), `402`, `403` (voice/sender/AI TTS permission), `422` (request_id length, phone format, recipients size, data length), `500` (acceptance failure, or retry of a previously-failed `request_id` — use a new `request_id` instead).
Idempotency: same `request_id` on an already-accepted request → `202` "Request already accepted". Same `request_id` on a failed request → `500` "Request failed" with the original error.

### `POST /broadcasts/direct` — Direct broadcast (extra permission)
Body: `request_id`, `sender`, `phone_numbers[]` (1-999, no dupes), `voices[]` (1-10 CDN URLs, WAV/8000Hz/mono/pcm_s16le, played in sequence), `metadata` (optional object).
Success: `{"success": true, "broadcast": {id, name, status, createdAt}}`. Poll `GET /broadcasts/{id}/result`.
Errors: `400` (dup numbers, bad audio format), `402`, `403` (permission not enabled, sender), `409` (`request_id` reused within 15 min), `422`.
Idempotency: `409` on reuse within 15 min (not the 202-idempotent style).

### `POST /broadcasts/direct-tts` — Direct TTS broadcast (needs direct-broadcast AND AI TTS permissions)
Body: `request_id`, `sender`, `phone_numbers[]` (1-999, no dupes), `texts[]` (1-10, max 5000 chars each), `voice` (optional, `male`|`female`, default provider default), `language_code` (optional, e.g. `bn-BD`, max 10 chars), `metadata` (optional).
Rate limit: 1 req/sec/account (`429` on exceed).
Success (202): `{"success": true, "message": "TTS broadcast request accepted, processing started", "data": {"request_id": "..."}}`.
Errors: `400` (dup numbers), `402`, `403` (permissions/sender), `422`, `429`.
Idempotency: same as Dynamic Broadcast (202 idempotent replay; failed → `500`, use new `request_id`).

### `GET /broadcasts/direct-tts/{requestId}/status`
Success: `{"success": true, "status": "pending"|"processing"|"completed"|"failed", "broadcast_id"?: int|null, "error"?: string}`.
Errors: `401`, `404` (not found / not owned).

### `GET /broadcasts` — List broadcasts
Query: `start_date`, `end_date` (ISO 8601, both optional; default last 30 days; max range 90 days).
Success: `{"success": true, "broadcasts": [{id, name, status, createdAt}], "dateRange": {"startDate", "endDate"}}`.
Error: `400` if range > 90 days: `{"success": false, "message": "Date range cannot exceed 90 days"}`.

### `GET /broadcasts/{id}/result`
In progress: `{"success": true, "broadcast": {id, name, status, listenerCount, completeCount, createdAt}, "isComplete": false, "message": "..."}`.
Complete: adds `"statusDistribution": {pending, answered, notAnswered, rejected, busy, failed, unknown}` and `"results": [{phoneNumber, status, duration}]`.

## Voices / Senders

### `GET /voices`
Success: `{"success": true, "voices": [{id, name, status: "pending"|"approved"|"rejected", createdAt}]}`.

### `POST /voices/upload` (multipart/form-data)
Fields: `name` (1-255 chars, unique per account, case-insensitive), `audio` (file, max 10MB; mp3/wav/ogg/m4a/aac/webm/flac).
Success (201): `{"id", "name", "status": "pending"}`.
Errors: `409` (duplicate name), `413` (too large), `415` (unsupported format).

### `GET /senders`
Success: `{"success": true, "senders": [{id, callingNumber, status: "active"|"inactive"}]}`.

## Surveys

### `POST /surveys` — template survey
Body: `request_id`, `template_name` (must be published), `sender`, `phone_numbers[]` (max 999, no dupes), `metadata` (optional), `webhook_url` (optional).
Success: `{"success": true, "survey": {id, name, status: "ready", totalCount, createdAt, metadata}}`.
Idempotency window: 15 minutes (duplicate creation prevented).

### `POST /v1/surveys/direct-order` — direct survey (note the `/v1` — full path `/api/v1/surveys/direct-order`)
No template: full survey tree in the request.
Body: `request_id`, `sender`, `phone_numbers[]` (1-999, no dupes), `start_voices[]` (0-10, optional), `question_voices[]` (1-10, required), `invalid_voice` (optional single entry), `end_voices[]` (0-10, optional), `dtmf_options[]` (1-9 items, required), `metadata` (optional), `webhook_url` (optional), `config.retry_count` (0-3, default 0).
`dtmf_options[]` item: `key` (`1`-`9`, unique, no `0`/`*`/`#`), `option_type` (`voice` default | `transfer`), `voices[]` (0-10, for `voice` type), `transfer_numbers[]` (required for `transfer`), `ringback_voice`, `all_busy_voice` (transfer-only, single entry).
Voice entry: `{"type": "voice", "name": "..."}` (approved, non-archived, fully static library voice — single-part only in single-file fields; a multi-part voice expands to all parts, counted before expansion against the 1-10 limits) OR a plain CDN URL string (WAV/8000Hz/mono/pcm_s16le). Arrays may mix both forms. URL entries need extra permission (send-direct-survey); `{"type": "tts"}` or any other type is reserved and fails `422`.
Success: `{"success": true, "survey": {id, name, status: "surveying", totalCount, createdAt, metadata}}`.
Errors: `400` (dup numbers/dtmf keys, bad audio format, voice not found/approved/archived/has dynamic parts, multi-part voice in single-file field — reported as `errors[]` with `{field, issue}`), `402`, `403` (URL entries without permission — same `errors[]` shape, or sender), `409` (`request_id` reused within 15 min), `422` (incl. unknown voice entry `type`).
Idempotency: `409` on `request_id` reuse within 15 min. A `403` for URL-permission is NOT recorded — same `request_id` can be retried once permission is fixed.

### `GET /surveys/{id}/result`
In progress: `{"success": true, "survey": {id, name, status, totalCount, completeCount, createdAt, metadata}, "isComplete": false, "statusDistribution": {pending, answered, not_answered, failed}, "numbers": [{number, status, duration, pressedKeys: []}]}`.
Complete: `isComplete: true`, same shape; `status` becomes `"completed"` (or `"cancelled"`).

### Survey webhook (POST to your `webhook_url`, sent once when status becomes "completed")
Body: `{"survey_id", "metadata", "results": [{"phone_number", "status": "pending"|"answered"|"not_answered"|"failed", "duration", "response"?, "responses"?}]}`. `response`/`responses` only present when `status` is `"answered"`.

## Payments (NOT in the old Postman collection)

### `POST /payments/create`
Body: `amount` (BDT, min 20), `success_url` (HTTPS, host must be allowlisted via support), `cancel_url` (optional, same host rule).
Success: `{"success": true, "payment_url": "...", "invoice_id": "..."}`. After payment, our server redirects the payer to `success_url` with `?invoice_id=...&status=completed|pending` — this redirect alone is NOT proof of payment; always confirm via status endpoint.
Errors: `401`, `403` (payment API not enabled), `422` (amount < 20, non-https, non-allowlisted host), `502` (gateway error).

### `GET /payments/{invoice_id}/status`
Also triggers fulfillment if payment completed but not yet credited. Success: `{"success": true, "invoice_id", "status": "completed"|other, "amount"}`.
Errors: `401`, `403`, `404`, `502`.

## Call Center (requires call-center permission)

Error shape for this whole section (and Exchange/Revoke/Mint SDK Token) is `{"error": "...", "code": "..."}`, NOT the `{success,message}` shape used elsewhere.

### `GET /cc/agents`
Rate limit: 60/min. Success: `{"data": [{id, full_name, email, extension, presence: offline|online|on_call|on_break|wrap_up, status: active|inactive|banned, approval_status: pending|approved|rejected, sender}]}`. Ordered by `id` asc. `id` is used as `agent_id` elsewhere.
Errors: `401`, `403`, `429`.

### `GET /cc/agents/{agent_id}/calls`
Rate limit: 60/hour. Page size fixed at 100. Covers outbound + internal calls only (not inbound queue).
Query: `date` (required, `YYYY-MM-DD`, Asia/Dhaka, by call **start**), `page` (optional, default 1).
Success: `{"meta": {total, per_page, current_page, last_page}, "data": [{id, agent_id, uuid, called_number, caller_number, call_type: internal|outbound, status: initiated|answered|failed|busy|no_answer|cancelled, duration, recording, created_at}]}`. Re-fetch same agent+date later to pick up final status/duration/recording (typically 1-10 min after hangup).
Errors: `401`, `403`, `404 agent_not_found`, `422` (bad date/page), `429`.

### `POST /sdk/token` — Mint SDK token (backend only)
Body: `{"agent_id": int}` (must be active + approved).
Success: `{"token": "avt_...", "expires_in": int, "expires_at": "...", "session_url": "..."}`. Forward this JSON unchanged to the browser widget; do not store/reuse.
Errors: `401`, `403` (no permission / `agent_inactive` / `agent_not_approved`), `404 agent_not_found`, `422`, `429`.

### `POST /sdk/session` — Exchange session (browser widget only; NOT called by this package's backend code)
Body: `{"token": "..."}` (16-128 chars). No Bearer auth. Requires HTTPS origin on an allowlisted host (or localhost test mode).
Success: SIP/WebSocket connection details (`wss_url`, `domain`, `extension`, `sip_username`, `sip_password`, `expires_in`, `expires_at`, `ice_servers`). Do not persist `sip_password`.
Errors: `401 token_invalid`, `403 agent_inactive` / `origin_not_allowed`, `422`.

### `DELETE /sdk/session` — Revoke session (backend only)
Body: `{"agent_id": int}`. Ends the current browser session for that agent (does not hang up an active call; does not disable the agent for future tokens).
Success: `{"revoked": true}`.
Errors: `401`, `403`, `404 agent_not_found`, `422`, `429`.

### Browser widget (`https://dashboard.awajdigital.com/sdk/amarvoice-call.js`)
Out of scope for a server-side Laravel package's PHP API — this package should only provide the two backend proxy routes' worth of client calls (`sdk/token`, `sdk/session` DELETE) and documentation/example Blade/JS snippet for the widget, not a JS SDK wrapper.

## Global error responses (outside the {error,code} call-center shape)
`401` unauthorized; `400` (OTP voice missing digit part, duplicate phone numbers — includes `duplicated_number`, audio validation failed — includes `errors[]` with `field`/`url`/`issue`); `402` insufficient balance; `403` (voice/sender not found or not approved/active); `404` (broadcast/survey not found); `409` (`request_id` reused within 15 min, for the endpoints that use 409 semantics); `500` (server error / failed-request retry).

## Notes / discrepancies vs the old Postman collection
- Postman collection was missing: Dynamic Broadcast, Create/Get Payment, and full call-center/SDK field-level detail, webhook payload shape, and all documented error codes/bodies.
- `sender` examples use the `8801XXXXXXXXX` form in broadcast/survey bodies, but List Senders / List Agents return numbers without the leading `880` in some examples (`01712345678`) — do not assume a single canonical format; pass through what the account's `/senders` or `/cc/agents` response returns.