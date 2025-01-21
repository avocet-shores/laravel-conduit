<?php

namespace AvocetShores\Conduit\Features\StructuredOutputs;

enum Type: string
{
    case String = 'string';
    case Number = 'number';
    case Boolean = 'boolean';
    case Integer = 'integer';
    case Object = 'object';
    case Array = 'array';
    case Enum = 'enum';
    case AnyOf = 'anyOf';
}
