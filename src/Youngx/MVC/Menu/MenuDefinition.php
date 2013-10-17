<?php

namespace Youngx\MVC\Menu;

class MenuDefinition extends Menu
{
    /**
     * @var string
     */
    protected $host = '';

    /**
     * @var array
     */
    protected $schemes = array();

    /**
     * @var array
     */
    protected $methods = array();

    /**
     * @var array
     */
    protected $defaults = array();

    /**
     * @var array
     */
    protected $requirements = array();

    protected $sort = 0;

    public function __construct($group, $path, $label, $controller, $type = self::MENU)
    {
        $this->group = $group;
        $this->setPath($path);
        $this->setLabel($label);
        $this->setController($controller);
        $this->setType($type);
    }

    public function addAttribute($key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function addAttributes(array $attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    public function setAccess($access)
    {
        $this->access = $access;

        return $this;
    }

    public function setAccessArguments($arguments)
    {
        $this->accessArguments = $arguments;

        return $this;
    }

    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    public function setParent($name)
    {
        $this->parent = $name;

        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function setDefault($key, $value)
    {
        $this->defaults[$key] = $value;

        return $this;
    }

    /**
     * @param array $defaults
     * @return $this
     */
    public function setDefaults(array $defaults)
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * @param string $host
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param array $methods
     * @return $this
     */
    public function setMethods($methods)
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }


    public function setRequirement($key, $pattern, $loader = null)
    {
        $this->requirements[$key] = $pattern;

        if (null !== $loader) {
            $this->loaders[$key] = $loader;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * @param array $schemes
     * @return $this
     */
    public function setSchemes($schemes)
    {
        $this->schemes = $schemes;

        return $this;
    }

    /**
     * @return array
     */
    public function getSchemes()
    {
        return $this->schemes;
    }
}