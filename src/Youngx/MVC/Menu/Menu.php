<?php

namespace Youngx\MVC\Menu;

class Menu
{
    const CALLBACK = 1;
    const MENU = 2;
    const TAB = 4;
    const MENU_TAB = 6;
    const TAB_DEFAULT = 12;
    const MENU_TAB_DEFAULT = 14;
    const ROOT = 16;
    const MENU_ROOT = 18;
    const MENU_ROOT_TAB_DEFAULT = 30;
    const MENU_ROOT_TAB = 22;
    const TAB_DEFAULT_SELF = 44;
    const MENU_TAB_DEFAULT_SELF = 46;

    protected $path;
    protected $controller;
    protected $type;
    protected $label;
    protected $access;
    protected $loaders = array();
    protected $attributes = array();
    protected $group;
    protected $parent;

    public function __construct($path, $label, $controller, $type, $access, array $loaders, array $attributes, $group, $parent)
    {
        $this->path = $path;
        $this->label = $label;
        $this->controller = $controller;
        $this->type = $type;
        $this->access = $access;
        $this->loaders = $loaders;
        $this->attributes = $attributes;
        $this->group = $group;
        $this->parent = $parent;
    }

    public function getAccess()
    {
        return $this->access;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return array
     */
    public function getLoaders()
    {
        return $this->loaders;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getType()
    {
        return $this->type;
    }

    public function isCallback()
    {
        return (Boolean) ($this->type & self::CALLBACK);
    }

    public function setLoader($key, $loader)
    {
        $this->loaders[$key] = $loader;

        return $this;
    }

    public function isMenu()
    {
        return (Boolean) (($this->type & self::MENU) == self::MENU);
    }

    public function isTab()
    {
        return (Boolean) (($this->type & self::TAB) == self::TAB);
    }

    public function isMenuTab()
    {
        return (Boolean) (($this->type & self::MENU_TAB) == self::MENU_TAB);
    }

    public function isTabDefault()
    {
        return (Boolean) (($this->type & self::TAB_DEFAULT) == self::TAB_DEFAULT);
    }

    public function isTabDefaultSelf()
    {
        return (Boolean) (($this->type & self::TAB_DEFAULT_SELF) == self::TAB_DEFAULT_SELF);
    }

    public function isMenuTabDefault()
    {
        return (Boolean) (($this->type & self::MENU_TAB_DEFAULT) == self::MENU_TAB_DEFAULT);
    }

    public function isMenuTabDefaultSelf()
    {
        return (Boolean) (($this->type & self::MENU_TAB_DEFAULT_SELF) == self::MENU_TAB_DEFAULT_SELF);
    }

    public function isMenuRoot()
    {
        return (Boolean) (($this->type & self::MENU_ROOT) == self::MENU_ROOT);
    }
}