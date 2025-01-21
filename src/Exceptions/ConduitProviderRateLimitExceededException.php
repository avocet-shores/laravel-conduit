<?php

namespace AvocetShores\Conduit\Exceptions;

use AvocetShores\Conduit\Contexts\AIRequestContext;

class ConduitProviderRateLimitExceededException extends ConduitException
{
    /**
     * Create a new exception instance.
     *
     * @param  AIRequestContext | string  $context
     */
    public function __construct(string $message, $context)
    {
        parent::__construct($message, $context, 429);
    }
}
