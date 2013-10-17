<?php

namespace Youngx\DI;

class DefinitionProcessor
{
    protected $definitions;
    protected $tags;

    /**
     * @param Definition[] $definitions
     */
    public function __construct(array $definitions)
    {
        $this->definitions = $definitions;

        foreach ($definitions as $id => $definition) {
            foreach ($definition->getTags() as $tag) {
                $this->tags[$tag][] = $id;
            }
        }
    }

    /**
     * @return Definition[]
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }

    /**
     * @param string $id
     * @throws \InvalidArgumentException
     * @return Definition
     */
    public function getDefinition($id)
    {
        if (isset($this->definitions[$id])) {
            return $this->definitions[$id];
        } else {
            throw new \InvalidArgumentException(sprintf('Service Definition id[%s] has not been registered.', $id));
        }
    }

    public function hasDefinition($id)
    {
        return isset($this->definitions[$id]);
    }

    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param $tag
     * @return Definition[]
     */
    public function getTaggedDefinitions($tag)
    {
        $definitions = array();
        if (isset($this->tags[$tag])) {
            foreach ($this->tags[$tag] as $tag => $id) {
                $definitions[$id] = $this->definitions[$id];
            }
        }
        return $definitions;
    }
}