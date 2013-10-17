<?php

namespace Youngx\MVC\Templating;

interface TemplateInterface
{
    public function render($path, array $variables);
    public function renderFile($file, array $variables);
}