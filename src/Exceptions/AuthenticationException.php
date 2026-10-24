<?php

namespace NyCorp\Shortext\Exceptions;

/**
 * The API key is missing, unknown, revoked or expired (codes 1 to 5).
 */
class AuthenticationException extends NotAuthorizedException {}
