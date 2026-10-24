<?php

namespace NyCorp\Shortext\Exceptions;

use NyCorp\Shortext\Http\Response;
use NyCorp\Shortext\ResponseCode;
use RuntimeException;

/**
 * The API answered with success = false. getCode() is the envelope code (see ResponseCode).
 */
class ApiException extends RuntimeException implements ShortextException
{
    final public function __construct(private readonly Response $response)
    {
        $message = $response->message() !== '' ? $response->message() : 'Shortext API error '.$response->code();

        parent::__construct($message, $response->code());
    }

    public static function fromResponse(Response $response): self
    {
        $code = $response->code();

        if ($code === ResponseCode::VERSION_SUNSET || $response->status() === 410) {
            return new ApiVersionSunsetException($response);
        }

        return match (true) {
            $code === ResponseCode::REQUEST_VALIDATION_ERROR => new ValidationException($response),
            $code === ResponseCode::REQUEST_NOT_FOUND => new NotFoundException($response),
            $code === ResponseCode::REQUEST_TRYING_TO_INSERT_DUPLICATE => new ConflictException($response),
            $code === ResponseCode::REQUEST_NOT_AUTHORIZED => new NotAuthorizedException($response),
            in_array($code, ResponseCode::AUTHENTICATION, true) => new AuthenticationException($response),
            default => new static($response),
        };
    }

    public function response(): Response
    {
        return $this->response;
    }

    public function body(): mixed
    {
        return $this->response->body();
    }

    public function traceId(): ?string
    {
        return $this->response->traceId();
    }
}
