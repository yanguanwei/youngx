<?php

namespace Youngx\EventHandler\Event;

class GetSortableArrayEvent
{
    private $items = array();
    private $sorts = array();

    public function set($key, $item, $sort = 0)
    {
        $this->items[$key] = $item;
        $this->sort($key, $sort);

        return $this;
    }

    public function sort($key, $sort)
    {
        $this->sorts[$key] = $sort;

        return $this;
    }

    public function remove($key)
    {
        unset($this->items[$key], $this->sorts[$key]);

        return $this;
    }

    public function has($key = null)
    {
        return $key === null ? !empty($this->items) : isset($this->items[$key]);
    }

    public function get($key)
    {
        return isset($this->items[$key]) ? $this->items[$key] :null;
    }

    public function all()
    {
        if ($this->items) {
            $sortedItems = $sortedKey = array();
            foreach ($this->sorts as $key => $sort) {
                $sortedKey[$sort][] = $key;
            }
            ksort($sortedKey);
            $sortedKey = call_user_func_array('array_merge', $sortedKey);
            foreach ($sortedKey as $key) {
                $sortedItems[$key] = $this->get($key);
            }
            return $sortedItems;
        } else {
            return array();
        }
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->all());
    }
}