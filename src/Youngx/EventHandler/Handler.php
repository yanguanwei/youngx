<?php

namespace Youngx\EventHandler;

use Youngx\DI\Container;

class Handler
{
    private $sorted = array();
    private $listeners = array();
    private $services = array();

    /**
     * @var Container
     */
    protected $container;

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    public function trigger($eventNames)
    {
        $arguments = func_get_args();
        array_shift($arguments);
        $return = null;
        foreach (is_array($eventNames) ? $eventNames : $this->resolveEvents($eventNames) as $eventName) {
            if ($this->hasListeners($eventName)) {
                if (null !== ($result = $this->doTrigger($this->getListeners($eventName), $arguments))) {
                    $return = $result;
                    if ($result === false) {
                        break;
                    }
                }
            }
        }
        return $return;
    }

    public function triggerForValue($eventNames)
    {
        $arguments = func_get_args();
        array_shift($arguments);
        $return = null;

        if (null !== ($listener = $this->parseFirstListener($eventNames))) {
            $return = call_user_func_array($listener, $arguments);
        }

        return $return;
    }

    private function parseFirstListener($eventNames)
    {
        if (is_array($eventNames)) {
            foreach ($eventNames as $eventName) {
                if (null !== ($first = $this->parseFirstListener($eventName))) {
                    return $first;
                }
            }
        } else {
            if ($this->hasListeners($eventNames)) {
                $listeners = $this->getListeners($eventNames);
                return reset($listeners);
            }
        }
    }

    /**
     * @param string | array $eventNames
     * @return mixed|null
     */
    public function triggerOne($eventNames)
    {
        $arguments = func_get_args();
        array_shift($arguments);
        $return = null;
        foreach (is_array($eventNames) ? $eventNames : $this->resolveEvents($eventNames) as $eventName) {
            if ($this->hasListeners($eventName)) {
                $return = $this->doTrigger($this->getListeners($eventName), $arguments);
                break;
            }
        }
        return $return;
    }

    public function getListeners($eventName = null)
    {
        $this->lazyLoad($eventName);

        if (null !== $eventName) {
            if (!isset($this->sorted[$eventName])) {
                $this->sortListeners($eventName);
            }

            return $this->sorted[$eventName];
        }

        foreach (array_keys($this->listeners) as $eventName) {
            if (!isset($this->sorted[$eventName])) {
                $this->sortListeners($eventName);
            }
        }

        return $this->sorted;
    }

    public function addListener($eventName, $listener, $priority = 0)
    {
        $this->listeners[$eventName][$priority][] = $listener;
        unset($this->sorted[$eventName]);
    }

    public function addRegistration(Registration $registration)
    {
        foreach ($registration->registerListeners() as $eventName => $params) {
            if (is_string($params)) {
                $this->addListener($eventName, array($registration, $params));
            } elseif (is_string($params[0])) {
                $this->addListener($eventName, array($registration, $params[0]), isset($params[1]) ? $params[1] : 0);
            } else {
                foreach ($params as $listener) {
                    $this->addListener($eventName, array($registration, $listener[0]), isset($listener[1]) ? $listener[1] : 0);
                }
            }
        }

        return $this;
    }

    public function addServiceRegistration($id, $registration)
    {
        foreach ($registration::registerListeners() as $eventName => $params) {
            if (is_string($params)) {
                $this->services[$eventName][] = array($id, $params, 0);
            } elseif (is_string($params[0])) {
                $this->services[$eventName][] = array($id, $params[0], isset($params[1]) ? $params[1] : 0);
            } else {
                foreach ($params as $listener) {
                    $this->services[$eventName][] = array($id, $listener[0], isset($listener[1]) ? $listener[1] : 0);
                }
            }
        }
    }

    public function hasListeners($eventName = null)
    {
        if (null === $eventName) {
            return ((Boolean) count($this->listeners)) || ((Boolean) count($this->services));
        } else {
            return isset($this->listeners[$eventName]) || isset($this->services[$eventName]);
        }
    }

    /**
     * @param $eventNames
     * @return array
     */
    protected function resolveEvents($eventNames)
    {
        if (strpos($eventNames, '>') !== false) {
            $resolved = $array = array();
            $parts = explode('>', $eventNames);
            while ($parts) {
                $array[] = array_shift($parts);
                $resolved[] = implode('.', $array);
            }
        } elseif (strpos($eventNames, '<') !== false) {
            $resolved = array();
            $parts = explode('<', $eventNames);
            while ($parts) {
                $resolved[] = implode('.', $parts);
                array_pop($parts);
            }
        } else {
            $resolved = array($eventNames);
        }

        return $resolved;
    }

    protected function doTrigger($listeners, array $arguments)
    {
        $return = null;
        foreach ($listeners as $listener) {
            if (null !== ($result = call_user_func_array($listener, $arguments))) {
                $return = $result;
                if ($result === false) {
                    break;
                }
            }
        }
        return $return;
    }

    protected function lazyLoad($eventName)
    {
        if (isset($this->services[$eventName])) {
            foreach ($this->services[$eventName] as $args) {
                list($id, $method, $priority) = $args;
                $listener = $this->container->get($id);
                $this->addListener($eventName, array($listener, $method), $priority);
            }
            unset($this->services[$eventName]);
        }
    }

    private function sortListeners($eventName)
    {
        $this->sorted[$eventName] = array();

        if (isset($this->listeners[$eventName])) {
            ksort($this->listeners[$eventName], SORT_NUMERIC);
            $this->sorted[$eventName] = call_user_func_array('array_merge', $this->listeners[$eventName]);
        }
    }
}