<?php

namespace Youngx\MVC\Event;

class GetRenderableResponseEvent
{
    private $variables = array();
    private $file;
    private $content;
    private $statusCode = 200;

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

    public function mergeVariables(array $variables)
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

    public function getContent()
    {
        return $this->content;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}