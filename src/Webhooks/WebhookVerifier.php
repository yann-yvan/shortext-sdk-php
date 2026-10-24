<?php

namespace NyCorp\Shortext\Webhooks;

use NyCorp\Shortext\Exceptions\InvalidSignatureException;
use NyCorp\Shortext\Webhooks\Events\WebhookEvent;

/**
 * Verifies the webhooks sent by Shortext (webhook endpoints and Shortext endpoint replies):
 *
 *   X-Shortext-Signature: sha256=hex(hmac_sha256(secret, "{X-Shortext-Timestamp}.{raw body}"))
 *
 * Always verify the raw body, before any JSON decoding.
 */
class WebhookVerifier
{
    public const SIGNATURE_HEADER = 'X-Shortext-Signature';

    public const TIMESTAMP_HEADER = 'X-Shortext-Timestamp';

    public const EVENT_HEADER = 'X-Shortext-Event';

    public const DELIVERY_HEADER = 'X-Shortext-Delivery';

    public const API_VERSION_HEADER = 'X-Shortext-Api-Version';

    public const DEFAULT_TOLERANCE = 300;

    /** @var \Closure(): int */
    private \Closure $clock;

    /**
     * @param  int  $tolerance  seconds a timestamp may differ from now (replay protection)
     * @param  (callable(): int)|null  $clock  current unix time, for tests
     */
    public function __construct(
        private readonly string $secret,
        private readonly int $tolerance = self::DEFAULT_TOLERANCE,
        ?callable $clock = null,
    ) {
        $this->clock = $clock !== null ? \Closure::fromCallable($clock) : static fn (): int => time();
    }

    /** Signature header value of a body: useful to test your own endpoint */
    public static function sign(string $secret, string|int $timestamp, string $payload): string
    {
        return 'sha256='.hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
    }

    public function isValid(string $payload, ?string $signature, string|int|null $timestamp): bool
    {
        try {
            $this->verify($payload, $signature, $timestamp);

            return true;
        } catch (InvalidSignatureException) {
            return false;
        }
    }

    /**
     * @throws InvalidSignatureException
     */
    public function verify(string $payload, ?string $signature, string|int|null $timestamp): void
    {
        if ($this->secret === '') {
            throw new InvalidSignatureException('No webhook secret configured');
        }

        if ($signature === null || $signature === '' || $timestamp === null || $timestamp === '') {
            throw new InvalidSignatureException('Missing '.self::SIGNATURE_HEADER.' or '.self::TIMESTAMP_HEADER.' header');
        }

        $timestamp = (string) $timestamp;
        if (! ctype_digit($timestamp)) {
            throw new InvalidSignatureException('Invalid '.self::TIMESTAMP_HEADER.' header');
        }

        if ($this->tolerance > 0 && abs(($this->clock)() - (int) $timestamp) > $this->tolerance) {
            throw new InvalidSignatureException('Webhook timestamp outside the tolerance window');
        }

        if (! hash_equals(self::sign($this->secret, $timestamp, $payload), trim($signature))) {
            throw new InvalidSignatureException('Invalid webhook signature');
        }
    }

    /**
     * Verifies then decodes a webhook into its typed event
     *
     * @param  array<string, string|list<string>>  $headers  request headers, any case (e.g. $request->headers->all(), getallheaders())
     *
     * @throws InvalidSignatureException
     */
    public function event(string $payload, array $headers): WebhookEvent
    {
        $this->verify($payload, self::header($headers, self::SIGNATURE_HEADER), self::header($headers, self::TIMESTAMP_HEADER));

        return WebhookEvent::fromPayload($payload);
    }

    /**
     * @param  array<string, string|list<string>>  $headers
     */
    public static function header(array $headers, string $name): ?string
    {
        foreach ($headers as $key => $value) {
            // $_SERVER style (HTTP_X_SHORTEXT_SIGNATURE) or header names in any case
            $normalized = strtolower(str_replace('_', '-', (string) preg_replace('/^HTTP_/i', '', (string) $key)));

            if ($normalized === strtolower($name)) {
                $value = is_array($value) ? reset($value) : $value;

                return $value === false ? null : (string) $value;
            }
        }

        return null;
    }
}
