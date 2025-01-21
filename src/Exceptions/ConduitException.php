<?php

namespace AvocetShores\Conduit\Exceptions;

use AvocetShores\Conduit\Contexts\AIRequestContext;
use Throwable;

class LaravelAIException extends \Exception
{
    /**
     * A unique identifier for a given request to the Laravel AI service.
     *
     * @var string $laravelAiRunId
     */
    public string $laravelAiRunId;

    /**
     * LaravelAIException constructor.
     *
     * @param string $message
     * @param AIRequestContext|string $laravelAiRunId
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message, $laravelAiRunId, int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->laravelAiRunId = $laravelAiRunId instanceof AIRequestContext ? $laravelAiRunId->getRunId() : $laravelAiRunId;
    }
}
