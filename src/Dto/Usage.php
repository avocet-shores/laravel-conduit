<?php

namespace AvocetShores\Conduit\Dto;

use Illuminate\Contracts\Support\Arrayable;

class Usage implements Arrayable
{
    public int $inputTokens;

    public int $outputTokens;

    public int $totalTokens;

    public function __construct(int $inputTokens, int $outputTokens, int $totalTokens)
    {
        $this->inputTokens = $inputTokens;
        $this->outputTokens = $outputTokens;
        $this->totalTokens = $totalTokens;
    }

    public function toArray(): array
    {
        return [
            'input_tokens' => $this->inputTokens,
            'output_tokens' => $this->outputTokens,
            'total_tokens' => $this->totalTokens,
        ];
    }
}
