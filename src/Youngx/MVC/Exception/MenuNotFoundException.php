<?php

namespace Youngx\MVC\Exception;

class MenuNotFoundException extends \Exception
{
    public function __construct($name)
    {
        parent::__construct("Menu[$name] not found.");
    }
}