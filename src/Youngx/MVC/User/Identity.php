<?php

namespace Youngx\MVC\User;

class Identity
{
    const ROLE_ANONYMOUS = 1;
    const ROLE_REGISTERED = 2;
    const ROLE_ADMINISTRATOR = 3;
    const ROLE_SELLER = 4;
    const ROLE_BUYER = 5;

    private $id;
    private $name;
    private $roles = array(
        self::ROLE_ANONYMOUS => '访客'
    );
    private $password;
    private $email;

    /**
     * @var UserEntityInterface
     */
    private $entity;
    
    public function __construct($id = 0, $name = '访客')
    {
        $this->id = intval($id);
        $this->name = $name;
    }

    public static function createFromEntity(UserEntityInterface $entity)
    {
        $identity = new self($entity->getId(), $entity->getName());
        $identity->roles = $entity->getRoles();
        $identity->password = $entity->getPassword();
        $identity->email = $entity->getEmail();
        $identity->entity = $entity;

        return $identity;
    }

    /**
     * @return UserEntityInterface
     */
    public function getUserEntity()
    {
        return $this->entity;
    }

    public function getId()
    {
        return $this->id;
    }
    
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    public function hasRole($role)
    {
        return isset($this->roles[$role]);
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function isLogged()
    {
        return $this->id > 0;
    }
}
