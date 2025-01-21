<?php

namespace AvocetShores\Conduit\Exceptions;

use AvocetShores\Conduit\Contexts\AIRequestContext;
use Throwable;

class ConduitException extends \Exception
{
    /**
     * A unique identifier for a given request to the Conduit service.
     *
     * @var string $conduitRunId
     */
    public string $conduitRunId;

    /**
     * LaravelAIException constructor.
     *
     * @param string $message
     * @param AIRequestContext|string $conduitRunId
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message, $conduitRunId = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->conduitRunId = $conduitRunId instanceof AIRequestContext ? $conduitRunId->getRunId() : $conduitRunId;
    }
}
