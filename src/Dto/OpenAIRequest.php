<?php

namespace AvocetShores\Conduit\Dto;

use AvocetShores\Conduit\Enums\ReasoningEffort;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class OpenAIRequest implements Arrayable, Jsonable
{
    public string $model;

    public array $messages;

    public array $responseFormat = [];

    public ?float $temperature = null;

    public ?ReasoningEffort $reasoningEffort = null;

    public function toArray(): array
    {
        $array = [
            'model' => $this->model,
            'messages' => $this->messages,
        ];

        if (! empty($this->responseFormat)) {
            $array['response_format'] = $this->responseFormat;
        }

        if (! is_null($this->temperature)) {
            $array['temperature'] = $this->temperature;
        }

        if (! is_null($this->reasoningEffort)) {
            $array['reasoning_effort'] = $this->reasoningEffort->value;
        }

        return $array;
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
