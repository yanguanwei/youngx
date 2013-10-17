<?php

namespace Youngx\DI;


class Container
{
    protected $parameters = array();
    protected $services = array();
    protected $loading = array();
    protected $resolving = array();
    protected $taggedIdClasses = array();
    protected $typeClasses = array();
    protected $subscribers = array();
    protected $subjects = array();

    public function __construct()
    {
    }

    /**
     * @param $callback
     * @param array $arguments
     * @param bool $throw
     * @return array
     */
    public function arguments($callback, array $arguments = array(), $throw = false)
    {
        if (is_array($callback)) {
            if (count($callback) == 2) {
                list($instance, $method) = $callback;
                $rm = new \ReflectionMethod(is_string($instance) ? $instance : get_class($instance), $method);
            } else {
                $instance = $callback[0];
                $rc = new \ReflectionClass($instance);
                $rm = $rc->getConstructor();
            }
            return isset($rm) ? $this->resolveArguments($rm, $arguments, $throw) : $arguments;
        } else {
            return $this->resolveArguments(new \ReflectionFunction($callback), $arguments, $throw);
        }
    }

    public function instantiate($class, array $arguments = array())
    {
        if (isset($this->resolving[$class])) {
            throw new \LogicException(sprintf(
                    'Circular reference detected for resolving class "%s", path: "%s".',
                    $class, implode(' -> ', array_keys($this->resolving))
                )
            );
        }

        $this->resolving[$class] = true;

        $r = new \ReflectionClass($class);
        $constructor = $r->getConstructor();
        if ($constructor && $constructor->getNumberOfParameters() > 0) {
            $arguments = $this->resolveArguments($constructor, $arguments);
            $instance = $r->newInstanceArgs($arguments);
        } else {
            $instance = $r->newInstanceArgs();
        }

        unset($this->resolving[$class]);

        return $instance;
    }

    protected function resolveArguments(\ReflectionFunctionAbstract $method, array $arguments = array(), $throw = true)
    {
        $resolved = array();
        foreach ($method->getParameters() as $i => $parameter) {
            $name = $parameter->getName();
            if (array_key_exists($name, $arguments)) {
                $resolved[$i] = $arguments[$name];
            } else if (array_key_exists($i, $arguments)) {
                $resolved[$i] = $arguments[$i];
            } else {
                if (null != ($class = $parameter->getClass())) {
                    if (isset($this->typeClasses[$class->getName()])) {
                        $resolved[$i] = $this->get($this->typeClasses[$class->getName()]);
                        continue;
                    }
                }

                if ($parameter->isDefaultValueAvailable()) {
                    $resolved[$i] = $parameter->getDefaultValue();
                    continue;
                }

                if ($throw) {
                    $methodName = $method->getName();
                    if (isset($method->class)) {
                        $methodName = $method->class . '::' . $methodName;
                    }
                    throw new \InvalidArgumentException(sprintf('%s() Argument#%s cannot be resolved.', $methodName, $i));
                } else {
                    $resolved[$i] = null;
                }
            }
        }
        return $resolved;
    }

    /**
     * @param $id
     * @param null $singleton
     * @return object
     */
    public function get($id, $singleton = null)
    {
        $key = $singleton ? "{$id}-{$singleton}" : $id;
        if (!isset($this->services[$key])) {
            $this->services[$key] = $this->instance($id, $singleton ? array($singleton) : array());
        }
        return $this->services[$key];
    }

    /**
     * @param $id
     * @param array $arguments
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @return object
     */
    public function instance($id, array $arguments = array())
    {
        if (isset($this->loading[$id])) {
            throw new \LogicException(sprintf(
                    'Circular reference detected for service "%s", path: "%s".',
                    $id, implode(' -> ', array_keys($this->loading))
                )
            );
        }

        $method = 'get' . implode(array_map('ucfirst', explode('.', strtr($id, array('_' => '.', '-' => '.')))));
        if (!method_exists($this, $method)) {
            throw new \InvalidArgumentException(sprintf('There is no Service registered by id[%s]', $id));
        }

        $this->loading[$id] = true;
        $instance = call_user_func_array(array($this, $method), $arguments);
        unset($this->loading[$id]);

        if (isset($this->subscribers[$id])) {
            foreach ($this->subscribers[$id] as $subject => $method) {
                if (isset($this->services[$subject])) {
                    $instance->$method($this->services[$subject]);
                }
            }
        }

        return $instance;
    }

    public function register($id, $instance, $typeClasses = array())
    {
        if (!is_object($instance)) {
            throw new \InvalidArgumentException(sprintf(
                    'Container::register(id[%s]) Arguments#2 must be object, %s given.', $id, gettype($instance))
            );
        }

        if ($typeClasses === true) {
            $typeClasses = array(get_class($instance));
        } elseif (is_string($typeClasses)) {
            $typeClasses = array($typeClasses);
        }

        foreach ($typeClasses as $class) {
            $this->typeClasses[$class] = $id;
        }

        if (isset($this->subjects[$id])) {
            foreach ($this->subjects[$id] as $subscriber => $method) {
                if (isset($this->services[$subscriber])) {
                    $this->services[$subscriber]->$method($instance);
                }
            }
        }

        $this->services[$id] = $instance;
    }

    public function getParameter($key, $default = null)
    {
        return isset($this->parameters[$key]) ? $this->parameters[$key] : $default;
    }

    /**
     * @param $tag
     * @return array
     */
    public function taggedIdClasses($tag)
    {
        return isset($this->taggedIdClasses[$tag]) ? $this->taggedIdClasses[$tag] : array();
    }
}