<?php

namespace AvocetShores\Conduit\Dto;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class BedrockConverseRequest implements Arrayable, Jsonable
{
    /**
     * Format is [[ 'text' => '']]
     */
    public array $system = [];

    public array $messages = [];

    public string $modelId;

    public function toArray(): array
    {
        return [
            'modelId' => $this->modelId,
            'system' => $this->system ?? [],
            'messages' => $this->messages,
        ];
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
