<?php

namespace AvocetShores\Conduit;

use AvocetShores\Conduit\Contexts\AIRequestContext;
use AvocetShores\Conduit\Drivers\DriverInterface;
use AvocetShores\Conduit\Dto\ConversationResponse;
use AvocetShores\Conduit\Enums\ResponseFormat;
use AvocetShores\Conduit\Enums\Role;
use AvocetShores\Conduit\Exceptions\AiModelNotSetException;
use AvocetShores\Conduit\Exceptions\ConduitException;
use AvocetShores\Conduit\Exceptions\ConduitProviderNotAvailableException;
use AvocetShores\Conduit\Exceptions\ConduitProviderRateLimitExceededException;
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
     * @throws ConduitException
     */
    public function run(): ConversationResponse
    {
        $this->assertModelIsSet();

        if ($this->context->isFallback()) {
            // If we are in fallback mode, we need to switch the driver and model
            $this->driver = $this->context->getFallbackDriver();
            $this->context->setModel($this->context->getFallbackModel());
        }

        try {
            return Pipeline::send($this->context)
                ->through($this->middlewares)
                ->then(function ($context) {
                    return $this->driver->run($context);
                });
        } catch (ConduitProviderNotAvailableException|ConduitProviderRateLimitExceededException $e) {
            return $this->handleProviderException($e);
        }
    }

    /**
     * @throws ConduitException
     * @throws AiModelNotSetException
     */
    protected function handleProviderException(ConduitException $e): ConversationResponse
    {
        if ($this->context->isFallback()) {
            // If we are in fallback mode and the fallback driver also fails, we need to throw the exception
            throw $e;
        }

        if ($this->context->getFallbackDriver() && $this->context->getFallbackModel()) {
            $this->context->setIsFallback(true);
        }

        return $this->run();
    }

    /**
     * Set the fallback driver and model to use if the primary driver fails.
     */
    public function withFallback(string $driverName, string $model): self
    {
        $this->context->setFallbackDriver(ConduitFactory::validateAndResolveDriver($driverName));
        $this->context->setFallbackModel($model);

        return $this;
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
