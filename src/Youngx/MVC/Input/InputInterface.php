<?php

namespace Youngx\MVC\Input;

interface InputInterface
{
    public function disable();
    public function enable();
    public function getId();
    public function setName($name);
    public function getName();
    public function setValue($value);
    public function getValue();
    public function __toString();
}