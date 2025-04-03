<?php

namespace NyCorp\Shortext\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \NyCorp\Shortext\Shortext
 */
class Shortext extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \NyCorp\Shortext\Shortext::class;
    }
}
