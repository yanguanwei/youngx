<?php

namespace Youngx\MVC;

class Object
{
    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    public function get($key)
    {
        if ($getter = $this->readable($key)) {
            return $this->$getter();
        }
        throw new \Exception(sprintf('Property %s::%s cannot be readable.', get_class($this), $key));
    }

    public function readable($key)
    {
        if ($key[0] !== '_') {
            if (method_exists($this, $method = 'get' . (strpos($key, '_') === false ? ucfirst($key) : implode(array_map('ucfirst', explode('_', $key)))))) {
                return $method;
            }
        }
        return false;
    }

    /**
     * @param string | array $key
     * @param null | bool | mixed $value
     * @param bool $throw
     * @throws \Exception
     */
    public function set($key, $value = null, $throw = true)
    {
        if (is_array($key)) {
            $throw = $value === true ? true : false;
            foreach ($key as $k => $v) {
                $this->set($k, $v, $throw);
            }
        } else {
            if ($setter = $this->writable($key)) {
                $this->$setter($value);
            } else if ($throw) {
                throw new \Exception(sprintf('Property %s::%s cannot be writable.', get_class($this), $key));
            }
        }
    }

    public function toArray()
    {
        $array = array();
        foreach (array_keys(get_object_vars($this)) as $key) {
            if ($getter = $this->readable($key)) {
                $array[$key] = $this->$getter();
            }
        }
        return $array;
    }

    public function writable($key)
    {
        if ($key[0] !== '_') {
            if (method_exists($this, $method = 'set' . (strpos($key, '_') === false ? ucfirst($key) : implode(array_map('ucfirst', explode('_', $key)))))) {
                return $method;
            }
        }
        return false;
    }
}