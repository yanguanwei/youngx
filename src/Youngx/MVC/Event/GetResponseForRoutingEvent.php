<?php

namespace Youngx\MVC\Event;

use Symfony\Component\Routing\Route;
use Youngx\MVC\Menu\Menu;

class GetResponseForRoutingEvent extends GetResponseEvent
{
    protected $name;
    protected $menu;
    protected $route;
    protected $controller;
    protected $attributes = array();

    public function setRouting($name, Menu $menu, Route $route)
    {
        $this->name = $name;
        $this->menu = $menu;
        $this->route = $route;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Menu
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
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

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }
}