<?php

namespace AvocetShores\Conduit\Drivers;

use AvocetShores\Conduit\Contexts\AIRequestContext;
use AvocetShores\Conduit\Dto\ConversationResponse;
use AvocetShores\Conduit\Exceptions\ConduitException;
use AvocetShores\Conduit\Exceptions\ConduitProviderNotAvailableException;
use AvocetShores\Conduit\Features\StructuredOutputs\Schema;

interface DriverInterface
{
    /**
     * Run the conversation.
     *
     * @throws ConduitException
     * @throws ConduitProviderNotAvailableException
     */
    public function run(AIRequestContext $context): ConversationResponse;
}
