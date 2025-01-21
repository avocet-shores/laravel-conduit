<?php

namespace AvocetShores\Conduit\Facades;

use AvocetShores\Conduit\ConduitFactory;
use Illuminate\Support\Facades\Facade;

/**
 * @see ConduitFactory
 *
 * @method static ConduitFactory make(string $driver = 'default', ?string $model = null)
 * @method static ConduitFactory openai(?string $model = null)
 * @method static ConduitFactory bedrock(?string $model = null)
 */
class Conduit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ConduitFactory::class;
    }
}
