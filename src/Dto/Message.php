<?php

namespace AvocetShores\Conduit\Dto;

use AvocetShores\Conduit\Enums\Role;

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
