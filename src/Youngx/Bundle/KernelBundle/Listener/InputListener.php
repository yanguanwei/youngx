<?php

namespace Youngx\Kernel\Listener;

use Youngx\Kernel\Container as Y;
use Youngx\Kernel\Event\GetElementEvent;
use Youngx\Kernel\Handler\ListenerRegistration;
use Youngx\Kernel\Html\CheckboxElement;
use Youngx\Kernel\Html\RadioElement;
use Youngx\Kernel\Html\SelectElement;

class InputListener implements ListenerRegistration
{
    public function text(GetElementEvent $event, array $attributes = array())
    {
        if (!$event->hasElement()) {
            if (!isset($attributes['type'])) {
                $attributes['type'] = 'text';
            }
            $event->setElement(Y::textElement($attributes));
        }
    }

    public function password(GetElementEvent $event, array $attributes = array())
    {
        if (!$event->hasElement()) {
            $attributes['type'] = 'password';
            Y::handler()->trigger('kernel.input.text', $event, $attributes);
        }
    }

    public function hidden(GetElementEvent $event, array $attributes = array())
    {
        if (!$event->hasElement()) {
            $attributes['type'] = 'hidden';
            $event->setElement(Y::textElement($attributes));
        }
    }

    public function textarea(GetElementEvent $event, array $attributes = array())
    {
        if (!$event->hasElement()) {
            $event->setElement(Y::Element('textarea', $attributes));
        }
    }

    public function checkbox(GetElementEvent $event, array $attributes = array())
    {
        if (!$event->hasElement()) {
            $input = new CheckboxElement(array_merge(array(
                        '#type' => 'input',
                        '#multiple' => true
                    ), $attributes));
            $event->setElement($input);
        }
    }

    public function radio(GetElementEvent $event, array $attributes = array())
    {
        if (!$event->hasElement()) {
            $input = new RadioElement(array_merge(array(
                        '#type' => 'input',
                        '#multiple' => false
                    ), $attributes));
            $event->setElement($input);
        }
    }

    public function select(GetElementEvent $event, array $attributes = array())
    {
        if (!$event->hasElement()) {
            $event->setElement(new SelectElement($attributes));
        }
    }

    public static function registerListeners()
    {
        return array(
            'kernel.input.text' => 'text',
            'kernel.input.textarea' => 'textarea',
            'kernel.input.password' => 'password',
            'kernel.input.hidden' => 'hidden',
            'kernel.input.checkbox' => 'checkbox',
            'kernel.input.radio' => 'radio',
            'kernel.input.select' => 'select',
        );
    }
}