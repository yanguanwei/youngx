<?php

namespace Youngx\MVC\Event;

class GetResponseForControllerResultEvent extends GetResponseEvent
{
    protected $controllerResult;
    
    public function __construct($controllerResult)
    {
        $this->controllerResult = $controllerResult;    
    }
    
    public function getControllerResult()
    {
        return $this->controllerResult;
    }
}
