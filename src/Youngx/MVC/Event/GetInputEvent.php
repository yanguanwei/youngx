<?php

namespace Youngx\MVC\Event;

use Youngx\MVC\Input\InputInterface;

class GetInputEvent
{
    private $input;

    /**
     * @return InputInterface
     */
    public function getInput()
    {
        return $this->input;
    }

    public function hasInput()
    {
        return null !== $this->input;
    }

    public function setInput(InputInterface $input)
    {
        $this->input = $input;

        return $this;
    }
}