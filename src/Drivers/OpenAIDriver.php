<?php

namespace JaredCannon\LaravelAI\Drivers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JaredCannon\LaravelAI\Contexts\AIRequestContext;
use JaredCannon\LaravelAI\Dto\OpenAiCompletionsResponse;
use JaredCannon\LaravelAI\Dto\OpenAIRequest;
use JaredCannon\LaravelAI\Enums\Role;
use JaredCannon\LaravelAI\Exceptions\AIProviderNotAvailableException;
use JaredCannon\LaravelAI\Exceptions\LaravelAIException;
use JaredCannon\LaravelAI\Features\StructuredOutputs\Schema;
use JaredCannon\LaravelAI\Message;

class OpenAIDriver implements DriverInterface
{
    protected array $messages = [];

    protected string $model;

    protected ?Message $instructions;

    /**
     * Whether the response should be in JSON.
     *
     * @var bool
     */
    protected bool $jsonMode = false;

    /**
     * Whether the response should be in structured mode.
     *
     * @var bool
     */
    protected bool $structuredMode = false;

    /**
     * The schema for the structured output.
     *
     * @var ?Schema
     */
    protected ?Schema $schema;

    protected OpenAIRequest $request;

    /**
     * @throws LaravelAIException
     */
    public function converse(AIRequestContext $context): OpenAiCompletionsResponse
    {
        $this->generateRequest();

        try {
            $response = Http::withToken(config('laravel-ai.providers.openai.key'))
                ->timeout(config('laravel-ai.providers.openai.openai_curl_timeout', 180))
                ->post(config('laravel-ai.providers.openai.completions_endpoint'), $this->request->toArray());

            // If the response is a 5xx error, throw a ProviderNotAvailableException
            if ($response->serverError()) {
                throw new AIProviderNotAvailableException(self::class, $context);
            }

            if ($response->status() === 401) {
                throw new LaravelAIException('OpenAI API key is invalid.', $context);
            }

            return OpenAiCompletionsResponse::create($response, $context, $this->jsonMode);
        } catch (LaravelAIException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new LaravelAIException($e->getMessage(), $context);
        }
    }

    protected function generateRequest(): void
    {
        $this->request = new OpenAIRequest();

        if ($this->instructions) {
            array_unshift($this->messages, $this->instructions);
        }

        $this->request->model = $this->model;
        $this->request->messages = $this->messages;

        if ($this->jsonMode) {
            $this->request->responseFormat = [
                'type' => 'json_object',
            ];
        }

        if ($this->structuredMode) {
            $this->request->responseFormat = [
                'type' => 'json_schema',
                'schema' => $this->schema,
            ];
        }
    }

    public function withStructuredSchema(Schema $schema): static
    {
        $this->schema = $schema;
        $this->structuredMode = true;

        return $this;
    }

    public function supportsTools(): bool
    {
        return false;
    }

    public function withInstructions(string $instructions): DriverInterface
    {
        if (isset($this->model) && config("laravel-ai.providers.openai.models.$this->model.instructions_role")) {
            $instructionsRole = config("laravel-ai.providers.openai.models.$this->model.instructions_role");
            $this->instructions = new Message($instructionsRole, $instructions);
        } else {
            $this->instructions = new Message(Role::SYSTEM, $instructions);
        }

        return $this;
    }

    public function addMessage(string $message, string $role): DriverInterface
    {
        $role = Role::fromString($role);
        $this->messages[] = new Message($role, $message);

        return $this;
    }

    public function supportsStructuredSchema(): bool
    {
        return true;
    }

    public function usingModel(string $model): DriverInterface
    {
        $this->model = $model;

        return $this;
    }

    public function withJsonMode(): DriverInterface
    {
        $this->jsonMode = true;

        return $this;
    }
}
