<?php

namespace Youngx\Kernel;

use Youngx\Kernel\Exception\DomainNotRegisteredException;

class Location
{
    protected $name;
    protected $domains = array();

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function register($baseUri, $domain = null)
    {
        if ($domain === null) {
            $this->domains[0][] = $baseUri;
        } else {
            $this->domains[$domain][] = $baseUri;
        }

        return $this;
    }

    public function locate($path, $domain = null)
    {
        if (null === $domain) {
            return end($this->domains[0]) . '/' . $path;
        } else {
            return end($this->domains[$domain]) . '/' . $path;
        }
    }

    public function all($domains = null)
    {
        if ($domains === null) {
            return call_user_func_array('array_merge', $this->domains);
        } else if (is_string($domains) && isset($this->domains[$domains])) {
            return $this->domains[$domains];
        } else if (is_array($domains)) {
            $base = array();
            foreach ($domains as $domain) {
                $base = array_merge($base, $this->all($domain));
            }
            return $base;
        }
        throw new DomainNotRegisteredException($this->getName(), $domains);
    }

    public function search($path, $domains = null)
    {
        foreach ($this->all($domains) as $base) {
            if (file_exists($base . '/' . $path)) {
                return $base . '/' . $path;
            }
        }
        return false;
    }
}