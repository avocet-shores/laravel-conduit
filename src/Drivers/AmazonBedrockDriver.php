<?php

namespace AvocetShores\Conduit\Drivers;

use AvocetShores\Conduit\Contexts\AIRequestContext;
use AvocetShores\Conduit\Dto\BedrockConverseRequest;
use AvocetShores\Conduit\Dto\BedrockConverseResponse;
use AvocetShores\Conduit\Dto\ConversationResponse;
use AvocetShores\Conduit\Exceptions\ConduitException;
use AvocetShores\Conduit\Exceptions\ConduitProviderNotAvailableException;
use AvocetShores\Conduit\Exceptions\ConduitProviderRateLimitExceededException;
use Aws\BedrockRuntime\BedrockRuntimeClient;
use Aws\BedrockRuntime\Exception\BedrockRuntimeException;
use Aws\Credentials\Credentials;

class AmazonBedrockDriver implements DriverInterface
{
    protected BedrockRuntimeClient $client;

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
    }

    /**
     * @throws ConduitException
     * @throws ConduitProviderNotAvailableException
     */
    public function run(AIRequestContext $context): ConversationResponse
    {
        try {
            // Generate the request
            $request = $this->generateRequest($context);

            // Send the request to the Amazon Bedrock API
            $result = $this->client->converse($request->toArray());

            // Create and return the response
            return BedrockConverseResponse::create($result, $context);

        } catch (BedrockRuntimeException $e) {
            // If this is a server error, throw a ProviderNotAvailableException so the request can be retried or rerouted to another provider.
            if ($e->getStatusCode() >= 500) {
                throw new ConduitProviderNotAvailableException(self::class, $context);
            }

            // If this is a rate limit error, throw a RateLimitExceededException so the request can be retried or rerouted to another provider.
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
    }

    protected function generateRequest(AIRequestContext $context): BedrockConverseRequest
    {
        $request = new BedrockConverseRequest;

        if ($context->getInstructions()) {
            $request->system = [
                ['text' => $context->getInstructions()],
            ];
        }

        foreach ($context->getMessages() as $message) {
            $request->messages[] = [
                'role' => $message->role->value,
                'content' => [
                    ['text' => $message->content],
                ],
            ];
        }

        $request->modelId = $context->getModel();

        return $request;
    }
}
