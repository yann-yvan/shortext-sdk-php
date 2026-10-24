<?php

namespace NyCorp\Shortext\Exceptions;

use DateTimeImmutable;

/**
 * The API version is no longer served (HTTP 410, code 1200): upgrade the SDK.
 */
class ApiVersionSunsetException extends ApiException
{
    public function sunsetAt(): ?DateTimeImmutable
    {
        $date = $this->response()->get('sunset_at');

        return is_string($date) ? new DateTimeImmutable($date) : $this->response()->sunsetAt();
    }

    /** Latest version of the API */
    public function latestVersion(): ?string
    {
        $latest = $this->response()->get('latest');

        return is_string($latest) ? $latest : null;
    }
}
