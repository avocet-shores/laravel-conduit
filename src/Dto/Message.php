<?php

namespace JaredCannon\LaravelAI;

use JaredCannon\LaravelAI\Enums\Role;

class Message
{
    public Role $role;

    public string $content;

    public function __construct(Role $role, string $content)
    {
        $this->role = $role;
        $this->content = $content;
    }
}
