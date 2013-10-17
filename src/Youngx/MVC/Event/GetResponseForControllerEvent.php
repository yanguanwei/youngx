<?php

namespace Youngx\MVC\Event;

use Youngx\MVC\Bundle;

class GetResponseForControllerEvent extends GetResponseEvent
{
    protected $controller;

    public function __construct($controller)
    {
        $this->setController($controller);
    }

    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    public function getController()
    {
        return $this->controller;
    }
}