<?php

namespace AvocetShores\Conduit;

use AvocetShores\Conduit\Contexts\AIRequestContext;
use AvocetShores\Conduit\Drivers\DriverInterface;
use InvalidArgumentException;

class ConduitFactory
{
    public static function make(string $driver = 'default', ?string $model = null): ConduitService
    {
        $driver = self::resolveDriver($driver);

        if ($model) {
            $context = AIRequestContext::create();
            $context->setModel($model);
        }

        return new ConduitService($driver, $context ?? null);
    }

    public static function openai(?string $model = null): ConduitService
    {
        return self::make('openai', $model);
    }

    public static function bedrock(?string $model = null): ConduitService
    {
        return self::make('amazon_bedrock', $model);
    }

    /**
     * @throws InvalidArgumentException
     */
    protected static function validateDriver(string $driver): void
    {
        if ($driver === 'default') {
            $driver = config('conduit.default_driver');
        }

        $driverClass = config("conduit.drivers.$driver");

        // Check if the driver exists
        if (! $driver || ! class_exists($driverClass)) {
            throw new InvalidArgumentException("Driver $driver does not exist.");
        }

        if (! in_array(DriverInterface::class, class_implements($driverClass))) {
            throw new InvalidArgumentException("Driver $driver must implement DriverInterface.");
        }
    }

    protected static function resolveDriver(string $driver): DriverInterface
    {
        self::validateDriver($driver);

        if ($driver === 'default') {
            $driver = config('conduit.default_driver');
        }

        $driverClass = config("conduit.drivers.$driver");

        return app($driverClass);
    }
}
