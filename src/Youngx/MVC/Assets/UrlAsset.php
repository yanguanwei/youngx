<?php

namespace Youngx\MVC\Assets;

class UrlAsset
{
    private $attributes;
    protected $urlKey = 'src';

    public function __construct($url)
    {
        if (is_array($url)) {
            $this->attributes = $url;
        } else {
            $this->attributes = array(
                $this->urlKey => $url
            );
        }
    }

    public function getUrl()
    {
        return isset($this->attributes[$this->urlKey]) ? $this->attributes[$this->urlKey] : null;
    }

    public function setUrl($url)
    {
        $this->attributes[$this->urlKey] = $url;

        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function addAttribute($key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function addAttributes(array $attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }
}