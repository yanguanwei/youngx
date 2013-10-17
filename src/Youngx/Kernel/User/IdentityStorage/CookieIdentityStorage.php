<?php

namespace Youngx\Bundle\UserBundle\IdentityStorage;

use Youngx\Bundle\UserBundle\IdentityStorage;
use Youngx\Bundle\UserBundle\Identity;
use Symfony\Component\HttpFoundation\Request;
use Youngx\EventHandler\EventHandler;
use Youngx\HttpKernel\KernelEvents;
use Youngx\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Cookie;

class CookieIdentityStorage implements IdentityStorage
{
    protected $key = 'youngx_identity';
    protected $verifyKey = 'youngx_identity_verifycode';
    protected $prefix = 'youngx';
    protected $identity;
    
    protected $request;
    protected $handler;
    
    protected $duration;
    
    public function __construct(EventHandler $handler, Request $request)
    {
        $this->handler = $handler;
        $this->request = $request;
    }

    public function clear()
    {
        setcookie($this->key, null);
        setcookie($this->verifyKey, null);
    }

    public function write(Identity $identity, $duration)
    {
        $this->identity = $identity;
        $this->duration = $duration;
        $this->handler->addListener(KernelEvents::RESPONSE, array($this, 'onResponse'));
    }
    
    public function onResponse(FilterResponseEvent $event, Request $request)
    {
        $header = $event->getResponse()->headers;
        
        $data = array(
            $this->identity->getId(), $this->identity->getName(), $this->identity->getRole()->getId(), $this->identity->getExtras()
        );
        
        $value = serialize($data);
        
        if ($this->duration) {
            $expire = time() + $this->duration;
            $header->setCookie(new Cookie($this->key, $value, $expire));
            $header->setCookie(new Cookie($this->verifyKey, $this->getVerifyCode($value), $expire));
        } else {
            $header->setCookie(new Cookie($this->key, $value));
            $header->setCookie(new Cookie($this->verifyKey, $this->getVerifyCode($value)));
        }
    }

    public function read()
    {
        if (null === $this->identity) {
            $cookie = $this->request->cookies;
            if ($cookie->has($this->verifyKey)) {
                $verifycode = $cookie->get($this->verifyKey);
                if ($cookie->has($this->key)) {
                    $value = $cookie->get($this->key);
                    $data = unserialize($value);
                    if (is_array($data) && $verifycode === $this->getVerifyCode($value)) {
                        list($id, $name, $roleId, $extras) = $data;
                        $this->identity = new Identity($id, $name, $roleId, $extras);
                    }
                }
            } else {
                $this->identity = new Identity();
            }
        }
        
        return $this->identity;
    }

    protected function getVerifyCode($value)
    {
        return substr(md5($this->prefix . $value), 0, 16);
    }
}
?>