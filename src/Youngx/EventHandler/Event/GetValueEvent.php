<?php

namespace Youngx\EventHandler\Event;

class GetValueEvent
{
    protected $value;

    public function __construct($value = null)
    {
        $this->value = $value;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function hasValue()
    {
        return null !== $this->value;
    }
}