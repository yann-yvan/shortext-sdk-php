<?php

namespace NyCorp\Shortext\Facades;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use NyCorp\Shortext\Exceptions\InvalidSignatureException;
use NyCorp\Shortext\Webhooks\Events\WebhookEvent;
use NyCorp\Shortext\Webhooks\WebhookVerifier;

/**
 * @method static \NyCorp\Shortext\Resource me()
 * @method static \NyCorp\Shortext\Api\Agents agents()
 * @method static \NyCorp\Shortext\Api\Channels channels()
 * @method static \NyCorp\Shortext\Api\ShortextEndpoints shortextEndpoints()
 * @method static \NyCorp\Shortext\Api\Conversations conversations()
 * @method static \NyCorp\Shortext\Api\Templates templates()
 * @method static \NyCorp\Shortext\Api\Webhooks webhooks()
 * @method static \NyCorp\Shortext\Api\HostedSessions hostedSessions()
 * @method static \NyCorp\Shortext\Api\Inbound inbound()
 * @method static \NyCorp\Shortext\Api\V1 v1()
 * @method static \NyCorp\Shortext\Http\HttpClient http()
 *
 * @see \NyCorp\Shortext\Shortext
 */
class Shortext extends Facade
{
    /**
     * Verifies and decodes the webhook of a Laravel request with config('shortext.webhooks.secret')
     *
     * @throws InvalidSignatureException
     */
    public static function webhookEvent(Request $request): WebhookEvent
    {
        return static::$app->make(WebhookVerifier::class)->event($request->getContent(), $request->headers->all());
    }

    protected static function getFacadeAccessor(): string
    {
        return \NyCorp\Shortext\Shortext::class;
    }
}
