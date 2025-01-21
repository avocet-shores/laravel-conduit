<?php

namespace AvocetShores\Conduit;

use AvocetShores\Conduit\Contexts\AIRequestContext;
use AvocetShores\Conduit\Drivers\DriverInterface;
use AvocetShores\Conduit\Dto\ConversationResponse;
use AvocetShores\Conduit\Middleware\MiddlewareInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Pipeline;
use phpDocumentor\Reflection\Types\ClassString;

class ConduitService
{
    protected DriverInterface $driver;

    /**
     * @var array<ClassString|callable>
     */
    protected array $middlewares = [];

    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    public function run(?AIRequestContext $context = null): ConversationResponse
    {
        $context = $context ?? AIRequestContext::create();

        return Pipeline::send($context)
            ->through($this->prepareMiddlewareStack())
            ->then(function ($context) {
                return $this->driver->run($context);
            });
    }

    protected function prepareMiddlewareStack(): Collection
    {
        return collect($this->middlewares)->map(function ($middleware) {
            // If it's a string, assume it's a class and resolve via the app container
            // If it's a callable, we can pass it along as is
            if (is_string($middleware) && class_exists($middleware)) {
                return function ($context, $next) use ($middleware) {
                    // Resolve from container
                    $callable = app($middleware);

                    // Ensure the middleware implements the MiddlewareInterface
                    if (! ($callable instanceof MiddlewareInterface)) {
                        throw new \InvalidArgumentException('Middleware must implement the MiddlewareInterface.');
                    }

                    return $callable->handle($context, $next);
                };
            }

            // If it’s already a callable, we just adapt it:
            if (is_callable($middleware)) {
                return function ($context, $next) use ($middleware) {

                    return $middleware($context, $next);
                };
            }

            // Fallback: throw exception or handle error
            throw new \InvalidArgumentException('Middleware must be a callable, or a class string that implements the MiddlewareInterface.');
        });
    }

    /**
     * Add a middleware to the stack.
     * This can be:
     *  - A string referencing a class that implements MiddlewareInterface.
     *  - A callable (e.g., closure).
     */
    public function pushMiddleware($middleware): self
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    public function withInstructions(string $instructions): self
    {
        $this->driver->withInstructions($instructions);

        return $this;
    }

    public function addMessage(string $message, string $role): self
    {
        $this->driver->addMessage($message, $role);

        return $this;
    }

    public function usingModel(string $model): self
    {
        $this->driver->usingModel($model);

        return $this;
    }

    public function withJsonMode(): self
    {
        $this->driver->withJsonMode();

        return $this;
    }
}
