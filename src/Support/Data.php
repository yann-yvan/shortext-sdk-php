<?php

namespace NyCorp\Shortext\Support;

/**
 * @internal
 */
final class Data
{
    /** Dot-notation lookup in nested arrays */
    public static function get(mixed $target, string $key, mixed $default = null): mixed
    {
        if ($key === '') {
            return $target;
        }

        foreach (explode('.', $key) as $segment) {
            if (! is_array($target) || ! array_key_exists($segment, $target)) {
                return $default;
            }

            $target = $target[$segment];
        }

        return $target;
    }
}
