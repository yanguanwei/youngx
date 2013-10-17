<?php

namespace Youngx\MVC;

class Locator
{
    protected $locations = array();
    protected $urls = array();

    public function register($scheme, $uri, $url = null)
    {
        $this->locations[$scheme] = $this->locate($uri);
        if (null !== $url) {
            $this->urls[$scheme] = $url;
        }

        return $this;
    }

    public function has($scheme)
    {
        return isset($this->locations[$scheme]);
    }

    /**
     *
     * @param $uri $schema://path
     * @return string
     */
    public function locate($uri)
    {
        $info = $this->parseUri($uri);
        if ($info) {
            list($scheme, $path) = $info;
            return $this->locations[$scheme] . ($path ? ('/'.$path) : '');
        }
        return $uri;
    }

    public function locateUrl($uri)
    {
        $info = $this->parseUri($uri);
        if ($info) {
            list($scheme, $path) = $info;
            if (isset($this->urls[$scheme])) {
                return $this->urls[$scheme] . ($path ? ('/'.$path) : '');
            }
        }
        return $uri;
    }

    protected function parseUri($uri)
    {
        if (preg_match('/^([a-zA-Z0-9-_]{2,}):\/\/([\S]+)?$/', $uri, $match) && $this->has($match[1])) {
            return array($match[1], isset($match[2]) ? $match[2] : null);
        }
    }
}