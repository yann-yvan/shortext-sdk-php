<?php

namespace NyCorp\Shortext\Http;

use DateTimeImmutable;
use NyCorp\Shortext\Exceptions\ApiException;
use NyCorp\Shortext\Support\Data;

/**
 * A Shortext API response: the envelope (success, code, message, body) and the HTTP metadata.
 *
 * The API answers HTTP 200 whatever the outcome (except 410 once a version is sunset):
 * the outcome is in `success` and `code`.
 */
class Response
{
    /**
     * @param  array<string, string>  $headers  lower-cased names
     * @param  array<string, mixed>  $envelope
     */
    public function __construct(
        protected int $status,
        protected array $headers,
        protected array $envelope,
    ) {}

    public function successful(): bool
    {
        return (bool) ($this->envelope['success'] ?? false);
    }

    public function failed(): bool
    {
        return ! $this->successful();
    }

    /** Absolute value of the envelope code (1000 on success, see ResponseCode) */
    public function code(): int
    {
        return abs((int) ($this->envelope['code'] ?? 0));
    }

    public function message(): string
    {
        return (string) ($this->envelope['message'] ?? '');
    }

    public function body(): mixed
    {
        return $this->envelope['body'] ?? null;
    }

    /** A value of the body with dot notation, e.g. get('session.assignee.id') */
    public function get(string $key, mixed $default = null): mixed
    {
        return Data::get($this->body(), $key, $default);
    }

    /** Present when success is false: give it to the Shortext support */
    public function traceId(): ?string
    {
        $traceId = $this->envelope['trace_id'] ?? null;

        return $traceId === null || $traceId === '' ? null : (string) $traceId;
    }

    /** HTTP status: 200, or 410 when the API version is sunset */
    public function status(): int
    {
        return $this->status;
    }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    /** @return array<string, string> */
    public function headers(): array
    {
        return $this->headers;
    }

    /** Version that served the request (X-Shortext-Api-Version) */
    public function apiVersion(): ?string
    {
        return $this->header('X-Shortext-Api-Version');
    }

    /** Set once the version is deprecated (Deprecation header, RFC 9745) */
    public function deprecatedAt(): ?DateTimeImmutable
    {
        $value = $this->header('Deprecation');

        if ($value === null) {
            return null;
        }

        if (preg_match('/^@(\d+)$/', trim($value), $matches)) {
            return (new DateTimeImmutable)->setTimestamp((int) $matches[1]);
        }

        return self::date($value);
    }

    public function isDeprecated(): bool
    {
        return $this->header('Deprecation') !== null;
    }

    /** Date from which the version answers 410 (Sunset header, RFC 8594) */
    public function sunsetAt(): ?DateTimeImmutable
    {
        $value = $this->header('Sunset');

        return $value === null ? null : self::date($value);
    }

    /** Changelog of the deprecation (Link: <...>; rel="deprecation") */
    public function deprecationLink(): ?string
    {
        $link = $this->header('Link');

        if ($link !== null && preg_match('/<([^>]+)>\s*;\s*rel="?deprecation"?/i', $link, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /** @return array<string, mixed> the decoded envelope */
    public function toArray(): array
    {
        return $this->envelope;
    }

    /**
     * @return $this
     *
     * @throws ApiException when success is false
     */
    public function throw(): static
    {
        if ($this->failed() || $this->status >= 400) {
            throw ApiException::fromResponse($this);
        }

        return $this;
    }

    /*
     * 1.x compatibility (Nycorp\LiteApi\Response\DefResponse)
     */

    public function isSuccess(): bool
    {
        return $this->successful();
    }

    public function getCode(): int
    {
        return $this->code();
    }

    public function getMessage(): string
    {
        return $this->message();
    }

    public function getBody(): mixed
    {
        return $this->body();
    }

    /** @deprecated use body() */
    public function getData(): mixed
    {
        return $this->body();
    }

    public function isCodeSame(int $code): bool
    {
        return $this->code() === abs($code);
    }

    private static function date(string $value): ?DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }
    }
}
