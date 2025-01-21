<?php

namespace AvocetShores\Conduit\Dto;

use AvocetShores\Conduit\Contexts\AIRequestContext;
use AvocetShores\Conduit\Enums\ResponseFormat;
use AvocetShores\Conduit\Exceptions\ConduitException;
use Exception;
use Illuminate\Http\Client\Response;

class OpenAiCompletionsResponse extends ConversationResponse
{
    /**
     * @throws Exception
     */
    public static function create(Response $openAIResponse, AIRequestContext $context): self
    {
        $response = new self;

        $decodedResponse = json_decode($openAIResponse->body(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ConduitException('Failed to decode JSON response from OpenAI', $context->getRunId());
        }

        // First check to make sure the array keys exist
        if (
            ! array_key_exists('usage', $decodedResponse)
            || ! array_key_exists('prompt_tokens', $decodedResponse['usage'])
            || ! array_key_exists('completion_tokens', $decodedResponse['usage'])
            || ! array_key_exists('total_tokens', $decodedResponse['usage'])
        ) {
            throw new ConduitException('Failed to get usage from response from OpenAI', $context->getRunId());
        }

        $response->usage = new Usage(
            $decodedResponse['usage']['prompt_tokens'],
            $decodedResponse['usage']['completion_tokens'],
            $decodedResponse['usage']['total_tokens']
        );

        if (
            ! array_key_exists('model', $decodedResponse)
            || ! array_key_exists('choices', $decodedResponse)
        ) {
            throw new ConduitException('Failed to get model or choices from response from OpenAI', $context->getRunId());
        }

        $response->modelUsed = $decodedResponse['model'];

        if ($decodedResponse['choices'] === null || $decodedResponse['choices'][0] === null) {
            throw new ConduitException('Failed to get message from response from OpenAI', $context->getRunId());
        }

        $response->output = $decodedResponse['choices'][0]['message']['content'] ?? '';

        if ($context->getResponseFormat() === ResponseFormat::JSON) {
            $trimmedOutput = $response->trimOutput($response->output);

            $response->outputArray = json_decode($trimmedOutput, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ConduitException('Failed to decode JSON output from OpenAI.', $context->getRunId());
            }
        }

        return $response;
    }
}
