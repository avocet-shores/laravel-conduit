<?php

namespace AvocetShores\Conduit\Exceptions;

use AvocetShores\Conduit\Contexts\AIRequestContext;

class ConduitProviderNotAvailableException extends ConduitException
{
    public function __construct(string $driver, AIRequestContext $context)
    {
        parent::__construct("The AI provider '$driver' is not available.", $context, 503);
    }
}
