<?php

namespace JaredCannon\LaravelAI\Dto;

use Exception;
use Illuminate\Http\Client\Response;
use JaredCannon\LaravelAI\Contexts\AIRequestContext;
use JaredCannon\LaravelAI\Dto\ConversationResponse;
use JaredCannon\LaravelAI\Exceptions\LaravelAIException;

class OpenAiCompletionsResponse extends ConversationResponse
{

    /**
     * @throws Exception
     */
    public static function create(Response $openAIResponse, AIRequestContext $context, bool $isJson): self
    {
        $response = new self();

        $decodedResponse = json_decode($openAIResponse->body(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new LaravelAIException('Failed to decode JSON response from OpenAI', $context->getRunId());
        }

        $response->usage = new Usage(
            $decodedResponse['usage']['prompt_tokens'],
            $decodedResponse['usage']['completion_tokens'],
            $decodedResponse['usage']['total_tokens']
        );

        $response->modelUsed = $decodedResponse['model'];

        if ($decodedResponse['choices'] === null || $decodedResponse['choices'][0] === null) {
            throw new LaravelAIException('Failed to get message from response from OpenAI', $context->getRunId());
        }

        $response->output = $decodedResponse['choices'][0]['message']['content'] ?? '';

        if ($isJson) {
            $trimmedOutput = $response->trimOutput($response->output);

            $response->outputArray = json_decode($trimmedOutput, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new LaravelAIException('Failed to decode JSON output from OpenAI.', $context->getRunId());
            }
        }

        return $response;
    }
}