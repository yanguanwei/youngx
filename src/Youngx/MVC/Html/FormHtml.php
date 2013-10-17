<?php

namespace Youngx\MVC\Html;

use Youngx\MVC\Context;
use Youngx\MVC\Html;

class FormHtml extends Html
{
    protected $uploadable = false;

    public function __construct(Context $context, array $attributes)
    {
        $attributes = array_merge(array(
            'method' => 'post',
            'action' => $context->request()->getUri()
        ), $attributes);

        parent::__construct($context, 'form', $attributes);
    }

    protected function format()
    {
        if ($this->uploadable) {
            $this->set('enctype', 'multipart/form-data');
        }
    }

    public function setUploadable($uploadable)
    {
        $this->uploadable = (Boolean) $uploadable;

        return $this;
    }
}