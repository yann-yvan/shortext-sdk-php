<?php

namespace NyCorp\Shortext;

use GuzzleHttp\ClientInterface;
use NyCorp\Shortext\Api\Agents;
use NyCorp\Shortext\Api\ApiResource;
use NyCorp\Shortext\Api\Channels;
use NyCorp\Shortext\Api\Conversations;
use NyCorp\Shortext\Api\HostedSessions;
use NyCorp\Shortext\Api\Inbound;
use NyCorp\Shortext\Api\ShortextEndpoints;
use NyCorp\Shortext\Api\Templates;
use NyCorp\Shortext\Api\V1;
use NyCorp\Shortext\Api\Webhooks;
use NyCorp\Shortext\Http\HttpClient;

/**
 * Client of the Shortext API v2.0, authenticated with a company API key.
 *
 *   $shortext = new Shortext('your-api-key');
 *   $shortext->conversations()->reply($id, Message::text('Hello'));
 */
class Shortext
{
    public const VERSION = '2.0.0';

    public const DEFAULT_BASE_URL = 'https://shortext.ny-corp.io/api';

    /** Version targeted by this SDK */
    public const API_VERSION = 'v2.0';

    protected HttpClient $http;

    /** @var array<class-string<ApiResource>, ApiResource> */
    private array $resources = [];

    /**
     * @param  string|null  $baseUrl  defaults to https://shortext.ny-corp.io/api
     * @param  array{timeout?: float, connect_timeout?: float, headers?: array<string, string>, handler?: callable}  $options  Guzzle options
     * @param  ClientInterface|null  $guzzle  your own configured Guzzle client
     */
    public function __construct(string $apiKey, ?string $baseUrl = null, array $options = [], ?ClientInterface $guzzle = null)
    {
        $this->http = new HttpClient($apiKey, $baseUrl, $options, $guzzle);
    }

    /** The API key, its company and its abilities (GET /me) */
    public function me(): Resource
    {
        $body = $this->http->request('GET', ApiResource::V2.'/me')->throw()->body();

        return Resource::from(is_array($body) ? $body : []);
    }

    public function agents(): Agents
    {
        return $this->resource(Agents::class);
    }

    public function channels(): Channels
    {
        return $this->resource(Channels::class);
    }

    public function shortextEndpoints(): ShortextEndpoints
    {
        return $this->resource(ShortextEndpoints::class);
    }

    public function conversations(): Conversations
    {
        return $this->resource(Conversations::class);
    }

    public function templates(): Templates
    {
        return $this->resource(Templates::class);
    }

    public function webhooks(): Webhooks
    {
        return $this->resource(Webhooks::class);
    }

    public function hostedSessions(): HostedSessions
    {
        return $this->resource(HostedSessions::class);
    }

    /** Messages of your users posted to a Shortext endpoint */
    public function inbound(): Inbound
    {
        return $this->resource(Inbound::class);
    }

    /** API v1.0 routes: POST /messages/send, dynamic links */
    public function v1(): V1
    {
        return $this->resource(V1::class);
    }

    /** Raw access, for routes the SDK does not cover yet */
    public function http(): HttpClient
    {
        return $this->http;
    }

    /**
     * @template T of ApiResource
     *
     * @param  class-string<T>  $class
     * @return T
     */
    private function resource(string $class): ApiResource
    {
        /** @var T */
        return $this->resources[$class] ??= new $class($this->http);
    }
}
