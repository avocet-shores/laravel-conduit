<?php

namespace AvocetShores\Conduit;

use AvocetShores\Conduit\Contexts\AIRequestContext;
use AvocetShores\Conduit\Dto\ConversationResponse;
use AvocetShores\Conduit\Drivers\DriverInterface;

class Conduit {

    protected DriverInterface $driver;

    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    public function run(?AIRequestContext $context = null): ConversationResponse
    {
        $context = $context ?? AIRequestContext::create();

        return $this->driver->run($context);
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
