<?php

namespace AvocetShores\Conduit\Exceptions;

use AvocetShores\Conduit\Contexts\AIRequestContext;
use Throwable;

class ConduitException extends \Exception
{
    /**
     * A unique identifier for a given request to the Conduit service.
     */
    public string $conduitRunId;

    /**
     * LaravelAIException constructor.
     *
     * @param  AIRequestContext|string  $conduitRunId
     */
    public function __construct(string $message, $conduitRunId = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->conduitRunId = $conduitRunId instanceof AIRequestContext ? $conduitRunId->getRunId() : $conduitRunId;
    }
}
