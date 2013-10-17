<?php

namespace Youngx\Kernel\Listener;

use Youngx\Kernel\Container as Y;
use Youngx\Kernel\Event\GetResponseForExceptionEvent;
use Youngx\Kernel\Exception\HttpException;
use Youngx\Kernel\GetValue\GetResponse;
use Youngx\Kernel\Handler\ListenerRegistration;

class ExceptionListener implements ListenerRegistration
{
    public function handleException(GetResponseForExceptionEvent $event)
    {
        $e = $event->getException();
        if ($e instanceof HttpException) {
            Y::handler()->trigger("kernel.exception.http.{$e->getStatusCode()}", $event);
        }
    }

    public static function registerListeners()
    {
        return array(
            'kernel.exception' => 'handleException'
        );
    }
}