<?php
namespace Youngx\Util;

class SortableArray
{
    private $autoKey = 0;
    private $items = array();
    private $sorts = array();

    public function set($key, $item, $sort = 0)
    {
        $key = $key === null ? ('auto-key-' . $this->autoKey++) : $key;

        $this->items[$key] = $item;
        $this->sort($key, $sort);

        return $this;
    }

    public function sort($key, $sort)
    {
        if (false !== array_key_exists($key, $this->items)) {
            $this->sorts[$key] = $sort;
        }

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
            $items = $sorted = array();
            foreach ($this->sorts as $key => $sort) {
                $sorted[$sort][] = $key;
            }
            ksort($sorted);
            $sorted = call_user_func_array('array_merge', $sorted);
            foreach ($sorted as $key) {
                $items[$key] = $this->get($key);
            }
            return $items;
        } else {
            return array();
        }
    }
}