<?php

namespace Youngx\DI\Argument;

class IdInstance
{
    private $id;
    
    public function __construct($id)
    {
        $this->id = $id;
    }
    
    public function __toString()
    {
        return "\$this->instance('{$this->id}')";
    }
}