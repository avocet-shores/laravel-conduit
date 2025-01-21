<?php

namespace AvocetShores\Conduit\Dto;

use Aws\Result;
use Illuminate\Contracts\Support\Arrayable;

class ConversationResponse implements Arrayable
{
    /**
     * The number of input and output tokens used in this request.
     */
    public Usage $usage;

    /**
     * The string output from the model.
     */
    public string $output;

    /**
     * The array of outputs if the output was JSON.
     *
     * @var array $outputArray
     */
    public array $outputArray = [];

    /**
     * The model used to generate this response.
     */
    public string $modelUsed;

    public function toArray(): array
    {
        return [
            'usage' => $this->usage->toArray(),
            'output' => $this->output,
            'outputArray' => $this->outputArray,
            'modelUsed' => $this->modelUsed,
        ];
    }

    public function trimOutput(string $output): string
    {
        $output = trim($output, '`');
        $output = trim($output, 'json');
        if (!str_contains($output, '{')) {
            $output = '{' . $output;
        }
        if (!str_contains($output, '}')) {
            $output = $output . '}';
        }

        return $output;
    }
}
