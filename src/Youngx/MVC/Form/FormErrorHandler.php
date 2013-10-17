<?php

namespace Youngx\MVC\Form;

class FormErrorHandler
{
    protected $errors;

    public function add($name, $error)
    {
        $this->errors[$name][] = $error;

        return $this;
    }

    public function all()
    {
        return $this->errors;
    }

    public function get($name)
    {
        return isset($this->errors[$name]) ? reset($this->errors[$name]) : '';
    }

    public function has($name = null)
    {
        return null === $name ? !empty($this->errors) : isset($this->errors[$name]);
    }

    public function names()
    {
        return array_keys($this->errors);
    }

    public function throwException($name, $error)
    {
        $this->add($name, $error);

        throw new FormErrorException();
    }
}