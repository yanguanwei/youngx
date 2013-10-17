<?php

namespace Youngx\MVC;

use Symfony\Component\HttpFoundation\Request as BaseRequest;
use Symfony\Component\Routing\Route;
use Youngx\MVC\Menu\Menu;

class Request extends BaseRequest
{
    private $routeName;
    private $menu;
    private $route;
    private $bundle;
    private $module;
    private $menuGroups;

    public function setMenuGroups(array $menuGroups)
    {
        $this->menuGroups = $menuGroups;
    }

    public function setMenu(Menu $menu)
    {
        $this->menu = $menu;
    }

    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }

    public function setRoute(Route $route)
    {
        $this->route = $route;
    }

    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @return Menu
     */
    public function getMenu()
    {
        return $this->menu;
    }

    public function getMenuGroup()
    {
        return $this->menuGroups ? reset($this->menuGroups) : null;
    }

    public function getMenuGroups()
    {
        return $this->menuGroups;
    }

    /**
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    public function setBundle(Bundle $bundle)
    {
        $this->bundle = $bundle;

        return $this;
    }

    /**
     * @return Bundle
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    public function getModule()
    {
        return $this->module;
    }
}