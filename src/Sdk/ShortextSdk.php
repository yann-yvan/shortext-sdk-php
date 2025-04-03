<?php

namespace Nycorp\Shortext\Sdk;

use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Nycorp\LiteApi\Models\ResponseCode;
use Nycorp\LiteApi\Response\DefResponse;
use Nycorp\LiteApi\Traits\ApiResponseTrait;

class ShortextSdk
{
    use ApiResponseTrait;

    protected PendingRequest $http;

    protected string $apiUrl;

    protected string $token;

    public function __construct(string $token, ?string $apiUrl = null)
    {
        $this->apiUrl = $apiUrl ?? 'https://shortext.ny-corp.io/api';
        $this->token = $token;
        $this->http = Http::baseUrl($this->apiUrl)->withToken($this->token);
    }

    protected function request($method, $endpoint, $data = []): DefResponse
    {
        try {
            Log::debug($endpoint, $data);
            $response = $this->http->{$method}($endpoint, $data);

            return DefResponse::parse(new JsonResponse($response->json()));
        } catch (Exception $e) {
            return new DefResponse(self::liteResponse(ResponseCode::REQUEST_EXCEPTION, [
                'error' => true,
                'message' => $e->getMessage(),
            ]));
        }
    }
}
