<?php

namespace Youngx\DI;

class Definition extends FunctionAbstract
{
    protected $tags = array();
    protected $class;
    protected $typeClasses = array();
    protected $methods = array();
    protected $properties = array();
    protected $subjects = array();

    public function __construct($class, $typeClasses = null)
    {
        $this->setClass($class);

        if (null !== $typeClasses) {
            if (true === $typeClasses) {
                $this->addTypeClass($class);
            } else {
                $this->addTypeClass($typeClasses);
            }
        }
    }

    public function addTypeClass($classes)
    {
        $classes = (array) $classes;
        foreach ($classes as $class) {
            $this->typeClasses[$class] = true;
        }
        return $this;
    }

    public function getTypeClasses()
    {
        return array_keys($this->typeClasses);
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @param string $method
     * @param array $arguments array
     * @internal param  $argument
     * @return Method
     */
    public function call($method, array $arguments = array())
    {
        return $this->methods[] = new Method($this->class, $method, $arguments);
    }

    public function set($property, $value)
    {
        $this->properties[$property] = $value;

        return $this;
    }

    /**
     * @return Method[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param $tag
     * @return $this
     */
    public function tag($tag)
    {
        $this->tags = array_unique(array_merge($this->tags, func_get_args()));

        return $this;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function hasTagged($tag)
    {
        return in_array($tag, $this->tags);
    }

    public function getReflectionFunction()
    {
        $reflection = new \ReflectionClass($this->class);
        return $reflection->getConstructor();
    }

    public function getSubjects()
    {
        return $this->subjects;
    }

    public function subscribe($id, $method = null)
    {
        $this->subjects[$id] = $method ?: ('set'.(strpos($id, '_') === false ? ucfirst($id) : implode(array_map('ucfirst', explode('_', $id)))));

        return $this;
    }
}