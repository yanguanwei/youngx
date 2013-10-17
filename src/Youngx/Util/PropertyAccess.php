<?php

namespace Youngx\Util;

class PropertyAccess
{
    /**
     * @var object
     */
    private $object;
    private $extraProperties = array();

    public function __construct($object)
    {
        $this->object = $object;
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    public function attach($property, $value = null)
    {
        $this->extraProperties[$property] = $value;

        return $this;
    }

    public function get($key, $throw = true)
    {
        if (($pos = strpos($key, '[')) !== false) {
            if ($pos === 0) {// [a]name[b][c], should ignore [a]
                if (preg_match('/\](\w+(\[.+)?)/', $key, $matches)) {
                    $key = $matches[1];
                } // we get: name[b][c]
                if (($pos = strpos($key, '[')) === false) {
                    return $this->getPropertyFromGetter($key, $throw);
                }
            }
            $name = substr($key, 0, $pos);
            $value = $this->getPropertyFromGetter($name, $throw);
            foreach (explode('][', rtrim(substr($key, $pos + 1), ']')) as $id) {
                if ((is_array($value) || $value instanceof \ArrayAccess) && isset($value[$id])) {
                    $value = $value[$id];
                } else if (is_object($value) && method_exists($value, $method = $this->parseMethodName('get', $id))) {
                    $value = $value->$method();
                } else {
                    return null;
                }
            }
            return $value;
        } else {
            return $this->getPropertyFromGetter($key, $throw);
        }
    }

    public function getPropertyFromGetter($key, $throw = true)
    {
        if ($getter = $this->readable($key)) {
            return $this->object->$getter();
        } elseif (false !== array_key_exists($key, $this->extraProperties)) {
            return $this->extraProperties[$key];
        } else if ($throw) {
            throw new \Exception(sprintf('Property %s::%s cannot be readable.', get_class($this->object), $key));
        }
    }

    public function readable($key)
    {
        if ($key[0] !== '_') {
            if (method_exists(
                $this->object,
                $method = $this->parseMethodName('get', $key)
            )
            ) {
                return $method;
            }
        }

        return false;
    }

    /**
     * @param string | array $key
     * @param null | bool | mixed $value
     * @param bool $throw
     * @throws \Exception
     */
    public function set($key, $value = null, $throw = true)
    {
        if (is_array($key)) {
            $throw = $value === true ? true : false;
            foreach ($key as $k => $v) {
                $this->set($k, $v, $throw);
            }
        } else {
            if ($setter = $this->writable($key)) {
                $this->object->$setter($value);
            } else if (false !== array_key_exists($key, $this->extraProperties)) {
                $this->extraProperties[$key] = $value;
            } else if ($throw) {
                throw new \Exception(sprintf('Property %s::%s cannot be writable.', get_class($this->object), $key));
            }
        }
    }

    public function toArray(array $fields)
    {
        $array = array();
        foreach ($fields as $key) {
            if ($getter = $this->readable($key)) {
                $array[$key] = $this->object->$getter();
            }
        }
        $array += $this->extraProperties;
        return $array;
    }

    public function writable($key)
    {
        if ($key[0] !== '_') {
            if (method_exists(
                $this->object,
                $method = $this->parseMethodName('set', $key)
            )
            ) {
                return $method;
            }
        }

        return false;
    }

    private function parseMethodName($prefix, $key)
    {
        return $prefix . (strpos($key, '_') === false ? ucfirst($key) : implode(
            array_map('ucfirst', explode('_', $key))
        ));
    }
}