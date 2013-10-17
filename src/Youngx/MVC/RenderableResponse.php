<?php

namespace Youngx\MVC;

use Symfony\Component\HttpFoundation\Response;

class RenderableResponse extends Response
{
    protected $variables = array();
    protected $file;
    /**
     * @var Context
     */
    protected $context;

    public function __construct(Context $context, $content = '', $status = 200, $headers = array())
    {
        $this->context = $context;

        parent::__construct($content, $status, $headers);
    }

    public function setVariables(array $variables)
    {
        $this->variables = $variables;

        return $this;
    }

    public function addVariable($key, $value)
    {
        $this->variables[$key] = $value;

        return $this;
    }

    public function addVariables(array $variables)
    {
        $this->variables = array_merge($this->variables, $variables);

        return $this;
    }

    public function getVariables()
    {
        return $this->variables;
    }

    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function sendContent()
    {
        $handler = $this->context->handler();
        foreach ($this->getVariables() as $key => $value) {
            if (is_string($key) && $key[0] === '#') {
                $name = substr($key, 1);
                $handler->triggerWithMenu("kernel.renderable.config#{$name}", $value, $name);
            }
        }

        if ($this->getFile()) {
            $this->context->block('content', $this->content);
            $content = $this->context->render($this->getFile(), $this->getVariables());
        } else {
            $content = $this->content;
        }

        parent::setContent($content);

        $handler->triggerWithMenu("kernel.renderable.format", $this);
        $handler->triggerOneWithMenu("kernel.renderable.layout", $this);

        echo $this->content;

        return $this;
    }
}