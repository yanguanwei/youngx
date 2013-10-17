<?php

namespace Youngx\MVC\Templating;

interface EngineInterface
{
    /**
     * @param $name
     * @param string | null $content
     * @return BlockInterface
     */
    public function block($name, $content = null);
    public function renderView($path, array $variables = array());
    public function render($path, array $variables);
}