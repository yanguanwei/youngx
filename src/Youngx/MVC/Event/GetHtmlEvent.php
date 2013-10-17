<?php

namespace Youngx\MVC\Event;

use Youngx\MVC\Html;

class GetHtmlEvent
{
    private $html;

    /**
     * @return Html
     */
    public function getHtml()
    {
        return $this->html;
    }

    public function hasHtml()
    {
        return null !== $this->html;
    }

    public function setHtml(Html $html)
    {
        $this->html = $html;

        return $this;
    }
}