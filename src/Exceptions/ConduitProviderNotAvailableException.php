<?php

namespace AvocetShores\Conduit\Exceptions;

use AvocetShores\Conduit\Contexts\AIRequestContext;

class AIProviderNotAvailableException extends LaravelAIException
{
    public function __construct(string $driver, AIRequestContext $context)
    {
        parent::__construct("The AI provider '$driver' is not available.", $context, 503);
    }
}
