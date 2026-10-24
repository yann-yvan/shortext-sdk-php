<?php

namespace NyCorp\Shortext\Exceptions;

use RuntimeException;

/**
 * The API could not be reached, or did not answer a Shortext envelope (network error, timeout, invalid JSON...).
 */
class TransportException extends RuntimeException implements ShortextException
{
    public function __construct(string $message, private readonly ?int $status = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    /** HTTP status when a response was received */
    public function status(): ?int
    {
        return $this->status;
    }
}
