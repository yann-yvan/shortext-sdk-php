<?php

namespace NyCorp\Shortext\Exceptions;

/**
 * The request is invalid (code 1002). errors() lists the messages per field.
 */
class ValidationException extends ApiException
{
    /** @return array<string, list<string>> messages per field */
    public function errors(): array
    {
        $body = $this->body();

        return is_array($body) ? $body : [];
    }
}
