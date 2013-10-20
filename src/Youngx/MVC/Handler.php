<?php

namespace Youngx\MVC;

class Handler extends \Youngx\EventHandler\Handler
{
    /**
     * @var Request
     */
    protected $request;

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function triggerWithMenu($eventNames)
    {
        $arguments = func_get_args();
        $arguments[0] = $this->parseEventNameWithMenu($eventNames);
        return call_user_func_array(array($this, 'trigger'), $arguments);
    }

    public function triggerOneWithMenu($eventNames)
    {
        $arguments = func_get_args();
        $arguments[0] = $this->parseEventNameWithMenu($eventNames);
        return call_user_func_array(array($this, 'triggerOne'), $arguments);
    }

    public function triggerForValueWithMenu($eventNames)
    {
        $arguments = func_get_args();
        $arguments[0] = $this->parseEventNameWithMenu($eventNames);
        return call_user_func_array(array($this, 'triggerForValue'), $arguments);
    }

    protected function parseEventNameWithMenu($eventNames)
    {
        $events = array();
        $eventNames = (array) $eventNames;
        if ($this->request) {
            foreach ($eventNames as $eventName) {
                $routeName = $this->request->getRouteName();
                if ($routeName) {
                    $events[] = "{$eventName}@menu:{$routeName}";
                    $menuGroups = $this->request->getMenuGroups();
                    if ($menuGroups) {
                        foreach ($menuGroups as $menuGroup) {
                            $events[] = "{$eventName}@menu-group:{$menuGroup}";
                        }
                    }
                }
                $events[] = $eventName;
            }
        } else {
            $events = $eventNames;
        }
        return $events;
    }
}