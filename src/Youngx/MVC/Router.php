<?php

namespace Youngx\MVC;

use Youngx\MVC\Menu\Menu;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Youngx\MVC\Handler;
use Youngx\MVC\Menu\MenuCollection;

class Router
{
    protected $routeCollection;
    protected $menuCollection;
    protected $matcher;
    protected $requestContext;
    protected $generator;
    protected $handler;
    protected $menus;
    protected $menuParents = array();
    protected $menuGroups = array();
    protected $groupParents = array();

    public function __construct(Handler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @param $name
     * @return Menu
     */
    public function getMenu($name)
    {
        $menus = $this->getMenus();
        return ($name !== null && isset($menus[$name])) ? $menus[$name] : null;
    }

    /**
     * @return Menu[]
     */
    public function getMenus()
    {
        if (null === $this->menus) {
            $this->init();
        }

        return $this->menus;
    }

    protected function init()
    {
        $menus = $definitions = $paths = array();
        $menuCollection = $this->getMenuCollection();
        foreach ($menuCollection->all() as $name => $definition) {
            $definition->setPath($this->getRoute($name)->getPath());
            $definitions[$name] = $definition;
            $paths[$definition->getPath()] = $name;
        }

        foreach ($definitions as $definition) {
            if ($definition->getPath() != '/' && !$definition->getParent()) {
                $parts = explode('/', trim($definition->getPath(), '/'));
                while (array_pop($parts)) {
                    $path = '/' . implode('/', $parts);
                    if (isset($paths[$path])) {
                        $definition->setParent($paths[$path]);
                        break;
                    }
                }
            }
        }

        foreach ($definitions as $name => $definition) {
            $menus[$name] = new Menu(
                $definition->getPath(),
                $definition->getLabel(),
                $definition->getController(),
                $definition->getType(),
                $definition->getAccess(),
                $definition->getLoaders(),
                $definition->getAttributes(),
                $definition->getGroup(),
                $definition->getParent()
            );

            $this->menuParents[$definition->getParent() ?: 0][] = $name;
        }

        $this->initMenuGroupsWithCollection($menuCollection);
        $this->menus = $menus;
    }

    protected function initMenuGroupsWithCollection(MenuCollection $collection)
    {
        foreach ($collection->all() as $name => $definition) {
            $this->menuGroups[$collection->getName()][] = $name;
        }

        $parent = $collection->getParentCollection();
        if ($parent) {
            $parent = $parent->getName();
        }
        $this->groupParents[$collection->getName()] = $parent;

        foreach ($collection->getCollections() as $subCollection) {
            $this->initMenuGroupsWithCollection($subCollection);
        }
    }

    public function getRootWithinGroup($name)
    {
        $current = $this->getMenu($name);
        $parent = $this->getMenu($current->getParent());
        $root = $name;
        while ($parent && isset($this->menuParents[$parent->getParent()])) {
            $root = $this->getMenu($root)->getParent();
            $parent = $this->getMenu($parent->getParent());
        }
        return $root;
    }

    public function getMenuRoot($name)
    {
        $menu = $this->getMenu($name);
        if ($menu->isMenuRoot()) {
            return $name;
        }

        $menuRoot = null;
        while ($menu->getParent()) {
            $menu = $this->getMenu($parent = $menu->getParent());
            if ($menu->isMenuRoot()) {
                $menuRoot = $parent;
                break;
            }
        }
        return $menuRoot;
    }

    /**
     * @param $group
     * @return Menu[]
     */
    public function getMenusGroupedBy($group)
    {
        $menus = array();
        if (isset($this->menuGroups[$group])) {
            foreach ($this->menuGroups[$group] as $name) {
                $menus[$name] = $this->getMenu($name);
            }
        }
        return $menus;
    }

    /**
     * @param $group
     * @return array
     */
    public function getMenuNamesGroupedBy($group)
    {
        return isset($this->menuGroups[$group]) ? $this->menuGroups[$group] : array();
    }

    /**
     * @param string | 0 $parent
     * @return Menu[]
     */
    public function getSubmenus($parent)
    {
        $menus = array();
        if (isset($this->menuParents[$parent])) {
            foreach ($this->menuParents[$parent] as $name) {
                $menus[$name] = $this->getMenu($name);
            }
        }
        return $menus;
    }

    public function hasSubmenus($parent)
    {
        return isset($this->menuParents[$parent]);
    }

    public function getMenuGroupParent($group)
    {
        return isset($this->groupParents[$group]) ? $this->groupParents[$group] : null;
    }

    /**
     * @param $name
     * @return null|\Symfony\Component\Routing\Route
     */
    public function getRoute($name)
    {
        return $this->getRouteCollection()->get($name);
    }

    /**
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function getRouteCollection()
    {
        if (null===$this->routeCollection) {
            $this->routeCollection = $this->getMenuCollection()->getRouteCollection();
        }
        return $this->routeCollection;
    }

    /**
     * @return MenuCollection
     */
    protected function getMenuCollection()
    {
        if (null === $this->menuCollection) {
            $this->menuCollection = new MenuCollection('default');
            $this->handler->trigger('kernel.menu.collect', $this->menuCollection);
        }
        return $this->menuCollection;
    }
    
    /**
     * @return \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    public function getGenerator()
    {
        if (null === $this->generator) {
            $this->generator = new UrlGenerator($this->getRouteCollection(), $this->getRequestContext());
        }
        return $this->generator;
    }
    
    public function match($pathInfo)
    {
        return $this->getMatcher()->match($pathInfo);
    }
    
    public function setRequest(Request $request)
    {
        $this->getRequestContext()->fromRequest($request);
    }
    
    public function generate($name, array $parameters = array(), $referenceType = UrlGenerator::ABSOLUTE_PATH)
    {
        return $this->getGenerator()->generate($name, $parameters, $referenceType);
    }

    /**
     * @return \Symfony\Component\Routing\RequestContext
     */
    protected function getRequestContext()
    {
        if (null === $this->requestContext) {
            $this->requestContext = new RequestContext();
        }
        return $this->requestContext;
    }

    /**
     * @return \Symfony\Component\Routing\Matcher\UrlMatcherInterface
     */
    protected function getMatcher()
    {
        if (null === $this->matcher) {
            $this->matcher = new UrlMatcher($this->getRouteCollection(), $this->getRequestContext());
        }
        return $this->matcher;
    }
}
