<?php

namespace JaredCannon\LaravelAI\Drivers;

use Aws\BedrockAgent\Exception\BedrockAgentException;
use Aws\BedrockRuntime\BedrockRuntimeClient;
use Aws\BedrockRuntime\Exception\BedrockRuntimeException;
use Aws\Credentials\Credentials;
use Illuminate\Support\Facades\Log;
use JaredCannon\LaravelAI\Contexts\AIRequestContext;
use JaredCannon\LaravelAI\Dto\BedrockConverseRequest;
use JaredCannon\LaravelAI\Dto\BedrockConverseResponse;
use JaredCannon\LaravelAI\Dto\ConversationResponse;
use JaredCannon\LaravelAI\Exceptions\AIProviderNotAvailableException;
use JaredCannon\LaravelAI\Exceptions\AIProviderRateLimitExceededException;
use JaredCannon\LaravelAI\Exceptions\LaravelAIException;
use JaredCannon\LaravelAI\Features\StructuredOutputs\Schema;
use Nette\NotImplementedException;

class AmazonBedrockDriver implements DriverInterface
{
    protected BedrockRuntimeClient $client;

    protected BedrockConverseRequest $request;

    protected bool $jsonMode = false;

    public function __construct()
    {
        $credentials = new Credentials(
            config('laravel-ai.providers.amazon_bedrock.key'),
            config('laravel-ai.providers.amazon_bedrock.secret'),
        );

        $this->client = new BedrockRuntimeClient([
            'credentials' => $credentials,
            'region' => config('laravel-ai.providers.amazon_bedrock.region'),
            'version' => 'latest',
        ]);

        $this->request = new BedrockConverseRequest();
    }

    /**
     * @throws LaravelAIException
     * @throws AIProviderNotAvailableException
     */
    public function converse(AIRequestContext $context): ConversationResponse
    {
        try {
            $result = $this->client->converse($this->request->toArray());
        } catch (BedrockRuntimeException $e) {
            // If this is a server error, throw a ProviderNotAvailableException
            if ($e->getStatusCode() >= 500) {
                throw new AIProviderNotAvailableException(self::class, $context);
            }

            if ($e->getStatusCode() === 429) {
                throw new AIProviderRateLimitExceededException('Amazon Bedrock API rate limit exceeded.', $context);
            }

            if ($e->getStatusCode() === 401) {
                throw new LaravelAIException('Amazon Bedrock API access denied.', $context, $e->getCode());
            }

            throw new LaravelAIException($e->getMessage(), $context);
        } catch (\Exception $e) {
            throw new LaravelAIException($e->getMessage(), $context);
        }

        return BedrockConverseResponse::create($result, $context, $this->jsonMode, $this->request->modelId);
    }

    public function supportsTools(): bool
    {
        // TODO: Tools/Functions are supported, but not yet implemented.
        throw new NotImplementedException();
    }

    public function supportsStructuredSchema(): bool
    {
        return false;
    }

    public function withInstructions(string $instructions): self
    {
        $this->request->system = [
            ['text' => $instructions],
        ];

        return $this;
    }

    public function addMessage(string $message, string $role): self
    {
        $this->request->messages[] = [
            'role' => $role,
            'content' => [
                ['text' => $message],
            ],
        ];

        return $this;
    }

    public function usingModel(string $model): self
    {
        $this->request->modelId = $model;

        return $this;
    }

    public function withJsonMode(): self
    {
        $this->jsonMode = true;

        return $this;
    }

    /**
     * @throws LaravelAIException
     */
    public function withStructuredSchema(Schema $schema): static
    {
        throw new LaravelAIException('Structured schema is not supported by Amazon Bedrock.', 0);
    }
}
