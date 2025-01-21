<?php

namespace AvocetShores\Conduit\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \AvocetShores\Conduit\Conduit
 */
class Conduit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \AvocetShores\Conduit\Conduit::class;
    }
}
