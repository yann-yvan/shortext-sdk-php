<?php

namespace NyCorp\Shortext\Exceptions;

use RuntimeException;

/**
 * A webhook does not come from Shortext: missing or wrong signature, or timestamp outside the tolerance.
 */
class InvalidSignatureException extends RuntimeException implements ShortextException {}
