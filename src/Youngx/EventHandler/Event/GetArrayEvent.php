<?php

namespace Youngx\EventHandler\Event;

class GetArrayEvent
{
    protected  $array;

    public function __construct(array $array = array())
    {
        $this->array = $array;
    }

    public function addValue($value, $key = null)
    {
        if (null === $key) {
            $this->array[] = $value;
        } else {
            $this->array[$key] = $value;
        }
        return $this;
    }

    public function setArray(array $array)
    {
        $this->array = $array;

        return $this;
    }

    public function addArray(array $array)
    {
        $this->array = array_merge($this->array, $array);

        return $this;
    }

    public function getArray()
    {
        return $this->array;
    }
}