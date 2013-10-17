<?php

namespace Youngx\MVC\Widget;

use Youngx\MVC\Action;
use Youngx\MVC\Html;
use Youngx\MVC\Form;
use Youngx\MVC\Html\FormHtml;
use Youngx\MVC\Input\InputInterface;
use Youngx\MVC\Widget;

class FieldWidget extends Widget
{
    protected $name;
    /**
     * @var FormHtml
     */
    protected $formHtml;

    /**
     * @var Action
     */
    protected $action;

    /**
     * @var Html
     */
    protected $labelHtml;
    /**
     * @var Html
     */
    protected $helpHtml;
    /**
     * @var InputInterface[]
     */
    protected $inputs;
    /**
     * @var Html
     */
    protected $wrapHtml;

    /**
     * @var Html
     */
    protected $inputWrapHtml;

    /**
     * @var Html
     */
    protected $errorHtml;

    /**
     * @var FormWidget
     */
    protected $formWidget;

    public function __call($type, array $arguments)
    {
        $attributes = isset($arguments[0]) ? $arguments[0] : array();

        $this->input($this->name, $type, $attributes);

        return $this;
    }

    public function name()
    {
        return 'field';
    }

    protected function setName($name)
    {
        $this->name = $name;
    }


    public function getName()
    {
        return $this->name;
    }

    protected function init()
    {
        if (!$this->name) {
            throw new \Exception('FieldWidget should be specified the name');
        }
    }

    public function setFormHtml(FormHtml $formHtml)
    {
        $this->formHtml = $formHtml;

        return $this;
    }

    /**
     * @return FormHtml | null
     */
    public function getFormHtml()
    {
        return $this->formHtml;
    }

    public function setAction(Action $action)
    {
        $this->action = $action;

        if ($action instanceof Form) {
            if ($action->hasError($this->name)) {
                $this->error($action->error($this->name));
            }
        }

        return $this;
    }

    /**
     * @return Form
     */
    public function getAction()
    {
        return $this->action;
    }

    protected function format($content)
    {
        if ($this->action && method_exists($this->action, 'renderFieldWidget')) {
            $this->action->renderFieldWidget($this->name, $this);
        }

        if ($this->labelHtml) {
            $content .= $this->labelHtml;
        }

        if ($this->helpHtml) {
            $this->getInputWrapHtml()->append($this->helpHtml);
        }

        $content .= $this->getInputWrapHtml();

        if ($this->errorHtml) {
            $content .= $this->errorHtml;
        }

        return $this->getWrapHtml()->setContent($content);
    }

    /**
     * @return Html
     */
    public function getWrapHtml()
    {
        if (null === $this->wrapHtml) {
            $this->wrapHtml = $this->context->html('div');
        }
        return $this->wrapHtml;
    }

    public function label($label)
    {
        if (null === $this->labelHtml) {
            $this->labelHtml = $this->context->html('label', array(
                    'name' => $this->name
                ));
        }

        if (is_array($label)) {
            $this->labelHtml->set($label);
        } else {
            $this->labelHtml->setContent($label);
        }

        return $this;
    }

    protected function setLabel($label)
    {
        $this->label($label);

        return $this;
    }

    /**
     * @return Html|null
     */
    public function getLabelHtml()
    {
        return $this->labelHtml;
    }

    public function help($help)
    {
        if (null === $this->helpHtml) {
            $this->helpHtml = $this->context->html('div');
        }

        if (is_array($help)) {
            $this->helpHtml->set($help);
        } else {
            $this->helpHtml->setContent($help);
        }

        return $this;
    }

    protected function setHelp($help)
    {
        $this->help($help);

        return $this;
    }

    /**
     * @return Html|null
     */
    public function getHelpHtml()
    {
        return $this->helpHtml;
    }

    public function input($name, $input, array $attributes = array())
    {
        if (is_string($input)) {
            $attributes['#name'] = $name;
            if (!isset($attributes['id'])) {
                $attributes['id'] = $name;
            }
            $input = $this->context->input($input, $attributes);
        }

        if ($input instanceof InputInterface) {
            ;
            $this->getInputWrapHtml()->append(
                $this->inputs[$name] = $input,
                $name
            );
            if ($this->action) {
                $input->setValue($this->action->get($name));
            }
        }

        return $this;
    }

    /**
     * @return InputInterface[]
     */
    public function getInputs()
    {
        return $this->inputs;
    }

    /**
     * @param $name
     * @return InputInterface | null
     */
    public function getInput($name = null)
    {
        $name = $name ?: $this->name;
        if (!$name) {
            return null;
        }

        return isset($this->inputs[$name]) ? $this->inputs[$name] : null;
    }

    /**
     * @return Html
     */
    public function getInputWrapHtml()
    {
        if (null === $this->inputWrapHtml) {
            $this->inputWrapHtml = $this->context->html('div');
        }
        return $this->inputWrapHtml;
    }

    public function error($error)
    {
        if (null === $this->errorHtml) {
            $this->errorHtml = $this->context->html('span');
        }

        if (is_array($error)) {
            $this->errorHtml->set($error);
        } else {
            $this->errorHtml->setContent($error);
        }

        return $this;
    }

    protected function setError($error)
    {
        $this->error($error);
    }

    /**
     * @return Html | null
     */
    public function getErrorHtml()
    {
        return $this->errorHtml;
    }

    /**
     * @param FormWidget $formWidget
     */
    public function setFormWidget(FormWidget $formWidget)
    {
        $this->formWidget = $formWidget;
    }

    /**
     * @return FormWidget | null
     */
    public function getFormWidget()
    {
        return $this->formWidget;
    }
}