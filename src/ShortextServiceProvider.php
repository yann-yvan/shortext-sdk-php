<?php

namespace NyCorp\Shortext;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use NyCorp\Shortext\Commands\ShortextCommand;

class ShortextServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('shortext-php')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_shortext_php_table')
            ->hasCommand(ShortextCommand::class);
    }
}
