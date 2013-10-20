<?php

namespace Youngx\MVC\Html;

use Youngx\MVC\Context;
use Youngx\MVC\Html;

class MessageHtml extends Html
{
    protected $type = 'success';

    public function __construct(Context $context, array $attributes = array())
    {
        parent::__construct($context, 'div', $attributes, 'message');
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }
}