<?php

namespace Youngx\MVC\Menu;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Youngx\MVC\Exception\MenuNotFoundException;

class MenuCollection
{
    protected $name;

    protected $prefix;

    /**
     * @var MenuDefinition[]
     */
    protected $definitions = array();

    /**
     * @var MenuCollection[]
     */
    protected $menuCollections = array();

    /**
     * @var MenuCollection
     */
    protected $parent;

    public function __construct($name = null, MenuCollection $parent = null)
    {
        $this->name = $name;
        $this->parent = $parent;
    }

    /**
     * @param $name
     * @param $path
     * @param $label
     * @param $controller
     * @param $type
     * @throws \RuntimeException
     * @return MenuDefinition
     */
    public function add($name, $path, $label, $controller, $type = Menu::CALLBACK)
    {
        if ($this->has($name)) {
            throw new \RuntimeException(sprintf('Menu[%s] has been registered.', $name));
        }

        return $this->definitions[$name] = new MenuDefinition($this->getName(), $path, $label, $controller, $type);
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getPrefix()
    {
        $prefix = $this->prefix;

        if ($this->parent) {
            $prefix = $this->parent->getPrefix() . $prefix;
        }

        return $prefix;
    }

    /**
     * @param $name
     * @throws MenuNotFoundException
     * @return Menu
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            throw new MenuNotFoundException($name);
        }

        return $this->definitions[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->definitions[$name]);
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return MenuCollection
     */
    public function getCollection($name)
    {
        if (!isset($this->menuCollections[$name])) {
            $this->menuCollections[$name] = new MenuCollection($name, $this);
        }

        return $this->menuCollections[$name];
    }

    /**
     * @return MenuCollection[]
     */
    public function getCollections()
    {
        return $this->menuCollections;
    }

    /**
     * @return MenuCollection
     */
    public function getRootCollection()
    {
        if ($this->parent) {
            return $this->parent->getRootCollection();
        }

        return $this;
    }

    /**
     * @return MenuCollection | null
     */
    public function getParentCollection()
    {
        return $this->parent;
    }

    /**
     * @return RouteCollection
     */
    public function getRouteCollection()
    {
        $routeCollection = new RouteCollection();
        foreach ($this->definitions as $name => $menu) {
            $routeCollection->add($name, new Route(
                    $menu->getPath(), $menu->getDefaults(), $menu->getRequirements(), array(), $menu->getHost(), $menu->getSchemes(), $menu->getMethods()
                ));
        }

        $prefix = $this->getPrefix();
        if ($prefix) {
            $prefix = trim(trim($prefix), '/');
            foreach ($routeCollection->all() as $route) {
                $route->setPath(rtrim('/' . $prefix . $route->getPath(), '/'));
            }
        }

        foreach ($this->menuCollections as $collection) {
            $routeCollection->addCollection($collection->getRouteCollection());
        }

        return $routeCollection;
    }

    /**
     * @return MenuDefinition[]
     */
    public function all()
    {
        $definitions = $unSorted = array();
        foreach ($this->definitions as $name => $definition) {
            $unSorted[$definition->getSort()][] = $name;
        }

        ksort($unSorted);
        foreach (call_user_func_array('array_merge', $unSorted) as $name) {
            $definitions[$name] = $this->definitions[$name];
        }

        foreach ($this->menuCollections as $collection) {
            $definitions = array_merge($definitions, $collection->all());
        }
        return $definitions;
    }
}
