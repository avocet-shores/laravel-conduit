<?php

namespace AvocetShores\Conduit;

use AvocetShores\Conduit\Contexts\AIRequestContext;
use AvocetShores\Conduit\Drivers\DriverInterface;
use AvocetShores\Conduit\Dto\ConversationResponse;
use AvocetShores\Conduit\Enums\ResponseFormat;
use AvocetShores\Conduit\Enums\Role;
use AvocetShores\Conduit\Exceptions\AiModelNotSetException;
use AvocetShores\Conduit\Features\StructuredOutputs\Schema;
use AvocetShores\Conduit\Middleware\MiddlewareInterface;
use Illuminate\Support\Facades\Pipeline;
use phpDocumentor\Reflection\Types\ClassString;

class ConduitService
{
    protected DriverInterface $driver;

    /**
     * @var array<ClassString|callable>
     */
    protected array $middlewares = [];

    /**
     * The context for the current run.
     */
    protected AIRequestContext $context;

    public function __construct(DriverInterface $driver, ?AIRequestContext $context = null)
    {
        $this->driver = $driver;
        $this->context = $context ?? AIRequestContext::create();
    }

    /**
     * @throws AiModelNotSetException
     */
    public function run(?AIRequestContext $context = null): ConversationResponse
    {
        $this->assertModelIsSet();

        // TODO : Does this lead to confusing outcomes if the context is accidentally overridden here?
        $this->context = $context ?? $this->context;

        return Pipeline::send($this->context)
            ->through($this->middlewares)
            ->then(function ($context) {
                return $this->driver->run($context);
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
        $this->context->setInstructions($instructions);

        return $this;
    }

    public function addMessage(string $message, Role $role): self
    {
        $this->context->addMessage($message, $role);

        return $this;
    }

    public function usingModel(string $model): self
    {
        $this->context->setModel($model);

        return $this;
    }

    public function enableJsonOutput(): self
    {
        $this->context->setResponseFormat(ResponseFormat::JSON);

        return $this;
    }

    /**
     * Enable structured output mode.
     * This is only available for certain drivers. Make sure the driver you are using supports structured outputs, otherwise
     * this will be ignored. The response will still be in JSON format if possible.
     */
    public function enableStructuredOutput(Schema $schema): self
    {
        $this->context->setResponseFormat(ResponseFormat::STRUCTURED_SCHEMA);
        $this->context->setSchema($schema);

        return $this;
    }

    /**
     * @throws AiModelNotSetException
     */
    protected function assertModelIsSet(): void
    {
        if (! $this->context->hasModel()) {
            throw new AiModelNotSetException('AI model must be set before running Conduit.', $this->context->getRunId());
        }
    }
}
