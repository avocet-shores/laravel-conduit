<?php

namespace AvocetShores\Conduit\Exceptions;

use AvocetShores\Conduit\Contexts\AIRequestContext;
use AvocetShores\Conduit\Exceptions\LaravelAIException;

class AIProviderRateLimitExceededException extends LaravelAIException
{
    /**
     * Create a new exception instance.
     *
     * @param string $message
     * @param AIRequestContext | string $context
     */
    public function __construct(string $message, $context)
    {
        parent::__construct($message, $context, 429);
    }
}
