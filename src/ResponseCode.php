<?php

namespace NyCorp\Shortext;

/**
 * Codes of the Shortext envelope (`code`). The API sends their absolute value.
 */
final class ResponseCode
{
    public const TOKEN_EXPIRED = 1;

    public const TOKEN_BLACK_LISTED = 2;

    public const TOKEN_INVALID = 3;

    public const TOKEN_NOT_FOUND = 4;

    public const TOKEN_USER_NOT_FOUND = 5;

    public const REQUEST_SUCCESS = 1000;

    public const REQUEST_FAILURE = 1001;

    public const REQUEST_VALIDATION_ERROR = 1002;

    public const REQUEST_EXPIRED = 1003;

    public const REQUEST_TRYING_TO_INSERT_DUPLICATE = 1004;

    public const REQUEST_NOT_AUTHORIZED = 1005;

    public const REQUEST_EXCEPTION = 1006;

    public const REQUEST_NOT_FOUND = 1007;

    public const REQUEST_WRONG_JSON_FORMAT = 1008;

    public const REQUEST_SERVICE_NOT_AVAILABLE = 1009;

    public const REQUEST_EMERGENCY = 1010;

    public const AUTH_WRONG_CREDENTIALS = 1103;

    public const VERSION_SUNSET = 1200;

    /** Codes meaning the API key is missing, unknown or expired */
    public const AUTHENTICATION = [
        self::TOKEN_EXPIRED,
        self::TOKEN_BLACK_LISTED,
        self::TOKEN_INVALID,
        self::TOKEN_NOT_FOUND,
        self::TOKEN_USER_NOT_FOUND,
        self::AUTH_WRONG_CREDENTIALS,
    ];
}
