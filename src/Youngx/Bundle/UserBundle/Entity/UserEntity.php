<?php

namespace Youngx\Bundle\UserBundle\Entity;

use Youngx\Database\Entity;
use Youngx\Database\Query;
use Youngx\MVC\User\UserEntityInterface;

class User extends Entity implements UserEntityInterface
{
    protected $uid;
    protected $name;
    protected $email;
    protected $password;
    protected $created_at;

    public function getRoles()
    {
        return $this->roles;
    }

    public function getId()
    {
        return $this->uid;
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setPassword($password)
    {
        $this->password = $this->encryptPassword($password);
    }

    /**
     * @param mixed $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    public static function updatePassword($uid, $password)
    {
        return Y::database()->update('{{' . self::table() . '}}', array(
                'password' => self::encryptPassword($password)
            ), 'uid=:uid', array(':uid' => $uid));
    }

    public function encryptPassword($password)
    {
        return md5($password);
    }

    public static function type()
    {
        return 'user';
    }

    public static function table()
    {
        return 'y_user';
    }

    public static function primaryKey()
    {
        return 'uid';
    }

    public static function fields()
    {
        return array(
            'uid', 'name', 'email', 'password', 'created_at'
        );
    }

    public static function findByName(Query $query, $name)
    {
        return $query->where(array(
                'name=?' => $name
            ))->one();
    }
}
