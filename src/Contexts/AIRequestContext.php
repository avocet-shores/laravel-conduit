<?php

namespace AvocetShores\Conduit\Contexts;

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
     * The messages to be sent to the AI model.
     *
     * @var array<array{role: string, content: string}>
     */
    protected array $messages = [];

    /**
     * Whether the response should be in JSON.
     *
     * @var bool
     */
    protected bool $jsonMode = false;

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

    public function addMessage(string $role, string $content): self
    {
        $this->messages[] = compact('content', 'role');
        return $this;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function enableJsonMode(bool $enable = true): self
    {
        $this->jsonMode = $enable;
        return $this;
    }

    public function isJsonMode(): bool
    {
        return $this->jsonMode;
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
}
