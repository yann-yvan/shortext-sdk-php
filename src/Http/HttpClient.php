<?php

namespace NyCorp\Shortext\Http;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use NyCorp\Shortext\Exceptions\TransportException;
use NyCorp\Shortext\Shortext;

/**
 * Sends the requests (company API key as Bearer token) and decodes the Shortext envelope.
 *
 * It never throws on an envelope with success = false: callers decide (Response::throw()).
 */
class HttpClient
{
    protected ClientInterface $guzzle;

    protected string $baseUrl;

    /**
     * @param  array{timeout?: float, connect_timeout?: float, headers?: array<string, string>, handler?: callable}  $options
     */
    public function __construct(
        protected string $apiKey,
        ?string $baseUrl = null,
        array $options = [],
        ?ClientInterface $guzzle = null,
    ) {
        $this->baseUrl = rtrim($baseUrl ?? Shortext::DEFAULT_BASE_URL, '/');

        $this->guzzle = $guzzle ?? new Client(array_filter([
            'handler' => $options['handler'] ?? null,
            RequestOptions::TIMEOUT => $options['timeout'] ?? 30,
            RequestOptions::CONNECT_TIMEOUT => $options['connect_timeout'] ?? 10,
            RequestOptions::HEADERS => $options['headers'] ?? [],
        ], static fn ($value) => $value !== null));
    }

    /**
     * @param  string  $path  relative to the base URL, e.g. "/v2.0/agents"
     * @param  array<string, mixed>  $query  null values are left out
     * @param  array<string, mixed>|null  $json  request body
     *
     * @throws TransportException
     */
    public function request(string $method, string $path, array $query = [], ?array $json = null): Response
    {
        $options = [
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::ALLOW_REDIRECTS => false,
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer '.$this->apiKey,
                'Accept' => 'application/json',
                'User-Agent' => 'shortext-php/'.Shortext::VERSION.' PHP/'.PHP_VERSION,
            ],
        ];

        $query = array_filter($query, static fn ($value) => $value !== null);
        if ($query !== []) {
            $options[RequestOptions::QUERY] = array_map(
                static fn ($value) => is_bool($value) ? (int) $value : $value,
                $query
            );
        }

        if ($json !== null) {
            // An empty object, not an empty list
            $options[RequestOptions::BODY] = json_encode($json === [] ? new \stdClass : $json, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $options[RequestOptions::HEADERS]['Content-Type'] = 'application/json';
        }

        try {
            $response = $this->guzzle->request($method, $this->baseUrl.'/'.ltrim($path, '/'), $options);
        } catch (GuzzleException $e) {
            throw new TransportException('Shortext API unreachable: '.$e->getMessage(), null, $e);
        }

        $status = $response->getStatusCode();
        $raw = (string) $response->getBody();

        try {
            $envelope = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new TransportException("Shortext API answered HTTP $status without a JSON envelope", $status, $e);
        }

        if (! is_array($envelope) || ! array_key_exists('success', $envelope)) {
            throw new TransportException("Shortext API answered HTTP $status without a Shortext envelope", $status);
        }

        $headers = [];
        foreach ($response->getHeaders() as $name => $values) {
            $headers[strtolower($name)] = implode(', ', $values);
        }

        return new Response($status, $headers, $envelope);
    }

    public function baseUrl(): string
    {
        return $this->baseUrl;
    }
}
