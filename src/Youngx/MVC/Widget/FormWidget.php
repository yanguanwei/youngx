<?php

namespace Youngx\MVC\Widget;

use Youngx\MVC\Form;
use Youngx\MVC\Html;
use Youngx\MVC\Widget;
use Youngx\MVC\Action;
use Youngx\MVC\Html\FormHtml;
use Youngx\MVC\Widget\FieldWidget;
use Youngx\MVC\Widget\FieldsetWidget;
use Youngx\MVC\Widget\ButtonGroupWidget;
use Youngx\Util\SortableArray;

class FormWidget extends Widget
{
    /**
     * @var Html
     */
    protected $cancelHtml;

    /**
     * @var Action
     */
    protected $action;

    /**
     * @var Html
     */
    protected $formHtml;
    /**
     * @var Html
     */
    protected $actionsWrapHtml;
    /**
     * @var Html
     */
    protected $submitHtml;

    protected $submit = array('#content' => '保存');

    protected $uploadable = false;
    /**
     * @var SortableArray
     */
    protected $sortableFields;
    /**
     * @var FieldWidget[]
     */
    protected $fieldWidgets = array();
    /**
     * @var FieldsetWidget[]
     */
    protected $fieldsetWidgets = array();

    /**
     * @var ButtonGroupWidget
     */
    protected $buttonGroupWidget;

    public function name()
    {
        return 'form';
    }

    public function setAction(Action $action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return Action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return SortableArray
     */
    protected function getSortableFields()
    {
        if (null === $this->sortableFields) {
            $this->sortableFields = new SortableArray();
        }
        return $this->sortableFields;
    }

    public function setSubmit($submit)
    {
        $this->submit = array_merge($this->submit, is_array($submit) ? $submit : array('#content' => $submit));
    }

    protected function format($content)
    {
        if ($this->action) {
            if (method_exists($this->action, 'renderFormWidget')) {
                $this->action->renderFormWidget($this);
            }
        }

        if ($this->sortableFields) {
            $content .= "\n" . implode("\n", $this->sortableFields->all());
        }

        $actionsWrapHtml = $this->getActionsWrapHtml();
        if (!$actionsWrapHtml->isFormatted() && $actionsWrapHtml->visible()) {
            $content .= "\n" . $actionsWrapHtml;
        }

        return $this->getFormHtml()->setContent($content);
    }

    /**
     * @return Html
     */
    public function getFormHtml()
    {
        if (null === $this->formHtml) {
            $this->formHtml = $this->initFormHtml();
        }
        return $this->formHtml;
    }

    /**
     * @return FormHtml
     */
    protected function initFormHtml()
    {
        return $this->context->html('form', array(
                '#uploadable' => $this->uploadable
            ));
    }

    /**
     * @return Html
     */
    public function getActionsWrapHtml()
    {
        if (null === $this->actionsWrapHtml) {
            $this->actionsWrapHtml = $this->context->html('div')->setContent($this->getButtonGroupWidget());
        }
        return $this->actionsWrapHtml;
    }

    /**
     * @return Html
     */
    public function getSubmitHtml()
    {
        if (null === $this->submitHtml) {
            $this->submitHtml = $this->context->html('submit', $this->submit);
        }
        return $this->submitHtml;
    }

    /**
     * @param $name
     * @param $type
     * @param array $attributes
     * @return \Youngx\MVC\Input\InputInterface
     */
    public function input($name, $type, array $attributes = array())
    {
        $input = $this->context->input($type, $attributes);
        $input->setName($name);
        if ($this->action) {
            $input->setValue($this->action->get($name));
        }
        return $input;
    }

    /**
     * @param $name
     * @param array $config
     * @return FieldWidget
     */
    public function field($name, array $config = array())
    {
        $default = array(
            '#name' => $name,
            '#formHtml' => $this->getFormHtml(),
            '#formWidget' => $this
        );

        if ($this->action) {
            $default['#action'] = $this->action;
        }

        return $this->fieldWidgets[$name] = $this->context->widget('Field', array_merge($default, $config));
    }

    /**
     * @return FieldWidget[]
     */
    public function getFieldWidgets()
    {
        return $this->fieldWidgets;
    }

    public function submit($submitLabel = null)
    {
        if ($submitLabel !== null) {
            $this->getActionsWrapHtml()->set('value', $submitLabel);
        }
        return $this->getActionsWrapHtml();
    }

    public function add($name, $field, $sort = 0)
    {
        $this->getSortableFields()->set($name, $field, $sort);

        return $this;
    }

    /**
     * @param $name
     * @param array $attributes
     * @param int $sort
     * @return FieldWidget
     */
    public function addField($name, array $attributes = array(), $sort = 0)
    {
        $this->add($name, $field = $this->field($name, $attributes), $sort);

        return $field;
    }

    /**
     * @param $name
     * @return FieldsetWidget
     */
    public function getFieldset($name)
    {
        if (!isset($this->fieldsetWidgets[$name])) {
            $attributes = array();
            if ($this->action) {
                $attributes['#action'] = $this->action;
            }
            if ($this->formHtml) {
                $attributes['#formHtml'] = $this->formHtml;
            }
            $this->fieldsetWidgets[$name] = $fieldset = $this->context->widget('Fieldset', $attributes);
            $this->add($name, $fieldset);
        }
        return $this->fieldsetWidgets[$name];
    }

    /**
     * @return FieldsetWidget[]
     */
    public function getFieldsetWidgets()
    {
        return $this->fieldsetWidgets;
    }

    public function sortField($name, $sort)
    {
        $this->getSortableFields()->sort($name, $sort);

        return $this;
    }

    public function setUploadable($uploadable)
    {
        $this->uploadable = $uploadable;
    }

    /**
     * @return ButtonGroupWidget
     */
    public function getButtonGroupWidget()
    {
        if (null === $this->buttonGroupWidget) {
            $this->buttonGroupWidget = $this->initButtonGroupWidget();
        }
        return $this->buttonGroupWidget;
    }

    /**
     * @return ButtonGroupWidget
     */
    protected function initButtonGroupWidget()
    {
        $buttonGroupWidget = $this->context->widget('ButtonGroup');
        $buttonGroupWidget->add('submit', $this->getSubmitHtml());
        return $buttonGroupWidget;
    }

    public function setForm(Form $form)
    {
        $this->setAction($form);

        return $this;
    }
}