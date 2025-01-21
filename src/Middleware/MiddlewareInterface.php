<?php

namespace AvocetShores\Conduit\Middleware;

use AvocetShores\Conduit\Contexts\AIRequestContext;

interface MiddlewareInterface
{
    public function handle(AIRequestContext $context, callable $next): AIRequestContext;
}
