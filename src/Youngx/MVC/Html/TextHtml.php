<?php

namespace Youngx\MVC\Html;

use Youngx\MVC\Context;
use Youngx\MVC\Html;

class TextHtml extends Html
{
    public function __construct(Context $context, array $attributes = array(), $formatter = 'text')
    {
        parent::__construct($context, 'input', $attributes, $formatter, true);
        if (!$this->has('type')) {
            $this->set('type', 'text');
        }
    }

    public function setValue($value)
    {
        $this->set('value', $value);
        parent::setValue($value);

        return $this;
    }
}