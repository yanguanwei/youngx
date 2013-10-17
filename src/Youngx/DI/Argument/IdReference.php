<?php

namespace Youngx\DI\Argument;

class IdReference
{
    private $id;
    
    public function __construct($id)
    {
        $this->id = $id;
    }
    
    public function __toString()
    {
        return "\$this->get('{$this->id}')";
    }
}
?>