---
name: awajdigital-api
description: Use when adding, changing, testing, or documenting any AwajDigital API endpoint in this package (broadcasts, OTP, dynamic broadcasts, direct/TTS broadcasts, surveys, voices, senders, payments, call center SDK). Contains the API contract, limits, and permission rules.
---

# AwajDigital API skill

## Before you write code
1. Read `references/endpoints.md` for the exact contract, limits, and permission requirements.
2. Anything not in that file (response bodies, error formats, webhook payloads) is unknown: do not guess. Add `TODO(verify)` and ask the maintainer to supply a real sample response.

## Workflow for a new or changed endpoint
1. Add or adjust enums and `final readonly` DTOs for the request and response in `src/Modules/<Area>/DataTransferObjects` and `src/Modules/<Area>/Enums`.
2. Add one Action per operation in `src/Modules/<Area>/Actions`; it uses the `src/Client` transport. Keep the endpoint path in one place.
3. Expose the operation through the manager `src/AwajDigital.php` (and the facade docblock).
4. Add client-side validation for every documented limit (see references/endpoints.md).
5. Map failures to the package exceptions through the central status-code mapper.
6. Add fake support (`AwajDigital::fake()`) and tests: success, validation, 401/403, 429, 5xx/timeout, exact request assertions.
7. Update `references/endpoints.md`, README usage and CHANGELOG where relevant.
8. Run `composer test`, `composer analyse`, `composer style:check`.

## Key rules
- Two error response shapes exist: `{success, message}` (broadcasts, surveys, voices, senders, payments) and `{error, code}` (call-center + all `/sdk/*` routes + `GET /cc/agents/{id}/calls`). The exception mapper must handle both shapes cleanly.
- Endpoints covered include: Account, Broadcasts (OTP, Bulk, Dynamic, Direct, Direct TTS, Results), Voices, Senders, Surveys (Template, Direct, Webhook), Payments, and Call Center (Agents, Calls, SDK Token, Revoke Session).
- `request_id` idempotency semantics differ by endpoint: some use `409` on reuse within 15 min (bulk/OTP, direct broadcast, direct survey), others use `202`-idempotent-replay with `500` on stored failure (dynamic broadcast, direct TTS). Inspect `references/endpoints.md` per endpoint.
- Permission-gated endpoints (direct broadcast, direct TTS, dynamic broadcast with TTS, audio URLs in direct survey, payments, call center) can fail with a permission error even for valid tokens. Surface it as `PermissionDeniedException` with a helpful message.
- Direct TTS and Dynamic Broadcast are asynchronous: dispatch returns 202. Direct TTS status is polled via `GET /broadcasts/direct-tts/{requestId}/status`; Dynamic broadcast creates individual broadcasts polled via `GET /broadcasts?request_id={id}` and `GET /broadcasts/{id}/result`.
- Rate limits: direct TTS 1 req/sec; `GET /cc/agents` 60/min; `GET /cc/agents/{id}/calls` 60/hour. Respect them; never retry a 429 in a tight loop.
- Call-center SDK: `POST /sdk/token` and `DELETE /sdk/session` are backend-only. `POST /sdk/session` is called by the browser widget without Bearer token and is not wrapped as a server-side action.
- Dates for call listing are calendar days in Asia/Dhaka (`YYYY-MM-DD`, call start time).
- Audio for direct broadcasts: WAV, 8000 Hz, mono, pcm_s16le, served from a CDN URL.

## Never
- Never make real API calls in tests or examples.
- Never log the token, phone numbers, or OTP codes unmasked.
- Never invent fields, enum values, or status codes.
