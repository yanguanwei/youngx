<?php

namespace Youngx\MVC\Yui;

use Youngx\MVC\Handler;
use Youngx\EventHandler\Event\GetSortableArrayEvent;

class Block
{
    protected $handler;
    /**
     * @var GetSortableArrayEvent
     */
    protected $contents;
    protected $sort = 0;
    protected $name;
    protected $count = 0;

    public function __construct(Handler $handler, $name)
    {
        $this->handler = $handler;
        $this->name = $name;
        $this->contents = new GetSortableArrayEvent();
    }

    public function __toString()
    {
        try {
            return $this->getContents();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getName()
    {
        return $this->name;
    }

    protected function getContents()
    {
        $this->handler->triggerWithMenu("kernel.block#{$this->name}", $this->contents, $this->name);

        return $this->handler->triggerForValueWithMenu(array(
                "kernel.block.render#{$this->name}",
                'kernel.block.render'
            ), $this->contents);
    }

    public function hasContents()
    {
        return $this->contents->has();
    }

    public function add($content, $sort = 0)
    {
        $this->contents->set("{$this->name}_".$this->count++, $content, $sort);

        return $this;
    }

    public function start($sort = 0)
    {
        $this->sort = $sort;
        ob_start();

        return $this;
    }

    public function end()
    {
        $this->add(ob_get_clean(), $this->sort);
    }
}