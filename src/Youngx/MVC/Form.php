<?php

namespace Youngx\MVC;

use Symfony\Component\HttpFoundation\Response;
use Youngx\MVC\Event\GetResponseEvent;
use Youngx\MVC\Form\FormErrorException;
use Youngx\MVC\Form\FormErrorHandler;
use Youngx\MVC\Widget\FieldWidget;

abstract class Form extends Action
{
    /**
     * @var FormErrorHandler | null
     */
    private $_errors;

    public function error($name)
    {
        if ($this->_errors) {
            return $this->_errors->get($name);
        }

        return null;
    }

    /**
     * @return array
     */
    public function errors()
    {
        return $this->_errors ? $this->_errors->all() : array();
    }

    public function renderFieldWidget($name, FieldWidget $field)
    {
        if (in_array($name, $this->disabledFields())) {
            $field->getInput($name)->disable();
        }
    }

    public function hasError($name = null)
    {
        return $this->_errors ? $this->_errors->has($name) : false;
    }

    public function type()
    {
        return 'form';
    }

    /**
     * @return array
     */
    public function validators()
    {
        $validators = $this->registerValidators();
        foreach ($this->disabledFields() as $field) {
            unset($validators[$field]);
        }
        return $validators;
    }

    public function set($key, $value = null)
    {
        if (is_array($key)) {
            $data = $key;
            foreach ($this->disabledFields() as $field) {
                unset($data[$field]);
            }
            $this->propertyAccess()->set($data, $value, false);
        } else if (!in_array($key, $this->disabledFields())) {
            $this->propertyAccess()->set($key, $value, false);
        }
    }

    public function toArray()
    {
        $array = $this->propertyAccess()->toArray($this->fields());
        foreach ($this->disabledFields() as $key) {
            unset($array[$key]);
        }
        return $array;
    }

    public function runPostRequest()
    {
        try {
            $data = $this->context->request()->request->all();
            if ($data) {
                $this->set($data);
            }
            $data = $this->context->request()->files->all();
            if ($data) {
                $this->set($data);
            }

            $this->initPostRequest();

            $event = new GetResponseEvent();
            $id = $this->id();

            $this->context->handler()->trigger(array(
                    "kernel.form.post#{$id}",
                    "kernel.form.post"
                ), $this, $event);

            if ($event->hasResponse()) {
                return $event->getResponse();
            }

            $feh = new FormErrorHandler();

            $this->autoValidate($this->context, $feh);
            if (!$feh->has()) {
                $this->validate($feh);
                $this->context->handler()->trigger(array(
                        "kernel.form.validate#{$id}",
                        "kernel.form.validate"
                    ), $this, $feh);

                if (!$feh->has()) {
                    $this->context->db()->beginTransaction();
                    try {
                        $this->submit($event);
                        $this->context->handler()->trigger(array(
                                "kernel.form.submit#{$id}",
                                "kernel.form.submit"
                            ), $this, $event);

                        $this->context->db()->commit();
                    } catch (\Exception $e) {
                        $this->context->db()->rollBack();
                        throw $e;
                    }
                }
            }

            if ($feh->has()) {
                $this->invalid($event, $feh);
                $this->context->handler()->trigger(array(
                        "kernel.form.invalid#{$id}",
                        "kernel.form.invalid"
                    ), $this, $feh, $event);

                $this->_errors = $feh;

                return $this->renderResponse();
            }

            if ($event->hasResponse()) {
                return $event->getResponse();
            }
        } catch (\Exception $e) {
            if ($e instanceof FormErrorException) {
                $this->context->flash()->add('error', $e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    public function runGetRequest()
    {
        $this->initGetRequest();
        $this->context->handler()->trigger(array(
                "kernel.form.get#{$this->id()}",
                "kernel.form.get"
            ), $this);

        return $this->renderResponse();
    }

    protected function autoValidate(Context $context, FormErrorHandler $errorHandler)
    {
        $disabledFields = $this->disabledFields();

        foreach ($this->validators() as $name => $validators) {
            if (in_array($name, $disabledFields)) {
                continue;
            }
            foreach ($validators as $validator => $arguments) {
                if (is_array($arguments)) {
                    $message = array_shift($arguments);
                } else {
                    $message = $arguments;
                    $arguments = array();
                }
                if (!$context->handler()->triggerOne(array(
                        "kernel.validate.form#{$validator}",
                        "kernel.validate.form"
                    ), $this, $name, $arguments, $validator)) {
                    $errorHandler->add($name, $message);
                    break;
                }
            }
        }
    }

    protected function initGetRequest()
    {
    }

    protected function initPostRequest()
    {
    }

    /**
     * @return array
     */
    protected function disabledFields()
    {
        return array();
    }

    /**
     * @return array
     */
    protected function fields()
    {
        return array();
    }

    /**
     * @return array
     */
    protected function registerValidators()
    {
        return array();
    }

    /**
     * @param Form\FormErrorHandler $feh
     */
    protected function validate(FormErrorHandler $feh)
    {
    }

    /**
     * @param GetResponseEvent $event
     * @param Form\FormErrorHandler $feh
     */
    protected function invalid(GetResponseEvent $event, FormErrorHandler $feh)
    {
    }

    /**
     * @param GetResponseEvent $event
     */
    protected function submit(GetResponseEvent $event)
    {
    }

    /**
     * @param RenderableResponse $response
     */
    protected function render(RenderableResponse $response)
    {
    }
}