<?php

namespace AvocetShores\Conduit;

use AvocetShores\Conduit\Commands\ConduitCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ConduitServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-conduit')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_conduit_table')
            ->hasCommand(ConduitCommand::class);
    }
}
