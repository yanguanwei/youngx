<?php

namespace Youngx\Kernel\Listener;

use Youngx\Kernel\Container as Y;
use Youngx\Kernel\Form;
use Youngx\Kernel\Handler\ListenerRegistration;

class ValidateListener implements ListenerRegistration
{
    public function validate(Form $form, $name, array $arguments, $validator)
    {
        array_unshift($arguments, "kernel.validate.{$validator}", $form->get($name));
        return call_user_func_array(array(Y::handler(), 'trigger'), $arguments);
    }

    public function required($value)
    {
        return !empty($value);
    }

    public function rangelength($value, $min, $max)
    {
        return !(($n = strlen($value) < $min) || $n > $max);
    }

    public function range($value, $min, $max)
    {
        $value = floatval($value);
        return $min <= $value && $value <= $max;
    }

    public function email($value)
    {
        return (Boolean) strpos($value, '@');
    }

    public function equalTo(Form $form, $name, array $arguments)
    {
        return $form->get($name) == $form->get($arguments[0]);
    }

    public function name($value)
    {
        return preg_match('/^[a-z][a-z0-9_]+$/', $value);
    }

    public static function registerListeners()
    {
        return array(
            'kernel.validate.form' => 'validate',
            'kernel.validate.form.equalTo' => 'equalTo',
            'kernel.validate.required' => 'required',
            'kernel.validate.rangelength' => 'rangelength',
            'kernel.validate.range' => 'range',
            'kernel.validate.email' => 'email',
            'kernel.validate.name' => 'name'
        );
    }
}