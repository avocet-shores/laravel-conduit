<?php

namespace AvocetShores\Conduit\Drivers;

use AvocetShores\Conduit\Contexts\AIRequestContext;
use AvocetShores\Conduit\Dto\BedrockConverseRequest;
use AvocetShores\Conduit\Dto\BedrockConverseResponse;
use AvocetShores\Conduit\Dto\ConversationResponse;
use AvocetShores\Conduit\Exceptions\ConduitException;
use AvocetShores\Conduit\Exceptions\ConduitProviderNotAvailableException;
use AvocetShores\Conduit\Exceptions\ConduitProviderRateLimitExceededException;
use AvocetShores\Conduit\Features\StructuredOutputs\Schema;
use Aws\BedrockRuntime\BedrockRuntimeClient;
use Aws\BedrockRuntime\Exception\BedrockRuntimeException;
use Aws\Credentials\Credentials;

class AmazonBedrockDriver implements DriverInterface
{
    protected BedrockRuntimeClient $client;

    protected BedrockConverseRequest $request;

    protected bool $jsonMode = false;

    public function __construct()
    {
        $credentials = new Credentials(
            config('conduit.amazon_bedrock.key'),
            config('conduit.amazon_bedrock.secret'),
        );

        $this->client = new BedrockRuntimeClient([
            'credentials' => $credentials,
            'region' => config('conduit.amazon_bedrock.region'),
            'version' => 'latest',
        ]);

        $this->request = new BedrockConverseRequest;
    }

    /**
     * @throws ConduitException
     * @throws ConduitProviderNotAvailableException
     */
    public function run(AIRequestContext $context): ConversationResponse
    {
        try {
            $result = $this->client->converse($this->request->toArray());
        } catch (BedrockRuntimeException $e) {
            // If this is a server error, throw a ProviderNotAvailableException so the request can be retried or rerouted to another provider.
            if ($e->getStatusCode() >= 500) {
                throw new ConduitProviderNotAvailableException(self::class, $context);
            }

            if ($e->getStatusCode() === 429) {
                throw new ConduitProviderRateLimitExceededException('Amazon Bedrock API rate limit exceeded.', $context);
            }

            if ($e->getStatusCode() === 401) {
                throw new ConduitException('Amazon Bedrock API access denied.', $context, $e->getCode());
            }

            throw new ConduitException($e->getMessage(), $context);
        } catch (\Exception $e) {
            throw new ConduitException($e->getMessage(), $context);
        }

        return BedrockConverseResponse::create($result, $context, $this->jsonMode, $this->request->modelId);
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
     * @throws ConduitException
     */
    public function withStructuredSchema(Schema $schema): static
    {
        throw new ConduitException('Structured schema is not supported by Amazon Bedrock.', 0);
    }
}
