<?php

namespace AvocetShores\Conduit\Drivers;

use AvocetShores\Conduit\Contexts\AIRequestContext;
use AvocetShores\Conduit\Dto\Message;
use AvocetShores\Conduit\Dto\OpenAiCompletionsResponse;
use AvocetShores\Conduit\Dto\OpenAIRequest;
use AvocetShores\Conduit\Enums\ResponseFormat;
use AvocetShores\Conduit\Enums\Role;
use AvocetShores\Conduit\Exceptions\ConduitException;
use AvocetShores\Conduit\Exceptions\ConduitProviderNotAvailableException;
use AvocetShores\Conduit\Features\StructuredOutputs\Schema;
use Illuminate\Support\Facades\Http;

class OpenAIDriver implements DriverInterface
{
    /**
     * The OpenAI request object.
     */
    protected ?OpenAIRequest $request;

    /**
     * @throws ConduitException
     * @throws ConduitProviderNotAvailableException
     */
    public function run(AIRequestContext $context): OpenAiCompletionsResponse
    {
        $this->generateRequest($context);

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

            return OpenAiCompletionsResponse::create($response, $context);
        } catch (ConduitException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ConduitException($e->getMessage(), $context);
        }
    }

    /**
     * @throws ConduitException
     */
    protected function generateRequest(AIRequestContext $context): void
    {
        $this->request = new OpenAIRequest;

        $messages = [];
        if ($context->getInstructions()) {
            $messages[] = $this->resolveInstructions($context);
        }

        $messages = array_merge($messages, $context->getMessages());

        $this->request->model = $context->getModel();
        $this->request->messages = $messages;

        match ($context->getResponseFormat()) {
            ResponseFormat::JSON => $this->request->responseFormat = [
                'type' => 'json_object',
            ],
            ResponseFormat::STRUCTURED_SCHEMA => $this->request->responseFormat = [
                'type' => 'json_schema',
                'schema' => $context->getSchema(),
            ],
            default => null,
        };
    }

    /**
     * @throws ConduitException
     */
    protected function resolveInstructions(AIRequestContext $context): Message
    {
        if (! $context->hasModel()) {
            throw new ConduitException('AI model must be set.');
        }

        return new Message(
            role: $this->resolveInstructionsRole(
                model: $context->getModel()
            ),
            content: $context->getInstructions()
        );
    }

    protected function resolveInstructionsRole(string $model): Role
    {
        // Get provider-config json from package's resources
        $providerConfig = json_decode(file_get_contents(__DIR__.'/../../resources/config/provider-config.json'), true);

        // Check to see if the given model has a record in the provider-config
        // If not, set to the default (System)
        if (! isset($providerConfig[$model]) || ! isset($providerConfig[$model]['instructions_role'])) {
            return Role::SYSTEM;
        }

        // Get the instructions role from the provider-config
        return Role::fromString($providerConfig[$model]['instructions_role']);
    }
}
