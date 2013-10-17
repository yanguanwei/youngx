<?php

namespace Youngx\Bundle\UserBundle\Listener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Youngx\MVC\Context;
use Youngx\MVC\Event\GetResponseForRoutingEvent;
use Youngx\EventHandler\Registration;

class AccessListener implements Registration
{
    /**
     * @var Context
     */
    protected $context;

    public function setContext(Context $context)
    {
        $this->context = $context;
    }

    public function login()
    {
        if ($this->context->identity()->isLogged()) {
            return false;
        }
        return true;
    }

    public function loginAccessDeny(GetResponseForRoutingEvent $event)
    {
        $event->setResponse(RedirectResponse::create($this->context->generateUrl('user-home')));
    }

    public function logout()
    {
        if (!$this->context->identity()->isLogged()) {
            return false;
        }
        return true;
    }

    public function logoutAccessDeny(GetResponseForRoutingEvent $event)
    {
        $event->setResponse(RedirectResponse::create($this->context->generateUrl('user-login')));
    }

    public function register()
    {
        if ($this->context->identity()->isLogged()) {
            return false;
        }
        return true;
    }

    public function registerAccessDeny(GetResponseForRoutingEvent $event)
    {
        $event->setResponse(RedirectResponse::create($this->context->generateUrl('user-home')));
    }

    public function userHome()
    {
        return $this->context->identity()->isLogged();
    }

    public function userHomeAccessDeny(GetResponseForRoutingEvent $event)
    {
        $event->setResponse(RedirectResponse::create($this->context->generateUrl('user-login')));
    }

    public static function registerListeners()
    {
        return array(
            'kernel.access#user-login' => 'login',
            'kernel.access#user-logout' => 'logout',
            'kernel.access#user-register' => 'register',
            'kernel.access#user-home' => 'userHome',
            'kernel.access.deny#user-login' => 'loginAccessDeny',
            'kernel.access.deny#user-logout' => 'logoutAccessDeny',
            'kernel.access.deny#user-register' => 'registerAccessDeny',
            'kernel.access.deny#user-home' => 'userHomeAccessDeny',
        );
    }
}