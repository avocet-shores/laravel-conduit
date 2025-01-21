<?php

namespace AvocetShores\Conduit\Drivers;

use AvocetShores\Conduit\Dto\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use AvocetShores\Conduit\Contexts\AIRequestContext;
use AvocetShores\Conduit\Dto\OpenAiCompletionsResponse;
use AvocetShores\Conduit\Dto\OpenAIRequest;
use AvocetShores\Conduit\Enums\Role;
use AvocetShores\Conduit\Exceptions\ConduitProviderNotAvailableException;
use AvocetShores\Conduit\Exceptions\ConduitException;
use AvocetShores\Conduit\Features\StructuredOutputs\Schema;
use AvocetShores\Conduit\Drivers\DriverInterface;

class OpenAIDriver implements DriverInterface
{
    /**
     * The messages to be sent to the AI model.
     *
     * @var array<Message>
     */
    protected array $messages = [];

    /**
     * The AI model name for the request.
     *
     * @var string|null
     */
    protected ?string $model;

    /**
     * Optional instruction message to be added to the beginning of the conversation with the given model's instructions role.
     * i.e. If the model is o1, the instructions will be added with the role 'developer'. But if the model is gpt-4o,
     * the instructions will be added with the role 'system'. These settings are maintained in the provider-config.json file.
     * By default, the 'system' role is used.
     *
     * @var Message|null
     */
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

    /**
     * The OpenAI request object.
     *
     * @var OpenAIRequest|null
     */
    protected ?OpenAIRequest $request;

    /**
     * @throws ConduitException
     */
    public function run(AIRequestContext $context): OpenAiCompletionsResponse
    {
        $this->generateRequest();

        try {
            $response = Http::withToken(config('conduit.openai.key'))
                ->timeout(config('conduit.openai.openai_curl_timeout', 180))
                ->post(config('conduit.openai.completions_endpoint'), $this->request->toArray());

            // If the response is a 5xx error, throw a ProviderNotAvailableException
            if ($response->serverError()) {
                throw new ConduitProviderNotAvailableException(self::class, $context);
            }

            if ($response->status() === 401) {
                throw new ConduitException('OpenAI API key is invalid.', $context);
            }

            return OpenAiCompletionsResponse::create($response, $context, $this->jsonMode);
        } catch (ConduitException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ConduitException($e->getMessage(), $context);
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

    /**
     * @throws ConduitException
     */
    public function withInstructions(string $instructions): DriverInterface
    {
        if (!isset($this->model))
            throw new ConduitException('Model must be set before adding instructions.');

        $this->instructions = new Message($this->resolveInstructionsRole($this->model), $instructions);

        return $this;
    }

    protected function resolveInstructionsRole(string $model): Role
    {
        // Get provider-config json from package's resources
        $providerConfig = json_decode(file_get_contents(__DIR__ . '/../../resources/config/provider-config.json'), true);

        // Check to see if the given model has a record in the provider-config
        // If not, set to the default (User)
        if (!isset($providerConfig[$model]) || !isset($providerConfig[$model]['instructions_role']))
            return Role::SYSTEM;

        // Get the instructions role from the provider-config
        return Role::fromString($providerConfig[$model]['instructions_role']);
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
