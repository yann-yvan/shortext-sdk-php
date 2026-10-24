<?php

namespace NyCorp\Shortext\Sdk;

use NyCorp\Shortext\Exceptions\TransportException;
use NyCorp\Shortext\Http\HttpClient;
use NyCorp\Shortext\Http\Response;
use NyCorp\Shortext\ResponseCode;
use NyCorp\Shortext\Shortext;

/**
 * @deprecated since 2.0, use NyCorp\Shortext\Shortext
 */
class ShortextSdk
{
    protected HttpClient $http;

    protected string $apiUrl;

    protected string $token;

    /**
     * @param  array{timeout?: float, connect_timeout?: float, headers?: array<string, string>, handler?: callable}  $options
     */
    public function __construct(string $token, ?string $apiUrl = null, array $options = [])
    {
        $this->apiUrl = $apiUrl ?? Shortext::DEFAULT_BASE_URL;
        $this->token = $token;
        $this->http = new HttpClient($this->token, $this->apiUrl, $options);
    }

    /**
     * Never throws, as in 1.x: a failure is a Response with isSuccess() = false
     */
    protected function request(string $method, string $endpoint, array $data = []): Response
    {
        try {
            return $this->http->request(strtoupper($method), $endpoint, json: $data);
        } catch (TransportException $e) {
            return new Response($e->status() ?? 0, [], [
                'success' => false,
                'code' => ResponseCode::REQUEST_EXCEPTION,
                'message' => $e->getMessage(),
                'body' => ['error' => true, 'message' => $e->getMessage()],
            ]);
        }
    }
}
