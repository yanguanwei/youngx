<?php

namespace Youngx\MVC;

class Bundle
{
    protected $name;
    protected $reflected;
    /**
     * @var Context
     */
    protected $context;

    public function __toString()
    {
        return $this->getName();
    }

    public function initialize(Context $context)
    {
        $this->context = $context;
    }
    
    public function getPath()
    {
        return dirname($this->getReflection()->getFileName());
    }

    public function getModulePath($module)
    {
        return $this->getPath() . "/Module/{$module}Module";
    }

    public function getModuleResourcesPath($module)
    {
        return $this->getModulePath($module) . '/Resources';
    }
    
    public function getResourcesPath()
    {
        return $this->getPath() . '/Resources';    
    }

    public function getNamespace()
    {
        return $this->getReflection()->getNamespaceName();
    }
    
    public function getName()
    {
        if (null !== $this->name) {
            return $this->name;
        }
        
        $name = get_class($this);
        $pos = strrpos($name, '\\');
        
        return $this->name = false === $pos ? $name : substr($name, $pos + 1, -6);
    }
    
    /**
     * @return \ReflectionObject
     */
    protected function getReflection()
    {
        if (null === $this->reflected) {
            $this->reflected = new \ReflectionObject($this);
        }
        return $this->reflected;
    }

    public function modules()
    {
        return array();
    }

    public function dependencies()
    {
        return array();
    }
}
