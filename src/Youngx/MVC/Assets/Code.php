<?php

namespace Youngx\MVC\Assets;

use Youngx\Util\SortableArray;

class Code extends SortableArray
{
    public function __construct($key, $code)
    {
        $this->set($key, $code);
    }

    public function toString()
    {
        return implode("\n", $this->all());
    }

    public function __toString()
    {
        try {
            return (string) $this->toString();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}