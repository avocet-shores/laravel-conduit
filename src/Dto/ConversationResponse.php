<?php

namespace AvocetShores\Conduit\Dto;

use Illuminate\Contracts\Support\Arrayable;

class ConversationResponse implements Arrayable
{
    public function __construct(
        public ?string $output = null,
        public ?string $modelUsed = null,
        public ?Usage $usage = null,
        public array $outputArray = [],
    ) {}

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
        if (! str_contains($output, '{')) {
            $output = '{'.$output;
        }
        if (! str_contains($output, '}')) {
            $output = $output.'}';
        }

        return $output;
    }
}
