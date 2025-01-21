<?php

namespace AvocetShores\Conduit\Enums;

enum ResponseFormat: string
{
    /**
     * This indicates to Conduit that the response will be in json, and will attempt to parse the response into the outputArray.
     * This will also be passed to the provider if the provider supports it.
     */
    case JSON = 'json';

    /**
     * This indicates to openAI that the response should mimic the Schema passed in the responseFormat field.
     * This is not currently supported by amazon bedrock.
     */
    case STRUCTURED_SCHEMA = 'structured_schema';
}
