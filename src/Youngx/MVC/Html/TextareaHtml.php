<?php

namespace Youngx\MVC\Html;

use Youngx\MVC\Context;
use Youngx\MVC\Html;

class TextareaHtml extends Html
{
    public function __construct(Context $context, array $attributes = array())
    {
        parent::__construct($context, 'textarea', $attributes);
    }

    public function setValue($value)
    {
        parent::setContent($value);

        return $this;
    }

    public function getValue()
    {
        return $this->getContent();
    }
}