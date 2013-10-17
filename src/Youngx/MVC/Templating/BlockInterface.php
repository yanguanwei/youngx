<?php

namespace Youngx\MVC\Templating;

interface BlockInterface
{
    public function add($content, $sort = 0);
    public function hasContents();
    public function start($sort = 0);
    public function end();
}