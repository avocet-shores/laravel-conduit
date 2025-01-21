<?php

namespace AvocetShores\Conduit\Dto;

use Aws\Result;
use AvocetShores\Conduit\Contexts\AIRequestContext;
use AvocetShores\Conduit\Dto\ConversationResponse;
use AvocetShores\Conduit\Exceptions\ConduitException;

class BedrockConverseResponse extends ConversationResponse
{
    /**
     * @throws ConduitException
     */
    public static function create(
        Result           $result,
        AIRequestContext $context,
        bool             $isJson,
        string           $model
    ): self
    {
        $response = new self();

        $response->usage = new Usage(
            $result['usage']['inputTokens'],
            $result['usage']['outputTokens'],
            $result['usage']['totalTokens']
        );

        $response->modelUsed = $model;

        $response->output = $result['output']['message']['content'][0]['text'] ?? '';

        if ($isJson) {
            $trimmedOutput = $response->trimOutput($response->output);

            $decodedOutput = json_decode($trimmedOutput, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ConduitException('Failed to decode JSON output from Bedrock.', $context->getRunId());
            }

            $response->outputArray = $decodedOutput;
        }

        return $response;
    }
}
