<?php

namespace AvocetShores\Conduit\Features\StructuredOutputs;

use Illuminate\Contracts\Support\Arrayable;

class Input implements Arrayable
{
    /**
     * The type of the input
     *
     * @var Type
     */
    public Type $type;

    /**
     * The name of the input
     *
     * @var ?string
     */
    public ?string $name;

    /**
     * The enum type of the input if the type is Enum
     *
     * @var ?Type
     */
    public ?Type $enumType;

    /**
     * Right now all inputs must be required for structured outputs to work. This may change in the future.
     *
     * @var bool Whether the input is required or not
     */
    public bool $required = true;

    /**
     * The description of the input
     *
     * @var ?string
     */
    public ?string $description;

    /**
     *
     * @var array The enum values for the input if the type is Enum
     */
    public array $enum = [];

    /**
     * @var array<Input> The properties of the input if the type is Object
     */
    public array $properties = [];

    /**
     * @var array<Input> The items of the input if the type is Array
     */
    public array $items = [];

    /**
     * @var array The types of the input if the type is AnyOf
     */
    public array $anyOf = [];

    public function __construct(
        Type $type,
        ?string $name = null,
        ?string $description = null,
        array $enum = [],
        Type $enumType = null,
        array $properties = [],
        array $items = [],
        array $anyOf = [],
    ) {
        $this->type = $type;
        $this->name = $name;
        $this->description = $description;
        $this->enumType = $enumType;
        $this->enum = $enum;
        $this->properties = $properties;
        $this->items = $items;
        $this->anyOf = $anyOf;
    }

    public static function create(
        Type $type,
        ?string $name = null,
        ?string $description = null,
        array $enum = [],
        Type $enumType = null,
        array $properties = [],
        array $items = [],
        array $anyOf = [],
    ): static {
        return new static($type, $name, $description, $enum, $enumType, $properties, $items, $anyOf);
    }

    public static function string(?string $name = null, ?string $description = null): static
    {
        return new static(Type::String, $name, $description);
    }

    public static function number(?string $name = null, ?string $description = null): static
    {
        return new static(Type::Number, $name, $description);
    }

    public static function boolean(?string $name = null, ?string $description = null): static
    {
        return new static(Type::Boolean, $name, $description);
    }

    public static function integer(?string $name = null, ?string $description = null): static
    {
        return new static(Type::Integer, $name, $description);
    }

    public static function object(?string $name = null, ?string $description = null, array $properties = []): static
    {
        return new static(Type::Object, $name, $description, [], null, $properties);
    }

    public static function array(?string $name = null, ?string $description = null, array $items = []): static
    {
        return new static(Type::Array, $name, $description, [], null, [], $items);
    }

    public static function enum(string $name, Type $enumType, ?string $description = null, array $enum = []): static
    {
        return new static(Type::Enum, $name, $description, $enum, $enumType);
    }

    public static function anyOf(?string $name = null, ?string $description = null, array $anyOf = []): static
    {
        return new static(Type::AnyOf, $name, $description, [], null, [], [], $anyOf);
    }

    public function toArray(): array
    {
        $input = [];

        if ($this->name) {
            $input['name'] = $this->name;
        }

        if ($this->description) {
            $input['description'] = $this->description;
        }

        switch ($this->type) {
            case Type::String:
            case Type::Number:
            case Type::Boolean:
            case Type::Integer:
                $input['type'] = $this->type->value;
                break;
            case Type::Enum:
                $input['type'] = $this->enumType->value;
                $input['enum'] = $this->enum;
                break;
            case Type::Object:
                $input['type'] = $this->type->value;
                $input['properties'] = collect($this->properties)->mapWithKeys(function (Input $input) {
                    $name = $input->name;
                    return [$input->name => collect($input->toArray())->filter(function ($value, $key) use ($name) {
                        return $key !== $name;
                    })->toArray()];
                })->toArray();
                $input['additionalProperties'] = false;
                $input['required'] = collect($this->properties)->filter(function (Input $input) {
                    return $input->required;
                })->map(function (Input $input) {
                    return $input->name;
                })->toArray();
                break;
            case Type::Array:
                $input['type'] = $this->type->value;
                $input['items'] = $this->formatArrayItems($this->items);
                break;
            case Type::AnyOf:
                $input['type'] = $this->type->value;
                $input['anyOf'] = $this->anyOf;
                break;
        }

        return $input;
    }

    protected function formatArrayItems(array $items): array
    {
        $formattedItems = [];

        if (count($items) === 1 && $items[0]->name === null) {
            // This is an array definition with only a type, so just return the type as a json object instead of an array
            return $items[0]->toArray();
        }

        foreach ($items as $item) {
            if ($item->name) {
                $formattedItems[$item->name] = $item->toArray();
                unset($formattedItems[$item->name]['name']);
                continue;
            }

            $formattedItems[] = $item->toArray();
        }

        return $formattedItems;
    }
}
