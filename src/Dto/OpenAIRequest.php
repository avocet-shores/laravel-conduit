<?php

namespace JaredCannon\LaravelAI\Dto;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JaredCannon\LaravelAI\Features\StructuredOutputs\Schema;

class OpenAIRequest implements Jsonable, Arrayable
{
    public string $model;

    public array $messages;

    public array $responseFormat = [];

    public function toArray(): array
    {
        $array = [
            'model' => $this->model,
            'messages' => $this->messages,
        ];

        if (!empty($this->responseFormat)) {
            $array['response_format'] = $this->responseFormat;
        }

        return $array;
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}