<?php

namespace Youngx\MVC\User;

interface UserEntityInterface
{
    public function getId();
    public function getName();
    public function getRoles();
    public function getEmail();
    public function getPassword();
}