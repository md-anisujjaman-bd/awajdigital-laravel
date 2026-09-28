---
name: awajdigital-api
description: Use when adding, changing, testing, or documenting any AwajDigital API endpoint in this package (broadcasts, OTP, direct/TTS broadcasts, surveys, voices, senders, call center SDK). Contains the API contract, limits, and permission rules.
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
- `request_id` is the idempotency key: 16-64 chars, dedup window is 15 minutes. Auto-generate if not provided. Reuse the same value on retries.
- Permission-gated endpoints (direct broadcast, direct TTS, audio URLs in direct survey, call center) can fail with a permission error even for valid tokens. Surface it as `PermissionDeniedException` with a helpful message ("contact AwajDigital support to enable this API").
- Direct TTS is asynchronous: sending returns 202; poll the status endpoint (`pending`, `processing`, `completed` with `broadcast_id`, `failed` with `error`). Provide a polling helper with a max-attempts and interval.
- Rate limits: direct TTS 1 req/sec; `GET /cc/agents` 60/min; `GET /cc/agents/{id}/calls` 60/hour. Respect them; never retry a 429 in a tight loop.
- Call-center SDK: `POST /sdk/token` is backend-only. `POST /sdk/session` is called by the browser widget and does NOT use the Bearer token; this package should not wrap it unless explicitly asked.
- Dates for call listing are calendar days in Asia/Dhaka (call start time).
- Audio for direct broadcasts: WAV, 8000 Hz, mono, pcm_s16le, served from a CDN URL.

## Never
- Never make real API calls in tests or examples.
- Never log the token, phone numbers, or OTP codes unmasked.
- Never invent fields, enum values, or status codes.
