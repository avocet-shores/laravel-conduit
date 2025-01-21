<?php

namespace AvocetShores\Conduit\Contexts;

use AvocetShores\Conduit\Dto\Message;
use AvocetShores\Conduit\Enums\ResponseFormat;
use AvocetShores\Conduit\Enums\Role;
use AvocetShores\Conduit\Features\StructuredOutputs\Schema;

class AIRequestContext
{
    /**
     * Unique identifier for a given request to the Conduit service.
     *
     * @var string
     */
    private string $laravelAiRunId;

    /**
     * @var array<string, mixed>
     */
    public array $metadata = [];

    /**
     * The optional instructions that will be passed to the model using the appropriate role.
     */
    protected ?string $instructions = null;

    /**
     * The AI model to use.
     */
    protected ?string $model = null;

    /**
     * The messages to be sent to the AI model.
     *
     * @var array<Message>
     */
    protected array $messages = [];

    /**
     * Whether the response should be in JSON mode.
     */
    protected ?ResponseFormat $responseFormat;

    protected bool $structuredMode = false;

    /**
     * The schema definition if using structured outputs.
     */
    protected ?Schema $schema;

    /**
     * Driver-specific data.
     *
     * @var array<string, mixed>
     */
    protected array $driverData = [];

    public static function create(?string $runId = null): self
    {
        $instance = new self();

        $instance->setRunId($runId ?? (string) \Str::uuid());

        return $instance;
    }

    public function setInstructions(?string $instructions): self
    {
        $this->instructions = $instructions;
        return $this;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function addMessage(string $content, Role $role): self
    {
        $this->messages[] = new Message($role, $content);
        return $this;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    // Driver-specific data
    public function setDriverData(string $driverKey, $value): self
    {
        $this->driverData[$driverKey] = $value;
        return $this;
    }

    public function getDriverData(string $driverKey)
    {
        return $this->driverData[$driverKey] ?? null;
    }

    public function setMetadata(string $key, $value): self
    {
        $this->metadata[$key] = $value;
        return $this;
    }

    public function getMetadata(string $key)
    {
        return $this->metadata[$key] ?? null;
    }

    public function getRunId(): string
    {
        return $this->laravelAiRunId;
    }

    public function setRunId(string $runId): void
    {
        $this->laravelAiRunId = $runId;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function hasModel(): bool
    {
        return $this->model !== null;
    }

    public function setSchema(Schema $schema): self
    {
        $this->schema = $schema;
        return $this;
    }

    public function getSchema(): ?Schema
    {
        return $this->schema;
    }

    public function setResponseFormat(ResponseFormat $responseFormat): self
    {
        $this->responseFormat = $responseFormat;

        return $this;
    }

    public function getResponseFormat(): ?ResponseFormat
    {
        return $this->responseFormat;
    }
}
