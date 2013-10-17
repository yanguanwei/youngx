<?php

namespace Youngx\DI\Argument;

class RequireInput
{
    protected $key;
    protected $default;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function __toString()
    {
        return "\${$this->key}";
    }
}