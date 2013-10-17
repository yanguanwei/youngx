<?php

namespace Youngx\MVC\Event;

use Youngx\MVC\Widget;

class GetWidgetEvent
{
    protected $widget;

    public function __construct(Widget $widget = null)
    {
        if ($widget) {
            $this->setWidget($widget);
        }
    }

    /**
     * @return Widget
     */
    public function getWidget()
    {
        return $this->widget;
    }

    public function hasWidget()
    {
        return null !== $this->widget;
    }

    public function setWidget(Widget $widget)
    {
        $this->widget = $widget;

        return $this;
    }
}