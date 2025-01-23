<?php

namespace AvocetShores\Conduit\Features\StructuredOutputs;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class Schema implements Arrayable, JsonSerializable
{
    /**
     * This should always be set to Type::Object
     */
    protected Type $type = Type::Object;

    /**
     * The name of the schema
     */
    protected string $name = '';

    /**
     * The description of the schema
     */
    protected string $description = '';

    /**
     * All the schema's inputs/properties
     *
     * @var array<Input>
     */
    protected array $properties;

    public function __construct(string $name, string $description, array $properties)
    {
        $this->name = $name;
        $this->description = $description;

        // Validate that all properties are of type Input
        foreach ($properties as $property) {
            if (! ($property instanceof Input)) {
                throw new \InvalidArgumentException('All properties must be of type Input');
            }
        }

        $this->properties = $properties;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getProperty(string $name): ?Input
    {
        return collect($this->properties)->first(function (Input $input) use ($name) {
            return $input->name === $name;
        });
    }

    public function toArray(): array
    {
        $requiredProperties = collect($this->properties)->filter(function (Input $input) {
            return $input->required;
        })->map(function (Input $input) {
            return $input->name;
        });

        $properties = [];
        foreach ($this->properties as $property) {
            // Move name field to the key of the properties array
            $properties[$property->name] = $property->toArray();
            unset($properties[$property->name]['name']);
        }

        return [
            'name' => $this->name,
            'description' => $this->description,
            'strict' => true,
            'schema' => [
                'type' => $this->type->value,
                'properties' => $properties,
                'additionalProperties' => false,
                'required' => $requiredProperties->toArray(),
            ],
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
