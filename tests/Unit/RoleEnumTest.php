<?php

use AvocetShores\Conduit\Enums\Role;

it('it can create from a string', function () {
    $roleStrings = [
        'user' => Role::USER,
        'developer' => Role::DEVELOPER,
        'assistant' => Role::ASSISTANT,
        'system' => Role::SYSTEM,
        'unknown' => Role::USER,
    ];

    foreach ($roleStrings as $roleString => $role) {
        expect(Role::fromString($roleString))->toBe($role);
    }
});
