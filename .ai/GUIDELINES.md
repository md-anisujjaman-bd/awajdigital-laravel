# Awajdigital Laravel

This repository is a Laravel package. Keep the package focused, idiomatic, and easy for Laravel developers to install, test, and maintain.

## Package Conventions

- Use Laravel-native package APIs and the existing service provider shape before adding abstractions.
- Keep package names, namespaces, Composer metadata, publish tags, documentation, and examples aligned with `md-anisujjaman-bd/awajdigital-laravel`.
- Add only the files and dependencies needed for the package behavior being implemented.
- Prefer explicit Laravel package code over helper abstractions unless the extension point is real.
- Keep tests focused on observable package behavior through public APIs, service provider wiring, commands, routes, published resources, and documentation promises.

## Structure Conventions

- `src/Http/<Domain>/{Controllers,Requests,Resources}`: HTTP concerns grouped by domain rather than by transport type. An API endpoint is loaded through `routes/api.php`, a browser-facing one through `routes/web.php`. Create only the subfolder a domain's endpoint actually needs.
- `src/Modules/<Domain>/{Actions,Services,...}`: business logic grouped by domain, kept out of `Http` and `Console`. Create a domain's folder only when it has real content. Do not pre-scaffold empty modules.
- `src/Console/Commands`: package Artisan commands, registered through the provider's console-guarded `commands()` call.

## Quick Commands

- Full validation: `composer test`
- Formatting check: `composer style:check`
- Static analysis: `composer analyse`
- Tests, in parallel: `composer test:parallel`
- Switch Laravel version: `composer switch:l12` (or `l13`)
- Workbench build: `composer build`
- Workbench server: `composer serve`

## Working with Subagents

The primary session plans, wires the feature through the service provider, and reviews the result. It delegates the actual implementation to a subagent whenever a change is non-trivial, spanning multiple files, adding a capability, or refactoring existing behavior. Before calling any such change done, review the subagent's diff against `write-php-code`, `write-comments`, and `write-php-test`, then run `task-finalization`. A one-file, low-risk change does not need the split; write and verify it directly.

## Local Skills

- `scaffold-module`: use when adding a package capability or a new domain module under `src/Modules`, where the files go, which subfolders and HTTP transport layer it needs, and how it's wired through the service provider. Covers commands, migrations, routes, config, views, translations, assets, middleware, publish tags, workbench files, and console-only behavior.
- `create-dto-action`: use when adding a single-purpose write operation, pairing an Action with the DTO that provisions its input.
- `create-query`: use when adding a read-only lookup, to decide between a Query class and a plain inline Eloquent call.
- `write-php-code`: use when writing or refactoring PHP classes. Covers strict typing, explicit types, constructor promotion, and when an abstraction earns its place.
- `write-comments`: use whenever writing or reviewing a comment, in PHP.
- `write-php-test`: use when writing, editing, fixing, or reviewing package tests with PHPUnit, Paratest, and Orchestra Testbench. Covers TDD, where a test belongs, and its naming, block structure, and mocking conventions.
- `task-finalization`: use before marking any change complete, to run the quality tools that match what changed.
- `package-release`: use when preparing changelog, release notes, tags, or GitHub release workflow changes.
- `package-compatibility`: use when reviewing code, dependencies, or CI against the PHP and Laravel support matrix.
- `package-generate-skill`: use when updating the bundled Boost skill from the package implementation, README, and examples.

## AwajDigital package specifics

Unofficial, open-source Laravel client for the AwajDigital Voice Broadcasting API. Not affiliated with or endorsed by AwajDigital. Keep this disclaimer in README and composer.json.

### Source of truth for the API
- Contract: `.ai/skills/awajdigital-api/references/endpoints.md`. Load the `awajdigital-api` skill before adding or changing any endpoint.
- If a response shape, error payload or webhook payload is not documented there, do not invent it. Mark it `TODO(verify)` and ask the maintainer for a real sample.

### Commands (use these exact script names)
- `composer test`, `composer test:parallel`
- `composer analyse` (PHPStan/Larastan level 8)
- `composer style:fix`, `composer style:check` (Pint)
- `composer rector`, `composer rector:check`
- `composer switch:l12`, `composer switch:l13` (test against a Laravel major)
- `composer serve` (workbench)
Never report a task as done without running tests, analyse and style:check.

### Structure
- `src/AwajDigital.php` manager, `src/Facades/AwajDigital.php` facade, `src/Client/` HTTP transport and error mapping, `src/Exceptions/` typed exceptions.
- One folder per API area: `src/Modules/{Account,Broadcasts,Surveys,Voices,Senders,CallCenter}/{Actions,DataTransferObjects,Enums}`. Shared code in `src/Modules/Shared`.
- One Action per operation. Requests and responses are `final readonly` DTOs and enums; no raw arrays in the public API.
- HTTP via Laravel's `Http` facade only (`illuminate/http`). No extra HTTP client dependency without maintainer approval.
- Config: `config/awajdigital.php` (`base_url`, `token`, `default_sender`, `timeout`, `retry`), all from env.
- Keep endpoint paths in one place. Most paths are `/api/...`, but direct survey is `/api/v1/surveys/direct-order`.

### Exceptions
`AwajDigitalException` (base), plus typed subclasses for authentication, permission denied, rate limited, validation and API errors. Map HTTP status codes to exceptions in ONE place.

### Client-side validation (before any HTTP call)
- `request_id`: auto-generate (ULID/UUID) when not supplied; 16-64 chars; reuse the same value on retries.
- Bulk broadcast max 999 numbers; direct broadcast 1-10 audio URLs; direct TTS 1-10 texts of max 5000 chars each; broadcast list date range max 90 days.
- Normalize Bangladeshi phone numbers in a single value object; reject invalid input early.

### Security (non-negotiable)
- The token comes only from config/env. Never log or dump it, never put it in exception messages.
- Mask phone numbers and OTP codes in logs and exception context.
- Call-center SDK token minting is server-side only; docs must say the API token never goes to a browser.
- No real network calls in tests. Use `Http::fake()` and `AwajDigital::fake()`. No real credentials or real phone numbers in fixtures.
- Fail loudly with typed exceptions; no silent fallbacks.
- No new runtime dependencies without asking.

### Testing
- Every endpoint: success, validation failure, 401/403, 429, 5xx/timeout.
- Assert the exact request (URL, method, headers, JSON body) with `Http::assertSent`.
- Test that retries reuse the same `request_id`.

### Definition of done
1. Tests pass on both supported Laravel majors.
2. `composer analyse` and `composer style:check` are clean.
3. `references/endpoints.md`, README and CHANGELOG are updated where relevant.
4. No unresolved `TODO(verify)` without maintainer sign-off.
