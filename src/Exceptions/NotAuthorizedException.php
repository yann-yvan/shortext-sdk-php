<?php

namespace NyCorp\Shortext\Exceptions;

/**
 * The API key lacks the permission of the route (code 1005), e.g. send_messages or manage_integrations.
 */
class NotAuthorizedException extends ApiException {}
