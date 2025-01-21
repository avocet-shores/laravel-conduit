<?php

namespace AvocetShores\Conduit\Drivers;

use AvocetShores\Conduit\Contexts\AIRequestContext;
use AvocetShores\Conduit\Dto\ConversationResponse;
use AvocetShores\Conduit\Features\StructuredOutputs\Schema;

interface DriverInterface
{
    /**
     * Run the conversation.
     */
    public function run(AIRequestContext $context): ConversationResponse;

    /**
     * Add instructions to the beginning of the conversation.
     */
    public function withInstructions(string $instructions): self;

    /**
     * Add a message to the conversation.
     */
    public function addMessage(string $message, string $role): self;

    /**
     * Determine if the driver supports structured schema.
     */
    public function supportsStructuredSchema(): bool;

    /**
     * Set the model to use for the conversation.
     */
    public function usingModel(string $model): self;

    /**
     * Set the response to be in JSON mode.
     */
    public function withJsonMode(): self;

    /**
     * Set the response to be in structured mode (if supported).
     */
    public function withStructuredSchema(Schema $schema): static;
}
