<?php

namespace Youngx\DI;

class DefinitionCollection
{
    protected $definitions = array();

    /**
     * @param $id
     * @param $class
     * @param null|string|array|true $typeClasses
     * @return Definition
     * @throws \InvalidArgumentException
     */
    public function register($id, $class, $typeClasses = null)
    {
        if (isset($this->definitions[$id])) {
            throw new \InvalidArgumentException(sprintf('Service Definition id[%s] has been registered.', $id));
        }
        return $this->definitions[$id] = new Definition($class, $typeClasses);;
    }

    /**
     * @return Definition[]
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }
}