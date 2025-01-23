<?php

use AvocetShores\Conduit\Features\StructuredOutputs\Input;
use AvocetShores\Conduit\Features\StructuredOutputs\Type;

it('returns a schema in the expected array format', function () {

    $name = 'Test Schema';
    $description = 'A test schema';

    $schema = new \AvocetShores\Conduit\Features\StructuredOutputs\Schema(
        name: $name,
        description: $description,
        properties: [
            Input::string('input1', 'A test input'),
            Input::number('input2', 'Another test input'),
            Input::enum('input3', Type::String, 'An enum input', ['value1', 'value2']),
            Input::object('input4', 'An object input', [
                Input::boolean('input5', 'A boolean input'),
                Input::integer('input6', 'An integer input'),
            ]),
            Input::array('input7', 'An array input', items: [
                Input::string('input8', 'An array string input'),
                Input::number('input9', 'An array number input'),
            ]),
            Input::array('input10', 'array with just a type', items: [
                Input::string(),
            ]),
        ]
    );

    $expected = [
        'name' => 'Test Schema',
        'description' => 'A test schema',
        'strict' => true,
        'schema' => [
            'type' => 'object',
            'properties' => [
                'input1' => [
                    'description' => 'A test input',
                    'type' => 'string',
                ],
                'input2' => [
                    'description' => 'Another test input',
                    'type' => 'number',
                ],
                'input3' => [
                    'description' => 'An enum input',
                    'type' => 'string',
                    'enum' => ['value1', 'value2'],
                ],
                'input4' => [
                    'description' => 'An object input',
                    'type' => 'object',
                    'properties' => [
                        'input5' => [
                            'name' => 'input5',
                            'description' => 'A boolean input',
                            'type' => 'boolean',
                        ],
                        'input6' => [
                            'name' => 'input6',
                            'description' => 'An integer input',
                            'type' => 'integer',
                        ],
                    ],
                    'additionalProperties' => false,
                    'required' => ['input5', 'input6'],
                ],
                'input7' => [
                    'description' => 'An array input',
                    'type' => 'array',
                    'items' => [
                        'input8' => [
                            'description' => 'An array string input',
                            'type' => 'string',
                        ],
                        'input9' => [
                            'description' => 'An array number input',
                            'type' => 'number',
                        ],
                    ],
                ],
                'input10' => [
                    'description' => 'array with just a type',
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                    ],
                ],
            ],
            'additionalProperties' => false,
            'required' => ['input1', 'input2', 'input3', 'input4', 'input7', 'input10'],
        ],
    ];

    expect($schema->toArray())->toBe($expected);
});
