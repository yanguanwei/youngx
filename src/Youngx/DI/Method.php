<?php

namespace Youngx\DI;

class Method extends FunctionAbstract
{
    private $method;
    private $reflection;

    public function __construct($class, $method, array $arguments = array())
    {
        $this->reflection = new \ReflectionMethod($class, $method);
        $this->method = $method;
        $this->setArguments($arguments);
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getReflectionFunction()
    {
        return $this->reflection;
    }
}