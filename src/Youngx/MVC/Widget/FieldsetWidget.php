<?php

namespace Youngx\MVC\Widget;

use Youngx\MVC\Action;
use Youngx\MVC\Form;
use Youngx\MVC\Html;
use Youngx\MVC\Html\FormHtml;
use Youngx\MVC\Widget;
use Youngx\Util\SortableArray;

class FieldsetWidget extends Widget
{
    /**
     * @var Html
     */
    protected $fieldsetHtml;
    /**
     * @var Html
     */
    protected $legendHtml;

    protected $formHtml;

    protected $action;
    /**
     * @var SortableArray
     */
    protected $sortableFields;

    public function name()
    {
        return 'fieldset';
    }

    protected function format($content)
    {
        $content = $this->getLegendHtml() . $content;

        if ($this->sortableFields) {
            $content .= implode("\n", $this->sortableFields->all());
        }

        return $this->getFieldsetHtml()->setContent($content);
    }

    protected function setLabel($label)
    {
        $this->label($label);
    }

    public function label($label)
    {
        $this->getLegendHtml()->setContent($label);

        return $this;
    }

    /**
     * @param $name
     * @param array $config
     * @return FieldWidget
     */
    public function field($name, array $config = array())
    {
        $defaults = array(
            '#name' => $name,
        );

        if ($this->formHtml) {
            $defaults['#formHtml'] = $this->formHtml;
        }

        if ($this->action) {
            $defaults['#action'] = $this->action;
        }

        return $this->context->widget('Field', array_merge($config, $defaults));
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
     * @return SortableArray
     */
    protected function getSortableFields()
    {
        if (null === $this->sortableFields) {
            $this->sortableFields = new SortableArray();
        }
        return $this->sortableFields;
    }

    /**
     * @param Action $form
     */
    public function setAction(Action $form)
    {
        $this->action = $form;
    }

    /**
     * @return Action
     */
    public function getAction()
    {
        return $this->action;
    }

    public function setFormHtml(FormHtml $formHtml)
    {
        $this->formHtml = $formHtml;

        return $this;
    }

    /**
     * @return FormHtml
     */
    public function getFormHtml()
    {
        return $this->formHtml;
    }

    /**
     * @return Html
     */
    public function getFieldsetHtml()
    {
        if (null === $this->fieldsetHtml) {
            $this->fieldsetHtml = $this->context->html('fieldset');
        }
        return $this->fieldsetHtml;
    }

    /**
     * @return Html
     */
    public function getLegendHtml()
    {
        if (null === $this->legendHtml) {
            $this->legendHtml = $this->context->html('legend');
        }
        return $this->legendHtml;
    }
}