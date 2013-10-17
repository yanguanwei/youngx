<?php

namespace Youngx\DI;

use Youngx\DI\Argument\IdReference;
use Youngx\DI\Argument\RequireInput;

abstract class FunctionAbstract
{
    private $arguments = array();
    private $requireInputArguments = array();

    public function getArguments()
    {
        return $this->arguments;
    }

    public function getResolvedArguments(array $typeClasses)
    {
        $resolved = array();
        $method = $this->getReflectionFunction();

        if ($method) {
            foreach ($method->getParameters() as $i => $parameter) {
                $name = $parameter->getName();
                if (array_key_exists($name, $this->arguments)) {
                    $resolved[$i] = $this->arguments[$name];
                } else if (array_key_exists($i, $this->arguments)) {
                    $resolved[$i] = $this->arguments[$i];
                } else {
                    if (null != ($class = $parameter->getClass())) {
                        if (isset($typeClasses[$class->getName()])) {
                            $resolved[$i] = new IdReference($typeClasses[$class->getName()]);
                            continue;
                        }
                    }

                    if ($parameter->isDefaultValueAvailable()) {
                        $resolved[$i] = $parameter->getDefaultValue();
                        continue;
                    }

                    $methodName = $method->getName();
                    if (isset($method->class)) {
                        $methodName = (isset($method->class) ? $method->class . '::' : null) . $method->getName();
                    }
                    throw new \InvalidArgumentException(sprintf('%s() Argument#%s cannot be resolved.', $methodName, $i));
                }
            }
        }

        return $resolved;
    }

    public function setArgument($key, $value)
    {
        $this->arguments[$key] = $value;

        return $this;
    }

    public function setArguments(array $arguments)
    {
        $this->arguments = array_replace($this->arguments, $arguments);

        return $this;
    }

    public function clearArguments()
    {
        $this->arguments = array();

        return $this;
    }

    public function requireInput($name, $default = null)
    {
        $this->setArgument($name, new RequireInput($name));
        $this->requireInputArguments[$name] = $default;

        return $this;
    }

    public function getRequireInputArguments()
    {
        return $this->requireInputArguments;
    }

    /**
     * @return \ReflectionFunctionAbstract
     */
    abstract public function getReflectionFunction();
}