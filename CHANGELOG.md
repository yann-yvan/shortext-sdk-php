# Changelog

All notable changes to `shortext-php` will be documented in this file.

## 2.0.0 - 2026-10-24

The SDK now covers the whole Shortext API v2.0 and runs outside Laravel. See "Upgrading from 1.x" in the README.

### Added

- `NyCorp\Shortext\Shortext`, the client of the API v2.0 (company API key as Bearer token, configurable base URL):
  `me()`, `agents()`, `channels()`, `shortextEndpoints()`, `conversations()`, `templates()`, `webhooks()`,
  `hostedSessions()`, `inbound()` (`POST /v1.0/messages/inbound`, sync / async reply modes), `v1()` (`POST /messages/send`, dynamic links)
  and `http()` for raw calls.
- Typed results: `Resource` (read-only, property / array / dot-notation access), `Page` (pagination, lazy `all()`).
- Exceptions mapped from the envelope code: `ValidationException`, `NotFoundException`, `ConflictException`,
  `NotAuthorizedException`, `AuthenticationException`, `ApiVersionSunsetException` (HTTP 410, code 1200), `ApiException`,
  `TransportException`, all implementing `ShortextException`.
- `Http\Response` exposes the API version, `Deprecation`, `Sunset` and deprecation `Link` headers.
- Message builders for the shared grammar (`Message::text()`, `template()`, `media()` / `image()`..., `buttons()`, `list()`,
  `ctaUrl()`, `payment()`, `interactive()`, `raw()`) and the contact grammar (`Contact`).
- `Webhooks\WebhookVerifier`: `X-Shortext-Signature` HMAC check in constant time with a timestamp tolerance window,
  decoding into typed events (`message.*`, `conversation.*`, `channel.*`, `agent.*`, `hosted_session.*`, `webhook.test`).
- Laravel: `config/shortext.php` (`SHORTEXT_API_KEY`, `SHORTEXT_BASE_URL`, `SHORTEXT_WEBHOOK_SECRET`...),
  `Shortext` and `WebhookVerifier` in the container, `Shortext::webhookEvent($request)`.
- Test suite (Pest, faked HTTP).

### Changed

- PHP 8.2 or later is required.
- HTTP goes through Guzzle; `nycorp/lite-api` and `spatie/laravel-package-tools` are no longer required, and Laravel is optional.
- `Sdk\ShortextClient::sendMessage()` / `createDynamicLink()` return `Http\Response` (same getters as `DefResponse`, still never throw).
- Payloads are no longer written to the Laravel log.

### Fixed

- `MessageBuilder::ctaUrl()` and `interactiveList()` send their header as `{type, value}`: the header text was dropped.
- The classes declared as `Nycorp\Shortext\...` could not be autoloaded without an optimized class map; every class is
  now `NyCorp\Shortext\...` and the 1.x spelling still resolves.
- `DynamicLinkBuilder::getPayload()` failed when the package names were not set.
- `FlowCrypto` declared its `phpseclib/phpseclib` dependency (suggested) and no longer creates a dynamic property.

### Deprecated

- `Sdk\ShortextClient`, `Sdk\ShortextSdk` and `Sdk\Builder\MessageBuilder`: use `Shortext`, `Messages\Message` and `Messages\Contact`.

### Removed

- The empty placeholder `Shortext` class (now the client), the `shortext-php` command, migration stub, views and `config/shortext-php.php`.
