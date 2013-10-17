<?php

namespace Youngx\Bundle\UserBundle\Listener;

use Youngx\Kernel\Container as Y;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Youngx\Kernel\Event\GetResponseForExceptionEvent;
use Youngx\Kernel\Handler\ListenerRegistration;

class KernelListener implements ListenerRegistration
{
    public function notLogged(GetResponseForExceptionEvent $event)
    {
        $event->setResponse(RedirectResponse::create(Y::generateUrl('user.login')));
    }

    public static function registerListeners()
    {
        return array(
            'kernel.exception.http.401' => 'notLogged'
        );
    }
}