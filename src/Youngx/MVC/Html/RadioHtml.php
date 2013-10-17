<?php

namespace Youngx\MVC\Html;

use Youngx\MVC\Context;
use Youngx\MVC\Html;

class RadioHtml extends Html
{
    private $label;
    private $options;

    public function __construct(Context $context, array $attributes = array())
    {
        parent::__construct($context, 'input', $attributes, 'radio', true);
        $this->set('type', 'radio');
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
        $value = $this->getValue();
        $this->set('checked', $this->has('value') && $value == $this->get('value'));

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
                $radio = clone $this;
                $radio->options = null;
                $radio->label = $value;
                $radio->set('value', $key);
                $radio->formatted(false);
                $s .= $radio;
            }
            return $s;
        } else {
            return parent::toString();
        }
    }
}