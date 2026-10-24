<?php

namespace NyCorp\Shortext\Tests;

use NyCorp\Shortext\ShortextServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            ShortextServiceProvider::class,
        ];
    }
}
