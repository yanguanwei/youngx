<?php

namespace Youngx\MVC\Html;

use Youngx\MVC\Context;
use Youngx\MVC\Html;

class CheckboxHtml extends Html
{
    private $label;
    private $options;

    public function __construct(Context $context, array $attributes = array())
    {
        parent::__construct($context, 'input', $attributes, 'checkbox', true);
        $this->set('type', 'checkbox');
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    protected function format()
    {
        $this->set('checked', $this->has('value') && in_array($this->get('value'), (array) $this->getValue()));

        if ($this->label) {
            $this->wrap(
                $this->context->html('label'), 'wrap'
            );
            $this->after(
                $this->context->html('span', array('#content' => $this->label)), 'label'
            );
        }
    }

    protected function toString()
    {
        if ($this->options) {
            $s = '';
            foreach ($this->options as $key => $value) {
                $checkbox = clone $this;
                $checkbox->options = null;
                $checkbox->label = $value;
                $checkbox->set('value', $key);
                $s .= $checkbox;
            }
            return $s;
        } else {
            return parent::toString();
        }
    }
}