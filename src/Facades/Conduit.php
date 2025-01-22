<?php

namespace AvocetShores\Conduit\Facades;

use AvocetShores\Conduit\ConduitFactory;
use AvocetShores\Conduit\ConduitService;
use AvocetShores\Conduit\Drivers\DriverInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @see ConduitFactory
 *
 * @method static ConduitService make(string $driver = 'default', ?string $model = null)
 * @method static ConduitService openai(?string $model = null)
 * @method static ConduitService bedrock(?string $model = null)
 */
class Conduit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ConduitFactory::class;
    }
}
