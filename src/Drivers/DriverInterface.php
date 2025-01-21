<?php

namespace JaredCannon\LaravelAI\Drivers;

use JaredCannon\LaravelAI\Contexts\AIRequestContext;
use JaredCannon\LaravelAI\Dto\ConversationResponse;
use JaredCannon\LaravelAI\Features\StructuredOutputs\Schema;

interface DriverInterface
{
    public function converse(AIRequestContext $context): ConversationResponse;

    /**
     * Determine if the driver supports tools.
     */
    public function supportsTools(): bool;

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

    public function usingModel(string $model): self;

    public function withJsonMode(): self;

    public function withStructuredSchema(Schema $schema): static;
}
