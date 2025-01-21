<?php

namespace JaredCannon\LaravelAI\Contexts;

class AIRequestContext
{
    private string $laravelAiRunId;

    public function __construct(string $runId = null)
    {
        $this->laravelAiRunId = $runId ?? $this->generateRunId();
    }

    public static function create(string $runId = null): self
    {
        return new self($runId);
    }

    public function getRunId(): string
    {
        return $this->laravelAiRunId;
    }

    public function setRunId(string $runId): void
    {
        $this->laravelAiRunId = $runId;
    }

    private function generateRunId(): string
    {
        return uniqid('run_', true);
    }
}