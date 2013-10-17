<?php

namespace Youngx\MVC\Html;

use Youngx\MVC\Context;
use Youngx\MVC\Html;

class MessageHtml extends Html
{
    protected $type;

    public function __construct(Context $context, array $attributes = array())
    {
        parent::__construct($context, 'div', $attributes, 'message');
    }

    public function getType()
    {
        return $this->getOption('type', 'success');
    }
}