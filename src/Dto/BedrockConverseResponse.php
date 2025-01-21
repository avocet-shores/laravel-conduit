<?php

namespace JaredCannon\LaravelAI\Dto;

use Aws\Result;
use JaredCannon\LaravelAI\Contexts\AIRequestContext;
use JaredCannon\LaravelAI\Dto\ConversationResponse;
use JaredCannon\LaravelAI\Exceptions\LaravelAIException;

class BedrockConverseResponse extends ConversationResponse
{
    /**
     * @throws LaravelAIException
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
                throw new LaravelAIException('Failed to decode JSON output from Bedrock.', $context->getRunId());
            }

            $response->outputArray = $decodedOutput;
        }

        return $response;
    }
}
