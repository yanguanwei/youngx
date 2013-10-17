<?php

namespace Youngx\MVC\Yui;

use Youngx\MVC\Templating\TemplateInterface;

class Template implements TemplateInterface
{
    protected $file;
    protected $parent;
    protected $variables;
    protected $engine;

    public function __construct(YuiEngine $engine)
    {
        $this->engine = $engine;
    }

    public function __get($key)
    {
        return $this->variables[$key];
    }

    public function __call($method, array $arguments = array())
    {
        return $this->engine->call($method, $arguments);
    }

    public function extend($path, array $variables = array())
    {
        if ($this->parent !== null)
            throw new \LogicException(sprintf('Template[%s] must be single extended.', $this->file));

        $this->parent = array($path, $variables);
    }

    public function render($path, array $variables = array())
    {
        echo $this->engine->render($path, $variables);
    }

    public function get($key, $default = null)
    {
        return isset($this->variables[$key]) ? $this->variables[$key] : $default;
    }

    public function has($key)
    {
        return isset($this->variables[$key]);
    }

    public function renderFile($file, array $variables = array())
    {
        try {
            $this->file = $file;
            $this->variables = $variables;

            ob_start();

            include $file;

            if (null !== $this->parent) {
                ob_clean();
                list($path, $variables) = $this->parent;
                $this->render($path, $variables);
            }

            return ob_get_clean();
        } catch (\Exception $e) {
            ob_get_clean();
            throw $e;
        }
    }
}