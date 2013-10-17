<?php

namespace Youngx\DI\Argument;

class Serialization
{
    private $serialized;

    public function __construct($variable)
    {
        $this->serialized = serialize($variable);
    }

    public function __toString()
    {
        return "unserialize('{$this->serialized}')";
    }
}